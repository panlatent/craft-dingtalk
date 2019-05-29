<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\plugin;

use panlatent\craft\dingtalk\services\Api;
use panlatent\craft\dingtalk\services\Approvals;
use panlatent\craft\dingtalk\services\Callbacks;
use panlatent\craft\dingtalk\services\Contacts;
use panlatent\craft\dingtalk\services\Corporations;
use panlatent\craft\dingtalk\services\Departments;
use panlatent\craft\dingtalk\services\Messages;
use panlatent\craft\dingtalk\services\Processes;
use panlatent\craft\dingtalk\services\Robots;
use panlatent\craft\dingtalk\services\SmartWorks;
use panlatent\craft\dingtalk\services\Employees;

/**
 * Trait ServiceTrait
 *
 * @package panlatent\craft\dingtalk\base
 * @property-read Api $api
 * @property-read Approvals $approvals
 * @property-read Callbacks $callbacks
 * @property-read Corporations $corporations
 * @property-read Contacts $contacts
 * @property-read Departments $departments
 * @property-read Messages $messages
 * @property-read Processes $processes
 * @property-read Robots $robots
 * @property-read SmartWorks $smartWorks
 * @property-read Employees $users
 * @author Panlatent <panlatent@gmail.com>
 */
trait Services
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
     * @return Callbacks
     */
    public function getCallbacks()
    {
        return $this->get('callbacks');
    }

    /**
     * @return Corporations
     */
    public function getCorporations()
    {
        return $this->get('corporations');
    }

    /**
     * @return Contacts
     */
    public function getContacts()
    {
        return $this->get('contacts');
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
     * @return Employees
     */
    public function getUsers()
    {
        return $this->get('users');
    }

    /**
     * Set the plugin service components.
     */
    private function _setComponents()
    {
        $this->setComponents([
            'api' => Api::class,
            'approvals' => Approvals::class,
            'callbacks' => Callbacks::class,
            'corporations' => Corporations::class,
            'contacts' => Contacts::class,
            'departments' => Departments::class,
            'messages' => Messages::class,
            'processes' => Processes::class,
            'robots' => Robots::class,
            'smartWorks' => SmartWorks::class,
            'users' => Employees::class,
        ]);
    }
}