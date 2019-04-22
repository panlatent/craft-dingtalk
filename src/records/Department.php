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
 * Class Department
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $corporationId
 * @property int $dingDepartmentId
 * @property string $name
 * @property int $parentId
 * @property string $settings
 * @property int $sortOrder
 * @property bool $archived
 * @author Panlatent <panlatent@gmail.com>
 */
class Department extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dingtalk_departments}}';
    }
}