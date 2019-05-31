<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use Craft;
use craft\base\Model;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\validators\HandleValidator;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\db\ApprovalQuery;
use panlatent\craft\dingtalk\elements\db\ContactQuery;
use panlatent\craft\dingtalk\elements\db\EmployeeQuery;
use panlatent\craft\dingtalk\elements\Employee;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\supports\Remote;
use Throwable;

/**
 * Class Corporation
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read bool $isRegisteredCallback
 * @property-read Department[] $departments
 * @property-read Department $rootDepartment
 * @property-read Remote $remote
 * @property-read ApprovalQuery|null $approvals
 * @property-read ContactQuery|null $contacts
 * @property-read EmployeeQuery|null $employees
 * @property CorporationCallbackSettings $callbackSettings
 * @author Panlatent <panlatent@gmail.com>
 */
class Corporation extends Model
{
    // Traits
    // =========================================================================

    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var bool
     */
    public $primary = false;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $handle;

    /**
     * @var string|null
     */
    public $corpId;

    /**
     * @var string|null
     */
    public $corpSecret;

    /**
     * @var bool
     */
    public $hasUrls = false;

    /**
     * @var string|null
     */
    public $url;

    /**
     * @var int|null
     */
    public $sortOrder;

    /**
     * @var string|null UID
     */
    public $uid;

    /**
     * @var Remote
     */
    private $_remote;

    /**
     * @var Department[]|null
     */
    private $_departments;

    /**
     * @var CorporationCallbackSettings|null
     */
    private $_callbackSettings;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['name', 'handle', 'corpId', 'corpSecret', 'hasUrls'], 'required'];
        $rules[] = [['name', 'corpId', 'corpSecret'], 'string'];
        $rules[] = [['primary', 'hasUrls'], 'boolean'];
        $rules[] = [['handle'], HandleValidator::class];
        $rules[] = [['corpId', 'corpSecret'], function($attribute) {
            if (!$this->getRemote()->validateAuth()) {
                $this->addError($attribute, '不合法的 Corp ID 或 Corp Secret');
            }
        }];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['corpId'], $fields['corpSecret']);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Craft::t('dingtalk', 'Corporation Name'),
            'handle' => Craft::t('app', 'handle'),
            'corpId' => Craft::t('dingtalk', 'Corp ID'),
            'corpSecret' => Craft::t('dingtalk', 'Corp Secret'),
            'primary' => Craft::t('dingtalk', 'Primary'),
        ];
    }

    /**
     * @return string|null
     */
    public function getCorpId()
    {
        return Craft::parseEnv($this->corpId);
    }

    /**
     * @return string|null
     */
    public function getCorpSecret()
    {
        return Craft::parseEnv($this->corpSecret);
    }

    /**
     * @param mixed $criteria
     * @return ApprovalQuery|null
     */
    public function getApprovals($criteria = null): ApprovalQuery
    {
        if (!$this->id) {
            return null;
        }

        $query = Approval::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query->corporationId($this->id);
    }

    /**
     * @param mixed $criteria
     * @return ContactQuery|null
     */
    public function getContacts($criteria = null): ContactQuery
    {
        if (!$this->id) {
            return null;
        }

        $query = Contact::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query->corporationId($this->id);
    }

    /**
     * @param mixed $criteria
     * @return EmployeeQuery|null
     */
    public function getEmployees($criteria = null): EmployeeQuery
    {
        if (!$this->id) {
            return null;
        }

        $query = Employee::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query->corporationId($this->id);
    }

    /**
     * @return Remote
     */
    public function getRemote(): Remote
    {
        if ($this->_remote !== null) {
            return $this->_remote;
        }

        return $this->_remote = new Remote($this);
    }

    /**
     * @return bool
     */
    public function getIsRegisteredCallback(): bool
    {
        try {
            return $this->getRemote()->getCallback() !== null;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getCallbackRegisterStatus(): string
    {
        $ret = $this->getRemote()->getCallback();

        try {
            return $this->getRemote()->getCallback() !== null;
        } catch (Throwable $exception) {
            return false;
        }
    }

    /**
     * @return Department[]
     */
    public function getDepartments(): array
    {
        if ($this->_departments !== null) {
            return $this->_departments;
        }

        $this->_departments = Plugin::$dingtalk
            ->getDepartments()
            ->findDepartments([
                'corporationId' => $this->id,
            ]);

        return $this->_departments;
    }

    /**
     * @return Department|null
     */
    public function getRootDepartment()
    {
        return ArrayHelper::firstWhere($this->getDepartments(), 'parentId', null);
    }

    /**
     * @return ProcessInterface[]
     */
    public function getProcesses(): array
    {
        return ArrayHelper::filterByValue(Plugin::$dingtalk->getProcesses()->getAllProcesses(), 'corporationId', $this->id);
    }

    /**
     * @return CorporationCallbackSettings
     */
    public function getCallbackSettings()
    {
        if ($this->_callbackSettings !== null) {
            return $this->_callbackSettings;
        }

        $this->setCallbackSettings();

        return $this->_callbackSettings;
    }

    /**
     * @param mixed $settings
     */
    public function setCallbackSettings($settings = [])
    {
        if (is_array($settings)) {
            $settings = new CorporationCallbackSettings($settings);
        }

        if (!$this->id) {
            if ($settings->token === null) {
                $settings->token = StringHelper::randomString(16);
            }
            if ($settings->aesKey === null) {
                $settings->aesKey = StringHelper::randomString(43);
            }
        }

        $this->_callbackSettings = $settings;
    }
}