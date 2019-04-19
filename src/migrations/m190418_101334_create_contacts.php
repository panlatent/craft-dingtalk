<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m190418_101334_create_contacts migration.
 */
class m190418_101334_create_contacts extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_contactlabels_contacts');
        $this->dropTableIfExists('{{%dingtalk_contacts}}');
        $this->dropTableIfExists('{{%dingtalk_contactlabels}}');
        $this->dropTableIfExists('{{%dingtalk_contactlabelgroups}}');
    }
}
