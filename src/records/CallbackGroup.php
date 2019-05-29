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
 * Class CallbackGroup
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property string $name
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackGroup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CALLBACKGROUPS;
    }
}