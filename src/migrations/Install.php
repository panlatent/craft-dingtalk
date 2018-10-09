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
        $this->addForeignKey(null, '{{%dingtalk_departments}}', 'parentId', '{{%dingtalk_departments}}', 'id');

        $this->createTable('{{%dingtalk_users}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'name' => $this->string(64),
            'position' => $this->string(64),
            'tel' => $this->string(64),
            'isAdmin' => $this->boolean(),
            'isBoss' => $this->boolean(),
            'isLeader' => $this->boolean(),
            'avatar' => $this->string(255),
            'jobNumber' => $this->string(255),
            'email' => $this->string(255),
            'active' => $this->boolean(),
            'mobile' => $this->string(255),
            'isHide' => $this->boolean(),
            'orgEmail' => $this->string(255),
            'dateHired' => $this->dateTime(),
            'settings' => $this->text(),
            'remark' => $this->string(1000),
            'sortOrder' => $this->string(64),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_users}}', ['userId'], true);
        $this->createIndex(null, '{{%dingtalk_users}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_users}}', ['mobile']);

        $this->addForeignKey(null, '{{%dingtalk_users}}', 'id', '{{%elements}}', 'id', 'CASCADE', null);

        $this->createTable('{{%dingtalk_userdepartments}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'departmentId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['userId']);
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['departmentId']);
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['userId', 'departmentId'], true);
        $this->addForeignKey(null, '{{%dingtalk_userdepartments}}', 'userId', '{{%dingtalk_users}}', 'userId');
        $this->addForeignKey(null, '{{%dingtalk_userdepartments}}', 'departmentId', '{{%departments}}', 'id');
    }

    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_userdepartments}}');
        $this->dropTableIfExists('{{%dingtalk_users}}');
        $this->dropTableIfExists('{{%dingtalk_departments}}');
    }
}