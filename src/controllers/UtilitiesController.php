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
use panlatent\craft\dingtalk\queue\jobs\SyncContactsJob;
use yii\web\Response;

/**
 * Class UtilitiesController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class UtilitiesController extends Controller
{
    public function actionSendRobotMessageAction()
    {
        $this->requirePostRequest();
        $this->requirePermission('sendDingTalkRobotMessages');

        $request = Craft::$app->getRequest();
        $messages = Plugin::$plugin->messages;
        $robots = Plugin::$plugin->robots;

        $robotId = $request->getBodyParam('robotId');
        $messageType = $request->getBodyParam('messageType');
        $types = $request->getBodyParam('types');

        if (!($robot = $robots->getRobotById($robotId))) {
            Craft::$app->session->setError(Craft::t('dingtalk', 'Not found a robot'));

            return;
        }

        $config = $types[$messageType];
        $config['type'] = $messageType;
        $message = $messages->createMessage($config);

        if (!$robot->send($message)) {
            Craft::$app->session->setError(Craft::t('dingtalk', 'Send message failed.'));

            return;
        }

        Craft::$app->session->setNotice(Craft::t('dingtalk', 'Message has been sent.'));
    }

    public function actionSyncContactsAction(): Response
    {
        $this->requirePermission('syncDingTalkContacts');
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        Craft::$app->queue->push(new SyncContactsJob([
            'withLeavedUsers' => $request->getBodyParam('withLeavedUsers'),
            'operateUserId' => $request->getBodyParam('operateUserId'),
        ]));

        return $this->redirectToPostedUrl();
    }
}