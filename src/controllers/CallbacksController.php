<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use panlatent\craft\dingtalk\models\Callback;
use panlatent\craft\dingtalk\models\CallbackGroup;
use panlatent\craft\dingtalk\Plugin;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CallbacksController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbacksController extends Controller
{
    /**
     * @inheritdoc
     */
   // public $enableCsrfValidation = false;

    /**
     * @inheritdoc
     */
    //protected $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    // Private Methods
    // =========================================================================

    // Groups
    // -------------------------------------------------------------------------

    /**
     * @param int|null $groupId
     * @param CallbackGroup|null $group
     * @return Response
     */
    public function actionEditGroup(int $groupId = null, CallbackGroup $group = null): Response
    {
        if ($group === null) {
            if ($groupId !== null) {
                $group = Plugin::$dingtalk->getCallbacks()->getGroupById($groupId);
                if (!$group) {
                    throw new NotFoundHttpException();
                }
            } else {
                $group = Plugin::$dingtalk->getCallbacks()->createGroup([]);
            }
        }

        $isNewGroup = !$group->id;

        if ($isNewGroup) {
            $title = Craft::t('dingtalk', 'Create a group');
        } else {
            $title = $group->name;
        }

        $crumbs = [
            [
                'label' => Craft::t('dingtalk', 'DingTalk'),
                'url' => UrlHelper::url('dingtalk'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => UrlHelper::url('dingtalk/settings'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Callback Groups'),
                'url' => UrlHelper::url('dingtalk/settings/callbackgroups'),
            ],
        ];

        return $this->renderTemplate('dingtalk/settings/callbackgroups/_edit', [
            'group' => $group,
            'isNewGroup' => $isNewGroup,
            'title' => $title,
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $callbacks = Plugin::$dingtalk->getCallbacks();

        $group = $callbacks->createGroup([
            'id' => $request->getBodyParam('groupId'),
            'name' => $request->getBodyParam('name'),
        ]);

        if (!$callbacks->saveGroup($group)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save group.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'group' => $group,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Group saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     */
    public function actionDeleteGroup()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $callbacks = Plugin::$dingtalk->getCallbacks();
        $id = Craft::$app->getRequest()->getBodyParam('id');

        $group = $callbacks->getGroupById($id);
        if (!Plugin::$dingtalk->getCallbacks()->deleteGroup($group)) {
            return $this->asJson([
                'success' => false,
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    // Callbacks
    // -------------------------------------------------------------------------

    /**
     * @param int|null $callbackId
     * @param Callback|null $callback
     * @return Response
     */
    public function actionEditCallback(int $callbackId = null, Callback $callback = null): Response
    {
        $callbacks = Plugin::$dingtalk->getCallbacks();

        if ($callback === null) {
            if ($callbackId !== null) {
                $callback = $callbacks->getCallbackById($callbackId);
                if (!$callback) {
                    throw new NotFoundHttpException();
                }
            } else {
                $callback = $callbacks->createCallback([]);
            }
        }

        $isNewCallback = !$callback->id;

        $groupOptions = [];
        foreach ($callbacks->getAllGroups() as $group) {
            $groupOptions[] = [
                'label' => $group->name,
                'value' => $group->id,
            ];
        }

        if ($isNewCallback) {
            $title = Craft::t('dingtalk', 'Create a callback');
        } else {
            $title = Craft::t('dingtalk', $callback->name);
        }

        $crumbs = [
            [
                'label' => Craft::t('dingtalk', 'DingTalk'),
                'url' => UrlHelper::url('dingtalk'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => UrlHelper::url('dingtalk/settings'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Callbacks'),
                'url' => UrlHelper::url('dingtalk/settings/callbacks'),
            ],
        ];

        return $this->renderTemplate('dingtalk/settings/callbacks/_edit', [
            'isNewCallback' => $isNewCallback,
            'callback' => $callback,
            'groupOptions' => $groupOptions,
            'title' => $title,
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveCallback()
    {
        $this->requirePostRequest();

        $requests = Craft::$app->getRequest();
        $callbacks = Plugin::$dingtalk->getCallbacks();

        $callback = $callbacks->createCallback([
            'id' => $requests->getBodyParam('callbackId'),
            'groupId' => $requests->getBodyParam('groupId'),
            'name' => $requests->getBodyParam('name'),
            'handle' => $requests->getBodyParam('handle'),
            'code' => $requests->getBodyParam('code'),
        ]);

        if (!$callbacks->saveCallback($callback)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save callback.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'callback' => $callback
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Callback saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     */
    public function actionDeleteCallback()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $callbacks = Plugin::$dingtalk->getCallbacks();

        $id = Craft::$app->getRequest()->getBodyParam('id');
        $callback = $callbacks->getCallbackById($id);
        if (!$callback) {
            throw new NotFoundHttpException();
        }

        if (!$callbacks->deleteCallback($callback)) {
            return $this->asJson([
                'success' => false
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }
}