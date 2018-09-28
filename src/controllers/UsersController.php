<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\web\Controller;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;
use yii\web\Response;

class UsersController extends Controller
{
    public function actionIndex()
    {

    }

    public function actionDepartmentUsers(int $departmentId): Response
    {
        $users = Plugin::$plugin->getUsers()->getUsersByDepartmentId($departmentId);
        $allDepartments = Plugin::$plugin->getDepartments()->getAllDepartments();
        $allDepartments = DepartmentHelper::tree($allDepartments);

        return $this->renderTemplate('dingtalk/users/_index', [
            'title' => Craft::t('dingtalk', 'Department Users'),
            'allDepartments' => $allDepartments,
            'users' => $users,
        ]);
    }
}