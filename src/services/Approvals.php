<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use craft\helpers\ArrayHelper;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\User;
use yii\base\Component;

/**
 * Class Approvals
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Approvals extends Component
{
    public function pullApprovals(ProcessInterface $process)
    {

    }

    /**
     * @param Approval $approval
     * @param array $config
     * @return bool
     */
    public function loadApprovalByApi(Approval $approval, array $config): bool
    {
        $originatorUserId = User::find()
            ->select(['elements.id'])
            ->userId(ArrayHelper::remove($config, 'originator_userid'))
            ->scalar();

        if (empty($originatorUserId)) {
            return false;
        }

        $approval->load([
            'title' => ArrayHelper::remove($config, 'title'),
            'originatorUserId' => $originatorUserId,
            'originatorDepartmentId' => ArrayHelper::remove($config, 'originator_dept_id'),
            'approveUserIds' => ArrayHelper::remove($config, 'approver_userids'),
            'ccUserIds' => ArrayHelper::remove($config, 'cc_userids'),
            'isAgree' => ArrayHelper::remove($config, 'result') == 'agree',
            'businessId' => ArrayHelper::remove($config, 'business_id'),
            'formValues' => ArrayHelper::remove($config, 'form_component_values'),
            'operationRecords' => ArrayHelper::remove($config, 'operation_records'),
            'tasks' => ArrayHelper::remove($config, 'tasks'),
            'bizAction' => ArrayHelper::remove($config, 'biz_action'),
            'attachedInstanceIds' => ArrayHelper::remove($config, 'attached_process_instance_ids'),
            'status' => ArrayHelper::remove($config, 'status'),
            'startDate' => ArrayHelper::remove($config, 'create_time'),
            'finishDate' => ArrayHelper::remove($config, 'finish_time'),
        ], '');

        return true;
    }
}