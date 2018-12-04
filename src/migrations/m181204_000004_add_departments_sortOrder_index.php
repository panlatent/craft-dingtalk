<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181204_000004_add_departments_sortOrder_index migration.
 */
class m181204_000004_add_departments_sortOrder_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex(null, '{{%dingtalk_departments}}', ['sortOrder']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('', '{{%dingtalk_departments}}');
    }
}
