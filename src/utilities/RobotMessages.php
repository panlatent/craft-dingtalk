<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\utilities;

use Craft;
use craft\base\Utility;
use panlatent\craft\dingtalk\base\MessageInterface;
use panlatent\craft\dingtalk\base\Robot;
use panlatent\craft\dingtalk\messages\Text;
use panlatent\craft\dingtalk\Plugin;

class RobotMessages extends Utility
{
    public static function id(): string
    {
        return 'dingtalk-robot-messages';
    }

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Robot Messages');
    }

    public static function iconPath()
    {
        return Craft::getAlias('@dingtalk/icons/robots.svg');
    }

    public static function contentHtml(): string
    {
        $robots = Plugin::$plugin->robots;
        $messages = Plugin::$plugin->messages;

        /** @var Robot[] $allRobots */
        $allRobots = $robots->getAllRobots();
        $robotOptions = [];

        foreach ($allRobots as $robot) {
            $robotOptions[] = [
                'label' => $robot->name,
                'value' => $robot->id,
            ];
        }

        $message = $messages->createMessage(Text::class);
        $allMessageTypes = $messages->getAllMessageTypes();
        $messageTypeOptions = [];
        $messageInstances = [];

        foreach ($allMessageTypes as $class) {
            /** @var MessageInterface|string $class */
            $messageInstances[$class] = $messages->createMessage($class);
            $messageTypeOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        return Craft::$app->getView()->renderTemplate('dingtalk/_components/utilities/RobotMessages', [
            'robotOptions' => $robotOptions,
            'message' => $message,
            'messageTypes' => $allMessageTypes,
            'messageTypeOptions' => $messageTypeOptions,
            'messageInstances' => $messageInstances,
        ]);
    }

}