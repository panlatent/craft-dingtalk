<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\user;

/**
 * Class Permissions
 *
 * @package panlatent\craft\dingtalk\user
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Permissions
{
    const MANAGE_CORPORATIONS = 'dingtalk-manageCorporations';
    const MANAGE_APPROVALS = 'dingtalk-manageApprovals';
    const MANAGE_CONTACTS = 'dingtalk-manageContacts';
    const MANAGE_ROBOTS = 'dingtalk-manageRobots';
    const MANAGE_USERS = 'dingtalk-manageUsers';
    const MANAGE_SETTINGS = 'dingtalk-manageSettings';
}