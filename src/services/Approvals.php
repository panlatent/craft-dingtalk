<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use DateTime;
use DateTimeZone;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;

/**
 * Class Approvals
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Approvals extends Component
{
    /**
     * @param Approval $approval
     * @param array $config
     * @return bool
     */
    public function loadApprovalByApi(Approval $approval, array $config): bool
    {
        $timezone = new DateTimeZone('Asia/Shanghai');

        $originatorDepartmentId = ArrayHelper::remove($config, 'originator_dept_id');
        if ($originatorDepartmentId != '-1') {
            $criteria = [
                'corporationId' => $approval->corporationId,
                'dingDepartmentId' => $originatorDepartmentId,
            ];
        } else {
            $criteria = [
                'corporationId' => $approval->corporationId,
                'root' => true,
            ];
        }
        $department = Plugin::$dingtalk
            ->getDepartments()
            ->findDepartment($criteria);

        $approval->load([
            'title' => ArrayHelper::remove($config, 'title'),
            'originatorUserId' => User::find()
                ->select(['elements.id'])
                ->userId(ArrayHelper::remove($config, 'originator_userid'))
                ->scalar(),
            'originatorDepartmentId' => $department->id ?? null,
            'approveUserIds' => User::find()
                ->select(['elements.id'])
                ->userId(ArrayHelper::remove($config, 'approver_userids'))
                ->column(),
            'ccUserIds' => User::find()
                ->select(['elements.id'])
                ->userId(ArrayHelper::remove($config, 'cc_userids'))
                ->column(),
            'isAgree' => ArrayHelper::remove($config, 'result') == 'agree',
            'businessId' => ArrayHelper::remove($config, 'business_id'),
            'formValues' => $this->_normalizeFormValues(ArrayHelper::remove($config, 'form_component_values')),
            'operationRecords' => ArrayHelper::remove($config, 'operation_records'),
            'tasks' => ArrayHelper::remove($config, 'tasks'),
            'bizAction' => ArrayHelper::remove($config, 'biz_action'),
            'attachedInstanceIds' => ArrayHelper::remove($config, 'attached_process_instance_ids'),
            'status' => ArrayHelper::remove($config, 'status'),
            'startDate' => new DateTime(ArrayHelper::remove($config, 'create_time'), $timezone),
            'finishDate' => new DateTime(ArrayHelper::remove($config, 'finish_time'), $timezone),
        ], '');

        return true;
    }

    /**
     * @param array $values
     * @return array
     */
    private function _normalizeFormValues(array $values): array
    {
        foreach ($values as &$value) {
            $value['value'] = Json::decodeIfJson($value['value']);
        }

        return $values;
    }
}