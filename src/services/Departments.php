<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use craft\helpers\Db;
use craft\helpers\Json;
use panlatent\craft\dingtalk\errors\DepartmentException;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\models\DepartmentCriteria;
use panlatent\craft\dingtalk\records\Department as DepartmentRecord;
use Throwable;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Departments
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Departments extends Component
{
    // Properties
    // =========================================================================

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

    // Public Methods
    // =========================================================================

    /**
     * 返回所有部门
     *
     * @return Department[]
     */
    public function getAllDepartments(): array
    {
        if ($this->_fetchedAllDepartments) {
            return array_values($this->_departmentsById);
        }

        $this->_departmentsById = [];
        $this->_departmentsByName = [];

        $results = $this->_createQuery()->all();

        foreach ($results as $result) {
            $department = $this->createDepartment($result);
            $this->_departmentsById[$department->id] = $department;
            $this->_departmentsByName[$department->name] = $department;
        }

        $this->_fetchedAllDepartments = true;

        return array_values($this->_departmentsById);
    }

    /**
     * @return Department[]
     */
    public function getActiveDepartments(): array
    {
        $departments = [];

        $results = $this->_createQuery()
            ->where(['archived' => false])
            ->all();

        foreach ($results as $result) {
            $departments[] = $department = $this->createDepartment($result);
            $this->_departmentsById[$department->id] = $department;
            $this->_departmentsByName[$department->name] = $department;
        }

        return $departments;
    }

    /**
     * @param int $parentId
     * @return Department[]
     */
    public function getDepartmentsByParentId(int $parentId): array
    {
        $departments = [];

        $results = $this->_createQuery()
            ->where([
                'parentId' => $parentId,
            ])
            ->all();

        foreach ($results as $result) {
            $department = $this->createDepartment($result);
            $departments[] = $department;
            $this->_departmentsById[$department->id] = $department;
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

        $result = $this->_createQuery()
            ->where(['id' => $id])
            ->one();

        return $this->_departmentsById[$id] = $result ? $this->createDepartment($result) : null;
    }

    /**
     * @param string $name
     * @return null|Department
     */
    public function getDepartmentByName(string $name)
    {
        if ($this->_fetchedAllDepartments && array_key_exists($name, $this->_departmentsByName)) {
            return $this->_departmentsByName[$name];
        }

        if ($this->_fetchedAllDepartments) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['name' => $name])
            ->one();

        return $this->_departmentsByName[$name] = $result ? $this->createDepartment($result) : null;
    }

    /**
     * @param mixed $criteria
     * @return Department[]
     */
    public function findDepartments($criteria): array
    {
        if (!$criteria instanceof DepartmentCriteria) {
            $criteria = new DepartmentCriteria($criteria);
        }

        $query = $this->_createQuery();
        $this->_applyDepartmentConditions($query, $criteria);

        if ($criteria->order) {
            $query->orderBy($criteria->order);
        }

        if ($criteria->offset) {
            $query->offset($criteria->offset);
        }

        if ($criteria->limit) {
            $query->limit($criteria->limit);
        }

        $results = $query->all();

        $departments = [];
        foreach ($results as $result) {
            $departments[] = $this->createDepartment($result);
        }

        return $departments;
    }

    /**
     * @param mixed $criteria
     * @return Department|null
     */
    public function findDepartment($criteria)
    {
        if (!$criteria instanceof DepartmentCriteria) {
            $criteria = new DepartmentCriteria($criteria);
        }

        $criteria->limit = 1;

        $results = $this->findDepartments($criteria);
        if (is_array($results) && !empty($results)) {
            return array_pop($results);
        }

        return null;
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
     * @param Department $department
     * @param bool $runValidation
     * @return bool
     */
    public function saveDepartment(Department $department, bool $runValidation = true): bool
    {
        $isNewDepartment = (empty($department->id) || !DepartmentRecord::find()->where(['id' => $department->id])->exists());

        if ($runValidation && !$department->validate()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();
        try {
            if ($isNewDepartment) {
                $departmentRecord = new DepartmentRecord();
            } else {
                $departmentRecord = DepartmentRecord::findOne(['id' => $department->id]);
                if (!$departmentRecord) {
                    throw new DepartmentException("No department exists due ID: “{$department->id}“");
                }
            }

            $departmentRecord->name = $department->name;
            $departmentRecord->parentId = $department->parentId;
            $departmentRecord->settings = Json::encode($department->settings);
            $departmentRecord->sortOrder = $department->sortOrder;
            $departmentRecord->archived = (bool)$department->archived;

            $departmentRecord->save(false);

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $department->id = $departmentRecord->id;

        return true;
    }

    /**
     * @param Department $department
     * @return bool
     */
    public function deleteDepartment(Department $department): bool
    {
        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete('{{%dingtalk_departments}}', [
                    'id' => $department->id,
                ])->execute();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createQuery(): Query
    {
        return (new Query())
            ->select(['id', 'name', 'parentId', 'settings', 'sortOrder', 'archived'])
            ->from('{{%dingtalk_departments}}')
            ->orderBy('sortOrder');
    }

    /**
     * @param Query $query
     * @param DepartmentCriteria $criteria
     */
    private function _applyDepartmentConditions(Query $query, DepartmentCriteria $criteria)
    {
        if ($criteria->corporationId) {
            $query->andWhere(Db::parseParam('corporationId', $criteria->corporationId));
        }

        if ($criteria->name) {
            $query->andWhere(Db::parseParam('name', $criteria->name));
        }

        if ($criteria->archived) {
            $query->andWhere(Db::parseParam('archived', $criteria->archived));
        }
    }
}