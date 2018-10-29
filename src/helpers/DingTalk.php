<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\helpers;

use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\services\Api;
use panlatent\craft\dingtalk\services\Departments;
use panlatent\craft\dingtalk\services\Messages;
use panlatent\craft\dingtalk\services\Robots;
use panlatent\craft\dingtalk\services\SmartWorks;
use panlatent\craft\dingtalk\services\Users;

class DingTalk
{
    /**
     * @return Api
     */
    public static function getApi(): Api
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->api;
    }

    /**
     * @return Departments
     */
    public static function getDepartments(): Departments
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->departments;
    }

    /**
     * @return Messages
     */
    public static function getMessages(): Messages
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->messages;
    }

    /**
     * @return Robots
     */
    public static function getRobots(): Robots
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->robots;
    }

    /**
     * @return Users
     */
    public static function getUsers(): Users
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->users;
    }

    /**
     * @return SmartWorks
     */
    public static function getSmartWorks(): SmartWorks
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Plugin::$plugin->smartWorks;
    }
}