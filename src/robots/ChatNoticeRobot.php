<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\robots;

use Craft;
use craft\validators\UrlValidator;
use GuzzleHttp\Client;
use panlatent\craft\dingtalk\base\MessageInterface;
use panlatent\craft\dingtalk\base\Robot;

/**
 * Class ChatNoticeRobot
 *
 * @package panlatent\craft\dingtalk\robots
 * @property-read Client $client
 * @property-read string[] $urls
 * @author Panlatent <panlatent@gmail.com>
 */
class ChatNoticeRobot extends Robot
{
    /**
     * @var array|null
     */
    public $webhooks;

    /**
     * @var Client|null
     */
    private $_client;

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Chat Notice Robot');
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['webhooks', function($property) {
                $validator= new UrlValidator();
                foreach ($this->$property as $id => ['name' => $name, 'url' => $url, 'enabled' => $enabled]) {
                    if (!empty($errors = $validator->validateValue($url))) {
                        $this->addError('webhooks['. $id . '][url]', $errors[0]);
                    }
                }
            }]
        ]);
    }

    public function send(MessageInterface $message): bool
    {
        if (empty($this->webhooks)) {
            return false;
        }

        $urls = $this->getUrls();
        foreach ($urls as $url) {
            $this->getClient()->post($url, [
                'json' => $message->getRequestBody(),
            ]);
        }

        return true;
    }

    public function getClient(): Client
    {
        if ($this->_client !== null) {
            return $this->_client;
        }

        return $this->_client = new Client();
    }

    /**
     * @return string[]
     */
    public function getUrls(): array
    {
        $urls = [];
        foreach ($this->webhooks as $webhook) {
            if ($webhook['enabled']) {
                $urls[] = $webhook['url'];
            }
        }

        return $urls;
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/robots/ChatNoticeRobot/settings', [
            'robot' => $this,
            'webhooks' => $this->webhooks,
        ]);
    }
}