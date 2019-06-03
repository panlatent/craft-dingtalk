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
 * Class Robot
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property string $handle
 * @property string $name
 * @property string $type
 * @property string $settings
 * @author Panlatent <panlatent@gmail.com>
 */
class Robot extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return Table::ROBOTS;
    }
}