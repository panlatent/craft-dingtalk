<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use panlatent\craft\dingtalk\models\User;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;

class Users extends Component
{
    private $_fetchedAllUsers = false;

    private $_usersById;

    public function getAllUsers()
    {
        if ($this->_fetchedAllUsers) {
            return array_values($this->_usersById);
        }

        $this->_usersById = [];

        $departments = Plugin::$plugin->getDepartments()->getAllDepartments();
        foreach ($departments as $department) {
            foreach ($this->getUsersByDepartmentId($department->id) as $user) {
                $this->_usersById[$user->userid] = $user;
            }
        }

        $this->_fetchedAllUsers = true;

        return array_values($this->_usersById);
    }

    /**
     * @param int $departmentId
     * @return User[]
     */
    public function getUsersByDepartmentId(int $departmentId): array
    {
        $users = [];
        $results = Plugin::$plugin->getClient()->user->list($departmentId)['userlist'];
        foreach ($results as $result) {
            $user = $this->createUser($result);
            $users[] = $user;
        }

        return $users;
    }

    /**
     * @param mixed $config
     * @return User
     */
    public function createUser($config): User
    {
        $user = new User($config);

        return $user;
    }
}