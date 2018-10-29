<?php

namespace panlatent\craft\dingtalk\migrations;

use Craft;
use craft\db\Migration;

/**
 * m181026_000000_create_robots migration.
 */
class m181026_000000_create_robots extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_robots}}', [
            'id' => $this->primaryKey(),
            'handle' => $this->string(255)->notNull(),
            'name' => $this->string(255),
            'type' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_robots}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['type']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_robots}}');
    }
}
