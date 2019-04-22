<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\db\ContactQuery;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * Class ContactLabel
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read ContactLabelGroup $group
 * @property-read ContactQuery $contacts
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactLabel extends Model
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
    public $groupId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var int|null
     */
    public $sourceId;

    /**
     * @var ContactLabelGroup|null
     */
    private $_group;

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
        $rules[] = [['groupId', 'name', 'sourceId'], 'required'];
        $rules[] = [['groupId', 'sourceId'], 'integer'];
        $rules[] = [['name'], 'string'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        $fields['groupName'] = function() {
            return $this->getGroup()->name;
        };

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'group';

        return  $fields;
    }

    /**
     * @return ContactLabelGroup
     */
    public function getGroup(): ContactLabelGroup
    {
        if ($this->_group !== null) {
            return $this->_group;
        }

        if ($this->groupId === null) {
            throw new InvalidConfigException();
        }

        $this->_group = Plugin::getInstance()->getContacts()->getLabelGroupById($this->groupId);
        if ($this->_group === null) {
            throw new InvalidConfigException();
        }

        return $this->_group;
    }

    /**
     * @return ContactQuery
     */
    public function getContacts(): ContactQuery
    {
        return Contact::find()
            ->labelOf($this->id);
    }
}