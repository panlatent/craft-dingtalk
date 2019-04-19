<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use panlatent\craft\dingtalk\models\ContactLabel;

/**
 * Class ContactQuery
 *
 * @package panlatent\craft\dingtalk\elements\db
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactQuery extends ElementQuery
{
    // Traits
    // =========================================================================

    use CorporationQuery;

    // Properties
    // =========================================================================

    /**
     * @var string[]|string|null
     */
    public $userId;

    /**
     * @var string[]|string|null
     */
    public $name;

    /**
     * @var string[]|string|null
     */
    public $mobile;

    /**
     * @var int[]|int|null
     */
    public $followerId;

    /**
     * @var string[]|string|null
     */
    public $companyName;

    /**
     * @var string[]|string|null
     */
    public $position;

    /**
     * @var ContactLabel|int|null
     */
    public $labelOf;

    // Public Methods
    // =========================================================================

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function userId($value)
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function name($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function mobile($value)
    {
        $this->mobile = $value;

        return $this;
    }

    /**
     * @param int[]|int|null $value
     * @return $this
     */
    public function followerId($value)
    {
        $this->followerId = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function companyName($value)
    {
        $this->companyName = $value;

        return $this;
    }

    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function position($value)
    {
        $this->position = $value;

        return $this;
    }

    /**
     * @param ContactLabel|int|null $value
     * @return $this
     */
    public function labelOf($value)
    {
        $this->labelOf = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforePrepare(): bool
    {
        $this->joinElementTable('dingtalk_contacts');

        $this->query->addSelect([
            'dingtalk_contacts.corporationId',
            'dingtalk_contacts.userId',
            'dingtalk_contacts.name',
            'dingtalk_contacts.mobile',
            'dingtalk_contacts.followerId',
            'dingtalk_contacts.stateCode',
            'dingtalk_contacts.companyName',
            'dingtalk_contacts.position',
            'dingtalk_contacts.address',
            'dingtalk_contacts.remark',
        ]);

        if ($this->userId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.userId', $this->userId));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.name', $this->name));
        }

        if ($this->mobile) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.mobile', $this->mobile));
        }

        if ($this->followerId) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.followerId', $this->followerId));
        }

        if ($this->companyName) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.companyName', $this->companyName));
        }

        if ($this->position) {
            $this->subQuery->andWhere(Db::parseParam('dingtalk_contacts.position', $this->position));
        }

        $this->_applyCorporationParam('dingtalk_contacts.corporationId');

        return parent::beforePrepare();
    }
}