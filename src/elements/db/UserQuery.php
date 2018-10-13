<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use craft\helpers\Db;
use panlatent\craft\dingtalk\helpers\DepartmentHelper;
use panlatent\craft\dingtalk\Plugin;

class UserQuery extends ElementQuery
{
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

    public function beforePrepare(): bool
    {
        $this->joinElementTable('dingtalk_users');

        $this->query->select([
            'dingtalk_users.userId',
            'dingtalk_users.name',
            'dingtalk_users.position',
            'dingtalk_users.tel',
            'dingtalk_users.isAdmin',
            'dingtalk_users.isBoss',
            'dingtalk_users.isLeader',
            'dingtalk_users.avatar',
            'dingtalk_users.jobNumber',
            'dingtalk_users.email',
            'dingtalk_users.active',
            'dingtalk_users.mobile',
            'dingtalk_users.isHide',
            'dingtalk_users.orgEmail',
            'dingtalk_users.dateHired' ,
            'dingtalk_users.settings',
            'dingtalk_users.remark',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.userId', $this->userId));
        }

        if ($this->departmentId) {
            $allDepartments = Plugin::$plugin->getDepartments()->getAllDepartments();
            $childrenDepartments = DepartmentHelper::parentSort($allDepartments, $this->departmentId);
            $departmentIds = ArrayHelper::getColumn($childrenDepartments, 'id');
            if (empty($departmentIds)) {
                $departmentIds =  $this->departmentId;
            } else {
                $departmentIds[] = $this->departmentId;
            }

            $this->subQuery->innerJoin('dingtalk_userdepartments', 'dingtalk_userdepartments.userId=dingtalk_users.userid');
            $this->subQuery->andWhere(Db::parseParam('dingtalk_userdepartments.departmentId', $departmentIds));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_users.name', $this->name));
        }

        return parent::beforePrepare();
    }
}