<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use Craft;
use craft\helpers\Json;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\errors\ContactException;
use panlatent\craft\dingtalk\events\ContactEvent;
use panlatent\craft\dingtalk\models\ContactLabel;
use panlatent\craft\dingtalk\models\ContactLabelGroup;
use panlatent\craft\dingtalk\records\ContactLabel as ContactLabelRecord;
use panlatent\craft\dingtalk\records\ContactLabelGroup as ContactLabelGroupRecord;
use Throwable;
use yii\base\Component;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class Contacts
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Contacts extends Component
{
    // Constants
    // =========================================================================

    /**
     * @event ContactEvent The event that is triggered before a contact is saved.
     */
    const EVENT_BEFORE_SAVE_CONTACT = 'beforeSaveContact';

    /**
     * @event ContactEvent The event that is triggered after a contact is saved.
     */
    const EVENT_AFTER_SAVE_CONTACT = 'afterSaveContact';

    /**
     * @event ContactEvent The event that is triggered before a contact is deleted.
     */
    const EVENT_BEFORE_DELETE_CONTACT = 'beforeDeleteContact';

    /**
     * @event ContactEvent The event that is triggered after a contact is deleted.
     */
    const EVENT_AFTER_DELETE_CONTACT = 'afterDeleteContact';

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $_fetchedAllLabelGroups = false;

    /**
     * @var ContactLabelGroup[]|null
     */
    public $_labelGroupsById;

    /**
     * @var bool
     */
    public $_fetchedAllLabels = false;

    /**
     * @var ContactLabel[]|null
     */
    public $_labelsById;

    // Public Methods
    // =========================================================================

    /**
     * @param int $corporationId
     * @return ContactLabelGroup[]
     */
    public function getCorporationLabelGroups(int $corporationId): array
    {
        $groups = [];

        $results = $this->_createLabelGroupQuery()
            ->where(['corporationId' => $corporationId])
            ->all();

        foreach ($results as $result) {
            $groups[] = $this->createLabelGroup($result);
        }

        return $groups;
    }

    /**
     * @param int $groupId
     * @return ContactLabelGroup|null
     */
    public function getLabelGroupById(int $groupId)
    {
        $result = $this->_createLabelGroupQuery()
            ->where(['id' => $groupId])
            ->one();

        return $result ? $this->createLabelGroup($result) : null;
    }

    /**
     * @param int $corporationId
     * @param string $name
     * @return ContactLabelGroup|null
     */
    public function getLabelGroupByName(int $corporationId, string $name)
    {
        $result = $this->_createLabelGroupQuery()
            ->where([
                'corporationId' => $corporationId,
                'name' => $name,
            ])
            ->one();

        return $result ? $this->createLabelGroup($result) : null;
    }

    /**
     * @param $config
     * @return ContactLabelGroup
     */
    public function createLabelGroup($config): ContactLabelGroup
    {
        return new ContactLabelGroup($config);
    }

    /**
     * @param ContactLabelGroup $group
     * @param bool $runValidation
     * @return bool
     */
    public function saveLabelGroup(ContactLabelGroup $group, bool $runValidation = true): bool
    {
        $isNewGroup = !$group->id;

        if ($runValidation && !$group->validate()) {
            Craft::info("Contact label group not saved due to validation error.", __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewGroup) {
                $record = ContactLabelGroupRecord::findOne(['id' => $group->id]);
                if (!$group) {
                    throw new ContactException("No label group exists with the ID: “{$group->id}“.");
                }
            } else {
                $record = new ContactLabelGroupRecord();
            }

            $record->corporationId = $group->corporationId;
            $record->name = $group->name;
            $record->color = $group->color;
            $record->save(false);

            if ($isNewGroup) {
                $group->id = $record->id;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        return true;
    }

    // Contact Labels
    // =========================================================================

    /**
     * @param int $groupId
     * @return ContactLabel[]
     */
    public function getLabelsByGroupId(int $groupId): array
    {
        $labels = [];

        $results = $this->_createLabelQuery()
            ->where(['groupId' => $groupId])
            ->all();

        foreach ($results as $result) {
            $labels[] = $this->createLabel($result);
        }

        return $labels;
    }

    /**
     * @param int $contactId
     * @return ContactLabel[]
     */
    public function getLabelsByContactId(int $contactId): array
    {
        $ids = (new Query())
            ->select(['labelId'])
            ->from('{{%dingtalk_contactlabels_contacts}}')
            ->where(['contactId' => $contactId])
            ->column();

        $labels = [];

        foreach ($ids as $id) {
           $labels[] = $this->getLabelById($id);
        }

        return $labels;
    }

    /**
     * @param array $sourceIds
     * @return int[]
     */
    public function getLabelsBySourceIds(array $sourceIds): array
    {
        $ids = (new Query())
            ->select(['id'])
            ->from('{{%dingtalk_contactlabels}}')
            ->where(['sourceId' => $sourceIds])
            ->column();

        $labels = [];

        foreach ($ids as $id) {
            $labels[] = $this->getLabelById($id);
        }

        return $labels;
    }

    /**
     * @param int $labelId
     * @return ContactLabel|null
     */
    public function getLabelById(int $labelId)
    {
        if ($this->_labelsById && array_key_exists($labelId, $this->_labelsById)) {
            return $this->_labelsById[$labelId];
        }

        $results = $this->_createLabelQuery()
            ->where(['id' => $labelId])
            ->one();

        return $this->_labelsById[$labelId] = $results ? $this->createLabel($results) : null;
    }

    /**
     * @param mixed $config
     * @return ContactLabel
     */
    public function createLabel($config): ContactLabel
    {
        return new ContactLabel($config);
    }

    /**
     * @param ContactLabel $label
     * @param bool $runValidation
     * @return bool
     */
    public function saveLabel(ContactLabel $label, bool $runValidation = true): bool
    {
        $isNewLabel = !$label->id;

        if ($runValidation && !$label->validate()) {
            Craft::info("Label not saved due to validation error.", __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if (!$isNewLabel) {
                $labelRecord = ContactLabelRecord::findOne(['id' => $label->id]);
                if (!$label) {
                    throw new ContactException("No label exists with the ID: “{$label->id}“.");
                }
            } else {
                $labelRecord = new ContactLabelRecord();
            }

            $labelRecord->groupId = $label->groupId;
            $labelRecord->name = $label->name;
            $labelRecord->sourceId = $label->sourceId;
            $labelRecord->save(false);

            if ($isNewLabel) {
                $label->id = $labelRecord->id;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }


        return true;
    }

    // Contacts
    // =========================================================================

    /**
     * @param Contact $contact
     * @param bool $runValidation
     * @return bool
     */
    public function saveContact(Contact $contact, bool $runValidation = true)
    {
        $isNew = !$contact->id;

        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_CONTACT)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_CONTACT, new ContactEvent([
                'contact' => $contact,
                'isNew' => $isNew,
            ]));
        }

        if ($runValidation && !$contact->validate()) {
            Craft::info('Contact not saved due to validation error: ' . Json::encode($contact->getErrors()), __METHOD__);
            return false;
        }

        $transaction = Craft::$app->getDb()->beginTransaction();
        try {
            if ($contact->saveWithRemote && !$this->_ensureRemoteContact($contact)) {
                return false;
            }

            if (!Craft::$app->getElements()->saveElement($contact, false)) {
                return false;
            }

            $transaction->commit();
        } catch (Throwable $exception) {
            $transaction->rollBack();

            throw $exception;
        }

        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_CONTACT)) {
            $this->trigger(self::EVENT_AFTER_SAVE_CONTACT, new ContactEvent([
                'contact' => $contact,
                'isNew' => $isNew,
            ]));
        }

        return true;

    }

    /**
     * @param Contact $contact
     * @return bool
     */
    public function deleteRemoteContact(Contact $contact): bool
    {
        return $contact->getCorporation()
            ->getRemote()
            ->deleteExternalContact($contact->userId);
    }

    // Private Methods
    // =========================================================================

    /**
     * @return Query
     */
    private function _createLabelGroupQuery(): Query
    {
        return (new Query())
            ->select(['id', 'corporationId', 'name', 'color'])
            ->from('{{%dingtalk_contactlabelgroups}}');
    }

    /**
     * @return Query
     */
    private function _createLabelQuery(): Query
    {
        return (new Query())
            ->select(['id', 'groupId', 'name', 'sourceId'])
            ->from('{{%dingtalk_contactlabels}}');
    }

    /**
     * @param Contact $contact
     * @return bool
     */
    private function _ensureRemoteContact(Contact $contact): bool
    {
        $remote = $contact->getCorporation()->getRemote();

        $data = [
            'name' => $contact->name,
            'mobile' => $contact->mobile, // No support update mobile
            'title' => (string)$contact->position,
            'follower_user_id' => $contact->getFollower()->userId,
            'address' => (string)$contact->address,
            'remark' => (string)$contact->remark,
            'state_code' => (string)$contact->stateCode,
            'company_name' => (string)$contact->companyName,
            'label_ids' => ArrayHelper::getColumn($contact->getLabels(), 'sourceId'),
            'share_dept_ids' => ArrayHelper::getColumn($contact->getShareDepartments(), 'dingDepartmentId'),
            'share_user_ids' => ArrayHelper::getColumn($contact->getShareUsers(), 'userId'),
        ];

        if (!$contact->id && !$contact->userId) {
            $userID = $remote->createExternalContact($data);
            if (!$userID) {
                return false;
            }

            $trashedContact = Contact::find()
                ->corporationId($contact->corporationId)
                ->userId($userID)
                ->trashed(true)
                ->one();

            if ($trashedContact) {
                Craft::$app->getElements()->restoreElement($trashedContact);
                $contact->id = $trashedContact->id;
            }

            $contact->userId = $userID;
        } else {
            $data['user_id'] = $contact->userId;
            if (!$remote->saveExternalContact($data)) {
                return false;
            }
        }

        return true;
    }
}