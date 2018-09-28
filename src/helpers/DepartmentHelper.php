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
            if ($department->parentid == $parentId) {
                $result = $department->toArray();
                if ($department->id == null) {
                    $result['children'] = static::tree($departments, 1);
                }
                $results[$result['id']] = $result;
            }
        }

        return $results;
    }
}