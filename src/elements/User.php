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
use DateTime;
use panlatent\craft\dingtalk\elements\db\UserQuery;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\User as UserRecord;
use panlatent\craft\dingtalk\records\UserDepartment;
use yii\helpers\Json;

/**
 * Class User
 *
 * @package panlatent\craft\dingtalk\elements
 * @property DateTime $dateHired
 * @property Department[] $departments
 * @author Panlatent <panlatent@gmail.com>
 */
class User extends Element
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'User');
    }

    /**
     * @return UserQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new UserQuery(static::class);
    }

    /**
     * @return null|string
     */
    public static function refHandle()
    {
        return 'user';
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('dingtalk', 'All users'),
                'criteria' => [],
            ],
        ];
        $allDepartments = Plugin::$plugin->getDepartments()->getAllDepartments();

        return array_merge($sources, DepartmentHelper::sourceTree($allDepartments, 1));
    }

    protected static function defineSortOptions(): array
    {
        return [
            'name' => Craft::t('app', 'Name'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('dingtalk', 'Name')],
            'position' => ['label' => Craft::t('dingtalk', 'Position')],
            'mobile' => ['label' => Craft::t('dingtalk', 'Mobile')],
            'jobNumber' => ['label' => Craft::t('dingtalk', 'Job Number')],
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'mobile'];
    }

    /**
     * @var string|null 员工唯一标识ID（不可修改）
     */
    public $userId;

    /**
     * @var string|null 成员名称
     */
    public $name;

    /**
     * @var string|null 职位信息, 长度为0~64个字符
     */
    public $position;

    /**
     * @var string|null 分机号，长度为0~50个字符
     */
    public $tel;

    /**
     * @var string|null 备注，长度为0~1000个字符
     */
    public $remark;

    /**
     * @var bool|null 是否为企业的老板，true表示是，false表示不是
     */
    public $isBoss;

    /**
     * @var bool|null 在对应的部门中是否为主管
     */
    public $isLeader;

    /**
     * @var bool|null 是否为企业的管理员
     */
    public $isAdmin;

    /**
     * @var bool|null 是否号码隐藏
     */
    public $isHide;

    /**
     * @var string|null 头像 URL
     */
    public $avatar;

    /**
     * @var string|null 员工工号
     */
    public $jobNumber;

    /**
     * @var string|null 员工的电子邮箱（ISV不可见）
     */
    public $email;

    /**
     * @var bool|null 表示该用户是否激活了钉钉
     */
    public $active;

    /**
     * @var string|null 用户在当前应用内的唯一标识（不可修改）
     */
    public $openId;

    /**
     * @var string|null 手机号码（ISV不可见）
     */
    public $mobile;

    /**
     * @var string|null 员工的企业邮箱，如果员工已经开通了企业邮箱，接口会返回，否则不会返回（ISV不可见）
     */
    public $orgEmail;

    /**
     * @var array|null
     */
    public $settings;

    /**
     * @var int|null 在对应的部门中的排序
     */
    public $sortOrder;

    /**
     * @var DateTime|null 入职时间 (Unix时间戳)
     */
    private $_dateHired;

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            $userRecord = new UserRecord();
        } else {
            $userRecord = UserRecord::findOne(['id' => $this->id]);
        }

        $userRecord->id = $this->id;
        $userRecord->userId = $this->userId;
        $userRecord->name = $this->name;
        $userRecord->position = $this->position;
        $userRecord->tel = $this->tel;
        $userRecord->isAdmin = $this->isAdmin;
        $userRecord->isBoss = $this->isBoss;
        $userRecord->isLeader = $this->isLeader;
        $userRecord->isHide = $this->isHide;
        $userRecord->avatar = $this->avatar;
        $userRecord->jobNumber = $this->jobNumber;
        $userRecord->email = $this->email;
        $userRecord->orgEmail = $this->orgEmail;
        $userRecord->active = $this->active;
        $userRecord->mobile = $this->mobile;
        $userRecord->dateHired = $this->dateHired->format('Y-m-d H:i:s');
        $userRecord->settings = Json::encode($this->settings ?? []);
        $userRecord->remark = $this->remark;
        $userRecord->sortOrder = $this->sortOrder;

        $userRecord->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @return DateTime|null
     */
    public function getDateHired()
    {
        return $this->_dateHired;
    }

    /**
     * @param DateTime|string|int|null $dateHired
     */
    public function setDateHired($dateHired)
    {
        if (is_int($dateHired)) {
            $dateHired = new DateTime(date('Y-m-d H:i:s', $dateHired));
        } elseif (is_string($dateHired)) {
            $dateHired = new DateTime($dateHired);
        }
        $this->_dateHired = $dateHired;
    }

    /**
     * @param array $departments
     */
    public function setDepartments($departments)
    {
        if ($departments === null) {
            UserDepartment::deleteAll(['userId' => $this->userId]);
            return;
        }

        $userDepartments = UserDepartment::find()
            ->where(['userId' => $this->userId])
            ->indexBy('departmentId')
            ->all();

        $transaction = Craft::$app->db->beginTransaction();
        try {
            foreach ($departments as $department) {
                if (is_int($department)) {
                    $department = Plugin::$plugin->getDepartments()->getDepartmentById($department);
                }

                if ($department && isset($userDepartments[$department->id])) {
                    unset($userDepartments[$department->id]);
                    continue;
                }

                $userDepartment = new UserDepartment();
                $userDepartment->userId = $this->userId;
                $userDepartment->departmentId = $department->id;
                $userDepartment->save(false);
            }

            foreach ($userDepartments as $department) {
                $department->delete();
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }
    }

    public function __toString()
    {
        return $this->name;
    }
}