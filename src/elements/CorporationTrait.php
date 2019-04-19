<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements;

use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\Plugin;

/**
 * Trait Corporation
 *
 * @package panlatent\craft\dingtalk\elements
 * @property Corporation $corporation
 * @author Panlatent <panlatent@gmail.com>
 */
trait CorporationTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $corporationId;

    /**
     * @var Corporation|null
     */
    private $_corporation;

    // Public Methods
    // =========================================================================

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

        $this->_corporation = Plugin::getInstance()->getCorporations()->getCorporationById($this->corporationId);

        return $this->_corporation;
    }

    /**
     * @param Corporation $corporation
     */
    public function setCorporation(Corporation $corporation)
    {
        $this->_corporation = $corporation;
    }
}