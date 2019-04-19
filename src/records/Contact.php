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
 * Class Contact
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $corporationId
 * @property string $userId
 * @property string $name
 * @property string $mobile
 * @property int $followerId
 * @property string $stateCode
 * @property string $companyName
 * @property string $position
 * @property string $address
 * @property string $remark
 * @author Panlatent <panlatent@gmail.com>
 */
class Contact extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dingtalk_contacts}}';
    }
}