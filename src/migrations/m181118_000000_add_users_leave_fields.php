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
 * m181118_000000_add_users_leave_field migration.
 */
class m181118_000000_add_users_leave_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%dingtalk_users}}', 'isLeaved', $this->boolean()
            ->defaultValue(false)
            ->after('isHide'));

        $this->addColumn('{{%dingtalk_users}}', 'dateLeaved', $this->dateTime()
            ->after('dateHired'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%dingtalk_users}}', 'isLeaved');
        $this->dropColumn('{{%dingtalk_users}}', 'dateLeaved');
    }
}
