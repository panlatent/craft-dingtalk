<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use craft\events\RegisterComponentTypesEvent;
use panlatent\craft\dingtalk\base\CallbackHandlerInterface;
use panlatent\craft\dingtalk\events\CallbackEvent;
use panlatent\craft\dingtalk\models\Callback;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;
use yii\base\Event;

/**
 * Class Callbacks
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class Callbacks extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_CALLBACK_HANDLER_TYPES = 'registerCallbackHandlerTypes';

    // Properties
    // =========================================================================

    /**
     * @var CallbackHandlerInterface[]|null
     */
    private $_handlers;

    // Public Methods
    // =========================================================================

    /**
     * @return string[]
     */
    public function getAllCallbackHandlerTypes(): array
    {
        $types = [];

        $event = new RegisterComponentTypesEvent([
            'types' => $types,
        ]);

        $this->trigger(self::EVENT_REGISTER_CALLBACK_HANDLER_TYPES, $event);

        return $event->types;
    }

    /**
     * @param Callback $event
     * @return bool
     */
    public function post(Callback $event): bool
    {
        if ($this->_handlers === null) {
            $this->_handlers = [];
            foreach ($this->getAllCallbackHandlerTypes() as $class) {
                $this->_handlers[] = new $class();
            }
        }

        foreach ($this->_handlers as $handler) {
            if (in_array($event->name, $handler::sources())) {
                $handler->handle($event);
            }
        }

        return true;
    }

    public function log()
    {

    }

    public function getTotalLogs(): int
    {

    }

    public function findLogs()
    {

    }

    public function saveCallBack()
    {

    }

    public function deleteCallBack()
    {

    }
}