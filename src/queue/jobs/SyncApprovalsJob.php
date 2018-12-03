<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\queue\jobs;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\queue\BaseJob;
use DateTime;
use panlatent\craft\dingtalk\base\Process;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;

class SyncApprovalsJob extends BaseJob
{
    /**
     * @var Process|string|int
     */
    public $process;

    /**
     * @var \DateTime|string|int|null
     */
    public $startTime = '1970-01-01';

    /**
     * @var \DateTime|string|int|null
     */
    public $endTime;

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $api = Plugin::$plugin->api;
        $approvals = Plugin::$plugin->approvals;
        $elements = Craft::$app->getElements();
        $processes = Plugin::$plugin->processes;

        $startTime = DateTimeHelper::toDateTime($this->startTime) ?: new DateTime('1970-01-01');
        $endTime = DateTimeHelper::toDateTime($this->endTime) ?: new DateTime();

        $process = $this->process;
        if (is_string($process)) {
            $process = $processes->getProcessByHandle($process);
        } elseif (is_int($this->process)) {
            $process = $processes->getProcessById($process);
        }

        if (!$process instanceof ProcessInterface) {
            throw new InvalidConfigException("No a valid process exists");
        }

        $ids = $api->getProcessInstanceIds($this->process->code, $startTime->getTimestamp(), $endTime->getTimestamp());

        foreach ($ids as $id) {
            $result = $api->getProcessInstanceById($id);

            if (!($approval = Approval::find()
                ->processId($this->process->id)
                ->instanceId($id)
                ->one())) {
                $approval = new Approval();
            }

            $approval->processId = $this->process->id;
            $approval->instanceId = $id;
            if (!$approvals->loadApprovalByApi($approval, $result)) {
                Craft::warning("Couldn‘t load approval from api data with the instance ID: “{$id}“.", __METHOD__);
                continue;
            }

            if (!$elements->saveElement($approval)) {
                Craft::warning("Couldn‘t save approval element with the instance ID: “{$id}“.", __METHOD__);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Process Approvals');
    }
}