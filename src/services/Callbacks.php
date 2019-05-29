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
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\errors\CallbackException;
use panlatent\craft\dingtalk\events\CallbackEvent;
use panlatent\craft\dingtalk\events\CallbackGroupEvent;
use panlatent\craft\dingtalk\models\Callback;
use panlatent\craft\dingtalk\models\CallbackGroup;
use panlatent\craft\dingtalk\models\CallbackRequest;
use panlatent\craft\dingtalk\models\CallbackRequestCriteria;
use panlatent\craft\dingtalk\records\Callback as CallbackRecord;
use panlatent\craft\dingtalk\records\CallbackGroup as CallbackGroupRecord;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Callbacks
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Callbacks extends Component
{
    // Constants
    // =========================================================================

    // Events
    // -------------------------------------------------------------------------

    /**
     * @event CallbackGroupEvent The event that is triggered before a group is saved.
     */
    const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';

    /**
     * @event CallbackGroupEvent The event that is triggered after a group is saved.
     */
    const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';

    /**
     * @event CallbackGroupEvent The event that is triggered before a group is deleted.
     */
    const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

    /**
     * @event CallbackGroupEvent The event that is triggered after a group is deleted.
     */
    const EVENT_AFTER_DELETE_GROUP = 'afterDeleteGroup';

    /**
     * @event CallbackEvent The event that is triggered before a callback is saved.
     */
    const EVENT_BEFORE_SAVE_CALLBACK = 'beforeSaveCallback';

    /**
     * @event CallbackEvent The event that is triggered after a callback is saved.
     */
    const EVENT_AFTER_SAVE_CALLBACK = 'afterSaveCallback';

    /**
     * @event CallbackEvent The event that is triggered before a callback is deleted.
     */
    const EVENT_BEFORE_DELETE_CALLBACK = 'beforeDeleteCallback';

    /**
     * @event CallbackEvent The event that is triggered after a callback is deleted.
     */
    const EVENT_AFTER_DELETE_CALLBACK = 'afterDeleteCallback';

    /**
     * @event CallbackRequestEvent The event that is triggered before a request is saved.
     */
    const EVENT_BEFORE_SAVE_REQUEST = 'beforeSaveRequest';

    /**
     * @event CallbackRequestEvent The event that is triggered after a request is saved.
     */
    const EVENT_AFTER_SAVE_REQUEST = 'afterSaveRequest';

    /**
     * @event CallbackRequestEvent The event that is triggered before a request is deleted.
     */
    const EVENT_BEFORE_DELETE_REQUEST = 'beforeDeleteRequest';

    /**
     * @event CallbackRequestEvent The event that is triggered after a request is deleted.
     */
    const EVENT_AFTER_DELETE_REQUEST = 'afterDeleteRequest';

    // Properties
    // =========================================================================

    /**
     * @var CallbackGroup[]|null
     */
    private $_groups;

    /**
     * @var Callback[]|null
     */
    private $_callbacks;

    // Public Methods
    // =========================================================================

    // Groups
    // -------------------------------------------------------------------------

    /**
     * @return CallbackGroup[]
     */
    public function getAllGroups(): array
    {
        if ($this->_groups !== null) {
            return $this->_groups;
        }

        $this->_groups = [];

        $results = (new Query())
            ->select(['id', 'name'])
            ->from(Table::CALLBACKGROUPS)
            ->all();

        foreach ($results as $result) {
            $this->_groups[] = $this->createGroup($result);
        }

        return $this->_groups;
    }

    /**
     * @param int $groupId
     * @return CallbackGroup|null
     */
    public function getGroupById(int $groupId)
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'id', $groupId);
    }

    /**
     * @param string $groupName
     * @return CallbackGroup|null
     */
    public function getGroupByName(string $groupName)
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'name', $groupName);
    }

    /**
     * @param mixed $config
     * @return CallbackGroup
     */
    public function createGroup($config): CallbackGroup
    {
        if (is_string($config)) {
            $config = [
                'name' => $config
            ];
        }

        return new CallbackGroup($config);
    }

    public function saveGroup(CallbackGroup $group, bool $runValidation = true): bool
    {
        $isNewGroup = !$group->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new CallbackGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        if ($runValidation && !$group->validate()) {
            Craft::info('Callback group not saved due to validation error.', __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if ($isNewGroup) {
                $record = new CallbackGroupRecord();
            } else {
                $record = CallbackGroupRecord::findOne(['id' => $group->id]);
                if (!$record) {
                    throw new CallbackException("No callback group exists due ID: “{$group->id}“");
                }
            }

            $record->name = $group->name;
            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_groups = null;
        $this->getAllGroups();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_GROUP)) {
            $this->trigger(self::EVENT_AFTER_SAVE_GROUP, new CallbackGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        return true;
    }

    /**
     * Delete a group.
     *
     * @param CallbackGroup $group
     * @return bool
     */
    public function deleteGroup(CallbackGroup $group): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_GROUP)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_GROUP, new CallbackGroupEvent([
                'group' => $group,
            ]));
        }

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete(Table::CALLBACKGROUPS, [
                    'id' => $group->id,
                ])->execute();

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_GROUP)) {
            $this->trigger(self::EVENT_AFTER_DELETE_GROUP, new CallbackGroupEvent([
                'group' => $group,
            ]));
        }

        return true;
    }

    // Callbacks
    // -------------------------------------------------------------------------

    /**
     * @return Callback[]
     */
    public function getAllCallbacks(): array
    {
        if ($this->_callbacks !== null) {
            return $this->_callbacks;
        }

        $this->_callbacks = [];

        $results = (new Query())
            ->select(['id', 'groupId', 'name', 'handle', 'code'])
            ->from(Table::CALLBACKS)
            ->all();

        foreach ($results as $result) {
            $this->_callbacks[] = $this->createCallback($result);
        }

        return $this->_callbacks;
    }

    /**
     * @param int $groupId
     * @return Callbacks[]
     */
    public function getCallbacksByGroupId(int $groupId): array
    {
        return ArrayHelper::filterByValue($this->getAllCallbacks(), 'groupId', $groupId);
    }

    /**
     * @param int $callbackId
     * @return Callback|null
     */
    public function getCallbackById(int $callbackId)
    {
        return ArrayHelper::firstWhere($this->getAllCallbacks(), 'id', $callbackId);
    }

    /**
     * @param string $handle
     * @return Callback|null
     */
    public function getCallbackByHandle(string $handle)
    {
        return ArrayHelper::firstWhere($this->getAllCallbacks(), 'handle', $handle);
    }

    /**
     * @param string $code
     * @return Callback|null
     */
    public function getCallbackByCode(string $code)
    {
        return ArrayHelper::firstWhere($this->getAllCallbacks(), 'code', $code);
    }

    /**
     * @param mixed $config
     * @return Callback
     */
    public function createCallback($config)
    {
        return new Callback($config);
    }

    /**
     * @param Callback $callback
     * @param bool $runValidation
     * @return bool
     */
    public function saveCallback(Callback $callback, bool $runValidation = true): bool
    {
        $isNewCallback = !$callback->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_CALLBACK)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_CALLBACK, new CallbackEvent([
                'callback' => $callback,
                'isNew' => $isNewCallback,
            ]));
        }

        if ($runValidation && !$callback->validate()) {
            Craft::info('Callback not saved due to validation error.', __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if ($isNewCallback) {
                $record = new CallbackRecord();
            } else {
                $record = CallbackRecord::findOne(['id' => $callback->id]);
                if (!$record) {
                    throw new CallbackException("No callback exists due ID: “{$callback->id}“");
                }
            }

            $record->groupId = $callback->groupId;
            $record->name = $callback->name;
            $record->handle = $callback->handle;
            $record->code = $callback->code;
            $record->save(false);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_callbacks = null;
        $this->getAllCallbacks();

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_CALLBACK)) {
            $this->trigger(self::EVENT_AFTER_SAVE_CALLBACK, new CallbackEvent([
                'callback' => $callback,
                'isNew' => $isNewCallback,
            ]));
        }

        return true;
    }

    /**
     * @param Callback $callback
     * @return bool
     */
    public function deleteCallback(Callback $callback): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_CALLBACK)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_CALLBACK, new CallbackEvent([
                'callback' => $callback,
            ]));
        }

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()
                ->delete(Table::CALLBACKS, [
                    'id' => $callback->id,
                ])
                ->execute();

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_CALLBACK)) {
            $this->trigger(self::EVENT_AFTER_DELETE_CALLBACK, new CallbackEvent([
                'callback' => $callback,
            ]));
        }

        return true;
    }

    // Requests
    // -------------------------------------------------------------------------

    /**
     * @param mixed $criteria
     * @return int
     */
    public function getTotalRequests($criteria = null): int
    {
        if (!$criteria instanceof CallbackRequestCriteria) {
            $criteria = Craft::createObject($criteria);
        }

        $query = $this->_createRequestQuery()
            ->offset($criteria->offset)
            ->limit($criteria->limit);

        $this->_applyRequestQueryConditions($query, $criteria);

        return $query->count('[[requests.id]]');
    }

    /**
     * @param mixed $criteria
     * @return CallbackRequest[]
     */
    public function findRequests($criteria = null): array
    {
        if (!$criteria instanceof CallbackRequestCriteria) {
            $criteria = Craft::createObject($criteria);
        }

        $query = $this->_createRequestQuery()
            ->orderBy($criteria->order)
            ->offset($criteria->offset)
            ->limit($criteria->limit);

        $this->_applyRequestQueryConditions($query, $criteria);

        $requests = [];

        foreach ($query->all() as $result) {
            $requests[] = $this->createRequest($result);
        }

        return $requests;
    }

    /**
     * @param mixed $criteria
     * @return CallbackRequest|null
     */
    public function findRequest($criteria = null)
    {
        if (!$criteria instanceof CallbackRequestCriteria) {
            $criteria = Craft::createObject($criteria);
        }

        $criteria->limit = 1;

        $results = $this->findRequests($criteria);
        if (!empty($results)) {
            return array_pop($results);
        }

        return null;
    }

    /**
     * @param int $requestId
     * @return CallbackRequest|null
     */
    public function getRequestById(int $requestId)
    {
        $result = $this->_createRequestQuery()
            ->where(['id' => $requestId])
            ->one();

        return $result ? $this->createRequest($result) : null;
    }

    /**
     * @param mixed $config
     * @return CallbackRequest
     */
    public function createRequest($config): CallbackRequest
    {
        return new CallbackRequest($config);
    }

    /**
     * @param CallbackRequest $request
     * @param bool $runValidation
     * @return bool
     */
    public function saveRequest(CallbackRequest $request, bool $runValidation = true): bool
    {
        return true;
    }

    /**
     * @param CallbackRequest $request
     * @return bool
     */
    public function deleteRequest(CallbackRequest $request): bool
    {
        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createRequestQuery(): Query
    {
        return (new Query())
            ->select([
                'requests.id',
                'requests.callbackId',
                'requests.corporationId',
                'requests.data',
                'requests.postDate',
                'requests.attempts',
                'requests.handled',
                'requests.handledDate',
                'requests.handleFailedReason',
            ])
            ->from(['requests' => Table::CALLBACKREQUESTS]);
    }

    /**
     * @param Query $query
     * @param CallbackRequestCriteria $criteria
     */
    private function _applyRequestQueryConditions(Query $query, CallbackRequestCriteria $criteria)
    {

    }
}