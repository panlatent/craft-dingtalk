<?php

namespace panlatent\craft\dingtalk\migrations;

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
            'name' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_robots}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_robots}}', ['type']);

        $this->createTable('{{%dingtalk_robotwebhooks}}', [
            'id' => $this->primaryKey(),
            'robotId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'rateLimit' =>$this->integer()->defaultValue(20),
            'rateWindow' => $this->integer()->defaultValue(60),
            'allowance' => $this->integer(),
            'dateAllowanceUpdated' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', ['robotId']);
        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', ['name']);
        $this->addForeignKey(null, '{{%dingtalk_robotwebhooks}}', 'robotId', '{{%dingtalk_robots}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_robots}}');
    }
}
