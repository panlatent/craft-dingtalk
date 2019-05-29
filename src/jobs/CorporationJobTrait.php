<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\jobs;

use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * Trait CorporationJobTrait
 *
 * @package panlatent\craft\dingtalk\jobs
 * @property-read Corporation $corporation
 * @author Panlatent <panlatent@gmail.com>
 */
trait CorporationJobTrait
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
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        if (!$this->corporationId) {
            throw new InvalidConfigException("Missing corporation ID");
        }

        $corporation = Plugin::$dingtalk
            ->getCorporations()
            ->getCorporationById($this->corporationId);

        if ($corporation === null) {
            throw new InvalidConfigException("Missing corporation with the ID: {$this->corporationId}");
        }

        return $this->_corporation = $corporation;
    }
}