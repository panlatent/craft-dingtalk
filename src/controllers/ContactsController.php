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
use panlatent\craft\dingtalk\elements\Contact;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ContactsController
 *
 * @package panlatent\craft\dingtalk\controllers
 * @author Panlatent <panlatent@gmail.com>
 */
class ContactsController extends Controller
{
    /**
     * @param int|null $contactId
     * @param Contact|null $contact
     * @return Response
     */
    public function actionEditContact(int $contactId = null, Contact $contact = null): Response
    {
        if ($contact === null) {
            if ($contactId !== null) {
                $contact = Contact::find()->id($contactId)->one();
                if (!$contact) {
                    throw new NotFoundHttpException();
                }
            } else {
                $contact = new Contact();
            }
        }

        $isNew = !$contact->id;
        if ($isNew) {
            $title = Craft::t('dingtalk', 'Create a new contact');
        } else {
            $title = $contact->name;
        }

        return $this->renderTemplate('dingtalk/contacts/_edit', [
            'contact' => $contact,
            'title' => $title,
        ]);
    }

    /**
     * Save a contact.
     *
     * @return Response|null
     */
    public function actionSaveContact()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $contactId = $request->getBodyParam('contactId');
        $followerId = $request->getBodyParam('followerId.0');

        if ($contactId) {
            $contact = Contact::find()->id($contactId)->one();
            if (!$contact) {
                throw new NotFoundHttpException();
            }
        } else {
            $contact = new Contact([
                'corporationId' => $request->getBodyParam('corporationId'),
            ]);
        }

        $contact->name =  $request->getBodyParam('name');
        $contact->mobile = $request->getBodyParam('mobile');
        $contact->position = $request->getBodyParam('position');
        $contact->followerId = $followerId;
        $contact->stateCode =  $request->getBodyParam('stateCode');
        $contact->companyName =  $request->getBodyParam('companyName');
        $contact->address =  $request->getBodyParam('address');
        $contact->remark =  $request->getBodyParam('remark');

        if (!Craft::$app->getElements()->saveElement($contact)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save contact.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'contact' => $contact
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Contact saved.'));

        return $this->redirectToPostedUrl();
    }

}