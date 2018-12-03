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
     * e.g.
     *
     * ```php
     * [
     *     [
     *         'department' => $department,
     *         'nested' => [
     *              [
     *                  'department' => $department,
     *                  'nested' => [
     *                  ]
     *              ]
     *         ]
     *     ]
     * ]
     * ```
     *
     * @param Department[] $departments
     * @param int|null $parentId
     * @return array
     */
    public static function tree(array $departments, int $parentId = null): array
    {
        $roots = [];

        foreach ($departments as $department) {
            if ($department->parentId == $parentId) {
                $roots[] = [
                    'department' => $department,
                    'nested' => static::tree($departments, $department->id),
                ];
            }
        }

        return $roots;
    }

    public static function sourceTree(array $departments, int $parentId = null): array
    {
        $sources = [];

        foreach ($departments as $department) {
            if ($department->parentId == $parentId) {
                $source = [
                    'key' => 'department:' . $department->id,
                    'label' => $department->name,
                    'criteria' => [
                        'departmentId' => $department->id
                    ],
                    'hasThumbs' => true,
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