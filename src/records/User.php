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
 * Class User
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property string $userId
 * @property string $name
 * @property string $position
 * @property string $tel
 * @property bool $isAdmin
 * @property bool $isBoss
 * @property bool $isLeader
 * @property bool $isHide
 * @property bool $isLeaved
 * @property string $avatar
 * @property string $jobNumber
 * @property string $email
 * @property bool $active
 * @property string $mobile
 * @property string $orgEmail
 * @property string $dateHired
 * @property string $dateLeaved
 * @property string $settings
 * @property string $remark
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class User extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%dingtalk_users}}';
    }
}