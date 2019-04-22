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
use panlatent\craft\dingtalk\elements\Contact;

/**
 * Class Contacts
 *
 * @package panlatent\craft\dingtalk\fields
 * @author Panlatent <panlatent@gmail.com>
 */
class Contacts extends BaseRelationField
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'DingTalk Contacts');
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return Contact::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return Craft::t('dingtalk', 'Add a contact');
    }
}