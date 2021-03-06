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
 * @property string $corporationId
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
 * @property bool $isActive
 * @property string $mobile
 * @property string $stateCode
 * @property string $orgEmail
 * @property \DateTime $hiredDate
 * @property \DateTime $leavedDate
 * @property string $remark
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class User extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%dingtalk_users}}';
    }
}