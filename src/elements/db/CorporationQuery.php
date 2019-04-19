<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\elements\db;

use craft\helpers\Db;

/**
 * Trait CorporationCondition
 *
 * @package panlatent\craft\dingtalk\elements\db
 * @author Panlatent <panlatent@gmail.com>
 */
trait CorporationQuery
{
    // Properties
    // =========================================================================

    /**
     * @var int[]|int|null
     */
    public $corporationId;

    // Public Methods
    // =========================================================================


    /**
     * @param string[]|string|null $value
     * @return $this
     */
    public function corporationId($value)
    {
        $this->corporationId = $value;

        return $this;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param string $column
     */
    private function _applyCorporationParam(string $column)
    {
        if ($this->corporationId) {
            $this->subQuery->andWhere(Db::parseParam($column, $this->corporationId));
        }
    }
}