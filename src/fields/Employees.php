<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\fields;

use Craft;
use craft\fields\BaseRelationField;
use panlatent\craft\dingtalk\elements\Employee;

/**
 * Class Employees
 *
 * @package panlatent\craft\dingtalk\fields
 * @author Panlatent <panlatent@gmail.com>
 */
class Employees extends BaseRelationField
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Employees');
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('dingtalk', 'Add a employees');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Employee::class;
    }
}