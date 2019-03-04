<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\web\Controller;
use panlatent\craft\dingtalk\Plugin;

/**
 * Class SettingsController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class SettingsController extends Controller
{
    public function actionSaveSettings()
    {
        $this->requirePostRequest();

        $params = Craft::$app->getRequest()->getBodyParams();
        $data = $params['settings'];

        $settings = Plugin::getInstance()->getSettings();
        $settings->callbackUrlRule = $data['callbackUrlRule'] ?? $settings->callbackUrlRule;

        if (!$settings->validate()) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save settings.'));

            return $this->renderTemplate('dingtalk/settings/general/index', compact('settings'));
        }

        Craft::$app->getPlugins()->savePluginSettings(Plugin::getInstance(), $settings->toArray());
    }
}