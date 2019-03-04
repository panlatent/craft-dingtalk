<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use Craft;
use craft\base\Model;

class SmartWork extends Model
{
    public $workPlace;
    public $remark;
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

    public function createFields($attributes)
    {
        Craft::$app->getFields()->createField([]);
    }

    public function attributeFields()
    {
        return [
            'workPlace' => [

            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'workPlace' => Craft::t('dingtalk', 'Work Place'),
            'remark' => Craft::t('dingtalk', 'Remark'),
            'employeeType' => Craft::t('dingtalk', 'Employee Type'),
            'employeeStatus' => Craft::t('dingtalk', 'Employee Status'),
            'probationPeriodType' => Craft::t('dingtalk', 'Probation Period Type'),
            'regularTime' => Craft::t('dingtalk', 'Regular Time'),
            'positionLevel' => Craft::t('dingtalk', 'Position Level'),
            'realName' => Craft::t('dingtalk', 'Real Name'),
            'certNo' => Craft::t('dingtalk', 'Cert No'),
            'birthTime' => Craft::t('dingtalk', 'Birth Time'),
            'sexType' => Craft::t('dingtalk', 'Sex Type'),
            'nationType' => Craft::t('dingtalk', 'Nation Type'),
            'certAddress' => Craft::t('dingtalk', 'Cert Address'),
            'certEndTime' => Craft::t('dingtalk', 'Cert End Time'),
            'marriage' => Craft::t('dingtalk', 'Marriage'),
            'joinWorkingTime' => Craft::t('dingtalk', 'Join Working Time'),
            'residenceType' => Craft::t('dingtalk', 'Residence Type'),
            'address' => Craft::t('dingtalk', 'Address'),
            'politicalStatus' => Craft::t('dingtalk', 'Political Status'),
            'personalSi' => Craft::t('dingtalk', 'Personal Si'),
            'personalHf' => Craft::t('dingtalk', 'Personal Hf'),
            'highestEdu' => Craft::t('dingtalk', 'Highest Edu'),
            'graduateSchool' => Craft::t('dingtalk', 'Graduate School'),
            'graduationTime' => Craft::t('dingtalk', 'Graduation Time'),
            'major' => Craft::t('dingtalk', 'Major'),
            'bankAccountNo' => Craft::t('dingtalk', 'Bank Account No'),
            'accountBank' => Craft::t('dingtalk', 'Account Bank'),
            'contractCompanyName' => Craft::t('dingtalk', 'Contract Company Name'),
            'contractType' => Craft::t('dingtalk', 'Contract Type'),
            'firstContractStartTime' => Craft::t('dingtalk', 'First Contract Start Time'),
            'firstContractEndTime' => Craft::t('dingtalk', 'First Contract End Time'),
            'nowContractStartTime' => Craft::t('dingtalk', 'Now Contract Start Time'),
            'nowContractEndTime' => Craft::t('dingtalk', 'Now Contract End Time'),
            'contractPeriodType' => Craft::t('dingtalk', 'Contract Period Type'),
            'contractRenewCount' => Craft::t('dingtalk', 'Contract Renew Count'),
            'urgentContactsName' => Craft::t('dingtalk', 'Urgent Contacts Name'),
            'urgentContactsRelation' => Craft::t('dingtalk', 'Urgent Contacts Relation'),
            'urgentContactsPhone' => Craft::t('dingtalk', 'Urgent Contacts Phone'),
            'haveChild' => Craft::t('dingtalk', 'Have Child'),
            'childName' => Craft::t('dingtalk', 'Child Name'),
            'childSex' => Craft::t('dingtalk', 'Child Sex'),
            'childBirthDate' => Craft::t('dingtalk', 'Child Birth Date'),
            'forntIDcard' => Craft::t('dingtalk', 'Fornt IDCard'),
            'rearIDcard' => Craft::t('dingtalk', 'Rear IDCard'),
            'academicCertificate' => Craft::t('dingtalk', 'Academic Certificate'),
            'diplomaCertificate' => Craft::t('dingtalk', 'Diploma Certificate'),
            'releaseLetter' => Craft::t('dingtalk', 'Release Letter'),
            'personalPhoto' => Craft::t('dingtalk', 'Personal Photo'),
        ];
    }
}