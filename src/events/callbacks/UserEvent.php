<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events\callbacks;

use panlatent\craft\dingtalk\events\CallbackEvent;

/**
 * 通讯录事件回调
 *
 * @package panlatent\craft\dingtalk\events\callbacks
 * @author Panlatent <panlatent@gmail.com>
 */
class UserEvent extends CallbackEvent
{
    /**
     * @var array|null
     */
    public $userIds;

    /**
     * @var bool
     */
    public $isNew = false;
}