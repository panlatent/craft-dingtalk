<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\plugin;

use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Event;

/**
 * Trait Routes
 *
 * @package panlatent\craft\dingtalk\plugin
 * @author Panlatent <panlatent@gmail.com>
 */
trait Routes
{
    // Private Methods
    // =========================================================================

    /**
     * Register Cp url rules.
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'dingtalk/approvals/<corporationHandle:{handle}>' => ['template' => 'dingtalk/approvals'],
                'dingtalk/contacts/new' => 'dingtalk/contacts/edit-contact',
                'dingtalk/contacts/<contactId:\d+>' => 'dingtalk/contacts/edit-contact',
                'dingtalk/corporations/<groupHandle:{handle}>' => 'dingtalk/corporations',
                'dingtalk/corporations/<groupHandle:{handle}>/new' => 'dingtalk/corporations/edit-corporation',
                'dingtalk/corporations/<groupHandle:{handle}>/<corporationId:\d+>' => 'dingtalk/corporations/edit-corporation',
                'dingtalk/processes' => 'dingtalk/processes',
                'dingtalk/processes/new' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>/sync' => 'dingtalk/processes/edit-process-sync',
                'dingtalk/robots/new' => 'dingtalk/robots/edit-robot',
                'dingtalk/robots/<robotId:\d+>' => 'dingtalk/robots/edit-robot',
                'dingtalk/users/<userId:\d+>' => 'dingtalk/users/edit-user',
                'dingtalk/settings/callbackgroups/new' => 'dingtalk/corporation/edit-group',
                'dingtalk/settings/callbackgroups/<groupId:\d+>' => 'dingtalk/corporation/edit-group',
                'dingtalk/settings/callbacks/new' => 'dingtalk/callbacks/edit-callback',
                'dingtalk/settings/callbacks/<callbackId:\d+>' => 'dingtalk/callbacks/edit-callback',
                'dingtalk/settings/corporationgroups/new' => 'dingtalk/corporations/edit-group',
                'dingtalk/settings/corporationgroups/<groupId:\d+>' => 'dingtalk/corporations/edit-group',
                'dingtalk/settings/processes/new' => 'dingtalk/processes/edit-process',
            ]);
        });
    }

    /**
     * Register site url rules.
     */
    private function _registerSiteRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            if (Plugin::$dingtalk->settings->getCallbackRule() !== null) {
                $event->rules[Plugin::$dingtalk->settings->getCallbackRule()] = 'dingtalk/callbacks/create-request';
            }
        });
    }
}