<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\web\twig;

use Craft;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\db\ApprovalQuery;
use panlatent\craft\dingtalk\elements\db\ContactQuery;
use panlatent\craft\dingtalk\elements\db\EmployeeQuery;
use panlatent\craft\dingtalk\elements\Employee;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @package panlatent\craft\dingtalk\web\twig
 * @author Panlatent <panlatent@gmail.com>
 */
class CraftVariableBehavior extends Behavior
{
    // Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public $dingtalk;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->dingtalk = Plugin::$dingtalk;
    }

    /**
     * @param mixed $criteria
     * @return ApprovalQuery
     */
    public function approvals($criteria = null): ApprovalQuery
    {
        $query = Approval::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * @param mixed $criteria
     * @return ContactQuery
     */
    public function contacts($criteria = null): ContactQuery
    {
        $query = Contact::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }

    /**
     * @param mixed $criteria
     * @return EmployeeQuery
     */
    public function employees($criteria = null): EmployeeQuery
    {
        $query = Employee::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }

        return $query;
    }
}