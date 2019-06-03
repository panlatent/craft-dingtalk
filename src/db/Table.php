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
    const APPS = '{{%dingtalk_apps}}';
    const APPROVALS = '{{%dingtalk_approvals}}';
    const APPROVALTYPES = '{{%dingtalk_approvaltypes}}';
    const ATTENDANCES = '{{%dingtalk_attendances}}';
    const CALLBACKGROUPS = '{{%dingtalk_callbackgroups}}';
    const CALLBACKREQUESTS = '{{%dingtalk_callbackrequests}}';
    const CALLBACKS = '{{%dingtalk_callbacks}}';
    const CONTACTLABELGROUPS = '{{%dingtalk_contactlabelgroups}}';
    const CONTACTLABELS = '{{%dingtalk_contactlabels}}';
    const CONTACTLABELS_CONTACTS = '{{%dingtalk_contactlabels_contacts}}';
    const CONTACTS = '{{%dingtalk_contacts}}';
    const CONTACTSHARES_DEPARTMENTS = '{{%dingtalk_contactshares_departments}}';
    const CONTACTSHARES_EMPLOYEES = '{{%dingtalk_contactshares_employees}}';
    const CORPORATIONS = '{{%dingtalk_corporations}}';
    const CORPORATIONAPPS = '{{%dingtalk_corporationapps}}';
    const CORPORATIONCALLBACKSETTINGS = '{{%dingtalk_corporationcallbacksettings}}';
    const CORPORATIONCALLBACKS = '{{%dingtalk_corporationcallbacks}}';
    const DEPARTMENTS = '{{%dingtalk_departments}}';
    const EMPLOYEES = '{{%dingtalk_employees}}';
    const EMPLOYEEDEPARTMENTS = '{{%dingtalk_employeedepartments}}';
    const PROCESSES = '{{%dingtalk_processes}}';
    const ROBOTS = '{{%dingtalk_robots}}';
    const ROBOTWEBHOOKS = '{{%dingtalk_robotwebhooks}}';
}