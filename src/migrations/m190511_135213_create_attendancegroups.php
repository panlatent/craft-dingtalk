<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m190511_135213_create_attendancegroups migration.
 */
class m190511_135213_create_attendancegroups extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_attendcegroupclasses}}', [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'dingClassId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'sections' => $this->text(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_attendcegroupclasses}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_attendcegroupclasses}}', 'dingClassId');
        $this->createIndex(null, '{{%dingtalk_attendcegroupclasses}}', ['corporationId', 'dingClassId'], true);
        $this->createIndex(null, '{{%dingtalk_attendcegroupclasses}}', 'name');
        $this->addForeignKey(null, '{{%dingtalk_attendcegroupclasses}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_attendancegroups}}', [
            'id' => $this->primaryKey(),
            'corporationId' => $this->integer()->notNull(),
            'dingGroupId' => $this->string()->notNull(),
            'primary' => $this->boolean(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'defaultClassId' => $this->integer(),
            'memberCount' => $this->integer(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_attendancegroups}}', 'corporationId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups}}', 'dingGroupId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups}}', ['corporationId', 'dingGroupId'], true);
        $this->createIndex(null, '{{%dingtalk_attendancegroups}}', 'name');
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups}}', 'corporationId', '{{%dingtalk_corporations}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups}}', 'defaultClassId', '{{%dingtalk_attendcegroupclasses}}', 'id', 'SET NULL');

        $this->createTable('{{%dingtalk_attendancegroups_classes}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'classId' => $this->integer()->notNull(),
            'week' => $this->tinyInteger(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_attendancegroups_classes}}', 'groupId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups_classes}}', 'classId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups_classes}}', ['groupId', 'classId'], true);
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups_classes}}', 'groupId', '{{%dingtalk_attendancegroups}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups_classes}}', 'classId', '{{%dingtalk_attendcegroupclasses}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_attendancegroupmanagers}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'managerId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_attendancegroupmanagers}}', 'groupId');
        $this->createIndex(null, '{{%dingtalk_attendancegroupmanagers}}', 'managerId');
        $this->createIndex(null, '{{%dingtalk_attendancegroupmanagers}}', ['groupId', 'managerId'], true);
        $this->addForeignKey(null, '{{%dingtalk_attendancegroupmanagers}}', 'groupId', '{{%dingtalk_attendancegroups}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_attendancegroupmanagers}}', 'managerId', '{{%dingtalk_users}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_attendancegroups_users}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'type' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_attendancegroups_users}}', 'groupId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups_users}}', 'userId');
        $this->createIndex(null, '{{%dingtalk_attendancegroups_users}}', ['groupId', 'userId'], true);
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups_users}}', 'groupId', '{{%dingtalk_attendancegroups}}', 'id', 'CASCADE');
        $this->addForeignKey(null, '{{%dingtalk_attendancegroups_users}}', 'userId', '{{%dingtalk_users}}', 'id', 'CASCADE');

        $this->createTable('{{%dingtalk_attendances}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'planId' => $this->bigInteger(),
            'dingAttendanceId' => $this->bigInteger()->notNull(),
            'workDate' => $this->dateTime()->notNull(),
            'checkType' => $this->enum('checkType', ['OnDuty', 'OffDuty'])->notNull(),
            'sourceType' => $this->enum('sourceType', ['ATM', 'Beacon', 'DingATM', 'User', 'Boss', 'Approve', 'System', 'AutoCheck'])->notNull(),
            'timeResult' => $this->enum('timeResult', ['Normal', 'Early', 'Late', 'SeriousLate', 'Absenteeism', 'NotSigned'])->notNull(),
            'locationResult' => $this->enum('locationResult', ['Normal', 'Outside'])->notNull(),
            'approveId' => $this->integer(),
            'processInstanceId' => $this->integer(),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190511_135213_create_attendancegroups cannot be reverted.\n";
        return false;
    }
}
