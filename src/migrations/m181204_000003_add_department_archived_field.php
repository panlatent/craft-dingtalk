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
 * m181204_000003_add_department_archived_field migration.
 */
class m181204_000003_add_department_archived_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%dingtalk_departments}}', 'archived', $this->boolean()->notNull()->defaultValue(0)->after('sortOrder'));
        $this->createIndex(null, '{{%dingtalk_departments}}', ['archived', 'dateCreated']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dingtalk_departments}}', 'archived');
        $this->dropIndex('dingtalk_departments_archived_dateCreated_idx','{{%dingtalk_departments}}');
    }
}
