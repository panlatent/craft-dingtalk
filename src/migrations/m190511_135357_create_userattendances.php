<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use Craft;
use craft\db\Migration;

/**
 * m190511_132957_create_userattendances migration.
 */
class m190511_135357_create_userattendances extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%dingtalk_userattendances}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer()->notNull(),

        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m190511_132957_create_userattendances cannot be reverted.\n";
        return false;
    }
}
