<?php

namespace panlatent\craft\dingtalk\migrations;

use craft\db\Migration;

/**
 * m181128_053900_advance_user_departments migration.
 */
class m181128_053900_advance_user_departments extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropForeignKey('dingtalk_userdepartments_userId_fk', '{{%dingtalk_userdepartments}}');

        $results = $this->db->createCommand("SELECT id,userId FROM {{%dingtalk_userdepartments}}")->queryAll();
        foreach ($results as $result) {
            $userId = $this->db->createCommand("SELECT id FROM {{%dingtalk_users}} WHERE userId={$result['userId']}")->queryScalar();
            if (empty($userId)) {
                $this->db->createCommand()->delete('{{%dingtalk_userdepartments}}', ['userId' => $result['userId']])->execute();
            } else {
                $this->db->createCommand()->update('{{%dingtalk_userdepartments}}', [
                    'userId' => $userId
                ], [
                    'id' => $result['id'],
                ])->execute();
            }
        }

        $this->alterColumn('{{%dingtalk_userdepartments}}', 'userId', $this->integer()->notNull());
        $this->addForeignKey(null, '{{%dingtalk_userdepartments}}', 'userId', '{{%dingtalk_users}}', 'id', 'CASCADE');

        $this->addColumn('{{%dingtalk_userdepartments}}', 'primary', $this->boolean()->notNull()->defaultValue(false)->after('departmentId'));
        $this->createIndex(null, '{{%dingtalk_userdepartments}}', ['primary']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return false;
    }
}
