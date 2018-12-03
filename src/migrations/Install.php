<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

class Install extends Migration
{
    public function safeUp()
    {
        // Create departments
        $this->createTable('{{%dingtalk_departments}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'parentId' => $this->integer(),
            'settings' => $this->text(),
            'sortOrder' => $this->smallInteger(6)->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_departments}}', ['name']);
        $this->addForeignKey(null, '{{%dingtalk_departments}}', 'parentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');

        // Create users
        $this->createTable('{{%dingtalk_users}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'name' => $this->string(64)->notNull(),
            'position' => $this->string(64),
            'tel' => $this->string(64),
            'avatar' => $this->string(255),
            'jobNumber' => $this->string(255),
            'email' => $this->string(255),
            'mobile' => $this->string(255),
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

        $this->createIndex(null, '{{%dingtalk_users}}', ['userId'], true);
        $this->createIndex(null, '{{%dingtalk_users}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_users}}', ['mobile']);
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
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_userdepartments}}');
        $this->dropTableIfExists('{{%dingtalk_users}}');
        $this->dropTableIfExists('{{%dingtalk_departments}}');
    }
}