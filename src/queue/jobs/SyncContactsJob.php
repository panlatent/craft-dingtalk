<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\queue\jobs;

use Craft;
use craft\helpers\ArrayHelper;
use craft\queue\BaseJob;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;

class SyncContactsJob extends BaseJob
{
    public $enableDepartments = true;

    public $enableUsers = true;

    public $enableSmartWork = true;

    public function execute($queue)
    {
        if ($this->enableDepartments) {
            $this->handleDepartments();
        }
        if ($this->enableUsers) {
            $this->handleUsers();
        }
        if ($this->enableSmartWork) {
            $this->handleSmartWork();
        }
    }

    protected function handleDepartments()
    {
        $allDepartments = Plugin::$plugin->departments->getAllDepartments();
        $allDepartments = ArrayHelper::index($allDepartments, 'id');

        $departments = [];
        $results = Plugin::$plugin->api->getAllDepartments();
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

            if (isset($allDepartments[$id])) {
                unset($allDepartments[$id]);
            }
        }

        $departments = DepartmentHelper::parentSort($departments);
        foreach ($departments as $department) {
            Plugin::$plugin->departments->saveDepartment($department);
        }

        foreach ($allDepartments as $department) {
            Plugin::$plugin->departments->deleteDepartment($department);
        }
    }

    protected function handleUsers()
    {
        $elements = Craft::$app->getElements();

        $activeUserIds = [];
        $departments = Plugin::$plugin->departments->getAllDepartments();

        foreach ($departments as $department) {
            $results = Plugin::$plugin->api->getUsersByDepartmentId($department->id);
            foreach ($results as $result) {
                if (!($user = User::find()->userId($result['userid'])->one())) {
                    $user = new User();
                }
                $user->userId = ArrayHelper::remove($result, 'userid');
                $user->name = ArrayHelper::remove($result, 'name');
                $user->position = ArrayHelper::remove($result, 'position');
                $user->tel = ArrayHelper::remove($result, 'tel');
                $user->isAdmin = ArrayHelper::remove($result, 'isAdmin');
                $user->isBoss = ArrayHelper::remove($result, 'isBoss');
                $user->isLeader = ArrayHelper::remove($result, 'isLeader');
                $user->isHide = ArrayHelper::remove($result, 'isHide');
                $user->avatar = ArrayHelper::remove($result, 'avatar');
                $user->jobNumber = ArrayHelper::remove($result, 'jobnumber');
                $user->email = ArrayHelper::remove($result, 'email');
                $user->orgEmail = ArrayHelper::remove($result, 'orgEmail');
                $user->active = ArrayHelper::remove($result, 'active');
                $user->mobile = ArrayHelper::remove($result, 'mobile');
                $user->remark = ArrayHelper::remove($result, 'remark');
                $user->sortOrder = ArrayHelper::remove($result, 'order');
                $user->departments = ArrayHelper::remove($result, 'department');

                if (!empty($result['hiredDate'])) {
                    $dateHired = ArrayHelper::remove($result, 'hiredDate');
                    $user->dateHired = date('Y-m-d H:i:s', (int)($dateHired/1000));
                }

                $user->settings = $result;

                $elements->saveElement($user);

                $activeUserIds[] = $user->userId;
            }
        }

        // Remove abandoned users.
        $abandonedElements = User::find()
            ->where(['not in', 'userId', $activeUserIds])
            ->all();

        foreach ($abandonedElements as $abandonedElement) {
            $elements->deleteElement($abandonedElement);
        }
    }

    protected function handleSmartWork()
    {
        foreach (User::find()->batch(20) as $users) {
            $userIds = ArrayHelper::getColumn($users, 'userId');
            $results = Plugin::$plugin->api->getUserSmartWorkFields($userIds);
            /** @var User $user */
            foreach ($users as $user) {
                $config = [
                    'userId' => $user->userId,
                ];
                $fields = $results[$user->userId]['field_list'];
                foreach ($fields as $value) {
                    $field = substr($value['field_code'], strlen($value['group_id']) + 1);
                    $config[$field] = $value['value'] ?? '';
                }

                $user->smartWork = Plugin::$plugin->smartWorks->createSmartWork($config);

                Craft::$app->getElements()->saveElement($user);
            }
        }
    }

    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Dingtalk Contacts');
    }
}