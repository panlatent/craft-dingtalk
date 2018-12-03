<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\utilities;

use Craft;
use craft\base\Utility;

/**
 * Class SyncContacts
 *
 * @package panlatent\craft\dingtalk\utilities
 * @author Panlatent <panlatent@gmail.com>
 */
class SyncContacts extends Utility
{
    public static function id(): string
    {
        return 'dingtalk-sync-contacts';
    }

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Sync Contacts');
    }

    public static function iconPath()
    {
        return Craft::getAlias('@dingtalk/icon.svg');
    }

    public static function contentHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('dingtalk/_components/utilities/SyncContacts', [
        ]);
    }
}