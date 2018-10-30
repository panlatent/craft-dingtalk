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
use panlatent\craft\dingtalk\base\Robot;
use panlatent\craft\dingtalk\base\RobotInterface;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\robots\ChatNoticeRobot;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class RobotsController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requirePermission('viewDingTalkRobots');

        $crumbs = [
            ['label' => Craft::t('dingtalk', 'DingTalk'), 'url' => UrlHelper::url('settings/plugins/dingtalk')],
        ];

        $allRobots = Plugin::$plugin->robots->getAllRobots();

        return $this->renderTemplate('dingtalk/robots/_index', [
            'robots' => $allRobots,
            'crumbs' => $crumbs,
        ]);
    }

    public function actionEditRobot(int $robotId = null, RobotInterface $robot = null): Response
    {
        $this->requirePermission('manageDingTalkRobots');

        $robots = Plugin::$plugin->robots;

        /** @var Robot $robot */
        if ($robot === null) {
            if ($robotId !== null) {
                $robot = $robots->getRobotById($robotId);

                if ($robot === null) {
                    throw new NotFoundHttpException('Robot not found');
                }
            } else {
                $robot = $robots->createRobot(ChatNoticeRobot::class);
            }
        }

        $isNewRobot = $robot->getIsNew();

        $allRobotTypes = $robots->getAllRobotTypes();

        $robotTypeOptions = [];
        $robotInstances = [];
        foreach ($allRobotTypes as $class) {
            $robotInstances[$class] = $robots->createRobot($class);
            $robotTypeOptions[] = [
                'label' => call_user_func([$class, 'displayName']),
                'value' => $class,
            ];
        }

        if ($robotId) {
            $title = $robot->name;
        } else {
            $title = 'Create a new robot';
        }

        $crumbs = [
            ['label' => Craft::t('dingtalk', 'DingTalk'), 'url' => UrlHelper::url('settings/plugins/dingtalk')],
            ['label' => Craft::t('dingtalk', 'Robots'), 'url' => UrlHelper::url('dingtalk/robots')],
        ];

        return $this->renderTemplate('dingtalk/robots/_edit', [
            'isNewRobot' => $isNewRobot,
            'robot' => $robot,
            'robotTypes' => $allRobotTypes,
            'robotTypeOptions' => $robotTypeOptions,
            'robotInstances' => $robotInstances,
            'title' => Craft::t('dingtalk', $title),
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveRobot()
    {
        $this->requirePostRequest();
        $this->requirePermission('manageDingTalkRobots');

        $request = Craft::$app->getRequest();
        $robots = Plugin::$plugin->robots;

        $type = $request->getBodyParam('type');

        $robot = $robots->createRobot([
            'id' => $request->getBodyParam('robotId'),
            'type' => $type,
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'settings' => $request->getBodyParam('types.' . $type)
        ]);

        $session = Craft::$app->getSession();

        if (!$robots->saveRobot($robot)) {
            $session->setError(Craft::t('dingtalk', 'Couldn’t save robot.'));

            // Send the volume back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'robot' => $robot
            ]);

            return null;
        }

        $session->setNotice(Craft::t('dingtalk', 'Robot saved.'));

        return $this->redirect('dingtalk/robots');
    }

    public function actionDeleteRobot()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('manageDingTalkRobots');

        $robots = Plugin::$plugin->robots;
        $robotId = Craft::$app->request->getBodyParam('id');
        if (!($robot = $robots->getRobotById($robotId))) {
            return $this->asJson(['success' => false]);
        }

        return $this->asJson(['success' => $robots->deleteRobot($robot)]);
    }
}