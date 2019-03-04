<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\errors\CorporationException;
use panlatent\craft\dingtalk\events\CorporationEvent;
use panlatent\craft\dingtalk\models\Corporation;
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
    public $_fetchedAllCorporations = false;

    /**
     * @var Corporation[]|null
     */
    public $_corporationsById;

    /**
     * @var Corporation[]|null
     */
    public $_corporationsByHandle;

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
    public function getCorporationsByHandleByHandle(string $handle)
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

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewCorporation) {
                $record = \panlatent\craft\dingtalk\records\Corporation::findOne(['id' => $corporation->id]);
                if (!$corporation) {
                    throw new CorporationException("No corporation exists with the ID: “{$corporation->id}“.");
                }
            } else {
                $record = new \panlatent\craft\dingtalk\records\Corporation();
            }

            $record->name = $corporation->name;
            $record->handle = $corporation->name;
            $record->corpId = $corporation->corpId;
            $record->corpSecret = $corporation->corpSecret;
            $record->hasUrls = $corporation->hasUrls;
            $record->url = $corporation->url;

            $record->save(false);

            if ($isNewCorporation) {
                $corporation->id = $record->id;
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
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

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            $db->createCommand()->delete(Table::CORPORATIONS, [
                'id' => $corporation->id,
            ])->execute();

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE_CORPORATION)) {
            $this->trigger(self::EVENT_AFTER_DELETE_CORPORATION, new CorporationEvent([
                'corporation' => $corporation,
            ]));
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
            ->select(['id', 'primary', 'name', 'handle', 'corpId', 'corpSecret', 'hasUrls', 'url'])
            ->from(Table::CORPORATIONS)
            ->orderBy(['sortOrder' => SORT_ASC, 'dateCreated' => SORT_ASC]);
    }
}