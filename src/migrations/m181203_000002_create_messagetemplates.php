<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * Class m181203_000002_create_messagetemplates
 *
 * @package panlatent\craft\dingtalk\migrations
 * @author Panlatent <panlatent@gmail.com>
 */
class m181203_000002_create_messagetemplates extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_messagetemplates}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'template' => $this->text(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_messagetemplates}}', ['name']);
        $this->createIndex(null, '{{%dingtalk_messagetemplates}}', ['handle'], true);
        $this->createIndex(null, '{{%dingtalk_messagetemplates}}', ['type']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_messagetemplates}}');
    }
}
