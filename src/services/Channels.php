<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use craft\events\RegisterComponentTypesEvent;
use yii\base\Component;

/**
 * Class Channels
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Channels extends Component
{
    // Constants
    // =========================================================================

    // Events
    // -------------------------------------------------------------------------

    /**
     * @event RegisterComponentTypesEvent
     */
    const EVENT_REGISTER_CHANNEL_TYPES = 'registerChannelTypes';

    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @return string[]
     */
    public function getAllChannelTypes(): array
    {
        $types = [

        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(self::EVENT_REGISTER_CHANNEL_TYPES, $event);

        return $event->types;
    }

    public function getAllChannels(): array
    {

    }

    public function getChannelById()
    {

    }

    public function getChannelByHandle()
    {

    }
}