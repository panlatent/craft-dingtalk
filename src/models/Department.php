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
 * Class Department
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Corporation $corporation
 * @property-read Department $parent
 * @property-read Department[] $parents
 * @property-read string fullName
 * @author Panlatent <panlatent@gmail.com>
 */
class Department extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null 部门唯一据柄（id）
     */
    public $id;

    /**
     * @var int|null 集团ID
     */
    public $corporationId;

    /**
     * @var int|null 钉钉部门 ID
     */
    public $dingDepartmentId;

    /**
     * @var string|null 部门名称
     */
    public $name;

    /**
     * @var int|null 父部门id，根部门为1
     */
    public $parentId;

    /**
     * @var array|null
     */
    public $settings;

    /**
     * @var int|null 在父部门中的次序值
     */
    public $sortOrder;

    /**
     * @var bool|null 部门是否归档（在钉钉中删除）
     */
    public $archived;

    /**
     * @var Corporation|null
     */
    private $_corporation;

    /**
     * @var Department|null
     */
    private $_parent;

    /**
     * @var string|null
     */
    private $_fullName;

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['corporationId', 'dingDepartmentId', 'name'], 'required'];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();

        return $fields;
    }

    /**
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        if (!$this->corporationId) {
            throw new InvalidConfigException("Invalid corporation id");
        }

        $this->_corporation = Plugin::$dingtalk
            ->getCorporations()
            ->getCorporationById($this->corporationId);

        if ($this->_corporation === null) {
            throw new InvalidConfigException("Missing corporation with the ID: {$this->corporationId}");
        }

        return $this->_corporation;
    }

    /**
     * @return null|Department
     */
    public function getParent()
    {
        if ($this->_parent !== null) {
            return $this->_parent;
        }

        if (!$this->parentId) {
            return null;
        }

        return $this->_parent = Plugin::$dingtalk->departments->getDepartmentById($this->parentId);
    }

    /**
     * @return Department[]
     */
    public function getParents(): array
    {
        $parents = [];
        $department = $this;
        while ($parent = $department->getParent()) {
            $parents[] = $parent;
            $department = $parent;
        }

        return array_reverse($parents);
    }

    /**
     * @param string $glue
     * @return string
     */
    public function getFullName(string $glue = '/'): string
    {
        if ($this->_fullName !== null) {
            return $this->_fullName;
        }

        $prefix = [];
        foreach ($this->getParents() as $parent) {
            $prefix[] = $parent->name;
        }
        $prefix[] = $this->name;

        return implode($glue, $prefix);
    }
}