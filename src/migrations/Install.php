<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * Class Install
 *
 * @package panlatent\craft\dingtalk\migrations
 * @author Panlatent <panlatent@gmail.com>
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Corporations
        // =====================================================================

        $this->createTable('{{%dingtalk_corporations}}', [
            'id' => $this->primaryKey(),
            'primary' => $this->boolean()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'corpId' => $this->string()->notNull(),
            'corpSecret' => $this->string()->notNull(),
            'hasUrls' => $this->boolean()->notNull()->defaultValue(1),
            'url' => $this->string(),
            'callbackEnabled' => $this->boolean()->notNull()->defaultValue(false),
            'callbackToken' => $this->string(),
            'callbackAesKey' => $this->string(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'archived' => $this->boolean()->notNull()->defaultValue(false),
            'sortOrder' => $this->smallInteger(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_corporations}}', 'name');
        $this->createIndex(null, '{{%dingtalk_corporations}}', 'handle', true);
        $this->createIndex(null, '{{%dingtalk_corporations}}', 'corpId', true);
        $this->createIndex(null, '{{%dingtalk_corporations}}', 'enabled');
        $this->createIndex(null, '{{%dingtalk_corporations}}', ['sortOrder', 'dateCreated']);

        // Departments
        // =====================================================================

        $this->createTable('{{%dingtalk_departments}}', [
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

        $this->createIndex(null, '{{%dingtalk_departments}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_departments}}', 'dingDepartmentId');
        $this->createIndex(null, '{{%dingtalk_departments}}', ['corporationId', 'dingDepartmentId'], true);
        $this->createIndex(null, '{{%dingtalk_departments}}', 'name');
        $this->createIndex(null, '{{%dingtalk_departments}}', ['archived', 'dateCreated']);
        $this->createIndex(null, '{{%dingtalk_departments}}', 'sortOrder');

        $this->addForeignKey(null, '{{%dingtalk_departments}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_departments}}', 'parentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');

        // Users
        // =====================================================================

        $this->createTable('{{%dingtalk_users}}', [
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

        $this->createIndex(null, '{{%dingtalk_users}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_users}}', 'userId');
        $this->createIndex(null, '{{%dingtalk_users}}', ['corporationId', 'userId'], true);
        $this->createIndex(null, '{{%dingtalk_users}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_users}}', ['mobile']);

        $this->addForeignKey(null, '{{%dingtalk_users}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_users}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);

        // Create user departments
        $this->createTable('{{%dingtalk_userdepartments}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),
            'departmentId' => $this->integer()->notNull(),
            'primary' => $this->boolean()->notNull()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['userId']);
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['departmentId']);
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['userId', 'departmentId'], true);
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['primary']);

        $this->addForeignKey(null, '{{%dingtalk_userdepartments}}', 'userId', '{{%dingtalk_users}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_userdepartments}}', 'departmentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');

        // Contacts
        // =========================================================================

        $this->createTable('{{%dingtalk_contactlabelgroups}}', [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'color' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_contactlabelgroups}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_contactlabelgroups}}', ['corporationId', 'name'], true);
        $this->addForeignKey(null, '{{%dingtalk_contactlabelgroups}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_contactlabels}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'sourceId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_contactlabels}}', 'groupId');
        $this->createIndex(null, '{{%dingtalk_contactlabels}}', 'name');
        $this->createIndex(null, '{{%dingtalk_contactlabels}}', 'sourceId');
        $this->createIndex(null, '{{%dingtalk_contactlabels}}', ['groupId', 'name'], true);
        $this->createIndex(null, '{{%dingtalk_contactlabels}}', ['groupId', 'sourceId'], true);
        $this->addForeignKey(null, '{{%dingtalk_contactlabels}}', 'groupId', '{{%dingtalk_contactlabelgroups}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_contacts}}', [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'userId' => $this->string()->notNull(),
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

        $this->createIndex(null, '{{%dingtalk_contacts}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_contacts}}', 'userId', true);
        $this->createIndex(null, '{{%dingtalk_contacts}}', 'name');
        $this->createIndex(null, '{{%dingtalk_contacts}}', 'mobile');
        $this->createIndex(null, '{{%dingtalk_contacts}}', 'followerId');
        $this->addForeignKey(null, '{{%dingtalk_contacts}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_contacts}}', 'followerId', '{{%dingtalk_users}}', 'id');

        $this->createTable('{{%dingtalk_contactlabels_contacts}}', [
            'id' => $this->primaryKey(),
            'labelId' => $this->integer()->notNull(),
            'contactId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_contactlabels_contacts}}', 'contactId');
        $this->createIndex(null, '{{%dingtalk_contactlabels_contacts}}', ['labelId', 'contactId'], true);
        $this->addForeignKey(null, '{{%dingtalk_contactlabels_contacts}}', 'labelId', '{{%dingtalk_contactlabels}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_contactlabels_contacts}}', 'contactId', '{{%dingtalk_contacts}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_contactshares_users}}', [
            'id' => $this->primaryKey(),
            'contactId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_contactshares_users}}', 'contactId');
        $this->createIndex(null, '{{%dingtalk_contactshares_users}}', 'userId');
        $this->createIndex(null, '{{%dingtalk_contactshares_users}}', ['contactId', 'userId'], true);
        $this->addForeignKey(null, '{{%dingtalk_contactshares_users}}', 'contactId', '{{%dingtalk_contacts}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_contactshares_users}}', 'userId', '{{%dingtalk_users}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_contactshares_departments}}', [
            'id' => $this->primaryKey(),
            'contactId' => $this->integer()->notNull(),
            'departmentId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_contactshares_departments}}', 'contactId');
        $this->createIndex(null, '{{%dingtalk_contactshares_departments}}', 'departmentId');
        $this->createIndex(null, '{{%dingtalk_contactshares_departments}}', ['contactId', 'departmentId'], true);
        $this->addForeignKey(null, '{{%dingtalk_contactshares_departments}}', 'contactId', '{{%dingtalk_contacts}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_contactshares_departments}}', 'departmentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');

        // Robots
        // =====================================================================

        $this->createTable('{{%dingtalk_robots}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'handle' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_robots}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['type']);

        $this->createTable('{{%dingtalk_robotwebhooks}}', [
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

        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', 'robotId');
        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', 'name');
        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', 'enabled');

        $this->addForeignKey(null, '{{%dingtalk_robotwebhooks}}', 'robotId', '{{%dingtalk_robots}}', 'id', 'CASCADE');

        // Processes
        // =====================================================================

        $this->createTable('{{%dingtalk_processes}}', [
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

        $this->createIndex(null, '{{%dingtalk_processes}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_processes}}', ['fieldLayoutId']);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['name'], true);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['type']);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['code'], true);

        $this->addForeignKey(null, '{{%dingtalk_processes}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_processes}}', 'fieldLayoutId', '{{%fieldlayouts}}', 'id', 'SET NULL');

        // Approvals
        // =====================================================================

        $this->createTable('{{%dingtalk_approvals}}', [
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

        $this->createIndex(null, '{{%dingtalk_processes}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_approvals}}', ['processId']);
        $this->createIndex(null, '{{%dingtalk_approvals}}', ['instanceId'], true);

        $this->addForeignKey(null, '{{%dingtalk_approvals}}', 'id', '{{%elements}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_processes}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_approvals}}', 'processId', '{{%dingtalk_processes}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_approvals}}', 'originatorUserId', '{{%dingtalk_users}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_approvals}}', 'originatorDepartmentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_approvals}}');
        $this->dropTableIfExists('{{%dingtalk_processes}}');
        $this->dropTableIfExists('{{%dingtalk_robotwebhooks}}');
        $this->dropTableIfExists('{{%dingtalk_robots}}');
        $this->dropTableIfExists('{{%dingtalk_contactshares_departments}}');
        $this->dropTableIfExists('{{%dingtalk_contactshares_users}}');
        $this->dropTableIfExists('{{%dingtalk_contactlabels_contacts}}');
        $this->dropTableIfExists('{{%dingtalk_contacts}}');
        $this->dropTableIfExists('{{%dingtalk_contactlabels}}');
        $this->dropTableIfExists('{{%dingtalk_contactlabelgroups}}');
        $this->dropTableIfExists('{{%dingtalk_userdepartments}}');
        $this->dropTableIfExists('{{%dingtalk_users}}');
        $this->dropTableIfExists('{{%dingtalk_departments}}');
        $this->dropTableIfExists('{{%dingtalk_corporations}}');
    }
}