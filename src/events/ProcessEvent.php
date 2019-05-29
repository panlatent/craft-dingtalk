<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use panlatent\craft\dingtalk\base\ProcessInterface;
use yii\base\Event;

/**
 * Class ProcessEvent
 *
 * @package panlatent\craft\dingtalk\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ProcessEvent extends Event
{
    /**
     * @var ProcessInterface|null
     */
    public $process;

    /**
     * @var bool
     */
    public $isNew = false;
}