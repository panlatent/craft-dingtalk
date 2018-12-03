<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use panlatent\craft\dingtalk\elements\Approval;

/**
 * Class ApprovalQuery
 *
 * @package panlatent\craft\dingtalk\elements\db
 * @method Approval all($db = null)
 * @method Approval one($db = null)
 * @author Panlatent <panlatent@gmail.com>
 */
class ApprovalQuery extends ElementQuery
{
    /**
     * @var int[]|int|null
     */
    public $processId;

    /**
     * @var string[]|string|null
     */
    public $instanceId;

    /**
     * @param int[]|int|null $value
     * @return $this
     */
    public function processId($value)
    {
        $this->processId = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function instanceId($value)
    {
        $this->instanceId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforePrepare(): bool
    {
        $this->joinElementTable('dingtalk_approvals');

        $this->query->addSelect([
            'dingtalk_approvals.processId',
            'dingtalk_approvals.businessId',
            'dingtalk_approvals.instanceId',
            'dingtalk_approvals.originatorUserId',
            'dingtalk_approvals.originatorDepartmentId',
            'dingtalk_approvals.title',
            'dingtalk_approvals.approveUserIds',
            'dingtalk_approvals.ccUserIds',
            'dingtalk_approvals.attachedInstanceIds',
            'dingtalk_approvals.isAgree',
            'dingtalk_approvals.bizAction',
            'dingtalk_approvals.formValues',
            'dingtalk_approvals.operationRecords',
            'dingtalk_approvals.tasks',
            'dingtalk_approvals.status',
            'dingtalk_approvals.startDate',
            'dingtalk_approvals.finishDate',
        ]);

        if ($this->processId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_approvals.processId', $this->processId));
        }

        if ($this->instanceId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_approvals.instanceId', $this->instanceId));
        }

        return parent::beforePrepare();
    }
}