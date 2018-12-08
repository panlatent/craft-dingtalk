<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\robots;

use Craft;
use panlatent\craft\dingtalk\base\Robot;

/**
 * Class ChatNoticeRobot
 *
 * @package panlatent\craft\dingtalk\robots
 * @author Panlatent <panlatent@gmail.com>
 */
class ChatNoticeRobot extends Robot
{
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Chat Notice Robot');
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/robots/ChatNoticeRobot/settings', [
            'robot' => $this,
        ]);
    }
}