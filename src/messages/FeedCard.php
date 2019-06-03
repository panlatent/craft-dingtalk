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

class FeedCard extends Message
{
    /**
     * @var array
     */
    public $links = [];

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Feed Card');
    }

    /**
     * @return array
     */
    public function getRequestPayload()
    {
        $links = array_map(function($link) {
            return [
                'title' => $link['title'] ?? '',
                'messageURL' => $link['messageURL'] ?? '',
                'picURL' => $link['pictureUrl'] ?? '',
            ];
        }, $this->links);

        return [
            'msgtype' => 'feedCard',
            'feedCard' => [
                'links' => $links,
            ]
        ];
    }

    /**
     * @param string $title
     * @param string $messageUrl
     * @param string $pictureUrl
     * @return $this
     */
    public function addLink(string $title, string $messageUrl, string $pictureUrl)
    {
        $this->links[] = [
            'title' => $title,
            'messageUrl' => $messageUrl,
            'pictureUrl' => $pictureUrl,
        ];

        return $this;
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/FeedCard', [
            'message' => $this,
        ]);
    }
}