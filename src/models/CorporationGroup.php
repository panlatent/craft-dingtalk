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
use craft\models\FieldLayout;
use craft\validators\HandleValidator;
use panlatent\craft\dingtalk\Plugin;

/**
 * Class CorporationGroup
 *
 * @package panlatent\craft\dingtalk\models
 * @property FieldLayout|null $fieldLayout
 * @property-read Corporation[] $corporations
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationGroup extends Model
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
    public $name;

    /**
     * @var string|null
     */
    public $handle;

    /**
     * @var int|null
     */
    public $fieldLayoutId;

    /**
     * @var string|null
     */
    public $uid;

    /**
     * @var FieldLayout|null
     */
    private $_fieldLayout;

    /**`
     * @var Corporation[]|null
     */
    private $_corporations;

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
        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name'], 'string', 'max' => 255];
        $rules[] = [['handle'], HandleValidator::class];

        return $rules;
    }

    /**
     * @return FieldLayout|null
     */
    public function getFieldLayout()
    {
        if ($this->_fieldLayout !== null) {
            return $this->_fieldLayout;
        }

        if (!$this->fieldLayoutId) {
            return null;
        }

        return $this->_fieldLayout = Craft::$app->getFields()->getLayoutById($this->fieldLayoutId);
    }

    /**
     * @param FieldLayout $fieldLayout
     */
    public function setFieldLayout(FieldLayout $fieldLayout)
    {
        $this->_fieldLayout = $fieldLayout;
    }

    /**
     * @return Corporation[]
     */
    public function getCorporations(): array
    {
        if ($this->_corporations !== null) {
            return $this->_corporations;
        }

        if (!$this->id) {
            return [];
        }

        $this->_corporations = Plugin::$dingtalk->getCorporations()
            ->getCorporationsByGroupId($this->id);

        return $this->_corporations;
    }
}