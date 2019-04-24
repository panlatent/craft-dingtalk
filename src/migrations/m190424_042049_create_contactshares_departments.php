<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m190424_042049_create_contactshares_departments migration.
 */
class m190424_042049_create_contactshares_departments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
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
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_contactshares_departments}}');
    }
}
