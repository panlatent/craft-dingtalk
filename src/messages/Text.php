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

class Text extends Message
{
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Text');
    }

    /**
     * @return array
     */
    public function getRequestBody()
    {
        return [
            'msgtype' => 'text',
            'text' => [
                'content' => $this->content,
            ],
            'at' => [
                'atMobiles' => $this->atMobiles,
                'isAtAll' => $this->isAtAll,
            ],
        ];
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/Text', [
            'message' => $this,
        ]);
    }
}