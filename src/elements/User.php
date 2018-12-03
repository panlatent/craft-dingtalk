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
use craft\validators\DateTimeValidator;
use DateTime;
use panlatent\craft\dingtalk\elements\db\UserQuery;
use panlatent\craft\dingtalk\errors\DepartmentException;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\User as UserRecord;
use panlatent\craft\dingtalk\records\UserDepartment as UserDepartmentRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class User
 *
 * @package panlatent\craft\dingtalk\elements
 * @property Department|null $primaryDepartment
 * @property Department[] $departments
 * @author Panlatent <panlatent@gmail.com>
 */
class User extends Element
{
    const STATUS_LEAVED = 'leaved';

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
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'dingtalk-user';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
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
            static::STATUS_ENABLED => Craft::t('app', 'Enabled'),
            static::STATUS_LEAVED => Craft::t('dingtalk', 'Leaved'),
            static::STATUS_DISABLED => ['label' => Craft::t('app', 'Disabled'), 'color' => 'red'],
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
                'label' => Craft::t('dingtalk', 'All users'),
                'criteria' => [],
                'hasThumbs' => true,
            ],

        ];
        $allDepartments = Plugin::$plugin->getDepartments()->getAllDepartments();

        $sources[] = ['heading' => Craft::t('dingtalk', 'Serving staffs')];
        $sources = array_merge($sources, DepartmentHelper::sourceTree($allDepartments, 1));

        $sources[] = ['heading' => Craft::t('dingtalk', 'Leaved staffs')];
        $sources[] = [
            'key' => 'isLeaved:*',
            'label' => Craft::t('dingtalk', 'Leaved users'),
            'criteria' => [
                'isLeaved' => true,
            ],
            'hasThumbs' => true,
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'name' => Craft::t('dingtalk', 'Name'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'name' => ['label' => Craft::t('dingtalk', 'Name')],
            'position' => ['label' => Craft::t('dingtalk', 'Position')],
            'primaryDepartment' =>  ['label' => Craft::t('dingtalk', 'Primary Department')],
            'mobile' => ['label' => Craft::t('dingtalk', 'Mobile')],
            'jobNumber' => ['label' => Craft::t('dingtalk', 'Job Number')],
            'email' => ['label' => Craft::t('dingtalk', 'Email')],
            'hiredDate' => ['label' => Craft::t('dingtalk', 'Hired Date')],
            'leavedDate' => ['label' => Craft::t('dingtalk', 'Leaved Date')],
            'remark' => ['label' => Craft::t('dingtalk', 'Remark')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        if ($source === '*') {
            return ['name', 'position', 'primaryDepartment', 'mobile', 'jobNumber'];
        }

        if (strncmp($source, 'department:', 11) === 0) {
            return ['name', 'position', 'mobile', 'jobNumber', 'hiredDate', 'remark'];
        } elseif ($source === 'isLeaved:*') {
            return ['name', 'position', 'hiredDate', 'leavedDate', 'remark'];
        }

        return parent::defineDefaultTableAttributes($source);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'mobile', 'tel', 'position'];
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
     * @var bool|null 是否离职
     */
    public $isLeaved;

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
    public $isActive;

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
     * @var int|null 在对应的部门中的排序
     */
    public $sortOrder;

    /**
     * @var DateTime|null 入职时间 (Unix时间戳)
     */
    public $hiredDate;

    /**
     * @var DateTime|null 离职时间 (Unix时间戳)
     */
    public $leavedDate;

    /**
     * @var  Department|null 主部门
     */
    private $_primaryDepartment;

    /**
     * @var Department[]|null
     */
    private $_departments;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        $rules = array_merge($rules, [
            [['userId', 'name'], 'required'],
            [['hiredDate', 'leavedDate'], DateTimeValidator::class],
        ]);

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'primaryDepartment';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'hiredDate';
        $attributes[] = 'leavedDate';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
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
        $userRecord->isAdmin = (bool)$this->isAdmin;
        $userRecord->isBoss = (bool)$this->isBoss;
        $userRecord->isLeader = (bool)$this->isLeader;
        $userRecord->isHide = (bool)$this->isHide;
        $userRecord->isLeaved = (bool)$this->isLeaved;
        $userRecord->isActive = (bool)$this->isActive;
        $userRecord->avatar = $this->avatar;
        $userRecord->jobNumber = $this->jobNumber;
        $userRecord->email = $this->email;
        $userRecord->orgEmail = $this->orgEmail;
        $userRecord->mobile = $this->mobile;
        $userRecord->hiredDate = $this->hiredDate;
        $userRecord->leavedDate = $this->leavedDate;
        $userRecord->remark = $this->remark;
        $userRecord->sortOrder = $this->sortOrder;

        $userRecord->save(false);

        if ($this->_primaryDepartment) {
            if (empty($this->_departments)) {
                $this->_departments = [$this->_primaryDepartment];
            } else {
                if (!in_array($this->_primaryDepartment, $this->_departments)) {
                    $this->_departments[] = $this->_primaryDepartment;
                }
            }
        }

        // Save user relation departments...
        if ($this->_departments !== null) {
            if (!empty($this->_departments)) {
                /** @var UserDepartmentRecord[] $departmentRecords */
                $departmentRecords = UserDepartmentRecord::find()->where(['userId' => $this->id])->all();
                $departmentRecords = ArrayHelper::index($departmentRecords, 'departmentId');

                foreach ($this->_departments as $department) {
                    if (!is_object($department)) {
                        Craft::warning($department,__METHOD__);
                    }
                    if (isset($departmentRecords[$department->id])) {
                        $departmentRecord = $departmentRecords[$department->id];
                        unset($departmentRecords[$department->id]);
                    } else {
                        $departmentRecord = new UserDepartmentRecord();
                    }

                    $departmentRecord->userId = $this->id;
                    $departmentRecord->departmentId = $department->id;

                    if ($this->_primaryDepartment) {
                        $departmentRecord->primary = $department === $this->_primaryDepartment;
                    }

                    $departmentRecord->save(false);
                }

                foreach ($departmentRecords as $departmentRecord) {
                    $departmentRecord->delete();
                }
            } else {
                UserDepartmentRecord::deleteAll(['userId' => $this->id]);
            }
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        if ($this->archived) {
            return static::STATUS_ARCHIVED;
        }

        if (!$this->enabled || !$this->enabledForSite) {
            return static::STATUS_DISABLED;
        }

        return $this->isLeaved ? static::STATUS_LEAVED : static::STATUS_ENABLED;
    }

    /**
     * @return Department|null
     */
    public function getPrimaryDepartment()
    {
        if ($this->_primaryDepartment !== null) {
            return $this->_primaryDepartment;
        }

        $departmentId = (new Query())
            ->select('departmentId')
            ->from('{{%dingtalk_userdepartments}}')
            ->where([
                'userId' => $this->id,
                'primary' => true,
            ])
            ->scalar();

        if (!$departmentId) {
            return null;
        }

        return $this->_primaryDepartment = Plugin::$plugin->getDepartments()->getDepartmentById($departmentId);
    }

    /**
     * @param Department|int|null $department
     */
    public function setPrimaryDepartment($department = null)
    {
        if (is_int($department) || ctype_digit($department)) {
            $department = Plugin::$plugin->getDepartments()->getDepartmentById($department);
        }

        if (!$department instanceof Department && $department !== null) {
            throw new DepartmentException('Primary department must be a department instance');
        }

        $this->_primaryDepartment = $department;
    }

    /**
     * @return Department[]
     */
    public function getDepartments()
    {
        if ($this->_departments !== null) {
            return $this->_departments;
        }

        $departmentIds = (new Query())
            ->select('departmentId')
            ->from('{{%dingtalk_userdepartment}}')
            ->where(['userId' => $this->id])
            ->column();

        $this->setDepartments($departmentIds);

        return $this->_departments;
    }

    /**
     * @param Department[]|int[]|null $departments
     */
    public function setDepartments($departments)
    {
        if ($departments === null) {
            $this->_departments = null;
            return;
        }

        $this->_departments = [];

        foreach ($departments as $department) {
            if (is_int($department) || ctype_digit($department)) {
                $department = Plugin::$plugin->getDepartments()->getDepartmentById($department);
            }

            if (!$department instanceof Department) {
                throw new DepartmentException('User department must be a department instance');
            }

            $this->_departments[] = $department;
        }
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return Craft::$app->getFields()->getLayoutByType(static::class);
    }

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return 'plugin-handle/products/'.$this->id;
    }

    /**
     * @inheritdoc
     */
    public function getThumbUrl(int $size)
    {
        return $this->avatar ?: null;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        return parent::tableAttributeHtml($attribute);
    }
}