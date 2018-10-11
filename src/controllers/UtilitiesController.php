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
use panlatent\craft\dingtalk\queue\jobs\SyncContactsJob;
use yii\web\Response;

class UtilitiesController extends Controller
{
    public function actionSyncContactsAction(): Response
    {
        Craft::$app->queue->push(new SyncContactsJob());

        return $this->redirectToPostedUrl();
    }
}