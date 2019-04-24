<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m190424_042045_create_contactshares_users migration.
 */
class m190424_042045_create_contactshares_users extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_contactshares_users}}');
    }
}
