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
 * Class SyncContactsJob
 *
 * @package panlatent\craft\dingtalk\queue\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncContactsJob extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $corporationId;

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
            $this->_syncIncumbentUsers();
            //Plugin::getInstance()->getUsers()->pullLeavedUsers();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();
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

        $results = $this->getCorporation()->getRemote()->getAllDepartments();
        foreach ($results as $result) {
            $id = ArrayHelper::remove($result, 'id');
            $department = Plugin::getInstance()->departments->createDepartment([
                'id' => $id,
                'name' => ArrayHelper::remove($result, 'name'),
                'parentId' => ArrayHelper::remove($result, 'parentid'),
                'sortOrder' => ArrayHelper::remove($result, 'order'),
                'settings' => $result,
            ]);
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
        foreach ($allDepartments as $sortOrder => $department) {
            $department->sortOrder = $sortOrder;
            $departments->saveDepartment($department);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function _syncIncumbentUsers(): bool
    {
        $departments = Plugin::getInstance()->getDepartments()->getAllDepartments();
        $elements = Craft::$app->getElements();

        $incumbentIds = [];
        $handledDingUserIds = [];

        foreach ($departments as $department) {
            $results = $this->getCorporation()->getRemote()->getUsersByDepartmentId($department->id);
            $results = array_filter($results, function ($result) use ($handledDingUserIds) {
                return !in_array($result['userid'], $handledDingUserIds);
            });

            for (; count($results);) {
                $userResults = ArrayHelper::index(array_splice($results, 0, 20), 'userid');
                $userFieldResults = $this->_fetchUserFieldsByUserIds(array_keys($userResults));
                foreach ($userResults as $key => &$value) {
                    $value = ArrayHelper::merge($value, $userFieldResults[$key]);
                }
                foreach ($userResults as $dingUserId => $result) {
                    if (!($user = User::find()->userId($dingUserId)->one())) {
                        $user = new User();
                    }

                    $user->position = ArrayHelper::remove($data, 'position');
                    $user->tel = ArrayHelper::remove($data, 'tel');
                    $user->isAdmin = ArrayHelper::remove($data, 'isAdmin');
                    $user->isBoss = ArrayHelper::remove($data, 'isBoss');
                    $user->isLeader = ArrayHelper::remove($data, 'isLeader');
                    $user->isHide = ArrayHelper::remove($data, 'isHide');
                    $user->avatar = ArrayHelper::remove($data, 'avatar');
                    $user->jobNumber = ArrayHelper::remove($data, 'jobnumber');
                    $user->email = ArrayHelper::remove($data, 'email');
                    $user->orgEmail = ArrayHelper::remove($data, 'orgEmail');
                    $user->mobile = ArrayHelper::remove($data, 'mobile');
                    $user->remark = ArrayHelper::remove($data, 'remark');
                    $user->sortOrder = ArrayHelper::remove($data, 'order');
                    $user->departments = ArrayHelper::remove($data, 'department');
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
        $leavedUsers = User::find()->where(['not in', 'dingtalk_users.id', $incumbentIds])->all();

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
            $user->primaryDepartment = ArrayHelper::remove($data, 'mainDeptId');
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
}