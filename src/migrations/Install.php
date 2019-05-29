<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;
use craft\db\Table as CraftTable;
use panlatent\craft\dingtalk\db\Table;

/**
 * Class Install
 *
 * @package panlatent\craft\dingtalk\migrations
 * @author Panlatent <panlatent@gmail.com>
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
         $this->createTables();
         $this->createIndexes();
         $this->addForeignKeys();

         return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTables();
    }

    /**
     * Create tables.
     */
    public function createTables()
    {
        // Corporations
        // ---------------------------------------------------------------------

        $this->createTable(Table::CORPORATIONS, [
            'id' => $this->primaryKey(),
            'primary' => $this->boolean()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'corpId' => $this->string()->notNull(),
            'corpSecret' => $this->string()->notNull(),
            'hasUrls' => $this->boolean()->notNull()->defaultValue(1),
            'url' => $this->string(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'archived' => $this->boolean()->notNull()->defaultValue(false),
            'sortOrder' => $this->smallInteger(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CORPORATIONCALLBACKSETTINGS, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'url' => $this->string(),
            'token' => $this->string()->notNull(),
            'aesKey' => $this->string()->notNull(),
            'enabled' => $this->boolean()->notNull()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CORPORATIONCALLBACKS, [
            'corporationId' => $this->integer()->notNull(),
            'callbackId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Callbacks
        // ---------------------------------------------------------------------

        $this->createTable(Table::CALLBACKGROUPS, [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CALLBACKS, [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'code' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CALLBACKREQUESTS, [
            'id' => $this->primaryKey(),
            'callbackId' => $this->integer()->notNull(),
            'corporationId' => $this->integer(),
            'url' => $this->string()->notNull(),
            'data' => $this->text(),
            'postDate' => $this->dateTime()->notNull(),
            'attempts' => $this->tinyInteger(),
            'handled' => $this->boolean()->notNull()->defaultValue(false),
            'handledDate' => $this->date(),
            'handleFailedReason' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Departments
        // ---------------------------------------------------------------------

        $this->createTable(Table::DEPARTMENTS, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'dingDepartmentId' => $this->integer()->notNull(),
            'name' => $this->string(255)->notNull(),
            'parentId' => $this->integer(),
            'settings' => $this->text(),
            'sortOrder' => $this->smallInteger(6)->unsigned(),
            'archived' => $this->boolean()->notNull()->defaultValue(0),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Employees
        // ---------------------------------------------------------------------

        $this->createTable(Table::EMPLOYEES, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'userId' => $this->string(32)->notNull(),
            'name' => $this->string(64)->notNull(),
            'position' => $this->string(64),
            'tel' => $this->string(64),
            'avatar' => $this->string(255),
            'jobNumber' => $this->string(255),
            'email' => $this->string(255),
            'mobile' => $this->string(255),
            'stateCode' => $this->string()->defaultValue('86'),
            'isActive' => $this->boolean()->notNull()->defaultValue(true),
            'isAdmin' => $this->boolean()->notNull()->defaultValue(false),
            'isBoss' => $this->boolean()->notNull()->defaultValue(false),
            'isLeader' => $this->boolean()->notNull()->defaultValue(false),
            'isHide' => $this->boolean()->notNull()->defaultValue(false),
            'isLeaved' => $this->boolean()->notNull()->defaultValue(false),
            'orgEmail' => $this->string(255),
            'remark' => $this->string(1000),
            'hiredDate' => $this->dateTime(),
            'leavedDate' => $this->dateTime(),
            'sortOrder' => $this->string(64),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::EMPLOYEEDEPARTMENTS, [
            'id' => $this->primaryKey(),
            'employeeId' => $this->integer()->notNull(),
            'departmentId' => $this->integer()->notNull(),
            'primary' => $this->boolean()->notNull()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

//        $this->createTable('{{%dingtalk_employeeextra', [
//            'id' => $this->primaryKey(),
//            'employeeId' => $this->integer()->notNull(),
//
//            'dateCreated' => $this->dateTime()->notNull(),
//            'dateUpdated' => $this->dateTime()->notNull(),
//            'uid' => $this->uid(),
//        ]);

        // Contacts
        // ---------------------------------------------------------------------

        $this->createTable(Table::CONTACTLABELGROUPS, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'color' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONTACTLABELS, [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'sourceId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONTACTS, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'employeeId' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
            'mobile' => $this->string()->notNull(),
            'followerId' => $this->integer()->notNull(),
            'stateCode' => $this->string()->notNull()->defaultValue('86'),
            'companyName' => $this->string(),
            'position' => $this->string(),
            'address' => $this->string(),
            'remark' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONTACTLABELS_CONTACTS, [
            'id' => $this->primaryKey(),
            'labelId' => $this->integer()->notNull(),
            'contactId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONTACTSHARES_USERS, [
            'id' => $this->primaryKey(),
            'contactId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::CONTACTSHARES_DEPARTMENTS, [
            'id' => $this->primaryKey(),
            'contactId' => $this->integer()->notNull(),
            'departmentId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Robots
        // ---------------------------------------------------------------------

        $this->createTable(Table::ROBOTS, [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'handle' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Table::ROBOTWEBHOOKS, [
            'id' => $this->primaryKey(),
            'robotId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'rateLimit' =>$this->integer()->defaultValue(20),
            'rateWindow' => $this->integer()->defaultValue(60),
            'allowance' => $this->integer(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'dateAllowanceUpdated' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Processes
        // ---------------------------------------------------------------------

        $this->createTable(Table::PROCESSES, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'fieldLayoutId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'code' => $this->string()->notNull(),
            'settings' => $this->text(),
            'sortOrder' => $this->smallInteger(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        // Approvals
        // ---------------------------------------------------------------------

        $this->createTable(Table::APPROVALS, [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'processId' => $this->integer()->notNull(),
            'businessId' => $this->string(21)->notNull(),
            'instanceId' => $this->string(36)->notNull(),
            'originatorUserId' => $this->integer()->notNull(),
            'originatorDepartmentId' => $this->integer()->notNull(),
            'title' => $this->string(),
            'approveUserIds' => $this->string(),
            'ccUserIds' => $this->string(),
            'attachedInstanceIds' => $this->string(),
            'isAgree' => $this->boolean(),
            'bizAction' => $this->enum('bizAction', ['None', 'Modify', 'Revoke']),
            'formValues' => $this->text(),
            'operationRecords' => $this->text(),
            'tasks' => $this->text(),
            'status' => $this->enum('status', ['New', 'Running', 'Terminated', 'Completed', 'Canceled'])->notNull()->defaultValue('New'),
            'startDate' => $this->dateTime(),
            'finishDate' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * Create indexes.
     */
    public function createIndexes()
    {
        $this->createIndex(null, Table::APPROVALS, 'corporationId');
        $this->createIndex(null, Table::APPROVALS, ['processId']);
        $this->createIndex(null, Table::APPROVALS, ['instanceId'], true);
        $this->createIndex(null, Table::CALLBACKGROUPS, 'name', true);
        $this->createIndex(null, Table::CALLBACKREQUESTS, 'callbackId');
        $this->createIndex(null, Table::CALLBACKREQUESTS, 'corporationId');
        $this->createIndex(null, Table::CALLBACKREQUESTS, ['handled', 'dateCreated']);
        $this->createIndex(null, Table::CALLBACKS, 'groupId');
        $this->createIndex(null, Table::CALLBACKS, 'handle', true);
        $this->createIndex(null, Table::CALLBACKS, 'code', true);
        $this->createIndex(null, Table::CONTACTLABELGROUPS, 'corporationId');
        $this->createIndex(null, Table::CONTACTLABELGROUPS, ['corporationId', 'name'], true);
        $this->createIndex(null, Table::CONTACTLABELS, 'groupId');
        $this->createIndex(null, Table::CONTACTLABELS, 'name');
        $this->createIndex(null, Table::CONTACTLABELS, 'sourceId');
        $this->createIndex(null, Table::CONTACTLABELS, ['groupId', 'name'], true);
        $this->createIndex(null, Table::CONTACTLABELS, ['groupId', 'sourceId'], true);
        $this->createIndex(null, Table::CONTACTS, 'corporationId');
        $this->createIndex(null, Table::CONTACTS, 'employeeId', true);
        $this->createIndex(null, Table::CONTACTS, 'name');
        $this->createIndex(null, Table::CONTACTS, 'mobile');
        $this->createIndex(null, Table::CONTACTS, 'followerId');
        $this->createIndex(null, Table::CONTACTLABELS_CONTACTS, 'contactId');
        $this->createIndex(null, Table::CONTACTLABELS_CONTACTS, ['labelId', 'contactId'], true);
        $this->createIndex(null, Table::CONTACTSHARES_USERS, 'contactId');
        $this->createIndex(null, Table::CONTACTSHARES_USERS, 'userId');
        $this->createIndex(null, Table::CONTACTSHARES_USERS, ['contactId', 'userId'], true);
        $this->createIndex(null, Table::CONTACTSHARES_DEPARTMENTS, 'contactId');
        $this->createIndex(null, Table::CONTACTSHARES_DEPARTMENTS, 'departmentId');
        $this->createIndex(null, Table::CONTACTSHARES_DEPARTMENTS, ['contactId', 'departmentId'], true);
        $this->createIndex(null, Table::CORPORATIONS, 'name');
        $this->createIndex(null, Table::CORPORATIONS, 'handle', true);
        $this->createIndex(null, Table::CORPORATIONS, 'corpId', true);
        $this->createIndex(null, Table::CORPORATIONS, 'enabled');
        $this->createIndex(null, Table::CORPORATIONS, ['sortOrder', 'dateCreated']);
        $this->createIndex(null, Table::CORPORATIONCALLBACKSETTINGS, 'corporationId', true);
        $this->createIndex(null, Table::CORPORATIONCALLBACKS, 'corporationId');
        $this->createIndex(null, Table::CORPORATIONCALLBACKS, 'callbackId');
        $this->createIndex(null, Table::CORPORATIONCALLBACKS, ['corporationId', 'callbackId'], true);
        $this->createIndex(null, Table::DEPARTMENTS, 'corporationId');
        $this->createIndex(null, Table::DEPARTMENTS, 'dingDepartmentId');
        $this->createIndex(null, Table::DEPARTMENTS, ['corporationId', 'dingDepartmentId'], true);
        $this->createIndex(null, Table::DEPARTMENTS, 'name');
        $this->createIndex(null, Table::DEPARTMENTS, ['archived', 'dateCreated']);
        $this->createIndex(null, Table::DEPARTMENTS, 'sortOrder');
        $this->createIndex(null, Table::EMPLOYEES, 'corporationId');
        $this->createIndex(null, Table::EMPLOYEES, 'userId');
        $this->createIndex(null, Table::EMPLOYEES, ['corporationId', 'userId'], true);
        $this->createIndex(null, Table::EMPLOYEES, ['name']);
        $this->createIndex(null, Table::EMPLOYEES, ['mobile']);
        $this->createIndex(null, Table::EMPLOYEEDEPARTMENTS, ['employeeId']);
        $this->createIndex(null, Table::EMPLOYEEDEPARTMENTS, ['departmentId']);
        $this->createIndex(null, Table::EMPLOYEEDEPARTMENTS, ['employeeId', 'departmentId'], true);
        $this->createIndex(null, Table::EMPLOYEEDEPARTMENTS, ['primary']);
        $this->createIndex(null, Table::PROCESSES, 'corporationId');
        $this->createIndex(null, Table::PROCESSES, ['fieldLayoutId']);
        $this->createIndex(null, Table::PROCESSES, ['name'], true);
        $this->createIndex(null, Table::PROCESSES, ['handle'], true);
        $this->createIndex(null, Table::PROCESSES, ['type']);
        $this->createIndex(null, Table::PROCESSES, ['code'], true);
        $this->createIndex(null, Table::ROBOTS, ['handle'], true);
        $this->createIndex(null, Table::ROBOTS, ['name']);
        $this->createIndex(null, Table::ROBOTS, ['type']);
        $this->createIndex(null, Table::ROBOTWEBHOOKS, 'robotId');
        $this->createIndex(null, Table::ROBOTWEBHOOKS, 'name');
        $this->createIndex(null, Table::ROBOTWEBHOOKS, 'enabled');
    }

    /**
     * Add foreign keys.
     */
    public function addForeignKeys()
    {
        $this->addForeignKey(null, Table::APPROVALS, 'id', CraftTable::ELEMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::APPROVALS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::APPROVALS, 'processId', Table::PROCESSES, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::APPROVALS, 'originatorUserId', Table::EMPLOYEES, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::APPROVALS, 'originatorDepartmentId', Table::DEPARTMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CALLBACKREQUESTS, 'callbackId', Table::CALLBACKS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CALLBACKREQUESTS, 'corporationId', Table::CORPORATIONS, 'id', 'SET NULL');
        $this->addForeignKey(null, Table::CALLBACKS, 'groupId', Table::CALLBACKGROUPS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTLABELGROUPS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTLABELS, 'groupId', Table::CONTACTLABELGROUPS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTS, 'followerId', Table::EMPLOYEES, 'id');
        $this->addForeignKey(null, Table::CONTACTLABELS_CONTACTS, 'labelId', Table::CONTACTLABELS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTLABELS_CONTACTS, 'contactId', Table::CONTACTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTSHARES_USERS, 'contactId', Table::CONTACTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTSHARES_USERS, 'userId', Table::EMPLOYEES, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTSHARES_DEPARTMENTS, 'contactId', Table::CONTACTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CONTACTSHARES_DEPARTMENTS, 'departmentId', Table::DEPARTMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CORPORATIONCALLBACKSETTINGS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CORPORATIONCALLBACKS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::CORPORATIONCALLBACKS, 'callbackId', Table::CALLBACKS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::DEPARTMENTS, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::DEPARTMENTS, 'parentId', Table::DEPARTMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::EMPLOYEES, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::EMPLOYEES, 'id', CraftTable::ELEMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::EMPLOYEEDEPARTMENTS, 'employeeId', Table::EMPLOYEES, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::EMPLOYEEDEPARTMENTS, 'departmentId', Table::DEPARTMENTS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::PROCESSES, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
        $this->addForeignKey(null, Table::PROCESSES, 'fieldLayoutId', CraftTable::FIELDLAYOUTS, 'id', 'SET NULL');
        $this->addForeignKey(null, Table::ROBOTWEBHOOKS, 'robotId', Table::ROBOTS, 'id', 'CASCADE');
    }

    /**
     * Drop tables.
     */
    public function dropTables()
    {
        $this->dropTableIfExists(Table::APPROVALS);
        $this->dropTableIfExists(Table::PROCESSES);
        $this->dropTableIfExists(Table::ROBOTWEBHOOKS);
        $this->dropTableIfExists(Table::ROBOTS);
        $this->dropTableIfExists(Table::CONTACTSHARES_DEPARTMENTS);
        $this->dropTableIfExists(Table::CONTACTSHARES_USERS);
        $this->dropTableIfExists(Table::CONTACTLABELS_CONTACTS);
        $this->dropTableIfExists(Table::CONTACTS);
        $this->dropTableIfExists(Table::CONTACTLABELS);
        $this->dropTableIfExists(Table::CONTACTLABELGROUPS);
        $this->dropTableIfExists(Table::EMPLOYEEDEPARTMENTS);
        $this->dropTableIfExists(Table::EMPLOYEES);
        $this->dropTableIfExists(Table::DEPARTMENTS);
        $this->dropTableIfExists(Table::CORPORATIONCALLBACKS);
        $this->dropTableIfExists(Table::CALLBACKREQUESTS);
        $this->dropTableIfExists(Table::CALLBACKS);
        $this->dropTableIfExists(Table::CALLBACKGROUPS);
        $this->dropTableIfExists(Table::CORPORATIONCALLBACKSETTINGS);
        $this->dropTableIfExists(Table::CORPORATIONS);
    }
}