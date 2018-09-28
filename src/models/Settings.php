<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

class Settings extends Model
{
    /**
     * @var string|null
     */
    public $corpId;
    /**
     * @var string|null
     */
    public $corpSecret;
    /**
     * @var bool|null
     */
    public $hasDepartmentsCpSection;
}