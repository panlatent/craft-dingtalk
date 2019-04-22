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

        $results = $this->_createQuery()->all();

        foreach ($results as $result) {
            $department = $this->createDepartment($result);
            $this->_departmentsById[$department->id] = $department;
        }

        $this->_fetchedAllDepartments = true;

        return array_values($this->_departmentsById);
    }

    /**
     * 返回所有未归档部门
     *
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
     * @param DepartmentCriteria|array $criteria
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
     * @param DepartmentCriteria|array $criteria
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
        if (is_array($config) && isset($config['settings']) && is_string($config['settings'])) {
            $config['settings'] = Json::decodeIfJson($config['settings']);
        }

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
        $isNewDepartment = !$department->id;

        if ($runValidation && !$department->validate()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();
        try {
            if ($isNewDepartment) {
                $record = new DepartmentRecord();
            } else {
                $record = DepartmentRecord::findOne(['id' => $department->id]);
                if (!$record) {
                    throw new DepartmentException("No department exists due ID: “{$department->id}“");
                }
            }

            $record->corporationId = $department->corporationId;
            $record->dingDepartmentId = $department->dingDepartmentId;
            $record->name = $department->name;
            $record->parentId = $department->parentId;
            $record->settings = $department->settings ? Json::encode($department->settings) : null;
            $record->sortOrder = $department->sortOrder;
            $record->archived = (bool)$department->archived;

            $record->save(false);

            if ($isNewDepartment) {
                $department->id = $record->id;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_departmentsById[$department->id] = $department;

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
            ->select(['id', 'corporationId', 'dingDepartmentId', 'name', 'parentId', 'settings', 'sortOrder', 'archived'])
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

        if ($criteria->dingDepartmentId) {
            $query->andWhere(Db::parseParam('dingDepartmentId', $criteria->dingDepartmentId));
        }

        if ($criteria->name) {
            $query->andWhere(Db::parseParam('name', $criteria->name));
        }

        if ($criteria->archived) {
            $query->andWhere(Db::parseParam('archived', $criteria->archived));
        }

        if ($criteria->root !== null) {
            $query->andWhere([$criteria->root ? 'is' : 'is not', 'parentId', null]);
        }
    }
}