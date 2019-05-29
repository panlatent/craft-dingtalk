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
use panlatent\craft\dingtalk\Plugin;

/**
 * Class SyncContacts
 *
 * @package panlatent\craft\dingtalk\utilities
 * @author Panlatent <panlatent@gmail.com>
 */
class Sync extends Utility
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'dingtalk-sync';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Dingtalk Sync');
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@dingtalk/icon.svg');
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $corporationOptions = [];
        foreach (Plugin::$dingtalk->getCorporations()->getAllCorporations() as $corporation) {
            $corporationOptions[] = [
                'label' => $corporation->name,
                'value' => $corporation->id,
            ];
        }

        return Craft::$app->getView()->renderTemplate('dingtalk/_components/utilities/Sync', [
            'corporationOptions' => $corporationOptions,
        ]);
    }
}