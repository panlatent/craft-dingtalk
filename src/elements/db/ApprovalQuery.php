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
    // Traits
    // =========================================================================

    use CorporationQueryTrait;

    // Properties
    // =========================================================================

    /**
     * @var int[]|int|null
     */
    public $processId;

    /**
     * @var string[]|string|null
     */
    public $instanceId;

    /**
     * @var int[]|int|null
     */
    public $originatorUserId;

    /**
     * @var int[]|int|null
     */
    public $originatorDepartmentId;

    /**
     * @var bool|null
     */
    public $isAgree;

    /**
     * @var mixed|null
     */
    public $startDate;

    /**
     * @var mixed|null
     */
    public $finishDate;

    // Public Methods
    // =========================================================================

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
     * @param int[]|int|null $value
     * @return $this
     */
    public function originatorUserId($value)
    {
        $this->originatorUserId = $value;

        return $this;
    }

    /**
     * @param int[]|int|null $value
     * @return $this
     */
    public function originatorDepartmentId($value)
    {
        $this->originatorDepartmentId = $value;

        return $this;
    }

    /**
     * @param  bool|null $value
     * @return ApprovalQuery
     */
    public function isAgree(bool $value = null)
    {
        $this->isAgree = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function startDate($value)
    {
        $this->startDate = $value;

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function finishDate($value)
    {
        $this->finishDate = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforePrepare(): bool
    {
        $this->joinElementTable('dingtalk_approvals');

        $this->query->addSelect([
            'dingtalk_approvals.corporationId',
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

        if ($this->originatorUserId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_approvals.originatorUserId', $this->originatorUserId));
        }

        if ($this->originatorDepartmentId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_approvals.originatorDepartmentId', $this->originatorDepartmentId));
        }

        if ($this->isAgree !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_approvals.isAgree', (bool)$this->isAgree));
        }

        if ($this->startDate) {
            $this->subQuery->andWhere(Db::parseDateParam('dingtalk_approvals.startDate', $this->startDate));
        }

        if ($this->finishDate) {
            $this->subQuery->andWhere(Db::parseDateParam('dingtalk_approvals.finishDate', $this->finishDate));
        }

        $this->_applyCorporationParam('dingtalk_approvals.corporationId');

        return parent::beforePrepare();
    }
}