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
 * Class Callback
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $groupId
 * @property string $name
 * @property string $handle
 * @property string $code
 * @author Panlatent <panlatent@gmail.com>
 */
class Callback extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CALLBACKS;
    }
}