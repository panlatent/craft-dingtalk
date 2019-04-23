<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use panlatent\craft\dingtalk\elements\Contact;
use yii\base\Event;

/**
 * Class ContactEvent
 *
 * @package panlatent\craft\dingtalk\events
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactEvent extends Event
{
    /**
     * @var Contact|null
     */
    public $contact;

    /**
     * @var bool
     */
    public $isNew = false;
}