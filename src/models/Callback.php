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
use craft\validators\UniqueValidator;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\Callback as CallbackRecord;
use panlatent\craft\dingtalk\records\CallbackGroup as CallbackGroupRecord;
use yii\base\InvalidConfigException;

/**
 * Class Callback
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read CallbackGroup $group
 * @author Panlatent <panlatent@gmail.com>
 */
class Callback extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $groupId;

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
    public $code;

    /**
     * @var string|null
     */
    public $uid;

    /**
     * @var CallbackGroup
     */
    private $_group;

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
        $rules[] = [['groupId', 'name', 'handle', 'code'], 'required'];
        $rules[] = [['id', 'groupId'], 'integer'];
        $rules[] = [['name', 'handle', 'code'], 'string'];
        $rules[] = [['handle'], HandleValidator::class];
        $rules[] = [['groupId'], 'exist', 'targetClass' => CallbackGroupRecord::class, 'targetAttribute' => 'id'];
        $rules[] = [['code'], UniqueValidator::class, 'targetClass' => CallbackRecord::class, 'targetAttribute' => 'code'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'groupId' => Craft::t('dingtalk', 'Group ID'),
            'name' => Craft::t('app', 'Name'),
            'handle' => Craft::t('app', 'handle'),
            'code' => Craft::t('dingtalk', 'Code'),
            'group' => Craft::t('dingtalk', 'Group'),
        ];
    }

    /**
     * @return CallbackGroup
     */
    public function getGroup(): CallbackGroup
    {
        if ($this->_group !== null) {
            return $this->_group;
        }

        if ($this->groupId === null) {
            throw new InvalidConfigException('Invalid group ID');
        }

        $this->_group = Plugin::$dingtalk->getCallbacks()->getGroupById($this->groupId);
        if (!$this->_group) {
            throw new InvalidConfigException("No callback group exists with the ID: {$this->groupId}");
        }

        return $this->_group;
    }
}