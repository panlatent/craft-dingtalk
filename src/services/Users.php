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
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;
use yii\base\InvalidArgumentException;

/**
 * Class Users
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Users extends Component
{
    // Public Methods
    // =========================================================================

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

        $api = Plugin::getInstance()->getApi();
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


}