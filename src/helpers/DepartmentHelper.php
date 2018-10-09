<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\helpers;

use panlatent\craft\dingtalk\models\Department;

class DepartmentHelper
{
    /**
     * @param Department[] $departments
     * @param int|null $parentId
     * @return array
     */
    public static function tree(array $departments, int $parentId = null): array
    {
        $results = [];

        foreach ($departments as $department) {
            if ($department->parentId == $parentId) {
                $result = $department->toArray();
                if ($department->id == null) {
                    $result['children'] = static::tree($departments, 1);
                }
                $results[$result['id']] = $result;
            }
        }

        return $results;
    }

    public static function sourceTree(array $departments, int $parentId = null): array
    {
        $sources = [];

        foreach ($departments as $department) {
            if ($department->parentId == $parentId) {
                $source = [
                    'key' => $department->id,
                    'label' => $department->name,
                    'criteria' => [
                        'departmentId' => $department->id
                    ],
                ];
                if (!empty($children = static::sourceTree($departments, $department->id))) {
                    $source['nested'] = $children;
                }
                $sources[] =  $source;
            }
        }

        return $sources;
    }

    /**
     * @param Department[] $departments
     * @param int|null $parentId
     * @return Department[]
     */
    public static function parentSort(array $departments, int $parentId = null): array
    {
        $results = [];

        foreach ($departments as $key => $department) {
            if ($department->parentId == $parentId) {
                $results[] = $department;
                unset($departments[$key]);
            }
        }

        if (!empty($departments)) {
            foreach ($results as $result) {
                $results = array_merge($results, static::parentSort($departments, $result->id));
            }
        }

        return $results;
    }
}