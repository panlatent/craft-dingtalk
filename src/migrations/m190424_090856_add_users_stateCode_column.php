<?php

namespace panlatent\craft\dingtalk\migrations;

use Craft;
use craft\db\Migration;
use panlatent\craft\dingtalk\elements\User;

/**
 * m190424_090856_add_users_stateCode_column migration.
 */
class m190424_090856_add_users_stateCode_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%dingtalk_users}}', 'stateCode', $this->string()->defaultValue('86')->after('mobile'));

        $elements = Craft::$app->getElements();

        foreach (User::find()->all() as $user) {
            if (preg_match('#^\+?(\d+)-(\d+)$#', $user->mobile, $match)) {
                $user->stateCode = $match[1];
                $user->mobile = $match[2];
            } else {
                $user->stateCode = '86';
            }

            $elements->saveElement($user);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dingtalk_users}}', 'stateCode');
    }
}
