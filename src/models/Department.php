<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

class Department extends Model
{
    /**
     * @var int|null 部门id
     */
    public $id;

    /**
     * @var bool|null 是否同步创建一个关联此部门的企业群 true表示是, false表示不是
     */
    public $createDeptGroup;

    /**
     * @var string|null 部门名称
     */
    public $name;

    /**
     * @var bool|null 当群已经创建后，是否有新人加入部门会自动加入该群, true表示是, false表示不是
     */
    public $autoAddUser;
    /**
     * @var int|null 父部门id，根部门为1
     */
    public $parentid;

    /**
     * @var bool|null 是否隐藏部门, true表示隐藏，false表示显示
     */
    public $deptHiding;

    /**
     * @var string|null 可以查看指定隐藏部门的其他部门列表，如果部门隐藏，则此值生效，取值为其他的部门id组成的的字符串，使用“|”符号进行分割
     */
    public $deptPermits;

    /**
     * @see $deptPermits
     */
    public $deptPerimits;

    /**
     * @var string|null 可以查看指定隐藏部门的其他人员列表，如果部门隐藏，则此值生效，取值为其他的人员userid组成的的字符串，使用“|”符号进行分割
     */
    public $userPermits;

    /**
     * @see $userPermits
     */
    public $userPerimits;

    /**
     * @var string|null 本部门的员工仅可见员工自己为true时，可以配置额外可见部门，值为部门id组成的的字符串，使用“|”符号进行分割
     */
    public $outerPermitDepts;

    /**
     * @var bool|null 部门群是否包含子部门
     */
    public $groupContainSubDept;

    /**
     * @var string|null 部门的主管列表，取值为由主管的userid组成的字符串，不同的userid使用“|”符号进行分割
     */
    public $deptManagerUseridList;

    /**
     * @var string 本部门的员工仅可见员工自己为true时，可以配置额外可见人员，值为userid组成的的字符串，使用“|”符号进行分割
     */
    public $outerPermitUsers;

    /**
     * @var
     */
    public $deptGroupChatId;

    /**
     * @var int|null 在父部门中的次序值
     */
    public $order;

    /**
     * @var bool|null 是否本部门的员工仅可见员工自己，为true时，本部门员工默认只能看到员工自己
     */
    public $outerDept;

    /**
     * @var string|null 企业群群主
     */
    public $orgDeptOwner;

    /**
     * @var int|null 返回码
     */
    public $errcode;

    /**
     * @var string|null 对返回码的文本描述内容
     */
    public $errmsg;
}