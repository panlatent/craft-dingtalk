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
 * Class Process
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property string $name
 * @property string $handle
 * @property string $code
 * @property string $type
 * @property string $settings
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class Process extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%dingtalk_processes}}';
    }
}