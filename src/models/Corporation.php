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
use craft\validators\HandleValidator;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\db\ApprovalQuery;
use panlatent\craft\dingtalk\elements\db\UserQuery;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\supports\Remote;
use Throwable;

/**
 * Class Corporation
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read bool $isNew
 * @property string $callbackToken
 * @property string $callbackAesKey
 * @property-read bool $isRegisteredCallback
 * @property-read Department[] $departments
 * @property-read Department $rootDepartment
 * @property-read Remote $remote
 * @property-read ApprovalQuery $approvals
 * @property-read UserQuery $users
 * @author Panlatent <panlatent@gmail.com>
 */
class Corporation extends Model
{
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
     * @var bool
     */
    public $callbackEnabled = false;

    /**
     * @var Remote
     */
    private $_remote;

    /**
     * @var string|null
     */
    private $_callbackToken;

    /**
     * @var string|null
     */
    private $_callbackAesKey;

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
        $rules[] = [['name', 'corpId', 'corpSecret', 'callbackToken', 'callbackAesKey'], 'string'];
        $rules[] = [['primary', 'hasUrls', 'callbackEnabled'], 'boolean'];
        $rules[] = [['handle'], HandleValidator::class];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['corpId'], $fields['corpSecret'], $fields['callbackEnabled']);

        return $fields;
    }

    /**
     * @return bool
     */
    public function getIsNew(): bool
    {
        return !$this->id;
    }

    /**
     * @return Remote
     */
    public function getRemote(): Remote
    {
        if ($this->_remote !== null) {
            return $this->_remote;
        }

        return $this->_remote = new Remote([
            'corpId' => $this->getCorpId(),
            'corpSecret' => $this->getCorpSecret(),
        ]);
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
     * @return string|null
     */
    public function getCallbackToken()
    {
        return Craft::parseEnv($this->_callbackToken);
    }

    /**
     * @param string|null $callbackToken
     */
    public function setCallbackToken(string $callbackToken = null)
    {
        $this->_callbackToken = $callbackToken;
    }

    /**
     * @return string|null
     */
    public function getCallbackAesKey()
    {
        return Craft::parseEnv($this->_callbackAesKey);
    }

    /**
     * @param string|null $callbackAesKey
     */
    public function setCallbackAesKey(string $callbackAesKey = null)
    {
        $this->_callbackAesKey = $callbackAesKey;
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
     * @return Department[]
     */
    public function getDepartments(): array
    {
        return Plugin::getInstance()
            ->getDepartments()
            ->findDepartments([
                'corporationId' => $this->id,
            ]);
    }

    /**
     * @return Department
     */
    public function getRootDepartment(): Department
    {
        return Plugin::getInstance()
            ->getDepartments()
            ->findDepartment([
                'corporationId' => $this->id,
                'root' => true,
            ]);
    }

    /**
     * @return ApprovalQuery
     */
    public function getApprovals(): ApprovalQuery
    {
        return Approval::find()
            ->corporationId($this->id);
    }

    /**
     * @return UserQuery
     */
    public function getUsers(): UserQuery
    {
        return User::find()
            ->corporationId($this->id);
    }

    /**
     * @return bool
     */
    public function beforeDelete(): bool
    {
        if ($this->getUsers()->exists()) {
            return false;
        }

        return true;
    }

    public function afterDelete()
    {

    }
}