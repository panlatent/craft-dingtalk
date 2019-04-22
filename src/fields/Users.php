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
use panlatent\craft\dingtalk\elements\User;

class Users extends BaseRelationField
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'DingTalk Users');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return User::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('dingtalk', 'Add a user');
    }
}