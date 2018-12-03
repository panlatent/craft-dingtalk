<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use craft\validators\DateTimeValidator;
use DateTime;
use panlatent\craft\dingtalk\base\Process;
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\elements\db\ApprovalQuery;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\Approval as ApprovalRecord;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;

/**
 * Class Approval
 *
 * @package panlatent\craft\dingtalk\elements
 * @property Process $process
 * @property User $originatorUser
 * @property Department $originatorDepartment
 * @property array $formValues
 * @author Panlatent <panlatent@gmail.com>
 */
class Approval extends Element
{
    // Statuses
    // -------------------------------------------------------------------------

    const STATUS_NEW = 'new';
    const STATUS_RUNNING = 'running';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';

    /**
     * @return ApprovalQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new ApprovalQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            static::STATUS_COMPLETED => ['label' => Craft::t('dingtalk', 'Completed'), 'color' => 'green'],
            static::STATUS_NEW => ['label' => Craft::t('dingtalk', 'New'), 'color' => 'blue'],
            static::STATUS_RUNNING => ['label' => Craft::t('dingtalk', 'Running'), 'color' => 'blue'],
            static::STATUS_TERMINATED => ['label' => Craft::t('dingtalk', 'Terminated'), 'color' => 'red'],
            static::STATUS_DISABLED => ['label' => Craft::t('app', 'Disabled'), 'color' => 'red'],
            static::STATUS_CANCELED => ['label' => Craft::t('dingtalk', 'Canceled')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('dingtalk', 'All approvals'),
                'criteria' => [],
            ],
            ['heading' => '审批流程']
        ];

        $processes = Plugin::$plugin->processes->getAllProcesses();
        foreach ($processes as $process) {
            /** @var Process $process */
            $sources[] = [
                'key' => 'process:' . $process->id,
                'label' => $process->name,
                'criteria' => [
                    'processId' => $process->id,
                ],
            ];
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('app', 'Title')],
            'businessId' => ['label' => Craft::t('dingtalk', 'Business Id')],
            'instanceId' => ['label' => Craft::t('dingtalk', 'Instance Id')],
            'originatorUser' => ['label' => Craft::t('dingtalk', 'Originator User')],
            'originatorDepartment' => ['label' => Craft::t('dingtalk', 'Originator Department')],
            'isAgree' => ['label' => Craft::t('dingtalk', 'Is Agree')],
            'status' => ['label' => Craft::t('app', 'Status')],
            'startDate' => ['label' => Craft::t('dingtalk', 'Start Date')],
            'finishDate' => ['label' => Craft::t('dingtalk', 'Finish Date')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['businessId', 'originatorUser', 'originatorDepartment', 'isAgree', 'status', 'startDate'];
    }

    /**
     * @var int|null 审批流程 ID
     */
    public $processId;

    /**
     * @var string|null 实例 ID
     */
    public $instanceId;

    /**
     * @var string|null 发起人
     */
    public $originatorUserId;

    /**
     * @var string|null 发起部门ID
     */
    public $originatorDepartmentId;

    /**
     * @var string|null 审批人 ID 列表
     */
    public $approveUserIds;

    /**
     * @var string|null 抄送人 ID 列表
     */
    public $ccUserIds;

    /**
     * @var bool|null 审批结果，是否同意
     */
    public $isAgree;

    /**
     * @var string|null 审批实例业务编号
     */
    public $businessId;

    /**
     * @var array|null 操作记录列表
     */
    public $operationRecords;

    /**
     * @var array|null 任务列表
     */
    public $tasks;

    /**
     * @var string|null 审批实例业务动作，MODIFY表示该审批实例是基于原来的实例修改而来，
     * REVOKE表示该审批实例是由原来的实例撤销后重新发起的，NONE表示正常发起
     */
    public $bizAction;

    /**
     * @var array|null 审批附属实例列表，当已经通过的审批实例被修改或撤销，会生成一个新的实例，
     * 作为原有审批实例的附属。如果想知道当前已经通过的审批实例的状态，可以依次遍历它的附属列表，
     * 查询里面每个实例的biz_action
     */
    public $attachedInstanceIds;

    /**
     * @var DateTime|null 审批开始时间
     */
    public $startDate;

    /**
     * @var DateTime|null  审批结束时间
     */
    public $finishDate;

    /**
     * @var string|null
     */
    private $_status;

    /**
     * @var array|null 表单值
     */
    private $_formValues = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules = array_merge($rules, [
            [['processId', 'instanceId', 'originatorUserId', 'originatorDepartmentId', 'businessId', 'status'], 'required'],
            [['id', 'processId', 'originatorUserId', 'originatorDepartmentId'], 'integer'],
            [['isAgree'], 'boolean'],
            [['title', 'status'], 'string'],
            [['startDate', 'finishDate'], DateTimeValidator::class],
            [['approveUserIds', 'ccUserIds', 'attachedInstanceIds', 'bizAction', 'formValues', 'operationRecords', 'tasks'], 'safe'],
        ]);

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'startDate';
        $attributes[] = 'finishDate';
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $approvalRecord = ApprovalRecord::findOne(['id' => $this->id]);
            if (!$approvalRecord) {
                throw new Exception('Invalid approval ID: ' . $this->id);
            }
        } else {
            $approvalRecord = new ApprovalRecord();
        }

        $approvalRecord->id = $this->id;
        $approvalRecord->processId = $this->processId;
        $approvalRecord->businessId = $this->businessId;
        $approvalRecord->instanceId = $this->instanceId;
        $approvalRecord->originatorUserId = $this->originatorUserId;
        $approvalRecord->originatorDepartmentId = $this->originatorDepartmentId;
        $approvalRecord->title = $this->title;
        $approvalRecord->approveUserIds = $this->approveUserIds ? Json::encode($this->approveUserIds) : null;
        $approvalRecord->ccUserIds = $this->ccUserIds ? Json::encode($this->ccUserIds) : null;
        $approvalRecord->isAgree = (bool)$this->isAgree;
        $approvalRecord->formValues = $this->_formValues ? Json::encode($this->_formValues) : null;
        $approvalRecord->operationRecords = $this->operationRecords ? Json::encode($this->operationRecords) : null;
        $approvalRecord->tasks = $this->tasks ? Json::encode($this->tasks) : null;
        $approvalRecord->bizAction = $this->bizAction ? ucfirst(strtolower($this->bizAction)) : null;
        $approvalRecord->attachedInstanceIds = $this->attachedInstanceIds ? Json::encode($this->attachedInstanceIds) : null;
        $approvalRecord->startDate = $this->startDate;
        $approvalRecord->finishDate = $this->finishDate;

        if ($this->_status) {
            $approvalRecord->status = ucfirst($this->_status);
        }

        $approvalRecord->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        if ($this->archived) {
            return self::STATUS_ARCHIVED;
        }

        if (!$this->enabled || !$this->enabledForSite) {
            return self::STATUS_DISABLED;
        }

        return $this->_status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $status = strtolower($status);
        if (!in_array($status, array_keys(static::statuses()))) {
            throw new InvalidArgumentException('Invalid status argument');
        }

        if ($status === static::STATUS_DISABLED) {
            return;
        }

        $this->_status = $status;
    }

    /**
     * @return ProcessInterface
     */
    public function getProcess()
    {
        if (!$this->processId) {
            throw new InvalidConfigException();
        }

        return Plugin::$plugin->processes->getProcessById($this->processId);
    }

    /**
     * @return User
     */
    public function getOriginatorUser()
    {
        if (!$this->originatorUserId) {
            throw new InvalidConfigException();
        }

        return User::find()->id($this->originatorUserId)->one();
    }

    /**
     * @return Department
     */
    public function getOriginatorDepartment()
    {
        if (!$this->originatorDepartmentId) {
            throw new InvalidConfigException();
        }

        return Plugin::$plugin->departments->getDepartmentById($this->originatorDepartmentId);
    }

    /**
     * @return array
     */
    public function getFormValues(): array
    {
        return $this->_formValues;
    }

    /**
     * @param array|string|null $formValues
     */
    public function setFormValues($formValues)
    {
        if (is_string($formValues)) {
            $formValues = Json::decode($formValues);
        }

        $this->_formValues = $formValues ?? [];
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'isAgree':
                return $this->$attribute ? '<span data-icon="check"></span>' : '';
            case 'status':
                return Craft::t('dingtalk', ucwords($this->$attribute));
        }

        return parent::tableAttributeHtml($attribute);
    }
}