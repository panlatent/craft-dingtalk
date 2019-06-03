<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\jobs;

use Craft;
use craft\helpers\ArrayHelper;
use craft\queue\BaseJob;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;

/**
 * Class SyncDepartmentsJob
 *
 * @package panlatent\craft\dingtalk\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncDepartmentsJob extends BaseJob
{
    // Traits
    // =========================================================================

    use CorporationJobTrait;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $departments = Plugin::$dingtalk->getDepartments();

        $allDepartments = [];
        $localDepartments = $this->getCorporation()->getDepartments();
        $localDepartments = ArrayHelper::index($localDepartments, 'dingDepartmentId');

        $results = $this->getCorporation()->getRemote()->getAllDepartments();
        $results = $this->_sortRemoteDepartments($results);

        foreach (array_values($results) as $sortOrder => $result) {
            $id = ArrayHelper::remove($result, 'id');

            $department = $departments->findDepartment([
                'corporationId' => $this->corporationId,
                'dingDepartmentId' => $id
            ]);

            if (!$department) {
                $department = $departments->createDepartment([
                    'corporationId' => $this->corporationId,
                    'dingDepartmentId' => $id
                ]);
            }

            $parentId = null;
            if ($dingParentId = ArrayHelper::remove($result, 'parentid')) {
                $parent = ArrayHelper::firstWhere($allDepartments, 'dingDepartmentId', $dingParentId);
                $parentId = $parent->id;
            }

            $department->name =  ArrayHelper::remove($result, 'name');
            $department->parentId = $parentId;
            $department->sortOrder = ArrayHelper::remove($result, 'order');
            $department->settings = $result;
            $department->archived = false;
            $department->sortOrder = $sortOrder;

            if (!$departments->saveDepartment($department)) {
                Craft::warning("Couldn’t save department.", __METHOD__);
            }

            $allDepartments[] = $department;

            if (isset($localDepartments[$id])) {
                unset($localDepartments[$id]);
            }
        }

        foreach ($localDepartments as $department) {
            $department->archived = true;
            $allDepartments[] = $department;
        }

        $allDepartments = DepartmentHelper::parentSort($allDepartments);
        foreach (array_values($allDepartments) as $sortOrder => $department) {
            $department->sortOrder = $sortOrder;
            $departments->saveDepartment($department);
        }

        return true;
    }

    /**
     * @param array $departments
     * @param int|null $parentId
     * @return array
     */
    private function _sortRemoteDepartments(array $departments, int $parentId = null): array
    {
        $results = [];

        foreach ($departments as $key => $department) {
            $currentParentId  = $department['parentid'] ?? null;
            if ($currentParentId == $parentId) {
                $results[] = $department;
                unset($departments[$key]);
            }
        }

        if (!empty($departments)) {
            foreach ($results as $result) {
                $results = array_merge($results, $this->_sortRemoteDepartments($departments, $result['id']));
            }
        }

        return $results;
    }
}