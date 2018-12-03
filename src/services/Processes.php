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
use craft\helpers\Component as ComponentHelper;
use craft\helpers\Json;
use panlatent\craft\dingtalk\base\Process;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\errors\ProcessException;
use panlatent\craft\dingtalk\processes\BasicProcess;
use panlatent\craft\dingtalk\processes\MissingProcess;
use panlatent\craft\dingtalk\records\Process as ProcessRecord;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Processes
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Processes extends Component
{
    const EVENT_REGISTER_PROCESS_TYPES = 'registerProcessTypes';

    /**
     * @var bool
     */
    private $_fetchedAllProcesses = false;

    /**
     * @var ProcessInterface[]|null
     */
    private $_processesById;

    /**
     * @var ProcessInterface[]|null
     */
    private $_processesByHandle;

    /**
     * @return string[]
     */
    public function getAllProcessTypes(): array
    {
        $types = [
            BasicProcess::class,
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types
        ]);

        $this->trigger(static::EVENT_REGISTER_PROCESS_TYPES, $event);

        return $event->types;
    }

    /**
     * @return ProcessInterface[]
     */
    public function getAllProcesses(): array
    {
        if ($this->_fetchedAllProcesses) {
            return array_values($this->_processesById);
        }

        $this->_processesById = [];
        $this->_processesByHandle = [];

        $results = $this->_createProcessQuery()->all();
        foreach ($results as $result) {
            /** @var Process $process */
            $process = $this->createProcess($result);
            $this->_processesById[$process->id] = $process;
            $this->_processesByHandle[$process->handle] = $process;
        }

        $this->_fetchedAllProcesses = true;

        return array_values($this->_processesById);
    }

    /**
     * @param int $processId
     * @return ProcessInterface|null
     */
    public function getProcessById(int $processId)
    {
        if ($this->_processesById && array_key_exists($processId, $this->_processesById)) {
            return $this->_processesById[$processId];
        }

        if ($this->_fetchedAllProcesses) {
            return null;
        }

        $result = $this->_createProcessQuery()
            ->where(['id' => $processId])
            ->one();

        return $this->_processesById[$processId] = $result ? $this->createProcess($result) : null;
    }

    /**
     * @param string $handle
     * @return ProcessInterface|null
     */
    public function getProcessByHandle(string $handle)
    {
        if ($this->_processesByHandle&& array_key_exists($handle, $this->_processesByHandle)) {
            return $this->_processesByHandle[$handle];
        }

        if ($this->_fetchedAllProcesses) {
            return null;
        }

        $result = $this->_createProcessQuery()
            ->where(['handle' => $handle])
            ->one();

        return $this->_processesByHandle[$handle] = $result ? $this->createProcess($result) : null;
    }

    /**
     * @param mixed $config
     * @return ProcessInterface
     */
    public function createProcess($config): ProcessInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $process = ComponentHelper::createComponent($config, ProcessInterface::class);
        } catch (MissingComponentException $exception) {
            unset($config['type']);
            $process = new MissingProcess($config);
        }

        return $process;
    }

    /**
     * @param ProcessInterface $process
     * @param bool $runValidation
     * @return bool
     */
    public function saveProcess(ProcessInterface $process, bool $runValidation = true): bool
    {
        /** @var Process $process */
        $isNewProcess = $process->getIsNew();

        if (!$process->beforeSave($isNewProcess)) {
            return false;
        }

        if ($runValidation && !$process->validate()) {
            Craft::info('Process not saved due to validation error.', __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewProcess) {
                $processRecord = ProcessRecord::findOne(['id' => $process->id]);
                if (!$processRecord) {
                    throw new ProcessException("No volume exists with the ID “{$process->id}”");
                }
            } else {
                $processRecord = new ProcessRecord();
            }

            $processRecord->name = $process->name;
            $processRecord->handle = $process->handle;
            $processRecord->code = $process->code;
            $processRecord->type = get_class($process);
            $processRecord->settings = Json::encode($process->getSettings());
            $processRecord->sortOrder = $process->sortOrder;

            $processRecord->save(false);

            if ($isNewProcess) {
                $process->id = $processRecord->id;
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_processesById[$process->id] = $process;
        $this->_processesByHandle[$process->handle] = $process;

        return true;
    }

    /**
     * @param ProcessInterface $process
     * @return bool
     */
    public function deleteProcess(ProcessInterface $process): bool
    {
        /** @var Process $process */
        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->delete('{{%dingtalk_processes}}', [
                'id' => $process->id,
            ])->execute();

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * @return Query
     */
    private function _createProcessQuery(): Query
    {
        return (new Query())
            ->select(['id', 'fieldLayoutId', 'name', 'handle', 'type', 'code', 'settings'])
            ->from('{{%dingtalk_processes}}')
            ->orderBy('sortOrder');
    }
}