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
 * m181208_000005_add_robotwebhook_enable_field migration.
 */
class m181208_000005_add_robotwebhook_enable_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('{{%dingtalk_robots}}', 'handle', $this->string()->notNull()->after('name'));
        $this->addColumn('{{%dingtalk_robotwebhooks}}', 'enabled', $this->boolean()->notNull()->defaultValue(true)->after('allowance'));
        $this->createIndex(null, '{{%dingtalk_robotwebhooks}}', ['enabled']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dingtalk_robotwebhooks}}', 'enabled');
        $this->alterColumn('{{%dingtalk_robots}}', 'name', $this->string()->after('handle'));
    }
}
