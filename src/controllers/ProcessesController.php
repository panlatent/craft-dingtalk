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
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\processes\BasicProcess;
use panlatent\craft\dingtalk\queue\jobs\SyncApprovalsJob;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProcessesController extends Controller
{
    public function actionIndex(): Response
    {
        $allProcesses = Plugin::$plugin->processes->getAllProcesses();

        return $this->renderTemplate('dingtalk/processes/_index', [
            'processes' => $allProcesses,
        ]);
    }

    /**
     * Edit a process.
     *
     * @param int|null $processId
     * @param ProcessInterface|null $process
     * @return Response
     */
    public function actionEditProcess(int $processId = null, ProcessInterface $process = null): Response
    {
        $processes = Plugin::$plugin->processes;

        $allProcessTypes = $processes->getAllProcessTypes();

        if ($process === null) {
            if ($processId !== null) {
                $process = $processes->getProcessById($processId);
                if (!$process) {
                    throw new NotFoundHttpException();
                }
            } else {
                $process = new BasicProcess();
            }
        }

        $isNewProcess = $process->getIsNew();

        $processOptions = [];
        $processInstances = [];
        foreach ($allProcessTypes as $class) {
            /** @var ProcessInterface|string $class */
            $processInstances[$class] = new $class();
            $processOptions[] = [
                'label' => $class::displayName(),
                'value' => $class,
            ];
        }

        $title = Craft::t('dingtalk', 'New a process');

        return $this->renderTemplate('dingtalk/processes/_edit', [
            'isNewProcess' => $isNewProcess,
            'process' => $process,
            'processOptions' => $processOptions,
            'processInstances' => $processInstances,
            'title' => $title,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveProcess()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $processes = Plugin::$plugin->processes;


        $process = $processes->createProcess([
            'id' => $request->getBodyParam('processId'),
            'type' => $request->getBodyParam('type'),
            'name' => $request->getBodyParam('name'),
            'handle' => $request->getBodyParam('handle'),
            'code' => $request->getBodyParam('code'),
            'settings' => $request->getBodyParam('settings'),
        ]);

        if (!$processes->saveProcess($process)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save process.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'process' => $process,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Process saved.'));

        return $this->redirectToPostedUrl($process);
    }

    /**
     * @return Response
     */
    public function actionDeleteProcess()
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $processes = Plugin::$plugin->processes;

        $processId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $process = $processes->getProcessById($processId);

        if (!$process || !$processes->deleteProcess($process)) {
            return $this->asJson([
                'success' => false,
            ]);
        }

        return $this->asJson([
            'success' => true,
        ]);
    }

    /**
     * @param int $processId
     * @return Response
     */
    public function actionEditProcessSync(int $processId): Response
    {
        $processes = Plugin::$plugin->processes;

        $process = $processes->getProcessById($processId);
        if (!$process) {
            throw new NotFoundHttpException();
        }

        return $this->renderTemplate('dingtalk/processes/_sync', [
            'process' => $process,
        ]);
    }

    public function actionSyncProcessAction()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $processId = $request->getBodyParam('processId');
        $process = Plugin::$plugin->processes->getProcessById($processId);
        if (!$process) {
            throw new NotFoundHttpException();
        }

        Craft::$app->getQueue()->push(new SyncApprovalsJob([
            'process' => $process,
            'startTime' => $request->getBodyParam('startTime'),
            'endTime' => $request->getBodyParam('endTime'),
        ]));
    }

}
