<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181129_023627_drop_userproperties migration.
 */
class m181129_023627_drop_userproperties extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTableIfExists('{{%dingtalk_userproperties}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
