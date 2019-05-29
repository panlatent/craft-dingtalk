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
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\CallbackGroup as CallbackGroupRecord;

/**
 * Class CallbackEventGroup
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Callback[] $callbacks
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackGroup extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null UID
     */
    public $uid;

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
        $rules[] = [['name'], 'required'];
        $rules[] = [['name'], 'string'];
        $rules[] = [['name'], 'unique', 'targetClass' => CallbackGroupRecord::class];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Craft::t('app', 'Name'),
        ];
    }

    /**
     * @return Callback[]
     */
    public function getCallbacks(): array
    {
        return $this->id ? Plugin::$dingtalk
            ->getCallbacks()
            ->getCallbacksByGroupId($this->id) : [];
    }
}