<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use panlatent\craft\dingtalk\models\Callback;
use yii\base\Event;

/**
 * Class CallbackEvent
 *
 * @package panlatent\craft\dingtalk\events
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Callback|null
     */
    public $callback;

    /**
     * @var bool
     */
    public $isNew = false;
}