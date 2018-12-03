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
 * Class UserDepartment
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $userId
 * @property int $departmentId
 * @property bool $primary
 * @author Panlatent <panlatent@gmail.com>
 */
class UserDepartment extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%dingtalk_userdepartments}}';
    }
}