<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use EasyDingTalk\Kernel\Exceptions\ClientError;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\supports\Client;
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
     * @var Client|null
     */
    public $client;

    /**
     * Init.
     */
    public function init()
    {
        parent::init();

        if ($this->client === null) {
            $this->client = new Client([
                'corp_id' => Plugin::getInstance()->settings->getCorpId(),
                'corp_secret' => Plugin::getInstance()->settings->getCorpSecret(),
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
     * @param string $userId
     * @return mixed
     */
    public function getUserById(string $userId)
    {
        $results = $this->client->user->get($userId);

        return $results;
    }

    /**
     * @param string $operateUserId
     * @return array
     */
    public function getDimissionUserIds(string $operateUserId)
    {
        return array_keys($this->getDimissionUsers($operateUserId));
    }

    /**
     * @param string $operateUserId
     * @return array
     */
    public function getDimissionUsers(string $operateUserId)
    {
        $users = [];

        $pageCount = 1;
        for ($page = 1; $page <= $pageCount; ++$page) {
            $results = $this->client->user->httpPostJson('topapi/hrm/employee/getdismissionlist', [
                'current' => $page,
                'page_size' => 50,
                'op_userid' => $operateUserId,
            ]);

            $page = $results['page']['current'];
            $pageCount = $results['page']['total_page'];
            $users = array_merge($users, $results['page']['data_list']);
        }

        return ArrayHelper::index($users, 'userid');
    }

    /**
     * 获取用户智能办公字段
     *
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

    /**
     * 批量获取审批实例 ID
     *
     * @param string $processCode
     * @param int $startTime
     * @param int $endTime
     * @param int $limit
     * @return array
     */
    public function getProcessInstanceIds(string $processCode, int $startTime, int $endTime = 0, int $limit = 100): array
    {
        $ids = [];
        for ($cursor = 0; $cursor !== null && count($ids) < $limit;) {
            $result = $this->client->process->httpPostJson('topapi/processinstance/listids', array_filter([
                'process_code' => $processCode,
                'start_time' => $startTime  * 1000,
                'end_time' => $endTime * 1000,
                'cursor' => $cursor,
            ]));
            $result = $result['result'];
            $ids = array_merge($ids, $result['list']);
            $cursor = $result['next_cursor'] ?? null;
        }
        $ids = array_unique($ids);

        return $ids;
    }

    /**
     * 获取审批实例
     *
     * @param string $instanceId
     * @return array
     */
    public function getProcessInstanceById(string $instanceId): array
    {
        $results = $this->client->process->httpPostJson('topapi/processinstance/get', [
            'process_instance_id' => $instanceId,
        ]);

        return $results['process_instance'];
    }

    /**
     * 获得已注册的业务事件回调信息
     *
     * @return array|null
     */
    public function getCallback()
    {
        try {
            $ret = $this->client->callback->get();
        } catch (ClientError $exception) {
            if ($exception->getCode() === 71007) {
                return null;
            }

            throw $exception;
        }

        return $ret;
    }
}