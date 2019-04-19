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

/**
 * Class ContactLabelGroup
 *
 * @package panlatent\craft\dingtalk\models
 * @property ContactLabel[] $label
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactLabelGroup extends Model
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
    public $color;

    /**
     * @var ContactLabel[]|null
     */
    private $_labels;

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
        $rules[] = [['corporationId', 'name'], 'required'];
        $rules[] = [['corporationId'], 'integer'];
        $rules[] = [['name', 'color'], 'string'];

        return $rules;
    }

    /**
     * @return ContactLabel[]
     */
    public function getLabels(): array
    {
        if ($this->_labels !== null) {
            return $this->_labels;
        }

        if ($this->id === null) {
            return [];
        }

        $this->_labels = Plugin::getInstance()
            ->getContacts()
            ->getLabelsByGroupId($this->id);

        return $this->_labels;
    }
}