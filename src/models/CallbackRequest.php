<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;
use DateTime;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class CallbackLog
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Callback $callback
 * @property-read Corporation|null $corporation
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackRequest extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $callbackId;

    /**
     * @var int|null
     */
    public $corporationId;

    /**
     * @var array|null
     */
    public $data;

    /**
     * @var DateTime|null
     */
    public $postDate;

    /**
     * @var int
     */
    public $attempts = 0;

    /**
     * @var bool
     */
    public $handled = false;

    /**
     * @var DateTime|null
     */
    public $handledDate;

    /**
     * @var string|null
     */
    public $handleFailedReason;

    /**
     * @var Callback|null
     */
    private $_callback;

    /**
     * @var Corporation|null
     */
    private $_corporation;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->id;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['callbackId'], 'required'];
        $rules[] = [['id', 'callbackId', 'corporationId'], 'integer'];

        return $rules;
    }

    /**
     * @return Callback
     */
    public function getCallback(): Callback
    {
        if ($this->_callback !== null) {
            return $this->_callback;
        }

        if ($this->callbackId === null) {
            throw new InvalidConfigException("Invalid callback type ID");
        }

        $this->_callback = Plugin::$dingtalk->getCallbacks()->getCallbackById($this->callbackId);
        if ($this->_callback === null) {
            throw new InvalidConfigException("No callback type exists with the ID: {$this->callbackId}");
        }

        return $this->_callback;
    }

    /**
     * @return Corporation|null
     */
    public function getCorporation()
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        if (!$this->corporationId) {
           return null;
        }

        $this->_corporation = Plugin::$dingtalk->getCorporations()->getCorporationById($this->corporationId);

        return $this->_corporation;
    }

}