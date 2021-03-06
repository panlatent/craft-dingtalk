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
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;

/**
 * Class UserQuery
 *
 * @package panlatent\craft\dingtalk\elements\db
 * @method User|null one($db = null)
 * @method User[] all($db = null)
 * @author Panlatent <panlatent@gmail.com>
 */
class UserQuery extends ElementQuery
{
    // Traits
    // =========================================================================

    use CorporationQuery;

    // Properties
    // =========================================================================

    /**
     * @var string[]|string|null
     */
    public $userId;

    /**
     * @var string[]|string|null
     */
    public $departmentId;

    /**
     * @var string[]|string|null
     */
    public $name;

    /**
     * @var string[]|string|null
     */
    public $position;

    /**
     * @var int[]|int|null
     */
    public $tel;

    /**
     * @var string[]|string|null
     */
    public $mobile;

    /**
     * @var string[]|string|null
     */
    public $stateCode;

    /**
     * @var string[]|string|null
     */
    public $jobNumber;

    /**
     * @var bool|int|null
     */
    public $isActive;

    /**
     * @var bool|null
     */
    public $isAdmin;

    /**
     * @var bool|null
     */
    public $isBoss;

    /**
     * @var bool|null
     */
    public $isLeader;

    /**
     * @var bool|null
     */
    public $isHide;

    /**
     * @var bool|null
     */
    public $isLeaved;

    /**
     * @var Contact|int|null
     */
    public $shareContactOf;

    // Public Methods
    // =========================================================================

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function userId($value)
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function departmentId($value)
    {
        $this->departmentId = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function position($value)
    {
        $this->position = $value;

        return $this;
    }

    /**
     * @param int[]|int|null $value
     * @return $this
     */
    public function tel($value)
    {
        $this->tel = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function mobile($value)
    {
        $this->mobile = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function stateCode($value)
    {
        $this->stateCode = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function jobNumber($value)
    {
        $this->jobNumber = $value;

        return $this;
    }

    /**
     * @param bool|int|null $value
     * @return $this
     */
    public function isActive($value = true)
    {
        $this->isActive = $value;

        return $this;
    }

    /**
     * @var bool|null
     * @return $this
     */
    public function isAdmin($value = true)
    {
        $this->isAdmin = $value;

        return $this;
    }

    /**
     * @var bool|null
     * @return $this
     */
    public function isBoss($value = true)
    {
        $this->isBoss = $value;

        return $this;
    }

    /**
     * @var bool|null
     * @return $this
     */
    public function isLeader($value = true)
    {
        $this->isLeader = $value;

        return $this;
    }

    /**
     * @param bool|null $value
     * @return $this
     */
    public function isHide($value = true)
    {
        $this->isHide = $value;

        return $this;
    }

    /**
     * @param bool|null $value
     * @return $this
     */
    public function isLeaved($value = true)
    {
        $this->isLeaved = $value;

        return $this;
    }

    /**
     * @param Contact|int|null $value
     * @return $this
     */
    public function shareContactOf($value)
    {
        $this->shareContactOf = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforePrepare(): bool
    {
        $this->joinElementTable('dingtalk_users');

        $this->query->select([
            'dingtalk_users.corporationId',
            'dingtalk_users.userId',
            'dingtalk_users.name',
            'dingtalk_users.position',
            'dingtalk_users.tel',
            'dingtalk_users.isAdmin',
            'dingtalk_users.isBoss',
            'dingtalk_users.isLeader',
            'dingtalk_users.isActive',
            'dingtalk_users.avatar',
            'dingtalk_users.jobNumber',
            'dingtalk_users.email',
            'dingtalk_users.mobile',
            'dingtalk_users.stateCode',
            'dingtalk_users.isHide',
            'dingtalk_users.isLeaved',
            'dingtalk_users.orgEmail',
            'dingtalk_users.hiredDate',
            'dingtalk_users.leavedDate',
            'dingtalk_users.remark',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.userId', $this->userId));
        }

        if ($this->departmentId) {
            $allDepartments = Plugin::getInstance()->departments->getAllDepartments();

            $departmentIds = (array)$this->departmentId;
            foreach ($departmentIds as $departmentId) {
                $childrenDepartments = DepartmentHelper::parentSort($allDepartments, $departmentId);
                foreach ($childrenDepartments as $childrenDepartment) {
                    if (!isset($departmentIds[$childrenDepartment->id])) {
                        $departmentIds[] = $childrenDepartment->id;
                    }
                }
            }

            $this->subQuery->innerJoin('dingtalk_userdepartments', 'dingtalk_userdepartments.userId=dingtalk_users.id');
            $this->subQuery->andWhere(Db::parseParam('dingtalk_userdepartments.departmentId', $departmentIds));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.name', $this->name));
        }

        if ($this->position) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.position', $this->position));
        }

        if ($this->tel) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.tel', $this->tel));
        }

        if ($this->mobile) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.mobile', $this->mobile));
        }

        if ($this->stateCode) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.stateCode', $this->stateCode));
        }

        if ($this->jobNumber) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.jobNumber', $this->jobNumber));
        }

        if ($this->shareContactOf) {
            $contactId = $this->shareContactOf instanceof Contact ? $this->shareContactOf->id : $this->shareContactOf;
            $this->subQuery->rightJoin(Table::CONTACTSHARES_USERS . ' contactshares_users', '[[contactshares_users.userId]] = [[dingtalk_users.id]]');
            $this->subQuery->andWhere(Db::parseParam('contactshares_users.contactId', $contactId));
        }

        $this->_prepareStatusConditions();
        $this->_applyCorporationParam('dingtalk_users.corporationId');

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        switch ($status) {
            case User::STATUS_IN_SERVICE:
                return ['dingtalk_users.isLeaved' => false];
            case User::STATUS_LEAVED:
                return ['dingtalk_users.isLeaved' => true];
            default:
                return parent::statusCondition($status);
        }
    }

    /**
     * Prepare status conditions
     */
    private function _prepareStatusConditions()
    {
        if ($this->isActive !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isActive', $this->isActive));
        }

        if ($this->isAdmin !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isAdmin', $this->isAdmin));
        }

        if ($this->isBoss !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isBoss', $this->isBoss));
        }

        if ($this->isLeader !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isLeader', $this->isLeader));
        }

        if ($this->isHide !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isHide', $this->isHide));
        }

        if ($this->isLeaved !== null) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.isLeaved', $this->isLeaved));
        }
    }
}