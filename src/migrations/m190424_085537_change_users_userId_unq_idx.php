<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m190424_085537_change_users_userId_unq_idx migration.
 */
class m190424_085537_change_users_userId_unq_idx extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropIndex('dingtalk_users_userId_unq_idx', '{{%dingtalk_users}}');
        $this->createIndex(null, '{{%dingtalk_users}}', 'userId');
        $this->createIndex(null, '{{%dingtalk_users}}', ['corporationId', 'userId'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
