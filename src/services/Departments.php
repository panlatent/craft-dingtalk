<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;

class Departments extends Component
{
    /**
     * @var bool
     */
    private $_fetchedAllDepartments = false;

    /**
     * @var Department[]|null
     */
    private $_departmentsById;

    /**
     * @var Department[]|null
     */
    private $_departmentsByName;

    /**
     * @return Department[]
     */
    public function getAllDepartments(): array
    {
        if ($this->_fetchedAllDepartments) {
            return array_values($this->_departmentsById);
        }

        $this->_departmentsById = [];
        $this->_departmentsByName = [];

        $results = $this->_createApiQuery()->list()['department'];
        foreach ($results as $result) {
            $department = $this->createDepartment($result);
            $this->_departmentsById[$department->id] = $department;
            $this->_departmentsByName[$department->name] = $department;
        }

        $this->_fetchedAllDepartments = true;

        return array_values($this->_departmentsById);
    }

    /**
     * @param int $parentId
     * @return Department[]
     */
    public function getDepartmentsByParentId(int $parentId): array
    {
        $departments = [];

        $results = $this->_createApiQuery()->list($parentId)['department'];
        foreach ($results as $result) {
            $department = $this->createDepartment($result);
            $departments[] = $department;
            $this->_departmentsById[$department->id] = $department;
            $this->_departmentsByName[$department->name] = $department;
        }

        return $departments;
    }

    /**
     * @param int $id
     * @return int[]
     */
    public function getParentDepartmentIdsById(int $id): array
    {
        $result = $this->_createApiQuery()->parent($id);
        if (!empty($result['parentIds']) && $result['parentIds'][0] == $id) {
            unset($result['parentIds'][0]);
        }

        return array_reverse($result['parentIds']);
    }

    /**
     * @param int $id
     * @return Department[]
     */
    public function getParentDepartmentsById(int $id): array
    {
        $departments = [];

        $departmentIds = $this->getParentDepartmentIdsById($id);
        foreach ($departmentIds as $departmentId) {
            $departments[] = $this->getDepartmentById($departmentId);
        }

        return $departments;
    }

    /**
     * @param int $id
     * @return null|Department
     */
    public function getDepartmentById(int $id)
    {
        if ($this->_fetchedAllDepartments && array_key_exists($id, $this->_departmentsById)) {
            return $this->_departmentsById[$id];
        }

        if ($this->_fetchedAllDepartments) {
            return null;
        }

        $result = $this->_createApiQuery()->get($id);

        return $this->_departmentsById[$id] = $result ? $this->createDepartment($result) : null;
    }

    public function getDepartmentByName(string $name)
    {

    }

    /**
     * @param mixed $config
     * @return Department
     */
    public function createDepartment($config): Department
    {
        $department = new Department($config);

        return $department;
    }

    /**
     * @return \EasyDingTalk\Department\Client
     */
    private function _createApiQuery()
    {
        return Plugin::$plugin->getClient()->department;
    }
}