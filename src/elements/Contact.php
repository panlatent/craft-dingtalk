<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use panlatent\craft\dingtalk\db\Table;
use panlatent\craft\dingtalk\elements\db\ContactQuery;
use panlatent\craft\dingtalk\elements\db\UserQuery;
use panlatent\craft\dingtalk\models\ContactLabel;
use panlatent\craft\dingtalk\models\Department;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\records\Contact as ContactRecord;
use yii\base\InvalidConfigException;
use yii\db\Query;

/**
 * Class Contact
 *
 * @package panlatent\craft\dingtalk\elements
 * @property User $follower
 * @property ContactLabel[] $labels
 * @property Department[] $shareDepartments
 * @property User[] $shareUsers
 * @author Panlatent <panlatent@gmail.com>
 */
class Contact extends Element
{
    // Traits
    // =========================================================================

    use CorporationTrait;

    // Constants
    // =========================================================================

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Contact');
    }

    /**
     * @return ContactQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new ContactQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('dingtalk', 'All Contacts'),
            ],
        ];

        $sources[] = ['heading' => Craft::t('dingtalk', 'Corporations')];
        foreach (Plugin::getInstance()->getCorporations()->getAllCorporations() as $corporation) {
            $sources[] = [
                'key' => $corporation->handle,
                'label' => $corporation->name,
                'criteria' => [
                    'corporationId' => $corporation->id,
                ]
            ];
        }

        $sources[] = ['heading' => Craft::t('dingtalk', 'Labels')];

        $contacts = Plugin::getInstance()->getContacts();

        //

        // Labels
        foreach (Plugin::getInstance()->getCorporations()->getAllCorporations() as $corporation) {
            $sources[] = ['heading' => $corporation->name];

            foreach ($contacts->getCorporationLabelGroups($corporation->id) as $group) {
                $labelNested = [];
                foreach ($group->getLabels() as $label) {
                    $labelNested[] = [
                        'key' => $label->id,
                        'label' => $label->name,
                        'criteria' => [
                            'corporationId' => $corporation->id,
                            'labelOf' => $label,
                        ],
                    ];
                }

                $sources[] = [
                    'key' => $group->id,
                    'label' => $group->name,
                    'status' => $group->color,
                    'criteria' => [
                        'corporationId' => $corporation->id,
                    ],
                    'nested' => $labelNested,
                ];
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'name' => Craft::t('dingtalk', 'Name'),
            'mobile' => Craft::t('dingtalk', 'Mobile'),
            'companyName' => Craft::t('dingtalk', 'Company Name'),
            'position' => Craft::t('dingtalk', 'Position'),
            'address' => Craft::t('dingtalk', 'Address'),
            'follower' => Craft::t('dingtalk', 'Follower'),
            'labels' => Craft::t('dingtalk', 'Labels'),
            'remark' => Craft::t('dingtalk', 'Remark'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return [
            'name',
            'userId',
            'mobile',
            'companyName',
            'position',
            'address',
            'remark',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'name' => Craft::t('dingtalk', 'Name'),
            'companyName' => Craft::t('dingtalk', 'Company Name'),
            'position' => Craft::t('dingtalk', 'Position'),
        ];
    }

    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $userId;

    /**
     * @var string|null
     */
    public $mobile;

    /**
     * @var int|null
     */
    public $followerId;

    /**
     * @var string|null
     */
    public $stateCode;

    /**
     * @var string|null
     */
    public $companyName;

    /**
     * @var string|null
     */
    public $position;

    /**
     * @var string|null
     */
    public $address;

    /**
     * @var string|null
     */
    public $remark;

    /**
     * @var bool 保存时同步远端数据
     */
    public $saveWithRemote = true;

    /**
     * @var bool 删除元素时也删除远端数据
     */
    public $deleteWithRemote = true;

    /**
     * @var User|null
     */
    private $_follower;

    /**
     * @var ContactLabel[]|null
     */
    private $_labels;

    /**
     * @var Department[]|null
     */
    private $_shareDepartments;

    /**
     * @var User[]|null
     */
    private $_shareUsers;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['corporationId', 'name', 'mobile', 'followerId', 'stateCode'], 'required'];
        $rules[] = [['userId', 'position', 'companyName', 'address', 'remark'], 'string'];
        $rules[] = [['mobile'], function() {
            $id = (new Query())
                ->select('id')
                ->from(Table::CONTACTS)
                ->where([
                    'corporationId' => $this->corporationId,
                    'mobile' => $this->mobile,
                ])
                ->scalar();

            if ($id && $id != $this->id) {
                $this->addError('mobile', Craft::t('dingtalk', 'Contact mobile already exists.'));
            }
        }];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['commitOnSave']);

        $fields[] = 'labels';
        $fields['followerName'] = function () {
            return $this->getFollower()->name;
        };

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields()
    {
        $fields = parent::extraFields();
        $fields[] = 'corporation';
        $fields[] = 'follower';

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = array_merge($labels, [
            'name' => Craft::t('dingtalk', 'Name'),
            'mobile' => Craft::t('dingtalk', 'Mobile'),
            'followerId' => Craft::t('dingtalk', 'Follower ID'),
            'labels' => Craft::t('dingtalk', 'Labels'),
        ]);

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl('dingtalk/contacts/' . $this->id);
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'follower') {
            return '<a href="' . $this->getFollower()->getCpEditUrl() . '">' . (string)$this->getFollower() . '</a>';
        } elseif ($attribute == 'labels') {
            $labelHtml = [];

            foreach ($this->getLabels() as $label) {
                $labelHtml[] = '<span style="color: ' . $label->getGroup()->color .'">' . $label->name . '</span>';
            }

            return implode(',', $labelHtml);
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return User
     */
    public function getFollower(): User
    {
        if ($this->_follower !== null) {
            return $this->_follower;
        }

        if (!$this->followerId) {
            throw new InvalidConfigException();
        }

        $this->_follower = User::find()
            ->id($this->followerId)
            ->one();

        if ($this->_follower === null) {
            throw new InvalidConfigException();
        }

        return $this->_follower;
    }

    /**
     * @return ContactLabel[]
     */
    public function getLabels(): array
    {
        if ($this->_labels !== null) {
            return $this->_labels;
        }

        if (!$this->id) {
            return [];
        }

        $this->_labels = Plugin::getInstance()
            ->getContacts()
            ->getLabelsByContactId($this->id);

        return $this->_labels;
    }

    /**
     * @param ContactLabel[] $labels
     */
    public function setLabels(array $labels)
    {
        $this->_labels = $labels;
    }

    /**
     * @return Department[]
     */
    public function getShareDepartments(): array
    {
        if ($this->_shareDepartments !== null) {
            return $this->_shareDepartments;
        }

        if (!$this->id) {
            return [];
        }

        $this->_shareDepartments = Plugin::getInstance()->getDepartments()
            ->findDepartments([
                'shareContactOf' => $this,
            ]);

        return $this->_shareDepartments;
    }

    /**
     * @param Department[] $departments
     */
    public function setShareDepartments(array $departments)
    {
        $this->_shareDepartments = $departments;
    }

    /**
     * @return User[]
     */
    public function getShareUsers(): array
    {
        if ($this->_shareUsers !== null) {
            return $this->_shareUsers;
        }

        if (!$this->id) {
            return [];
        }

        $this->_shareUsers =  User::find()
            ->shareContactOf($this)
            ->all();

        return $this->_shareUsers;
    }

    /**
     * @param User[] $users
     */
    public function setShareUsers(array $users)
    {
        $this->_shareUsers = $users;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        if ($this->stateCode === null) {
            $this->stateCode = '86';
        }

        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $record = ContactRecord::findOne(['id' => $this->id]);
        } else {
            $record = new ContactRecord();
            $record->id = $this->id;
        }

        $record->corporationId = $this->corporationId;
        $record->name = $this->name;
        $record->userId = $this->userId;
        $record->mobile = $this->mobile;
        $record->followerId = $this->followerId;
        $record->stateCode = $this->stateCode;
        $record->companyName = $this->companyName;
        $record->position = $this->position;
        $record->address = $this->address;
        $record->remark = $this->remark;

        $record->save(false);

        if ($this->_labels !== null) {
            foreach ($this->_labels as $label) {
                Craft::$app->getDb()
                    ->createCommand()
                    ->upsert('{{%dingtalk_contactlabels_contacts}}', [
                        'labelId' => $label->id,
                        'contactId' => $this->id,
                    ])
                    ->execute();
            }
        }

        $db = Craft::$app->getDb();

        if ($this->_shareDepartments) {
            $oldShareDepartmentIds = (new Query())
                ->select('departmentId')
                ->from(Table::CONTACTSHARES_DEPARTMENTS)
                ->where(['contactId' => $this->id])
                ->indexBy('id')
                ->column();

            foreach ($this->getShareDepartments() as $department) {
                $db->createCommand()
                    ->upsert(Table::CONTACTSHARES_DEPARTMENTS, [
                        'contactId' => $this->id,
                        'departmentId' => $department->id,
                    ])
                    ->execute();

                unset($oldShareDepartmentIds[$department->id]);
            }

            if ($oldShareDepartmentIds) {
                $db->createCommand()
                    ->delete(Table::CONTACTSHARES_DEPARTMENTS, [
                        'id' => $oldShareDepartmentIds,
                    ])
                    ->execute();
            }
        }

        if ($this->_shareUsers) {
            $oldShareUserIds = (new Query())
                ->select('userId')
                ->from(Table::CONTACTSHARES_USERS)
                ->where(['contactId' => $this->id])
                ->indexBy('id')
                ->column();

            foreach ($this->_shareUsers as $user) {
                $db->createCommand()
                    ->upsert(Table::CONTACTSHARES_USERS, [
                        'contactId' => $this->id,
                        'userId' => $user->id,
                    ])
                    ->execute();

                unset($oldShareUserIds[$user->id]);
            }

            if ($oldShareUserIds) {
                $db->createCommand()
                    ->delete(Table::CONTACTSHARES_USERS, [
                        'id' => $oldShareUserIds,
                    ])
                    ->execute();
            }
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        if ($this->deleteWithRemote) {
            if (!Plugin::getInstance()->getContacts()->deleteRemoteContact($this)) {
                return false;
            }
        }

        return parent::beforeDelete();
    }
}