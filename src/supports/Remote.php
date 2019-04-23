<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\supports;

use EasyDingTalk\Kernel\Exceptions\ClientError;
use Generator;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Remote
 *
 * @package panlatent\craft\dingtalk\supports
 * @author Panlatent <panlatent@gmail.com>
 */
class Remote extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var Client|null
     */
    public $client;

    /**
     * @var string|null
     */
    public $corpId;

    /**
     * @var string|null
     */
    public $corpSecret;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->client === null) {
            $this->client = new Client([
                'corp_id' => $this->corpId,
                'corp_secret' => $this->corpSecret,
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
     * 返回所有外部联系人标签
     *
     * @return Generator
     */
    public function getExternalContactLabels()
    {
        for ($offset = 0; ; $offset += 100) {
            $results = $this->client->extcontact->listLabelGroups(100, $offset);

            foreach ($results['results'] as $result) {
                yield $result;
            }

            if (count($results['results']) < 100) {
                break;
            }
        }
    }

    /**
     * 返回所有外部联系人
     *
     * @return Generator
     */
    public function getExternalContacts()
    {
        for ($offset = 0; ; $offset += 100) {
            $results = $this->client->extcontact->list(100, $offset);

            foreach ($results['results'] as $result) {
                yield $result;
            }

            if (count($results['results']) < 100) {
                break;
            }
        }
    }

    /**
     * 返回外部联系人
     *
     * @param string $userId
     * @return array
     */
    public function getExternalContactById(string $userId): array
    {
        $result = $this->client->extcontact->get($userId);

        return $result['result'];
    }

    /**
     * @param array $config
     * @return string
     */
    public function createExternalContact(array $config): string
    {
        $result = $this->client->extcontact->create([
            'contact' => $config
        ]);

        return $result['userid'];
    }

    /**
     * @param array $config
     */
    public function saveExternalContact(array $config)
    {
        $this->client->extcontact->update([
            'contact' => $config
        ]);
    }

    /**
     * @param string $userId
     * @return bool
     */
    public function deleteExternalContact(string $userId): bool
    {
        $result = $this->client->extcontact->delete($userId);

        return isset($result['errcode']) && $result['errcode'] == 0;
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