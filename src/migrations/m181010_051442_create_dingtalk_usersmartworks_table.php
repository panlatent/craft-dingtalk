<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181010_051442_create_dingtalk_usersmartworks_table migration.
 */
class m181010_051442_create_dingtalk_usersmartworks_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_usersmartworks}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_usersmartworks}}', ['userId'], true);
        $this->addForeignKey(null, '{{%dingtalk_usersmartworks}}', 'userId', '{{%dingtalk_users}}', 'userId');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_usersmartworks}}');
    }
}
