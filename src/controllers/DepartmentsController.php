<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;
use yii\web\Response;

class DepartmentsController extends Controller
{
    public function actionIndex(int $departmentId = 1): Response
    {
        $departments = Plugin::$plugin->getDepartments();

        $children = $departments->getDepartmentsByParentId($departmentId);
        $children = DepartmentHelper::tree($children, $departmentId);

        $department = Plugin::$plugin->getDepartments()->getDepartmentById($departmentId);

        $crumbs = [
            [
                'label' => Craft::t('dingtalk', 'Departments'),
                'url' => UrlHelper::url('dingtalk/departments'),
            ]
        ];

        if ($departmentId !== 1) {
            $parents = $departments->getParentDepartmentsById($departmentId);
            foreach ($parents as $parent) {
                $crumbs[] = [
                    'label' => $parent->name,
                    'url' => UrlHelper::url("dingtalk/departments/{$parent->id}"),
                ];
            }
        }

        if (empty($children)) {
            $users = Plugin::$plugin->getUsers()->getUsersByDepartmentId($departmentId);
        }

        return $this->renderTemplate('dingtalk/departments/_index', [
            'title' => $department->name,
            'crumbs' => $crumbs,
            'department' => $department,
            'children' => $children,
            'users' => $users ?? [],
        ]);
    }
}