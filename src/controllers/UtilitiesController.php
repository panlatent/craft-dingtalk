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
use panlatent\craft\dingtalk\models\SyncUtilityForm;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\queue\jobs\SyncApprovalsJob;
use panlatent\craft\dingtalk\queue\jobs\SyncUsersJob;
use panlatent\craft\dingtalk\queue\jobs\SyncExternalContactsJob;
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
        $messages = Plugin::getInstance()->messages;
        $robots = Plugin::getInstance()->robots;

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

    /**
     * 钉钉同步动作
     *
     * @return Response|null
     */
    public function actionSyncAction()
    {
        $this->requirePermission('syncDingTalkContacts');
        $this->requirePostRequest();

        $corporations = Plugin::getInstance()->getCorporations();
        $request = Craft::$app->getRequest();

        $form = new SyncUtilityForm();

        if ($form->load($request->getBodyParams(), '') && !$form->validate()) {
            Craft::$app->getSession()->setError("Create sync jobs error.");

            Craft::$app->getUrlManager()->setRouteParams([
                'sync' => $form,
            ]);

            return null;
        }

        $corporationIds = $request->getBodyParam('corporationIds', []);
        $types = $request->getBodyParam('types', []);

        if (empty($corporationIds) || empty($types)) {
            Craft::$app->getSession()->setError('Sync error.');

            return null;
        }

        if ($corporationIds === '*') {
            $selectedCorporations = $corporations->getAllCorporations();
        } else {
            $selectedCorporations = [];
            foreach ($corporationIds as $corporationId) {
                $selectedCorporations[] = $corporations->getCorporationById($corporationId);
            }
        }

        if ($types === '*') {
            $types = ['users', 'externalcontacts', 'approvals'];
        }

        foreach ($selectedCorporations as $corporation) {

            foreach ($types as $type) {
                switch ($type) {
                    case 'users':
                        Craft::$app->getQueue()->push(new SyncUsersJob([
                            'corporationId' => $corporation->id,
                            'withSmartWorks' => $request->getBodyParam('withSmartWorks'),
                        ]));
                        break;
                    case 'externalcontacts':
                        Craft::$app->getQueue()->push(new SyncExternalContactsJob([
                            'corporationId' => $corporation->id,
                        ]));
                        break;
                    case 'approvals':
                        Craft::$app->getQueue()->push(new SyncApprovalsJob([]));
                        break;
                }
            }
        }

        return $this->redirectToPostedUrl();
    }
}