<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class Process
 *
 * @package panlatent\craft\dingtalk\base
 * @property-read Corporation $corporation
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Process extends SavableComponent implements ProcessInterface
{
    // Traits
    // =========================================================================

    use ProcessTrait;

    // Properties
    // =========================================================================

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
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        return $rules;
    }

    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @inheritdoc
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        if (!$this->corporationId) {
            throw new InvalidConfigException();
        }

        $this->_corporation = Plugin::$dingtalk->getCorporations()->getCorporationById($this->corporationId);
        if ($this->_corporation === null) {
            throw new InvalidConfigException();
        }

        return $this->_corporation;
    }

    public function getApprovals()
    {

    }
}