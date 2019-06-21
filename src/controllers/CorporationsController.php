<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use panlatent\craft\dingtalk\models\Corporation;
use panlatent\craft\dingtalk\models\CorporationGroup;
use panlatent\craft\dingtalk\Plugin;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CorporationsController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationsController extends Controller
{
    // Public Methods
    // =========================================================================

    // Groups
    // -------------------------------------------------------------------------

    /**
     * @param int|null $groupId
     * @param CorporationGroup|null $group
     * @return Response
     */
    public function actionEditGroup(int $groupId = null, CorporationGroup $group = null): Response
    {
        if ($group === null) {
            if ($groupId !== null) {
                $group = Plugin::$dingtalk->getCorporations()->getGroupById($groupId);
                if (!$group) {
                    throw new NotFoundHttpException();
                }
            } else {
                $group = new CorporationGroup();
            }
        }

        $isNewGroup = !$group->id;

        if ($isNewGroup) {
            $title = Craft::t('dingtalk', 'Craft a new group');
        } else {
            $title = $group->name;
        }

        $crumbs = [
            [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => UrlHelper::url('dingtalk/settings'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Corporation Groups'),
                'url' => UrlHelper::url('dingtalk/settings/corporationgroups'),
            ]
        ];

        $tabs = [
            [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => '#settings',
            ],
            [
                'label' => Craft::t('app', 'Field Layout'),
                'url' => '#fieldlayout',
            ],
        ];

        return $this->renderTemplate('dingtalk/settings/corporationgroups/_edit', [
            'group' => $group,
            'title' => $title,
            'crumbs' => $crumbs,
            'tabs' => $tabs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveGroup()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();



        $group = new CorporationGroup([
            'id' => $request->getBodyParam('groupId'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
        ]);

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = Corporation::class;
        $group->setFieldLayout($fieldLayout);

        if (!Plugin::$dingtalk->getCorporations()->saveGroup($group)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save group.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'group' => $group
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Group saved.'));

        return $this->redirectToPostedUrl($group);
    }

    // Corporations
    // -------------------------------------------------------------------------

    /**
     * @param string $groupHandle
     * @param int|null $corporationId
     * @param Corporation|null $corporation
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionEditCorporation(string $groupHandle, int $corporationId = null, Corporation $corporation = null): Response
    {
        $corporations = Plugin::$dingtalk->getCorporations();
        $callbacks = Plugin::$dingtalk->getCallbacks();

        $group = $corporations->getGroupByHandle($groupHandle);

        if ($corporation === null) {
            if ($corporationId !== null) {
                $corporation = $corporations->getCorporationById($corporationId);
                if (!$corporation) {
                    throw new NotFoundHttpException();
                }
            } else {
                $corporation = new Corporation([
                    'groupId' => $group->id
                ]);
            }
        }

        $isNewCorporation = !$corporation->id;

        $groupOptions = [];
        foreach ($corporations->getAllGroups() as $group) {
            $groupOptions[] = [
                'label' => $group->name,
                'value' => $group->id,
            ];
        }

        $callbackGroupOptions = [];
        foreach ($callbacks->getAllGroups() as $group) {
            $callbackOptions = [
                'label' => $group->name,
                'value' => $group->id,
                'values' => [],
                'options' => [],
            ];

            foreach ($corporation->getCallbackSettings()->getCallbacks() as $callback) {
                if ($callback->groupId == $group->id) {
                    $callbackOptions['values'][] = $callback->id;
                }
            }

            if (count($callbackOptions['values']) != 0 &&
                count($callbackOptions['values']) == count($group->getCallbacks())) {
                $callbackOptions['values'] = '*';
            }

            foreach ($group->getCallbacks() as $callback) {
                $callbackOptions['options'][] = [
                    'label' => $callback->name,
                    'value' => $callback->id,
                ];
            }

            $callbackGroupOptions[] = $callbackOptions;
        }

        if ($isNewCorporation) {
            $title = Craft::t('dingtalk', 'Create a corporation');
        } else {
            $title = Craft::t('dingtalk', $corporation->name);
        }

        $crumbs = [
            [
                'label' => Craft::t('dingtalk', 'DingTalk'),
                'url' => UrlHelper::url('dingtalk'),
            ],
            [
                'label' => Craft::t('dingtalk', 'Corporations'),
                'url' => UrlHelper::url('dingtalk/corporations'),
            ],
        ];

        $tabs = [
            'settings' => [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => '#settings'
            ],
            'callback' => [
                'label' => Craft::t('dingtalk', 'Callbacks'),
                'url' => '#callbacks'
            ],
        ];

        $fieldLayout = $corporation->getGroup()->getFieldLayout();
        if ($fieldLayout) {
            foreach ($corporation->getGroup()->getFieldLayout()->getTabs() as $tab) {
                $tabs[] = [
                    'label' => $tab->name,
                    'url' => '#' . $tab->getHtmlId(),
                ];
            }
        }

        return $this->renderTemplate('dingtalk/corporations/_edit', [
            'isNewCorporation' => $isNewCorporation,
            'corporation' => $corporation,
            'groupOptions' => $groupOptions,
            'callbackGroupOptions' => $callbackGroupOptions,
            'title' => $title,
            'crumbs' => $crumbs,
            'tabs' => $tabs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveCorporation()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $corporations = Plugin::$dingtalk->getCorporations();
        $callbacks = Plugin::$dingtalk->getCallbacks();

        $callbackIds = [];
        foreach ($request->getBodyParam('callbacks.groups', []) as $groupId => $values) {
            if (is_string($values) && $values == '*') {
                $group = $callbacks->getGroupById($groupId);
                if (!$group) {
                    throw new InvalidConfigException();
                }
                $callbackIds = array_merge($callbackIds, ArrayHelper::getColumn($group->getCallbacks(), 'id'));
            } elseif (is_array($values)) {
                $callbackIds = array_merge($callbackIds, $values);
            }
        }

        $corporation = new Corporation([
            'id' => $request->getBodyParam('corporationId'),
            'groupId' => $request->getBodyParam('groupId'),
            'primary' => $request->getBodyParam('primary'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'corpId' => $request->getBodyParam('corpId'),
            'corpSecret' => $request->getBodyParam('corpSecret'),
            'hasUrls' => (bool)$request->getBodyParam('hasUrls'),
            'url' => $request->getBodyParam('url'),
            'callbackSettings' => [
                'url' => $request->getBodyParam('callbacks.url'),
                'token' => $request->getBodyParam('callbacks.token'),
                'aesKey' => $request->getBodyParam('callbacks.aesKey'),
                'enabled' => $request->getBodyParam('callbacks.enabled'),
                'callbackIds' => $callbackIds,
            ],
        ]);

        if (!$corporations->saveCorporation($corporation)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save corporation.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'corporation' => $corporation,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Corporation saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * 删除企业
     *
     * @return Response
     */
    public function actionDeleteCorporation()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $corporations = Plugin::$dingtalk->getCorporations();

        $robotId = Craft::$app->request->getBodyParam('id');
        if (!($robot = $corporations->getCorporationById($robotId))) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => $corporations->deleteCorporation($robot)]);
    }

    /**
     * @return Response
     */
    public function actionReorderCorporations()
    {
        $this->requirePostRequest();

        $ids = Craft::$app->getRequest()->getBodyParam('ids');
        $ids = Json::decodeIfJson($ids);

        return $this->asJson([
           'success' => Plugin::$dingtalk->getCorporations()->reorderCorporations($ids)
        ]);
    }
}