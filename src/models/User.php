<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

class User extends Model
{
    /**
     * @var string|null 职位信息, 长度为0~64个字符
     */
    public $position;

    /**
     * @var string|null 备注，长度为0~1000个字符
     */
    public $remark;

    /**
     * @var array 成员所属部门 id 列表
     */
    public $department = [];

    /**
     * @var string|null 分机号，长度为0~50个字符
     */
    public $tel;

    /**
     * @var string|null 在当前 isv 全局范围内唯一标识一个用户的身份，用户无法修改
     */
    public $unionid;

    /**
     * @var string|null 员工唯一标识ID（不可修改）
     */
    public $userid;

    /**
     * @var string|null 办公地点（ISV不可见）
     */
    public $workPlace;

    /**
     * @var string|null
     */
    public $dingId;

    /**
     * @var bool|null 是否为企业的老板，true表示是，false表示不是
     */
    public $isBoss;

    /**
     * @var int|null 在对应的部门中的排序
     */
    public $order;

    /**
     * @var string|null 成员名称
     */
    public $name;

    /**
     * @var array 扩展属性（ISV不可见）
     */
    public $extattr = [];

    /**
     * @var bool|null 在对应的部门中是否为主管
     */
    public $isLeader;

    /**
     * @var string|null 头像 URL
     */
    public $avatar;

    /**
     * @var string|null 员工工号
     */
    public $jobnumber;

    /**
     * @var string|null 员工的电子邮箱（ISV不可见）
     */
    public $email;

    /**
     * @var bool|null 表示该用户是否激活了钉钉
     */
    public $active;

    /**
     * @var bool|null 是否为企业的管理员
     */
    public $isAdmin;

    /**
     * @var string|null 用户在当前应用内的唯一标识（不可修改）
     */
    public $openId;

    /**
     * @var string|null 手机号码（ISV不可见）
     */
    public $mobile;

    /**
     * @var bool|null 是否号码隐藏
     */
    public $isHide;

    /**
     * @var string|null 员工的企业邮箱，如果员工已经开通了企业邮箱，接口会返回，否则不会返回（ISV不可见）
     */
    public $orgEmail;

    /**
     * @var int|null 入职时间 (Unix时间戳)
     */
    public $hiredDate;
}