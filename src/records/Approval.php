<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\records;

use craft\db\ActiveRecord;

/**
 * Class Approval
 *
 * @package panlatent\craft\dingtalk\records
 * @internal
 * @property int $id
 * @property int $corporationId
 * @property int $processId
 * @property string $businessId
 * @property string $instanceId
 * @property int $originatorUserId
 * @property int $originatorDepartmentId
 * @property string $title
 * @property string $approveUserIds
 * @property string $ccUserIds
 * @property bool $isAgree
 * @property string $formValues
 * @property string $operationRecords
 * @property string $tasks
 * @property string $bizAction
 * @property string $attachedInstanceIds
 * @property string $status
 * @property \DateTime $startDate
 * @property \DateTime $finishDate
 * @author Panlatent <panlatent@gmail.com>
 */
class Approval extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return '{{%dingtalk_approvals}}';
    }
}