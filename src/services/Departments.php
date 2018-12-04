<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use panlatent\craft\dingtalk\errors\DepartmentException;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\Department as DepartmentRecord;
use yii\base\Component;
use yii\db\Query;

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
     * @return bool
     */
    public function pullAllDepartments(): bool
    {
        $this->getAllDepartments();
        $allLocalDepartments = $this->_departmentsById;
        $this->_fetchedAllDepartments = false;

        $departments = [];

        $results = Plugin::$plugin->getApi()->getAllDepartments();

        foreach ($results as $result) {
            $id = ArrayHelper::remove($result, 'id');
            $department = Plugin::$plugin->departments->createDepartment([
                'id' => $id,
                'name' => ArrayHelper::remove($result, 'name'),
                'parentId' => ArrayHelper::remove($result, 'parentid'),
                'sortOrder' => ArrayHelper::remove($result, 'order'),
                'settings' => $result,
            ]);
            $departments[] = $department;

            if (isset($allLocalDepartments[$id])) {
                unset($allLocalDepartments[$id]);
            }
        }

        foreach ($allLocalDepartments as $department) {
            $department->archived = true;
            $departments[] = $department;
        }

        $departments = DepartmentHelper::parentSort($departments);
        foreach ($departments as $sortOrder => $department) {
            $department->sortOrder = $sortOrder;
            $this->saveDepartment($department);
        }

        return true;
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
        $isNew = (empty($department->id) || !DepartmentRecord::find()->where(['id' => $department->id])->exists());

        if ($runValidation && !$department->validate()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();
        try {
            if ($isNew) {
                $departmentRecord = new DepartmentRecord();
            } else {
                $departmentRecord = DepartmentRecord::findOne(['id' => $department->id]);
                if (!$departmentRecord) {
                    throw new DepartmentException("No department exists due ID: “{$department->id}“");
                }
            }

            $departmentRecord->id = $department->id;
            $departmentRecord->name = $department->name;
            $departmentRecord->parentId = $department->parentId;
            $departmentRecord->settings = Json::encode($department->settings);
            $departmentRecord->sortOrder = $department->sortOrder;
            $departmentRecord->archived = (bool)$department->archived;

            $departmentRecord->save(false);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $department->id = $departmentRecord->id;

        return true;
    }

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
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    private function _createQuery(): Query
    {
        return (new Query())
            ->select(['id', 'name', 'parentId', 'settings'])
            ->from('{{%dingtalk_departments}}')
            ->orderBy('sortOrder');
    }
}