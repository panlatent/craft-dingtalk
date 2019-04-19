<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\errors\CorporationException;
use panlatent\craft\dingtalk\events\CorporationEvent;
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\records\Corporation as CorporationRecord;
use Throwable;
use yii\base\Component;
use yii\db\Query;

/**
 * Class Corporations
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Corporations extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event CorporationEvent The event that is triggered before a corporation is saved.
     */
    const EVENT_BEFORE_SAVE_CORPORATION = 'beforeSaveCorporation';

    /**
     * @event CorporationEvent The event that is triggered after a corporation is saved.
     */
    const EVENT_AFTER_SAVE_CORPORATION = 'afterSaveCorporation';

    /**
     * @event CorporationEvent The event that is triggered before a corporation is deleted.
     */
    const EVENT_BEFORE_DELETE_CORPORATION = 'beforeDeleteCorporation';

    /**
     * @event CorporationEvent The event that is triggered after a corporation is deleted.
     */
    const EVENT_AFTER_DELETE_CORPORATION = 'afterDeleteCorporation';

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    private $_fetchedAllCorporations = false;

    /**
     * @var Corporation[]|null
     */
    private $_corporationsById;

    /**
     * @var Corporation[]|null
     */
    private $_corporationsByHandle;

    /**
     * @var Corporation|null
     */
    private $_primaryCorporation;

    // Public Methods
    // =========================================================================

    /**
     * @return Corporation[]
     */
    public function getAllCorporations(): array
    {
        if ($this->_fetchedAllCorporations) {
            return array_values($this->_corporationsById);
        }

        $this->_corporationsById = [];
        $this->_corporationsByHandle = [];

        $results = $this->_createQuery()->all();
        foreach ($results as $result) {
            $corporation = $this->createCorporation($result);
            $this->_corporationsById[$corporation->id] = $corporation;
            $this->_corporationsByHandle[$corporation->name] = $corporation;
        }

        $this->_fetchedAllCorporations = true;

        return array_values($this->_corporationsById);
    }

    /**
     * @param int $corporationId
     * @return Corporation|null
     */
    public function getCorporationById(int $corporationId)
    {
        if ($this->_corporationsById && array_key_exists($corporationId, $this->_corporationsById)) {
            return $this->_corporationsById[$corporationId];
        }

        if ($this->_fetchedAllCorporations) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['id' => $corporationId])
            ->one();

        return $this->_corporationsById[$corporationId] = $result ? $this->createCorporation($result): null;
    }

    /**
     * @param string $handle
     * @return Corporation|null
     */
    public function getCorporationByHandle(string $handle)
    {
        if ($this->_corporationsByHandle && array_key_exists($handle, $this->_corporationsByHandle)) {
            return $this->_corporationsByHandle[$handle];
        }

        if ($this->_fetchedAllCorporations) {
            return null;
        }

        $result = $this->_createQuery()
            ->where(['handle' => $handle])
            ->one();

        return $this->_corporationsByHandle[$handle] = $result ? $this->createCorporation($result): null;
    }

    /**
     * @param string $corpId
     * @return Corporation|null
     */
    public function getCorporationByCorpId(string $corpId)
    {
        if ($this->_fetchedAllCorporations) {
            return ArrayHelper::firstWhere($this->_corporationsById, 'corpId', $corpId);
        }

        $result = $this->_createQuery()
            ->where(['corpId' => $corpId])
            ->one();

        return $result ? $this->createCorporation($result) : null;
    }

    /**
     * @return Corporation|null
     */
    public function getPrimaryCorporation()
    {
        if ($this->_primaryCorporation !== null) {
            return $this->_primaryCorporation;
        }

        $corporationId = $this->_createQuery()
            ->select('id')
            ->where(['primary' => true])
            ->scalar();

        if (!$corporationId) {
            return null;
        }

        return $this->_primaryCorporation = $this->getCorporationById($corporationId);
    }

    /**
     * @param mixed $config
     * @return Corporation
     */
    public function createCorporation($config): Corporation
    {
        if (is_string($config)) {
            $config = ['name' => $config];
        }

        return new Corporation($config);
    }

    /**
     * @param Corporation $corporation
     * @param bool $runValidation
     * @return bool
     */
    public function saveCorporation(Corporation $corporation, bool $runValidation = true): bool
    {
        $isNewCorporation = $corporation->getIsNew();

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_CORPORATION)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_CORPORATION, new CorporationEvent([
                'corporation' => $corporation,
                'isNew' => $isNewCorporation,
            ]));
        }

        if ($runValidation && !$corporation->validate()) {
            Craft::info("Corporation not saved due to validation error.", __METHOD__);
            return false;
        }

        $oldPrimaryCorporationId = $this->getPrimaryCorporation()->id ?? false;

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewCorporation) {
                $record = CorporationRecord::findOne(['id' => $corporation->id]);
                if (!$corporation) {
                    throw new CorporationException("No corporation exists with the ID: “{$corporation->id}“.");
                }
            } else {
                $record = new CorporationRecord();
            }

            if ($isNewCorporation) {
                if (empty($corporation->callbackToken)) {
                    $corporation->callbackToken = StringHelper::randomString(16, true);
                }
                if (empty($corporation->callbackAesKey)) {
                    $corporation->callbackAesKey = StringHelper::randomString(43);
                }
            }

            $record->primary = (bool)$corporation->primary;
            $record->name = $corporation->name;
            $record->handle = $corporation->handle;
            $record->corpId = $corporation->corpId;
            $record->corpSecret = $corporation->corpSecret;
            $record->hasUrls = (bool)$corporation->hasUrls;
            $record->url = $corporation->url;
            $record->callbackEnabled = $corporation->callbackEnabled;
            $record->callbackToken = $corporation->callbackToken;
            $record->callbackAesKey = $corporation->callbackAesKey;

            $record->save(false);

            if ($isNewCorporation) {
                $corporation->id = $record->id;
            }

            if ($oldPrimaryCorporationId && $corporation->primary && $oldPrimaryCorporationId !== $corporation->id) {
                Craft::$app->getDb()->createCommand()
                    ->update('{{%dingtalk_corporations}}', [
                        'primary' => false,
                    ], [
                        'id' => $oldPrimaryCorporationId,
                    ])
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_corporationsById[$corporation->id] = $corporation;
        $this->_corporationsByHandle[$corporation->handle] = $corporation;

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_CORPORATION)) {
            $this->trigger(self::EVENT_AFTER_SAVE_CORPORATION, new CorporationEvent([
                'corporation' => $corporation,
                'isNew' => $isNewCorporation,
            ]));
        }

        return true;
    }

    /**
     * Delete a corporation.
     *
     * @param Corporation $corporation
     * @return bool
     */
    public function deleteCorporation(Corporation $corporation): bool
    {
        if ($this->hasEventHandlers(self::EVENT_BEFORE_DELETE_CORPORATION)) {
            $this->trigger(self::EVENT_BEFORE_DELETE_CORPORATION, new CorporationEvent([
                'corporation' => $corporation,
            ]));
        }

        if (!$corporation->beforeDelete()) {
            return false;
        }

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->delete(Table::CORPORATIONS, [
                'id' => $corporation->id,
            ])->execute();

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $corporation->afterDelete();

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_CORPORATION)) {
            $this->trigger(self::EVENT_AFTER_DELETE_CORPORATION, new CorporationEvent([
                'corporation' => $corporation,
            ]));
        }

        return true;
    }

    /**
     * @param array $corporationIds
     * @return bool
     */
    public function reorderCorporations(array $corporationIds)
    {
        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();
        try {
            foreach ($corporationIds as $order => $id) {
                $db->createCommand()
                    ->update(Table::CORPORATIONS, [
                        'sortOrder' => $order + 1
                    ], [
                        'id' => $id,
                    ])
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createQuery(): Query
    {
        return (new Query())
            ->select(['id', 'primary', 'name', 'handle', 'corpId', 'corpSecret', 'hasUrls', 'url', 'callbackEnabled', 'callbackToken', 'callbackAesKey'])
            ->from(Table::CORPORATIONS)
            ->orderBy(['sortOrder' => SORT_ASC, 'dateCreated' => SORT_ASC]);
    }
}