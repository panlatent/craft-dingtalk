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
use yii\base\Event;

/**
 * Trait Routes
 *
 * @package panlatent\craft\dingtalk\plugin
 * @author Panlatent <panlatent@gmail.com>
 */
trait Routes
{
    /**
     * Register Cp Url Rules.
     */
    private function _registerCpRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'dingtalk/contacts/new' => 'dingtalk/contacts/edit-contact',
                'dingtalk/contacts/<contactId:\d+>' => 'dingtalk/contacts/edit-contact',
                'dingtalk/approvals/<corporationHandle:{handle}>' => ['template' => 'dingtalk/approvals'],
                'dingtalk/processes' => 'dingtalk/processes',
                'dingtalk/processes/new' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>/sync' => 'dingtalk/processes/edit-process-sync',
                'dingtalk/robots/new' => 'dingtalk/robots/edit-robot',
                'dingtalk/robots/<robotId:\d+>' => 'dingtalk/robots/edit-robot',
                'dingtalk/users/<userId:\d+>' => 'dingtalk/users/edit-user',
                'dingtalk/settings/corporations/new' => ['template' => 'dingtalk/settings/corporations/_edit'],
                'dingtalk/settings/corporations/<corporationId:\d+>' => ['template' => 'dingtalk/settings/corporations/_edit'],
            ]);
        });
    }

    private function _registerSiteRoutes()
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            if ($this->getSettings()->callbackUrlRule !== null) {
                $event->rules[$this->getSettings()->callbackUrlRule] = 'dingtalk/callbacks/receive-event';
            }
        });
    }
}