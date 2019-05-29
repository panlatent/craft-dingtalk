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
use panlatent\craft\dingtalk\base\ProcessInterface;
use panlatent\craft\dingtalk\Plugin;
use panlatent\craft\dingtalk\processes\BasicProcess;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ProcessesController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class ProcessesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Edit a process.
     *
     * @param int|null $processId
     * @param ProcessInterface|null $process
     * @return Response
     */
    public function actionEditProcess(int $processId = null, ProcessInterface $process = null): Response
    {
        $processes = Plugin::$dingtalk->processes;

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

        $corporationOptions = [];
        foreach (Plugin::$dingtalk->getCorporations()->getAllCorporations() as $corporation) {
            $corporationOptions[] = [
                'label' => $corporation->name,
                'value' => $corporation->id,
            ];
        }

        if ($isNewProcess) {
            $title = Craft::t('dingtalk', 'New process');
        } else {
            $title = Craft::t('dingtalk', 'Edit process');
        }

        $crumbs = [
            ['label' => Craft::t('dingtalk', 'DingTalk'), 'url' => UrlHelper::cpUrl('dingtalk')],
            ['label' => Craft::t('dingtalk', 'Settings'), 'url' => UrlHelper::cpUrl('dingtalk/settings')],
            ['label' => Craft::t('dingtalk', 'Processes'), 'url' => UrlHelper::cpUrl('dingtalk/settings/processes')],
        ];

        return $this->renderTemplate('dingtalk/settings/processes/_edit', [
            'isNewProcess' => $isNewProcess,
            'process' => $process,
            'processOptions' => $processOptions,
            'processInstances' => $processInstances,
            'corporationOptions' => $corporationOptions,
            'title' => $title,
            'crumbs' => $crumbs,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveProcess()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $processes = Plugin::$dingtalk->processes;


        $process = $processes->createProcess([
            'id' => $request->getBodyParam('processId'),
            'corporationId' => $request->getBodyParam('corporationId'),
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

        $processes = Plugin::$dingtalk->processes;

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
        $processes = Plugin::$dingtalk->processes;

        $process = $processes->getProcessById($processId);
        if (!$process) {
            throw new NotFoundHttpException();
        }

        return $this->renderTemplate('dingtalk/processes/_sync', [
            'process' => $process,
        ]);
    }
}
