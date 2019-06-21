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
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\errors\CorporationException;
use panlatent\craft\dingtalk\errors\CorporationGroupNotFoundException;
use panlatent\craft\dingtalk\events\CorporationEvent;
use panlatent\craft\dingtalk\events\CorporationGroupEvent;
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\models\CorporationGroup;
use panlatent\craft\dingtalk\records\Corporation as CorporationRecord;
use panlatent\craft\dingtalk\records\CorporationGroup as CorporationGroupRecord;
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

    // Events
    // -------------------------------------------------------------------------

    /**
     * @event CorporationGroupEvent The event that is triggered before a group is saved.
     */
    const EVENT_BEFORE_SAVE_GROUP = 'beforeSaveGroup';

    /**
     * @event CorporationGroupEvent The event that is triggered after a group is saved.
     */
    const EVENT_AFTER_SAVE_GROUP = 'afterSaveGroup';

    /**
     * @event CorporationGroupEvent The event that is triggered before a group is deleted.
     */
    const EVENT_BEFORE_DELETE_GROUP = 'beforeDeleteGroup';

    /**
     * @event CorporationGroupEvent The event that is triggered after a group is deleted.
     */
    const EVENT_AFTER_DELETE_GROUP = 'afterDeleteGroup';

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
     * @var CorporationGroup[]|null
     */
    private $_groups;

    /**
     * @var Corporation[]|null
     */
    private $_corporations;

    // Public Methods
    // =========================================================================

    /**
     * @return CorporationGroup[]
     */
    public function getAllGroups(): array
    {
        if ($this->_groups !== null) {
            return $this->_groups;
        }

        $this->_groups = [];

        $results = (new Query())
            ->select([
                'groups.id',
                'groups.name',
                'groups.handle',
                'groups.fieldLayoutId',
                'groups.uid',
            ])
            ->from(['groups' => Table::CORPORATIONGROUPS])
            ->all();

        foreach ($results as $result) {
            $this->_groups[] = new CorporationGroup($result);
        }

        return $this->_groups;
    }

    /**
     * @param int $groupId
     * @return CorporationGroup|null
     */
    public function getGroupById(int $groupId)
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'id', $groupId);
    }

    /**
     * @param string $handle
     * @return CorporationGroup|null
     */
    public function getGroupByHandle(string $handle)
    {
        return ArrayHelper::firstWhere($this->getAllGroups(), 'handle', $handle);
    }

    /**
     * @param CorporationGroup $group
     * @param bool $runValidation
     * @return bool
     */
    public function saveGroup(CorporationGroup $group, bool $runValidation = true): bool
    {
        $isNewGroup = !$group->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_GROUP)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_GROUP, new CorporationGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        if ($runValidation && !$group->validate()) {
            Craft::info("Group not saved due to validation error.", __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewGroup) {
                $record = CorporationGroupRecord::findOne(['id' => $group->id]);
                if (!$group) {
                    throw new CorporationGroupNotFoundException("No group exists with the ID: “{$group->id}“.");
                }
            } else {
                $record = new CorporationGroupRecord();
            }

            $layout = $group->getFieldLayout();
            if ($layout) {
                Craft::$app->getFields()->saveLayout($layout);
            }

            $record->name = $group->name;
            $record->handle = $group->handle;
            $record->fieldLayoutId = $layout ? $layout->id : null;
            $record->save(false);

            if ($isNewGroup) {
                $group->id = $record->id;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        $this->_groups = null;
        $this->getAllGroups();

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_GROUP)) {
            $this->trigger(self::EVENT_AFTER_SAVE_GROUP, new CorporationGroupEvent([
                'group' => $group,
                'isNew' => $isNewGroup,
            ]));
        }

        return true;
    }

    // Corporations
    // -------------------------------------------------------------------------

    /**
     * @return int
     */
    public function getTotalCorporations(): int
    {
        return count($this->getAllCorporations());
    }

    /**
     * Return all corporations.
     *
     * @return Corporation[]
     */
    public function getAllCorporations(): array
    {
        if ($this->_corporations !== null) {
            return $this->_corporations;
        }

        $this->_corporations = [];

        $results = $this->_createQuery()
            ->addSelect([
                'cbsUrl' => 'cbs.url',
                'cbsToken' => 'cbs.token',
                'cbsAesKey' => 'cbs.aesKey',
                'cbsEnabled' => 'cbs.enabled',
            ])
            ->leftJoin(['cbs' => Table::CORPORATIONCALLBACKSETTINGS], '[[cbs.corporationId]]=[[corporations.id]]')
            ->all();

        foreach ($results as $result) {
            $callbackSettings = [
                'url' => $result['cbsUrl'],
                'token' => $result['cbsToken'],
                'aesKey' => $result['cbsAesKey'],
                'enabled' => $result['cbsEnabled'],
                'callbackIds' => (new Query())
                    ->select('callbackId')
                    ->from(Table::CORPORATIONCALLBACKS)
                    ->where([
                        'corporationId' => $result['id']
                    ])
                    ->column()
            ];

            unset($result['cbsUrl'], $result['cbsToken'], $result['cbsAesKey'], $result['cbsEnabled']);

            $corporation = new Corporation($result);
            $corporation->setCallbackSettings($callbackSettings);

            $this->_corporations[] = $corporation;
        }

        return $this->_corporations;
    }

    /**
     * @param int $groupId
     * @return Corporation[]
     */
    public function getCorporationsByGroupId(int $groupId): array
    {
        return ArrayHelper::filterByValue($this->getAllCorporations(), 'groupId', $groupId);
    }

    /**
     * @param int $corporationId
     * @return Corporation|null
     */
    public function getCorporationById(int $corporationId)
    {
        return ArrayHelper::firstWhere($this->getAllCorporations(), 'id', $corporationId);
    }

    /**
     * @param string $handle
     * @return Corporation|null
     */
    public function getCorporationByHandle(string $handle)
    {
        return ArrayHelper::firstWhere($this->getAllCorporations(), 'handle', $handle);
    }

    /**
     * @param string $corpId
     * @return Corporation|null
     */
    public function getCorporationByCorpId(string $corpId)
    {
        return ArrayHelper::firstWhere($this->getAllCorporations(), 'corpId', $corpId);
    }

    /**
     * @return Corporation|null
     */
    public function getPrimaryCorporation()
    {
        return ArrayHelper::firstWhere($this->getAllCorporations(), 'primary', true);
    }

    /**
     * @param Corporation $corporation
     * @param bool $runValidation
     * @return bool
     */
    public function saveCorporation(Corporation $corporation, bool $runValidation = true): bool
    {
        $isNewCorporation = !$corporation->id;

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

        /** @var Corporation|null $oldPrimaryCorporation */
        $oldPrimaryCorporation = $this->getPrimaryCorporation();

        $db = Craft::$app->getDb();

        $transaction = $db->beginTransaction();
        try {
            if (!$isNewCorporation) {
                $record = CorporationRecord::findOne(['id' => $corporation->id]);
                if (!$record) {
                    throw new CorporationException("No corporation exists due ID: “{$corporation->id}“");
                }
            } else {
                $record = new CorporationRecord();
            }

            $record->primary = $oldPrimaryCorporation ? (bool)$corporation->primary : true;
            $record->name = $corporation->name;
            $record->handle = $corporation->handle;
            $record->corpId = $corporation->corpId;
            $record->corpSecret = $corporation->corpSecret;
            $record->hasUrls = (bool)$corporation->hasUrls;
            $record->url = $corporation->url;

            $record->save(false);

            if ($isNewCorporation) {
                $corporation->id = $record->id;
            }

            if ($oldPrimaryCorporation && $corporation->primary && $oldPrimaryCorporation->id !== $corporation->id) {
                Craft::$app->getDb()->createCommand()
                    ->update(Table::CORPORATIONS, [
                        'primary' => false,
                    ], [
                        'id' => $oldPrimaryCorporation->id,
                    ])
                    ->execute();
            }

            $callbackSettings = $corporation->getCallbackSettings();

            $db->createCommand()
                ->upsert(Table::CORPORATIONCALLBACKSETTINGS, [
                    'corporationId' => $corporation->id,
                ], [
                    'url' => $callbackSettings->url,
                    'token' => $callbackSettings->token,
                    'aesKey' => $callbackSettings->aesKey,
                    'enabled' => (bool)$callbackSettings->enabled,
                ])
                ->execute();

            $oldCallbackIds = [];
            if (!$isNewCorporation) {
                $oldCallbackIds = (new Query())
                    ->select('callbackId')
                    ->from(Table::CORPORATIONCALLBACKS)
                    ->where(['corporationId' => $corporation->id])
                    ->column();
            }

            foreach ($callbackSettings->callbackIds as $callbackId) {
                if ($isNewCorporation || !in_array($callbackId, $oldCallbackIds)) {
                    $db->createCommand()
                        ->insert(Table::CORPORATIONCALLBACKS, [
                            'corporationId' => $corporation->id,
                            'callbackId' => $callbackId,
                        ])
                        ->execute();
                }
            }

            if (!$isNewCorporation) {
                $deletedCallbackIds = array_diff($oldCallbackIds, $callbackSettings->callbackIds);

                $db->createCommand()
                    ->delete(Table::CORPORATIONCALLBACKS, [
                        'corporationId' => $corporation->id,
                        'callbackId' => $deletedCallbackIds
                    ])
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($callbackSettings->enabled) {
            $corporation->getRemote()->registerCallbacks();
        }

        $this->_corporations = null;
        $this->getAllCorporations();

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
        } catch (Throwable $exception) {
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
            ->select([
                'corporations.id',
                'corporations.groupId',
                'corporations.primary',
                'corporations.name',
                'corporations.handle',
                'corporations.corpId',
                'corporations.corpSecret',
                'corporations.hasUrls',
                'corporations.url'
            ])
            ->from(['corporations' => Table::CORPORATIONS])
            ->orderBy(['sortOrder' => SORT_ASC]);
    }
}