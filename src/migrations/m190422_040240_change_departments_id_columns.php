<?php

namespace panlatent\craft\dingtalk\migrations;

use Craft;
use craft\db\Migration;
use yii\db\Query;

/**
 * m190422_040240_change_departments_id_columns migration.
 */
class m190422_040240_change_departments_id_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%dingtalk_departments}}', 'dingDepartmentId', $this->integer()->notNull()->after('corporationId'));

        $ids = (new Query())
            ->select(['id'])
            ->from('{{%dingtalk_departments}}')
            ->column();

        foreach ($ids as $id) {
            $this->update('{{%dingtalk_departments}}', [
                'dingDepartmentId' => $id
            ], [
                'id' => $id,
            ]);
        }

        $this->createIndex(null, '{{%dingtalk_departments}}', 'dingDepartmentId');
        $this->createIndex(null, '{{%dingtalk_departments}}', ['corporationId', 'dingDepartmentId'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dingtalk_departments}}', 'dingDepartmentId');
    }
}
