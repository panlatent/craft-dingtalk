<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

class UserSmartWork extends Model
{
    public $name;
    public $email;
    public $dept;
    public $deptIds;
    public $mainDept;
    public $mainDeptId;
    public $position;
    public $mobile;
    public $jobNumber;
    public $tel;
    public $workPlace;
    public $remark;
    public $confirmJoinTime;
    public $employeeType;
    public $employeeStatus;
    public $probationPeriodType;
    public $regularTime;
    public $positionLevel;
    public $realName;
    public $certNo;
    public $birthTime;
    public $sexType;
    public $nationType;
    /**
     * @var string 身份证地址（ISV不可申请该字段权限）
     */
    public $certAddress;
    /**
     * @var string 证件有效期（ISV不可申请该字段权限）
     */
    public $certEndTime;
    /**
     * @var string 婚姻状况
     */
    public $marriage;
    /**
     * @var string 首次参加工作时间
     */
    public $joinWorkingTime;
    /**
     * @var string 户籍类型
     */
    public $residenceType;
    /**
     * @var string 住址
     */
    public $address;
    /**
     * @var string 政治面貌
     */
    public $politicalStatus;
    /**
     * @var string 个人社保账号
     */
    public $personalSi;
    /**
     * @var string 个人公积金账号
     */
    public $personalHf;
    /**
     * @var string 最高学历
     */
    public $highestEdu;
    /**
     * @var string 毕业院校
     */
    public $graduateSchool;
    /**
     * @var string 毕业时间
     */
    public $graduationTime;
    /**
     * @var string 所学专业
     */
    public $major;
    /**
     * @var string 银行卡号（ISV不可申请该字段权限）
     */
    public $bankAccountNo;
    /**
     * @var string 开户行
     */
    public $accountBank;
    /**
     * @var string 合同公司
     */
    public $contractCompanyName;
    /**
     * @var string 合同类型
     */
    public $contractType;
    /**
     * @var string 首次合同起始日
     */
    public $firstContractStartTime;
    /**
     * @var string 首次合同到期日
     */
    public $firstContractEndTime;
    /**
     * @var string 现合同起始日
     */
    public $nowContractStartTime;
    /**
     * @var string 现合同到期日
     */
    public $nowContractEndTime;
    /**
     * @var string 合同期限
     */
    public $contractPeriodType;
    /**
     * @var string 续签次数
     */
    public $contractRenewCount;
    /**
     * @var string 紧急联系人姓名（ISV不可申请该字段权限）
     */
    public $urgentContactsName;
    /**
     * @var string 联系人关系（ISV不可申请该字段权限）
     */
    public $urgentContactsRelation;
    /**
     * @var string 联系人电话（ISV不可申请该字段权限）
     */
    public $urgentContactsPhone;
    /**
     * @var string 有无子女
     */
    public $haveChild;
    /**
     * @var string 子女姓名
     */
    public $childName;
    /**
     * @var string 子女性别
     */
    public $childSex;
    /**
     * @var string 子女出生日期
     */
    public $childBirthDate;
    /**
     * @var string 身份证（人像面）（ISV不可申请该字段权限）
     */
    public $forntIDcard;
    /**
     * @var string 身份证（国徽面）（ISV不可申请该字段权限）
     */
    public $rearIDcard;
    /**
     * @var string 学历证书
     */
    public $academicCertificate;
    /**
     * @var string 学位证书
     */
    public $diplomaCertificate;
    /**
     * @var string 前公司离职证明
     */
    public $releaseLetter;
    /**
     * @var string 员工照片（ISV不可申请该字段权限）
     */
    public $personalPhoto;
}