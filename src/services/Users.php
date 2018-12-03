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
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\events\ElementLoadEvent;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;
use yii\base\InvalidArgumentException;

class Users extends Component
{
    /**
     * @event \panlatent\craft\dingtalk\events\ElementLoadEvent
     */
    const EVENT_BEFORE_LOAD_USER_PROPERTIES = 'beforeLoadUserProperties';

    /**
     * @event \panlatent\craft\dingtalk\events\ElementLoadEvent
     */
    const EVENT_AFTER_LOAD_USER_PROPERTIES = 'afterLoadUserProperties';

    /**
     * @param User $user
     * @param array $data
     */
    public function loadUser(User $user, array $data)
    {
        $isNewUser = !$user->id;

        if ($this->hasEventHandlers(static::EVENT_BEFORE_LOAD_USER_PROPERTIES)) {
            $this->trigger(static::EVENT_BEFORE_LOAD_USER_PROPERTIES, new ElementLoadEvent([
                'element' => $user,
                'isNew' => $isNewUser,
                'data' => $data,
            ]));
        }

        if ($isNewUser && !empty($data['userid'])) {
            $user->userId = ArrayHelper::remove($data, 'userid');
        }

        if (!empty($data['name'])) {
            $user->name = ArrayHelper::remove($data, 'name');
        }

        if (isset($data['active'])) {
            $user->active = ArrayHelper::remove($data, 'active');
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

        if ($this->hasEventHandlers(static::EVENT_AFTER_LOAD_USER_PROPERTIES)) {
            $this->trigger(static::EVENT_AFTER_LOAD_USER_PROPERTIES, new ElementLoadEvent([
                'element' => $user,
                'isNew' => $isNewUser,
                'data' => $data,
            ]));
        }
    }

    /**
     * @return bool
     */
    public function pullIncumbentUsers(): bool
    {
        $api = Plugin::$plugin->getApi();
        $departments = Plugin::$plugin->getDepartments()->getAllDepartments();
        $elements = Craft::$app->getElements();

        $incumbentIds = [];
        $handledDingUserIds = [];

        foreach ($departments as $department) {
            $results = $api->getUsersByDepartmentId($department->id);
            $results = array_filter($results, function($result) use($handledDingUserIds) {
                return !in_array($result['userid'], $handledDingUserIds);
            });

            for (; count($results); ) {
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
                    $this->loadUser($user, $result);

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
     * @param User|null $operateUser
     * @return bool
     */
    public function pullLeavedUsers(User $operateUser = null): bool
    {
        if ($operateUser === null) {
            $operateUser = User::find()
                ->isLeaved(false)
                ->isAdmin(true)
                ->isLeader(true)
                ->one();
            if (!$operateUser) {
                throw new InvalidArgumentException("Not found a administrator");
            }
        } elseif (!$operateUser->isAdmin) {
            throw new InvalidArgumentException("Operate user not is a administrator");
        }

        $api = Plugin::$plugin->getApi();
        $elements = Craft::$app->getElements();

        $results = $api->getDimissionUsers($operateUser->userId);

        for (; count($results); ) {
            $userResults = ArrayHelper::index(array_splice($results, 0, 20), 'userid');
            $userFieldResults = $this->_fetchUserFieldsByUserIds(array_keys($userResults));
            foreach ($userResults as $key => &$value) {
                $value = ArrayHelper::merge($value, $userFieldResults[$key]);
            }

            foreach ($userResults as $dingUserId => $result) {
                if (User::find()->isLeaved(false)->mobile($result['mobile'])->exists()) {
                    continue;
                }

                if (!($user = User::find()->userId($dingUserId)->one())) {
                    $user = new User();
                }

                $user->userId = ArrayHelper::remove($result, 'userid');
                $user->name = ArrayHelper::remove($result, 'name');
                $user->email = ArrayHelper::remove($result, 'email');
                $user->remark = ArrayHelper::remove($result, 'dismission_memo', '');

                if (empty($user->position) && !empty($result['position'])) {
                    $user->position = ArrayHelper::remove($result, 'position');
                }
                if (empty($user->dateHired) && !empty($result['confirm_join_time'])) {
                    $user->hiredDate = ArrayHelper::remove($result, 'confirm_join_time');
                }
                if (!empty($result['last_work_date'])) {
                    $user->leavedDate = ArrayHelper::remove($result, 'last_work_date');
                }

                // Filter old leaved users
                if (empty($result['mainDeptId']) || $result['mainDeptId'] <= 0) {
                    continue;
                }

                $user->isLeaved = true;
                $this->loadUser($user, $result);

                if (!$elements->saveElement($user)) {
                    Craft::warning('Couldn’t save dingtalk user with the ID: ' . $user->userId, __METHOD__);
                    continue;
                }
            }
        }

        return true;
    }

    /**
     * @param array $userIds
     * @return array
     */
    private function _fetchUserFieldsByUserIds(array $userIds): array
    {
        $results = Plugin::$plugin->getApi()->getUserSmartWorkFields($userIds);

        array_walk($results, function(&$values) {
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