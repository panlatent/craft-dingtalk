<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Component as ComponentHelper;
use panlatent\craft\dingtalk\base\MessageInterface;
use panlatent\craft\dingtalk\messages\ActionCard;
use panlatent\craft\dingtalk\messages\FeedCard;
use panlatent\craft\dingtalk\messages\Link;
use panlatent\craft\dingtalk\messages\Markdown;
use panlatent\craft\dingtalk\messages\MissingMessage;
use panlatent\craft\dingtalk\messages\Text;
use yii\base\Component;

/**
 * Class Messages
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Messages extends Component
{
    // Constants
    // =========================================================================

    // Events
    // -------------------------------------------------------------------------
    const EVENT_REGISTER_MESSAGE_TYPES = 'registerMessageTypes';

    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * @return string[]
     */
    public function getAllMessageTypes(): array
    {
        $types = [
            ActionCard::class,
            FeedCard::class,
            Link::class,
            Markdown::class,
            Text::class,
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(self::EVENT_REGISTER_MESSAGE_TYPES, $event);

        return $event->types;
    }

    public function sendMessage(MessageInterface $message)
    {

    }

    /**
     * @param mixed $config
     * @return MessageInterface
     */
    public function createMessage($config): MessageInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            $message = ComponentHelper::createComponent($config, MessageInterface::class);
        } catch (MissingComponentException $exception) {
            unset($config['type']);
            $message = new MissingMessage($config);
        }

        return $message;
    }

    public function saveMessage(MessageInterface $message, bool $runValidation = true): bool
    {
        return true;
    }
}