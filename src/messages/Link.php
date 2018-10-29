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

class Link extends Message
{
    /**
     * @var string|null
     */
    public $messageUrl;

    /**
     * @var string|null
     */
    public $pictureUrl;

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Link');
    }

    /**
     * @return array
     */
    public function getRequestBody()
    {
        return [
            'msgtype' => 'link',
            'link' => [
                'title' => $this->title,
                'text' => $this->content,
                'messageUrl' => $this->messageUrl,
                'picUrl' => $this->pictureUrl,
            ],
        ];
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/Link', [
            'message' => $this,
        ]);
    }
}