<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use GuzzleHttp\Client;
use panlatent\craft\dingtalk\models\RobotWebhook;
use panlatent\craft\dingtalk\records\Robot as RobotRecord;
use panlatent\craft\dingtalk\records\RobotWebhook as RobotWebhookRecord;
use yii\web\Response;

/**
 * Class Robot
 *
 * @package panlatent\craft\dingtalk\base
 * @property-read Client $client
 * @property-read string[] $urls
 * @property RobotWebhook[] $webhooks
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Robot extends SavableComponent implements RobotInterface
{
    use RobotTrait;

    /**
     * @var Client|null
     */
    private $_client;

    /**
     * @var RobotWebhook[]|null
     */
    private $_webhoos;

    /**
     * @return bool
     */
    public static function canHandleRequest(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
            [['handle'], UniqueValidator::class, 'targetClass' => RobotRecord::class],
            [['handle', 'name'], 'string', 'max' => 255],
            [['name', 'handle'], 'required'],
            [
                ['handle'],
                HandleValidator::class,
                'reservedWords' => [
                    'id',
                    'dateCreated',
                    'dateUpdated',
                    'uid',
                    'title',
                ],
            ],
            [['webhooks'], function($webhooks) {
                /** @var RobotWebhook $webhook */
                foreach ($this->$webhooks as $webhook) {
                    if (!$webhook->validate()) {
                        $this->addError($webhooks, 'Validation error');
                    }
                }
            }],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'webhooks';

        return $attributes;
    }

    /**
     * @param Response $response
     */
    public function handle(Response $response)
    {

    }

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function send(MessageInterface $message): bool
    {
        $urls = $this->getUrls();
        foreach ($urls as $url) {
            $this->getClient()->post($url, [
                'json' => $message->getRequestPayload(),
            ]);
        }

        return true;
    }

    /**
     * @return Client
     */
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
        foreach ($this->getWebhooks() as $webhook) {
            if ($webhook->enabled) {
                $urls[] = $webhook['url'];
            }
        }

        return $urls;
    }

    /**
     * @return RobotWebhook[]
     */
    public function getWebhooks(): array
    {
        if ($this->_webhoos !== null) {
            return $this->_webhoos;
        }

        $this->_webhoos = [];

        /** @var RobotWebhookRecord[] $webhookRecords */
        $webhookRecords = RobotWebhookRecord::find()
            ->where(['robotId' => $this->id])
            ->all();

        foreach ($webhookRecords as $webhookRecord) {
            $this->_webhoos[] = new RobotWebhook([
                'id' => $webhookRecord->id,
                'name' => $webhookRecord->name,
                'url' => $webhookRecord->url,
                'enabled' => $webhookRecord->enabled,
                'rateLimit' =>$webhookRecord->rateLimit,
                'rateWindow' => $webhookRecord->rateWindow,
            ]);
        }

        return $this->_webhoos;
    }

    /**
     * @param RobotWebhook[]|null $webhooks
     */
    public function setWebhooks($webhooks)
    {
        if ($webhooks === null) {
            $this->_webhoos = null;
            return;
        }

        foreach ($webhooks as $webhook) {
            if (is_array($webhook)) {
                $webhook = new RobotWebhook([
                    'id' => $webhook['id'] ?? null,
                    'name' => $webhook['name'],
                    'url' => $webhook['url'],
                    'enabled' => $webhook['enabled'],
                ]);
            }

            $this->_webhoos[] = $webhook;
        }
    }
}