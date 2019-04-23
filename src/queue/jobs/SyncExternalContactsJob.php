<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\queue\jobs;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\queue\BaseJob;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * 同步钉钉外部联系人任务
 *
 * @package panlatent\craft\dingtalk\queue\jobs
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncExternalContactsJob extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var int|null
     */
    public $corporationId;

    /**
     * @var Corporation|null
     */
    private $_corporation;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue)
    {
        $elements = Craft::$app->getElements();
        $contacts = Plugin::getInstance()->getContacts();

        // 同步标签
        foreach ($this->getCorporation()->getRemote()->getExternalContactLabels() as $groupData) {
            $labelGroup = $contacts->getLabelGroupByName($this->corporationId, $groupData['name']);
            if (!$labelGroup) {
                $labelGroup = $contacts->createLabelGroup([
                    'corporationId' => $this->corporationId,
                    'name' => $groupData['name'],
                ]);
            }

            $labelGroup->color = $this->_id2hex($groupData['color']);

            if (!$contacts->saveLabelGroup($labelGroup)) {
                throw new Exception("Couldn’t save label group: {$groupData['name']}. " . Json::encode($labelGroup->getErrors()));
            }

            $labels = ArrayHelper::index($contacts->getLabelsByGroupId($labelGroup->id), 'sourceId');
            foreach ($groupData['labels'] as $labelData) {
                if (isset($labels[$labelData['id']])) {
                    $label = ArrayHelper::remove($labels, $labelData['id']);
                } else {
                    $label = $contacts->createLabel([
                        'groupId' => $labelGroup->id,
                        'sourceId' => $labelData['id'],
                    ]);
                }

                $label->name = $labelData['name'];

                if (!$contacts->saveLabel($label)) {
                    throw new Exception("Couldn’t save contact label: {$labelData['name']}");
                }
            }

            // 移除
        }

        // 同步外部联系人
        foreach ($this->getCorporation()->getRemote()->getExternalContacts() as $result) {
            $contact = Contact::find()
                ->corporationId($this->corporationId)
                ->userId($result['userid'])
                ->one();

            if (!$contact) {
                $contact = new Contact();
                $contact->corporationId = $this->corporationId;
                $contact->userId = $result['userid'];
            }

            if (empty($result['follower_user_id'])) {
                Craft::warning("Missing external contact follower_user_id field from remote data.");

                continue;
            }

            $follower = User::find()
                ->corporationId($this->corporationId)
                ->userId($result['follower_user_id'])
                ->one();

            if (!$follower) {
                continue;
            }

            $contact->name = $result['name'];
            $contact->mobile = $result['mobile'];
            $contact->followerId = $follower->id;
            $contact->companyName = $result['company_name'] ?? null;
            $contact->stateCode = $result['state_code'] ?? null;
            $contact->position = isset($result['title']) && $result['title'] != 'null' ? $result['title'] : null;
            $contact->address = $result['address'] ?? null;
            $contact->remark = $result['remark'] ?? null;
            $contact->saveWithRemote = false;

            if (isset($result['label_ids'])) {
                $contact->labels = $contacts->getLabelsBySourceIds($result['label_ids']);
            }

            if (!$elements->saveElement($contact)) {
                throw new Exception("Couldn’t save contact");
            }
        }
    }

    /**
     * @return Corporation
     */
    public function getCorporation(): Corporation
    {
        if ($this->_corporation !== null) {
            return $this->_corporation;
        }

        $corporation = Plugin::getInstance()
            ->getCorporations()
            ->getCorporationById($this->corporationId);

        if ($corporation === null) {
            throw new InvalidConfigException("Missing corporation with the ID: {$this->corporationId}");
        }

        return $this->_corporation = $corporation;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription()
    {
        return Craft::t('dingtalk', 'Sync Dingtalk External Contacts');
    }

    // Private Methods
    // =========================================================================

    /**
     * @param int $value
     * @return string
     */
    private function _id2hex(int $value): string
    {
        switch ($value) {
            case -15220075:
            case -15352701:
                return 'green';
            case -11687445:
                return 'blue';
            case -543394:
                return 'orange';
            case -405222:
                return 'yellow';
            case -895421:
                return 'red';
            case -3044894:
                return 'purple';
        }

        return '';
    }
}