<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\records;

use panlatent\craft\dingtalk\db\Table;
use yii\db\ActiveRecord;

/**
 * Class ContactLabelGroup
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property int $corporationId
 * @property string $name
 * @property string $color
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactLabelGroup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CONTACTLABELGROUPS;
    }
}