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
    const CONTACTLABELGROUPS = '{{%dingtalk_contactslabelgroups}}';
    const CONTACTLABELS = '{{%dingtalk_contactslabels}}';
    const CONTACTLABELS_CONTACTS = '{{%dingtalk_contactslabelcontacts}}';
    const CONTACTS = '{{%dingtalk_contacts}}';

    const CORPORATIONS = '{{%dingtalk_corporations}}';
    const DEPARTMENTS = '{{%dingtalk_departments}}';
    const PROCESSES = '{{%dingtalk_processes}}';
    const ROBOTS = '{{%dingtalk_robots}}';
    const USERS = '{{%dingtalk_users}}';
    const USERDEPARTMENTS = '{{%dingtalk_userdepartments}}';
}