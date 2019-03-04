<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\db;

/**
 * Class Table
 *
 * @package panlatent\craft\dingtalk\db
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Table
{
    const APPROVALS = '{{%dingtalk_approvals}}';
    const CORPORATIONS = '{{%dingtalk_corporation}}';
    const DEPARTMENTS = '{{%dingtalk_departments}}';
    const PROCESSES = '{{%dingtalk_processes}}';
    const Robots = '{{%dingtalk_robots}}';
    const USERS = '{{%dingtalk_users}}';
    const USERDEPARTMENTS = '{{%dingtalk_userdepartments}}';
}