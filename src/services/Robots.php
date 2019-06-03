<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\ArrayHelper;
use craft\helpers\Component as ComponentHelper;
use panlatent\craft\dingtalk\base\Robot;
use panlatent\craft\dingtalk\base\RobotInterface;
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\errors\RobotException;
use panlatent\craft\dingtalk\records\Robot as RobotRecord;
use panlatent\craft\dingtalk\records\RobotWebhook as RobotWebhookRecord;
use panlatent\craft\dingtalk\robots\ChatNoticeRobot;
use panlatent\craft\dingtalk\robots\MissingRobot;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Robots
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Robots extends Component
{
    /**
     * @event \craft\events\RegisterComponentTypesEvent
     */
    const EVENT_REGISTER_ROBOT_TYPES = 'registerRobotTypes';

    /**
     * @var bool
     */
    private $_fetchedAllRobots = false;

    /**
     * @var RobotInterface[]|null
     */
    private $_robotsById;

    /**
     * @var RobotInterface[]|null
     */
    private $_robotsByHandle;

    /**
     * @return string[]
     */
    public function getAllRobotTypes(): array
    {
        $types = [
            ChatNoticeRobot::class,
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(static::EVENT_REGISTER_ROBOT_TYPES, $event);

        return $event->types;
    }

    /**
     * @return RobotInterface[]
     */
    public function getAllRobots(): array
    {
        if ($this->_fetchedAllRobots) {
            return array_values($this->_robotsById);
        }

        $this->_robotsById = [];
        $this->_robotsByHandle = [];

        $results = $this->_createQuery()->all();
        foreach ($results as $result) {
            /** @var Robot $robot */
            $robot = $this->createRobot($result);
            $this->_robotsById[$robot->id] = $robot;
            $this->_robotsByHandle[$robot->handle] = $robot;
        }

        $this->_fetchedAllRobots = true;

        return array_values($this->_robotsById);
    }

    /**
     * @param int $robotId
     * @return RobotInterface|null
     */
    public function getRobotById(int $robotId)
    {
        if ($this->_robotsById && array_key_exists($robotId, $this->_robotsById)) {
            return $this->_robotsById[$robotId];
        }

        if ($this->_fetchedAllRobots) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['id' => $robotId])
            ->one();

        return $this->_robotsById[$robotId] = $result ? $this->createRobot($result) : null;
    }

    /**
     * @param string $handle
     * @return RobotInterface|null
     */
    public function getRobotByHandle(string $handle)
    {
        if ($this->_robotsByHandle && array_key_exists($handle, $this->_robotsByHandle)) {
            return $this->_robotsByHandle[$handle];
        }

        if ($this->_fetchedAllRobots) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['handle' => $handle])
            ->one();

        return $this->_robotsByHandle[$handle] = $result ? $this->createRobot($result) : null;
    }

    /**
     * @param mixed $config
     * @return RobotInterface
     */
    public function createRobot($config): RobotInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $robot = ComponentHelper::createComponent($config, RobotInterface::class);
        } catch (MissingComponentException $exception) {
            unset($config['type']);
            $robot = new MissingRobot($config);
        }

        return $robot;
    }

    /**
     * @param RobotInterface $robot
     * @param bool $runValidation
     * @return bool
     */
    public function saveRobot(RobotInterface $robot, bool $runValidation = true): bool
    {
        /** @var Robot $robot */
        $isNewRobot = $robot->getIsNew();

        if (!$robot->beforeSave($isNewRobot)) {
            return false;
        }

        if ($runValidation && !$robot->validate()) {
            Craft::info('Robot not saved due to validation error.', __METHOD__);
            return false;
        }

        $db = Craft::$app->getDb();

        $translation = $db->beginTransaction();
        try {
            $robotRecord = $this->_getRobotRecordById($robot->id);

            $robotRecord->handle = $robot->handle;
            $robotRecord->name = $robot->name;
            $robotRecord->type = get_class($robot);
            $robotRecord->settings = $robot->getSettings();

            $robotRecord->save(false);

            if ($isNewRobot) {
                $robot->id = $robotRecord->id;
            }

            if (!$isNewRobot) {
                $oldWebhookIds = RobotWebhookRecord::find()
                    ->select('id')
                    ->where(['robotId' => $robot->id])
                    ->column();
            }

            $webhooks = $robot->getWebhooks();
            foreach ($webhooks as $webhook) {
                RobotWebhookRecord::find()
                    ->select('id')
                    ->where(['robotId' => $robot->id])
                    ->column();

                $db->createCommand()->upsert(Table::ROBOTWEBHOOKS, [
                    'robotId' => $robot->id,
                    'name' => $webhook->name,
                    'url' => $webhook->url,
                    'rateLimit' => $webhook->rateLimit,
                    'rateWindow' => $webhook->rateWindow,
                    'enabled' => (bool)$webhook->enabled,
                ], [
                    'id' => $webhook->id,
                    'name' => $webhook->name,
                    'url' => $webhook->url,
                    'rateLimit' => $webhook->rateLimit,
                    'rateWindow' => $webhook->rateWindow,
                    'enabled' => (bool)$webhook->enabled,
                ])->execute();
            }

            if (!$isNewRobot && isset($oldWebhookIds)) {
                $deleteWebhookIds = [];
                $webhookIds = ArrayHelper::getColumn($webhooks, 'id');
                $webhookIds = array_filter($webhookIds);
                foreach ($oldWebhookIds as $oldWebhookId) {
                    if (!in_array($oldWebhookId, $webhookIds)) {
                        $deleteWebhookIds[] = $oldWebhookId;
                    }
                }

                if ($deleteWebhookIds) {
                    $db->createCommand()->delete(Table::ROBOTWEBHOOKS, [
                        'id' => $deleteWebhookIds,
                    ])->execute();
                }
            }

            $robot->afterSave($isNewRobot);

            $translation->commit();
        } catch (\Throwable $exception) {
            $translation->rollBack();

            throw $exception;
        }

        $this->_robotsById[$robot->id] = $robot;
        $this->_robotsByHandle[$robot->handle] = $robot;

        return true;
    }

    /**
     * @param RobotInterface $robot
     * @return bool
     */
    public function deleteRobot(RobotInterface $robot): bool
    {
        /** @var Robot $robot */
        if (!$robot->beforeDelete()) {
            return false;
        }

        $translation = Craft::$app->db->beginTransaction();
        try {

            Craft::$app->db->createCommand()
                ->delete(Table::ROBOTS, ['id' => $robot->id])
                ->execute();

            $robot->afterDelete();

            $translation->commit();
        } catch (\Throwable $exception) {
            $translation->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * @return Query
     */
    private function _createQuery(): Query
    {
        return (new Query())
            ->select(['id', 'handle', 'name', 'type', 'settings'])
            ->from(Table::ROBOTS);
    }

    /**
     * @param int|null $robotId
     * @return RobotRecord
     */
    private function _getRobotRecordById(int $robotId = null): RobotRecord
    {
        if ($robotId !== null) {
            $robotRecord = RobotRecord::findOne(['id' => $robotId]);

            if (!$robotRecord) {
                throw new RobotException(Craft::t('dingtalk', 'No robot exists with the ID “{id}”.', ['id' => $robotId]));
            }
        } else {
            $robotRecord = new RobotRecord();
        }

        return $robotRecord;
    }
}