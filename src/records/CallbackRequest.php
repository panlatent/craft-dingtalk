<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\records;

use craft\db\ActiveRecord;
use panlatent\craft\dingtalk\db\Table;

/**
 * Class CallbackRequest
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $callbackId
 * @property int $corporationId
 * @property array $data
 * @property \DateTime $postDate
 * @property int $attempts
 * @property bool $handled
 * @property \DateTime $handledDate
 * @property string $handleFailedReason
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackRequest extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CALLBACKREQUESTS;
    }
}