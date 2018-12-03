<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\queue\jobs;

use Craft;
use craft\queue\BaseJob;
use panlatent\craft\dingtalk\Plugin;

/**
 * Class SyncContactsJob
 *
 * @package panlatent\craft\dingtalk\queue\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncContactsJob extends BaseJob
{
    /**
     * @var bool
     */
    public $enableDepartments = true;

    /**
     * @var bool
     */
    public $enableUsers = true;

    /**
     * @var bool
     */
    public $withLeavedUsers = true;


    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            if ($this->enableDepartments) {
                Plugin::$plugin->getDepartments()->pullAllDepartments();
            }


            if ($this->enableUsers) {
                Plugin::$plugin->getUsers()->pullIncumbentUsers();
            }

            if ($this->enableUsers && $this->withLeavedUsers) {
                Plugin::$plugin->getUsers()->pullLeavedUsers();
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }
    }

    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Dingtalk Contacts');
    }
}