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
use panlatent\craft\dingtalk\models\Settings;
use panlatent\craft\dingtalk\Plugin;
use yii\web\Response;

/**
 * Class SettingsController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class SettingsController extends Controller
{
    /**
     * 保存插件设置
     *
     * @return Response|null
     */
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

        if (!Craft::$app->getPlugins()->savePluginSettings(Plugin::getInstance(), $settings->toArray())) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save settings.'));

            return $this->renderTemplate('dingtalk/settings/general/index', compact('settings'));
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response|null
     */
    public function actionSaveExternalContactsSettings()
    {
        $this->requirePostRequest();

        $uid = Craft::$app->getRequest()->getBodyParam('categoryGroupUid');
        $categoryGroup = Craft::$app->getCategories()->getGroupByUid($uid);

        $settings = Plugin::getInstance()->getSettings();
        $settings->externalContactCategoryGroupUid = $categoryGroup->uid;

        if (!$this->_saveSettings($settings)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save settings.'));

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Settings saved.'));

        return $this->redirectToPostedUrl();
    }

    private function _saveSettings(Settings $settings, bool $runValidation = true)
    {
        if ($settings === null) {
            $settings = Plugin::getInstance()->getSettings();
        }

        if ($runValidation && !$settings->validate()) {
            return false;
        }

        if (!Craft::$app->getPlugins()->savePluginSettings(Plugin::getInstance(), $settings->toArray())) {
            return false;
        }

        return true;
    }

}