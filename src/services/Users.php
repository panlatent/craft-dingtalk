<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use yii\base\Component;
use yii\db\Query;

class Users extends Component
{
    /**
     * @param string $userId
     * @return array
     */
    public function getPropertiesByUserId(string $userId): array
    {
        return $this->_createPropertyQuery()
            ->select('value')
            ->where(['userId' => $userId])
            ->indexBy('key')
            ->column();
    }

    /**
     * @param string $userId
     * @return string[]
     */
    public function getPropertyFieldsByUserId(string $userId): array
    {
        return $this->_createPropertyQuery()
            ->select('key')
            ->where(['userId' => $userId])
            ->column();
    }

    /**
     * @param string $userId
     * @param array $properties
     * @return bool
     */
    public function saveProperties(string $userId, array $properties): bool
    {
        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            foreach ($properties as $key => $value) {
                $db->createCommand()->upsert('{{%dingtalk_userproperties}}', [
                    'userId' => $userId,
                    'key' => $key,
                ], [
                    'value' => $value,
                ]);
            }

            $activeKeys = array_keys($properties);
            $invalidKeys = [];
            foreach ($this->getPropertyFieldsByUserId($userId) as $key) {
                if (!in_array($key, $activeKeys)) {
                    $invalidKeys[] = $key;
                }
            }

            if ($invalidKeys) {
                $db->createCommand()->delete('{{%dingtalk_userproperties}}', [
                    'userId' => $userId,
                    ['in', 'key', $invalidKeys,]
                ])->execute();
            }

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
    private function _createPropertyQuery(): Query
    {
        return (new Query())
            ->select(['id', 'userId', 'key', 'value'])
            ->from('{{%dingtalk_userproperties}}');
    }
}