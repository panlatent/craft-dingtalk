<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\widgets;

use Craft;
use craft\base\Widget;
use panlatent\craft\dingtalk\Plugin;

class DingTalk extends Widget
{
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'DingTalk');
    }

    public function getTitle(): string
    {
        return Craft::t('dingtalk', 'DingTalk Info');
    }

    public function getBodyHtml()
    {
        Plugin::$plugin->getUsers()->getAllUsers();

        return Craft::$app->view->renderTemplate('dingtalk/_components/widgets/DingTalk', [

        ]);
    }
}