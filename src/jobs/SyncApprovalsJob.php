<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\jobs;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\queue\BaseJob;
use DateTime;
use panlatent\craft\dingtalk\base\Process;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

/**
 * 同步钉钉审批任务
 *
 * @package panlatent\craft\dingtalk\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncApprovalsJob extends BaseJob
{
    // Traits
    // =========================================================================

    use CorporationJobTrait;

    // Properties
    // =========================================================================

    /**
     * @var ProcessInterface|string|int
     */
    public $process;

    /**
     * @var DateTime|string|int|null
     */
    public $startTime = '2000-01-01';

    /**
     * @var DateTime|string|int|null
     */
    public $endTime;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $approvals = Plugin::$dingtalk->approvals;
        $elements = Craft::$app->getElements();
        $remote = $this->getProcess()->getCorporation()->getRemote();

        /** @var Process $process */
        $process = $this->getProcess();

        Craft::info("Sync process {$process->name} instances from {$this->getStartTime()->format('Ymd')} to {$this->getEndTime()->format('Ymd')}", __METHOD__);

        $ids = $remote->getProcessInstanceIds($process->code, $this->getStartTime()->getTimestamp(), $this->getEndTime()->getTimestamp());
        foreach ($ids as $id) {
            $result = $remote->getProcessInstanceById($id);

            if (!($approval = Approval::find()
                ->processId($process->id)
                ->instanceId($id)
                ->one())) {

                $approval = new Approval();
                $approval->corporationId = $process->corporationId;
            }

            $approval->processId = $process->id;
            $approval->instanceId = $id;
            if (!$approvals->loadApprovalByApi($approval, $result)) {
                Craft::warning("Couldn‘t load approval from api data with the instance ID: “{$id}“.", __METHOD__);
                continue;
            }

            if (!$elements->saveElement($approval)) {
                Craft::warning("Couldn‘t save approval with the instance ID: “{$id}“. " . Json::encode($approval->getErrors()), __METHOD__);
            }
        }
    }

    /**
     * @return ProcessInterface
     */
    public function getProcess(): ProcessInterface
    {
        if ($this->process instanceof ProcessInterface) {
            return $this->process;
        }

        $process = $this->process;

        if (ctype_alnum((string)$this->process)) {
            $process = Plugin::$dingtalk->getProcesses()->getProcessById($process);
        } else {
            $process = Plugin::$dingtalk->getProcesses()->getProcessByHandle((string)$process);
        }

        if (!$process instanceof ProcessInterface) {
            throw new InvalidConfigException("Invalid process");
        }

        return $this->process = $process;
    }

    /**
     * @return DateTime
     */
    public function getStartTime(): DateTime
    {
        return DateTimeHelper::toDateTime($this->startTime) ?: new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getEndTime(): DateTime
    {
        return DateTimeHelper::toDateTime($this->endTime) ?: new DateTime();
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Process Approvals');
    }
}