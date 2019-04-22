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
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\enums\PermissionItems;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class UsersController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class UsersController extends Controller
{
    /**
     * @param int|null $userId
     * @param User|null $user
     * @return Response
     */
    public function actionEditUser(int $userId = null, User $user = null): Response
    {
        if ($user === null) {
            if ($userId !== null) {
                $user = User::find()->id($userId)->one();
                if (!$user) {
                    throw new NotFoundHttpException();
                }
            } else {
                $user = new User();
            }
        }

        $isNew = !$user->id;


        return $this->renderTemplate('dingtalk/users/_edit', [
            'user' => $user,
            'title' => $isNew ? Craft::t('dingtalk', 'Create a new user') : $user->name,
        ]);
    }

    /**
     * @return Response|null
     */
    public function actionSaveUserFieldLayout()
    {
        $this->requirePostRequest();
        $this->requirePermission(PermissionItems::EditUserSettings);

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = User::class;

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save field layout.'));

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Field layout saved.'));

        return $this->redirectToPostedUrl();
    }
}