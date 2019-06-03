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
 * Class RobotWebhook
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $robotId
 * @property string $name
 * @property string $url
 * @property int $rateLimit
 * @property int $rateWindow
 * @property int $allowance
 * @property bool $enabled
 * @property \DateTime $dateAllowanceUpdated
 * @author Panlatent <panlatent@gmail.com>
 */
class RobotWebhook extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::ROBOTWEBHOOKS;
    }
}