<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181128_033802_advance_users_fields migration.
 */
class m181128_033802_advance_users_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('{{%dingtalk_users}}', 'dateHired', 'hiredDate');
        $this->renameColumn('{{%dingtalk_users}}', 'dateLeaved', 'leavedDate');
        $this->dropColumn('{{%dingtalk_users}}', 'settings');
        $this->dropForeignKey('dingtalk_userproperties_userId_fk', '{{%dingtalk_userproperties}}');

        $results = $this->db->createCommand("SELECT id,userId FROM {{%dingtalk_userproperties}}")->queryAll();
        foreach ($results as $result) {
            $userId = $this->db->createCommand("SELECT id FROM {{%dingtalk_users}} WHERE userId={$result['userId']}")->queryScalar();
            if (empty($userId)) {
                $this->db->createCommand()->delete('{{%dingtalk_userproperties}}', ['userId' => $result['userId']])->execute();
            } else {
                $this->db->createCommand()->update('{{%dingtalk_userproperties}}', [
                    'userId' => $userId
                ], [
                    'id' => $result['id'],
                ])->execute();
            }
        }

        $this->alterColumn('{{%dingtalk_userproperties}}', 'userId', $this->integer()->notNull());
        $this->createIndex(null, '{{%dingtalk_userproperties}}', 'userId');
        $this->addForeignKey(null, '{{%dingtalk_userproperties}}', 'userId', '{{%dingtalk_users}}', 'id', 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
