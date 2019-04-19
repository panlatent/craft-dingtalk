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
 * Class ContactLabel
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $groupId
 * @property string $name
 * @property int $sourceId
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactLabel extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dingtalk_contactlabels}}';
    }
}