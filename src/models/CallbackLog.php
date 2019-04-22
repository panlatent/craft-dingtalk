<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;
use DateTime;

/**
 * Class CallbackLog
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackLog extends Model
{
    /**
     * @var int|null
     */
    public $corporationId;

    /**
     * @var string|null
     */
    public $eventType;

    /**
     * @var array|null
     */
    public $eventData;

    /**
     * @var DateTime|null
     */
    public $postDate;

}