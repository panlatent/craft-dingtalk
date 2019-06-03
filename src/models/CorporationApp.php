<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class CorporationApp
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Corporation $corporation
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationApp extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $id;

    /**
     * @var int|null
     */
    public $corporationId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $agentId;

    /**
     * @var string|null
     */
    public $icon;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $homepage;

    /**
     * @var string|null
     */
    public $pcHomepage;

    /**
     * @var string|null
     */
    public $adminUrl;

    /**
     * @var bool
     */
    public $isSelfBuilt = false;

    /**
     * @var bool
     */
    public $enabled = false;

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
        $rules[] = [['corporationId', 'name', 'agentId', 'icon'], 'required'];
        $rules[] = [['id', 'corporationId'], 'integer'];
        $rules[] = [['name', 'agentId', 'icon', 'description', 'homepage', 'pcHomepage', 'adminUrl'], 'string'];
        $rules[] = [['isSelfBuilt', 'enabled'], 'boolean'];

        return $rules;
    }

    /**
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        if ($this->corporationId === null) {
            throw new InvalidConfigException("Invalid corporation ID");
        }

        $this->_corporation = Plugin::$dingtalk->getCorporations()->getCorporationById($this->corporationId);
        if ($this->_corporation === null) {
            throw new InvalidConfigException("No corporation exists with the ID: {$this->corporationId}");
        }

        return $this->_corporation;
    }
}