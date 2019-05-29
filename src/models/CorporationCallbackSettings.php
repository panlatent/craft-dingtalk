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
use panlatent\craft\dingtalk\Plugin;

/**
 * Class CorporationCallback
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Callback[] $callbacks
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationCallbackSettings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $url;

    /**
     * @var string|null
     */
    public $token;

    /**
     * @var string|null
     */
    public $aesKey;

    /**
     * @var bool
     */
    public $enabled = true;

    /**
     * @var int[]
     */
    public $callbackIds = [];

    /**
     * @var Callback[]|null
     */
    private $_callbacks;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['url', 'token', 'aesKey'], 'required'];
        $rules[] = [['url', 'token', 'aesKey'], 'string'];
        $rules[] = [['enabled'], 'boolean'];

        return $rules;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return Craft::parseEnv($this->url);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return Craft::parseEnv($this->token);
    }

    /**
     * @return string|null
     */
    public function getAesKey()
    {
        return Craft::parseEnv($this->aesKey);
    }

    /**
     * @return Callback[]
     */
    public function getCallbacks(): array
    {
        if ($this->_callbacks !== null) {
            return $this->_callbacks;
        }

        $this->_callbacks = [];
        foreach ($this->callbackIds as $callbackId) {
            $this->_callbacks[] = Plugin::$dingtalk->getCallbacks()->getCallbackById($callbackId);
        }

        return $this->_callbacks;
    }
}