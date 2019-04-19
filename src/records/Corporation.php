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
 * Class Corporation
 *
 * @package panlatent\craft\dingtalk\records
 * @property int $id
 * @property bool $primary
 * @property string $name
 * @property string $handle
 * @property string $corpId
 * @property string $corpSecret
 * @property bool $hasUrls
 * @property string $url
 * @property bool $callbackEnabled
 * @property string $callbackToken
 * @property string $callbackAesKey
 * @property bool $enabled
 * @property bool $archived
 * @property int $sortOrder
 * @author Panlatent <panlatent@gmail.com>
 */
class Corporation extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Table::CORPORATIONS;
    }
}