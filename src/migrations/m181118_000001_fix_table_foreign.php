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
 * m181118_000001_fix_table_foreign migration.
 */
class m181118_000001_fix_table_foreign extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('dingtalk_departments_parentId_fk', '{{%dingtalk_departments}}');
        $this->addForeignKey(null, '{{%dingtalk_departments}}', 'parentId', '{{%dingtalk_departments}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
       return false;
    }
}
