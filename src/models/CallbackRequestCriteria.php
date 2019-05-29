<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use yii\base\Model;

/**
 * Class CallbackRequestCriteria
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackRequestCriteria extends Model
{
    // Properties
    // =========================================================================

    public $id;

    public $corporationId;

    public $callbackId;

    public $postDate;

    public $handledDate;

    public $order;

    public $offset;

    public $limit;
}