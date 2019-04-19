<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;
use panlatent\craft\dingtalk\db\Table;

/**
 * m190415_114816_add_corporations_callback migration.
 */
class m190415_114816_add_corporations_callback extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(Table::CORPORATIONS, 'callbackEnabled', $this->string()->notNull()->defaultValue(false)->after('url'));
        $this->addColumn(Table::CORPORATIONS, 'callbackToken', $this->string()->after('callbackEnabled'));
        $this->addColumn(Table::CORPORATIONS, 'callbackAesKey', $this->string()->after('callbackToken'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn(Table::CORPORATIONS, 'callbackEnabled');
        $this->dropColumn(Table::CORPORATIONS, 'callbackToken');
        $this->dropColumn(Table::CORPORATIONS, 'callbackAesKey');
    }
}
