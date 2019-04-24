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
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\Plugin;
use Throwable;
use yii\base\InvalidConfigException;

/**
 * 同步钉钉用户任务
 *
 * @package panlatent\craft\dingtalk\queue\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncUsersJob extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var int|null Corporation ID
     */
    public $corporationId;

    /**
     * @var bool With smart work data
     */
    public $withSmartWorks = false;

    /**
     * @var Corporation|null
     */
    private $_corporation;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $this->_syncAllDepartments();
            $this->_syncAllUsers();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            Craft::error($exception->getMessage(), 'dingtalk-sync-job');

            throw $exception;
        }
    }

    /**
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        $corporation = Plugin::getInstance()
            ->getCorporations()
            ->getCorporationById($this->corporationId);

        if ($corporation === null) {
            throw new InvalidConfigException("Missing corporation with the ID: {$this->corporationId}");
        }

        return $this->_corporation = $corporation;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Dingtalk Contacts');
    }

    // Private Methods
    // =========================================================================

    /**
     * 同步所有部门
     *
     * @return bool
     */
    private function _syncAllDepartments(): bool
    {
        $departments = Plugin::getInstance()->getDepartments();

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

            $departments->saveDepartment($department);

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
     * @return bool
     */
    private function _syncAllUsers(): bool
    {
        $departments = $this->getCorporation()->getDepartments();
        $elements = Craft::$app->getElements();

        $incumbentIds = [];
        $handledDingUserIds = [];

        foreach ($departments as $department) {
            $results = $this->getCorporation()->getRemote()->getUsersByDepartmentId($department->dingDepartmentId);
            $results = array_filter($results, function ($result) use ($handledDingUserIds) {
                return !in_array($result['userid'], $handledDingUserIds);
            });

            for (; count($results);) {
                $userResults = ArrayHelper::index(array_splice($results, 0, 20), 'userid');

                if ($this->withSmartWorks) {
                    $userFieldResults = $this->_fetchUserFieldsByUserIds(array_keys($userResults));
                    foreach ($userResults as $key => &$value) {
                        $value = ArrayHelper::merge($value, $userFieldResults[$key]);
                    }
                }

                foreach ($userResults as $dingUserId => $result) {
                    $user = User::find()
                        ->corporationId($this->corporationId)
                        ->userId($dingUserId)
                        ->one();

                    if (!$user) {
                        $user = new User();
                    }

                    $userDepartments = [];
                    foreach (ArrayHelper::remove($result, 'department') as $dingDepartmentId) {
                        $userDepartments[] = ArrayHelper::firstWhere($departments, 'dingDepartmentId', $dingDepartmentId);
                    }

                    $user->corporationId = $this->corporationId;
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
                    $user->mobile = ArrayHelper::remove($result, 'mobile');
                    $user->remark = ArrayHelper::remove($result, 'remark');
                    $user->sortOrder = ArrayHelper::remove($result, 'order');
                    $user->departments = $userDepartments;
                    $user->isLeaved = false;
                    $this->_loadUser($user, $result);

                    if (!$elements->saveElement($user)) {
                        Craft::warning('Couldn’t save dingtalk user with the ID: ' . $user->userId, __METHOD__);
                        continue;
                    }

                    $incumbentIds[] = $user->id;
                    $handledDingUserIds[] = $dingUserId;
                }
            }
        }

        // Remove abandoned users.
        /** @var User[] $leavedUsers */
        $leavedUsers = User::find()
            ->corporationId($this->corporationId)
            ->andWhere(['not in', 'dingtalk_users.id', $incumbentIds])
            ->all();

        if (!empty($leavedUsers)) {
            foreach ($leavedUsers as $leavedUser) {
                $leavedUser->isLeaved = true;
                $elements->saveElement($leavedUser);
            }
        }

        return true;
    }

    /**
     * @param User $user
     * @param array $data
     */
    private function _loadUser(User $user, array $data)
    {
        $isNewUser = !$user->id;

        if ($isNewUser && !empty($data['userid'])) {
            $user->userId = ArrayHelper::remove($data, 'userid');
        }

        if (!empty($data['name'])) {
            $user->name = ArrayHelper::remove($data, 'name');
        }

        if (isset($data['active'])) {
            $user->isActive = ArrayHelper::remove($data, 'active');
        }

        if (!empty($data['mainDeptId'])) {
            $departments = $this->getCorporation()->getDepartments();
            $department = ArrayHelper::firstWhere($departments, 'dingDepartmentId', ArrayHelper::remove($data, 'mainDeptId'));
            $user->primaryDepartment = $department;
        }

        if (!empty($data['hiredDate'])) {
            $hiredDate = ArrayHelper::remove($data, 'hiredDate');
            $user->hiredDate = date('Y-m-d H:i:s', (int)substr($hiredDate, 0, -3));
        }

        if (empty($user->hiredDate) && !empty($value['confirmJoinTime'])) {
            $user->hiredDate = $value['confirmJoinTime'];
        }

        foreach (array_keys($data) as $name) {
            if ($user->canSetProperty($name)) {
                $user->$name = ArrayHelper::remove($data, $name);
            }
        }
    }

    /**
     * @param array $userIds
     * @return array
     */
    private function _fetchUserFieldsByUserIds(array $userIds): array
    {
        $results = $this->getCorporation()->getRemote()->getUserSmartWorkFields($userIds);

        array_walk($results, function (&$values) {
            $fields = [];
            foreach ($values['field_list'] as $value) {
                $field = substr($value['field_code'], strlen($value['group_id']) + 1);
                $fields[$field] = $value['value'] ?? '';
            }

            $values = array_filter($fields);
        });

        return $results;
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