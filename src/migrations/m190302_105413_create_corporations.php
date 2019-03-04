<?php

namespace panlatent\craft\dingtalk\migrations;

use Craft;
use craft\db\Migration;
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\Plugin;
use yii\db\Query;

/**
 * m190302_105413_create_corporations migration.
 */
class m190302_105413_create_corporations extends Migration
{
    public $tables = [
        Table::APPROVALS,
        Table::DEPARTMENTS,
        Table::PROCESSES,
        Table::USERS,
    ];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable(Table::CORPORATIONS, [
            'id' => $this->primaryKey(),
            'primary' => $this->boolean()->notNull(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'corpId' => $this->string()->notNull(),
            'corpSecret' => $this->string()->notNull(),
            'hasUrls' => $this->boolean()->notNull()->defaultValue(1),
            'url' => $this->string(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'archived' => $this->boolean()->notNull()->defaultValue(false),
            'sortOrder' => $this->smallInteger(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, Table::CORPORATIONS, 'name');
        $this->createIndex(null, Table::CORPORATIONS, 'handle', true);
        $this->createIndex(null, Table::CORPORATIONS, 'corpId', true);
        $this->createIndex(null, Table::CORPORATIONS, 'enabled');
        $this->createIndex(null, Table::CORPORATIONS, ['sortOrder', 'dateCreated']);

        $info = Craft::$app->getPlugins()->getPluginInfo('dingtalk');

        $enabled = $info['isEnabled'] === true;
        if ($enabled) {
            if ($this->getDb()->createCommand())

            $this->getDb()->createCommand()->insert(Table::CORPORATIONS, [
                'primary' => true,
                'name' => 'Default Corporation',
                'handle' => 'default',
                'corpId' => Plugin::$plugin->getSettings()->corpId ?? '',
                'corpSecret' => Plugin::$plugin->getSettings()->corpSecret ?? '',
                'hasUrls' => false,
                'enabled' => true,
                'archived' => false,
            ])->execute();

            $corporationId = $this->getDb()->getLastInsertID();
        }

        foreach ($this->tables as $table) {
            $this->_addCorporationColumn($table);
        }

        if ($enabled && !empty($corporationId)) {
            foreach ($this->tables as $table) {
                $this->_updateCorporationColumn($table, $corporationId);
            }
        }

        foreach ($this->tables as $table) {
            $this->_createCorporationIndex($table);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ((new Query())->from(Table::CORPORATIONS)->count() > 1) {
            return false;
        }

        foreach ($this->tables as $table) {
            $this->dropForeignKey(substr($table, 3, -2) . '_corporationId_fk', $table);
            $this->dropColumn($table, 'corporationId');
        }

        $this->dropTableIfExists(Table::CORPORATIONS);

        return true;
    }

    /**
     * @param string $table
     */
    private function _addCorporationColumn(string $table)
    {
        $this->addColumn($table, 'corporationId', $this->integer()->after('id'));
    }

    /**
     * @param string $table
     * @param int $corporationId
     */
    private function _updateCorporationColumn(string $table, int $corporationId)
    {
        $this->getDb()->createCommand()->update($table, [
            'corporationId' => $corporationId
        ])->execute();
    }

    private function _createCorporationIndex(string $table)
    {
        $this->createIndex(null, $table, 'corporationId');
        $this->addForeignKey(null, $table, 'corporationId', Table::CORPORATIONS, 'id', 'CASCADE');
    }
}
