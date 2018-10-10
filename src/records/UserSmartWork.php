<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\records;

use craft\db\ActiveRecord;

/**
 * Class UserSmartWork
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property string userId
 * @property string settings
 * @author Panlatent <panlatent@gmail.com>
 */
class UserSmartWork extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%dingtalk_usersmartworks}}';
    }
}