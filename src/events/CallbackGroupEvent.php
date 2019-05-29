<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use panlatent\craft\dingtalk\models\CallbackGroup;
use yii\base\Event;

/**
 * Class CallbackGroupEvent
 *
 * @package panlatent\craft\dingtalk\events
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackGroupEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var CallbackGroup|null
     */
    public $group;

    /**
     * @var bool
     */
    public $isNew = false;
}