<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use Craft;
use craft\base\Model;
use craft\helpers\UrlHelper;

/**
 * Class Settings
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $cpSectionName;

    /**
     * @var string|null
     */
    public $callbackRule;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['cpSectionName', 'callbackRule'], 'string'];

        return $rules;
    }

    /**
     * @return string|null
     */
    public function getCallbackRule()
    {
        return Craft::parseEnv($this->callbackRule);
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return UrlHelper::siteUrl(Craft::parseEnv($this->callbackRule));
    }
}