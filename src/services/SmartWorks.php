<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use panlatent\craft\dingtalk\models\UserSmartWork;
use panlatent\craft\dingtalk\records\UserSmartWork as UserSmartWorkRecord;
use yii\base\Component;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class SmartWorks extends Component
{
    /**
     * @var UserSmartWork[]|null
     */
    private $_smartWorksByUserId;

    /**
     * @param string $userId
     * @return null|UserSmartWork
     */
    public function getSmartWorkByUserId(string $userId)
    {
        if ($this->_smartWorksByUserId && array_key_exists($userId, $this->_smartWorksByUserId)) {
            return $this->_smartWorksByUserId[$userId];
        }

        $result = $this->_createQuery()
            ->where(['userId' => $userId])
            ->one();

        return $this->_smartWorksByUserId[$userId] = $result ? $this->createSmartWork($result) : null;
    }

    /**
     * @param mixed $config
     * @return UserSmartWork
     */
    public function createSmartWork($config): UserSmartWork
    {
        if (isset($config['settings'])) {
            $settings = ArrayHelper::remove($config, 'settings');
            if (is_string($settings)) {
                $settings = Json::decode($settings);
            }
            $config = array_merge($config, $settings);
        }
        $smartWork = new UserSmartWork($config);

        return $smartWork;
    }

    /**
     * @param UserSmartWork $smartWork
     * @param bool $runValidation
     * @return bool
     */
    public function saveSmartWork(UserSmartWork $smartWork, bool $runValidation = true): bool
    {
        if ($runValidation && !$smartWork->validate()) {
            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();
        try {
            $smartWorkRecord = UserSmartWorkRecord::findOne(['userId' => $smartWork->userId]);
            if (!$smartWorkRecord) {
                $smartWorkRecord = new UserSmartWorkRecord();
            }

            $settings = $smartWork->toArray();
            unset($settings['id'], $settings['userId']);

            $smartWorkRecord->userId = $smartWork->userId;
            $smartWorkRecord->settings = Json::encode($settings);
            $smartWorkRecord->save(false);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    /**
     * @return Query
     */
    private function _createQuery(): Query
    {
        return (new Query())
            ->select(['id', 'userId', 'settings'])
            ->from('{{%dingtalk_usersmartworks}}');
    }
}