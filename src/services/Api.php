<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use EasyDingTalk\Application as DingTalkClient;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Api
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Api extends Component
{
    /**
     * @var DingTalkClient|null
     */
    public $client;

    /**
     * Init.
     */
    public function init()
    {
        parent::init();

        if ($this->client === null) {
            $this->client = new DingTalkClient([
                'corp_id' => Plugin::$plugin->settings->corpId,
                'corp_secret' => Plugin::$plugin->settings->corpSecret,
            ]);
        }
    }

    /**
     * @return array
     */
    public function getAllDepartments(): array
    {
        $results = $this->client->department->list();

        return $results['department'];
    }

    /**
     * @param int $parentId
     * @return array
     */
    public function getDepartmentsByParentId(int $parentId): array
    {
        $results = $this->client->department->list($parentId);

        return $results['department'];
    }

    /**
     * @param int $id
     * @return int[]
     */
    public function getParentDepartmentIdsById(int $id): array
    {
        $result = $this->client->department->parent($id);
        if (!empty($result['parentIds']) && $result['parentIds'][0] == $id) {
            unset($result['parentIds'][0]);
        }

        return array_reverse($result['parentIds']);
    }

    /**
     * @param int $id
     * @return array
     */
    public function getParentDepartmentsById(int $id): array
    {
        $results = [];

        $departmentIds = $this->getParentDepartmentIdsById($id);
        foreach ($departmentIds as $departmentId) {
            $results[] = $this->getDepartmentById($departmentId);
        }

        return $results;
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function getDepartmentById(int $id)
    {
        $result = $this->client->department->get($id);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllUsers(): array
    {
        $users = [];

        $departments = $this->getAllDepartments();
        foreach ($departments as $department) {
            foreach ($this->getUsersByDepartmentId($department['id']) as $user) {
                $users[$user->userid] = $user;
            }
        }

        return $users;
    }

    /**
     * @param int $departmentId
     * @return array
     */
    public function getUsersByDepartmentId(int $departmentId): array
    {
        $results = $this->client->user->httpGet('user/list', ['department_id' => $departmentId]);

        return $results['userlist'];
    }

    /**
     * @param array|string $userId
     * @return array
     */
    public function getUserSmartWorkFields($userId): array
    {
        if (is_array($userId)) {
            $userId = implode(',', $userId);
        }

        $results = $this->client->user->httpPostJson('topapi/smartwork/hrm/employee/list', [
            'userid_list' => $userId,
        ]);

        return ArrayHelper::index($results['result'], 'userid');
    }
}