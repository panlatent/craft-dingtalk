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
 * Class Department
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read Department $parent
 * @property-read Department[] $parents
 * @author Panlatent <panlatent@gmail.com>
 */
class Department extends Model
{
    /**
     * @var int|null 部门唯一据柄（id）
     */
    public $id;

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
     * @var Department|null
     */
    private $_parent;

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

        return $this->_parent = Plugin::$plugin->getDepartments()->getDepartmentById($this->parentId);
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

        return $parents;
    }
}