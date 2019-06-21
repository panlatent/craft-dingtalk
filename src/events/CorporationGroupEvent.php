<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use panlatent\craft\dingtalk\models\CorporationGroup;
use yii\base\Event;

/**
 * Class CorporationGroupEvent
 *
 * @package panlatent\craft\dingtalk\events
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationGroupEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var CorporationGroup|null
     */
    public $group;

    /**
     * @var bool
     */
    public $isNew = false;
}