<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181123_134534_create_processes migration.
 */
class m181123_134534_create_processes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_processes}}', [
            'id' => $this->primaryKey(),
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

        $this->createIndex(null, '{{%dingtalk_processes}}', ['fieldLayoutId']);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['name'], true);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['type']);
        $this->createIndex(null, '{{%dingtalk_processes}}', ['code'], true);

        $this->addForeignKey(null, '{{%dingtalk_processes}}', 'fieldLayoutId', '{{%fieldlayouts}}', 'id', 'SET NULL');

        $this->createTable('{{%dingtalk_approvals}}', [
            'id' => $this->primaryKey(),
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
            'startData' => $this->dateTime(),
            'finishDate' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_approvals}}', ['processId']);
        $this->createIndex(null, '{{%dingtalk_approvals}}', ['instanceId'], true);

        $this->addForeignKey(null, '{{%dingtalk_approvals}}', 'id', '{{%elements}}', 'id', 'CASCADE');
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
    }
}
