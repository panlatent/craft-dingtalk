<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;
use yii\helpers\Json;

/**
 * m181118_000000_advance_usersmartworks_table migration.
 */
class m181118_000000_advance_usersmartworks_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $results = $this->db->createCommand("SELECT userId,settings FROM {{%dingtalk_usersmartworks}}")->query();

        $this->createTable('{{%dingtalk_userproperties}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'key' => $this->string(255)->notNull(),
            'value' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_userproperties}}', ['userId', 'key'], true);
        $this->addForeignKey(null, '{{%dingtalk_userproperties}}', 'userId', '{{%dingtalk_users}}', 'userId', 'CASCADE');

        foreach ($results as $result) {
            $fields = Json::decode($result['settings']);
            $rows = [];
            foreach ($fields as $field => $value) {
                if (empty($value)) {
                    continue;
                }
                $rows[] = [
                    $result['userId'],
                    $field,
                    $value,
                ];
            }

            if (!empty($rows)) {
                $this->db
                    ->createCommand()
                    ->batchInsert('{{%dingtalk_userproperties}}', ['userId', 'key', 'value'], $rows)
                    ->execute();
            }
        }

        $this->dropTable('{{%dingtalk_usersmartworks}}');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('{{%dingtalk_userproperties}}');

        $this->createTable('{{%dingtalk_usersmartworks}}', [
            'id' => $this->primaryKey(),
            'userId' => $this->string(32)->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%dingtalk_usersmartworks}}', ['userId'], true);
        $this->addForeignKey(null, '{{%dingtalk_usersmartworks}}', 'userId', '{{%dingtalk_users}}', 'userId', 'CASCADE');
    }
}
