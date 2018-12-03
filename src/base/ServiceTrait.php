<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use panlatent\craft\dingtalk\services\Api;
use panlatent\craft\dingtalk\services\Approvals;
use panlatent\craft\dingtalk\services\Departments;
use panlatent\craft\dingtalk\services\Messages;
use panlatent\craft\dingtalk\services\Processes;
use panlatent\craft\dingtalk\services\Robots;
use panlatent\craft\dingtalk\services\SmartWorks;
use panlatent\craft\dingtalk\services\Users;

/**
 * Trait ServiceTrait
 *
 * @package panlatent\craft\dingtalk\base
 * @property-read Api $api
 * @property-read Approvals $approvals
 * @property-read Departments $departments
 * @property-read Messages $messages
 * @property-read Processes $processes
 * @property-read Robots $robots
 * @property-read SmartWorks $smartWorks
 * @property-read Users $users
 * @author Panlatent <panlatent@gmail.com>
 */
trait ServiceTrait
{
    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->get('api');
    }

    /**
     * @return Approvals
     */
    public function getApprovals()
    {
        return $this->get('approvals');
    }

    /**
     * @return Departments
     */
    public function getDepartments()
    {
        return $this->get('departments');
    }

    /**
     * @return Messages
     */
    public function getMessages()
    {
        return $this->get('messages');
    }

    /**
     * @return Processes
     */
    public function getProcesses()
    {
        return $this->get('processes');
    }

    /**
     * @return Robots
     */
    public function getRobots()
    {
        return $this->get('robots');
    }

    /**
     * @return SmartWorks
     */
    public function getSmartWorks()
    {
        return $this->get('smartWorks');
    }

    /**
     * @return Users
     */
    public function getUsers()
    {
        return $this->get('users');
    }
}