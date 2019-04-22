<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use panlatent\craft\dingtalk\Plugin;
use yii\web\Response;

/**
 * Class CorporationsController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class CorporationsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->requireAdmin(false);

        return parent::init();
    }

    /**
     * @return Response|null
     */
    public function actionSaveCorporation()
    {
        $this->requirePostRequest();

        $corporations = Plugin::getInstance()->getCorporations();
        $request = Craft::$app->getRequest();

        $corporation = $corporations->createCorporation([
            'id' => $request->getBodyParam('corporationId'),
            'primary' => $request->getBodyParam('primary'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'corpId' => $request->getBodyParam('corpId'),
            'corpSecret' => $request->getBodyParam('corpSecret'),
            'hasUrls' => (bool)$request->getBodyParam('hasUrls'),
            'url' => $request->getBodyParam('url'),
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

        $corporations = Plugin::getInstance()->getCorporations();

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
           'success' => Plugin::getInstance()->getCorporations()->reorderCorporations($ids)
        ]);
    }
}