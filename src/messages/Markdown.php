<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\messages;

use Craft;
use panlatent\craft\dingtalk\base\Message;

class Markdown extends Message
{
    public function getRequestPayload()
    {
        return [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $this->title,
                'text' => $this->content,
            ],
            'at' => [
                'atMobiles' => $this->atMobiles,
                'isAtAll' => $this->isAtAll,
            ],
        ];
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/Markdown', [
            'message' => $this,
        ]);
    }
}