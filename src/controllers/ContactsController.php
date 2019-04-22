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
use panlatent\craft\dingtalk\Plugin;
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
            $title = Craft::t('dingtalk', 'Edit contact: {name}', ['name' => $contact->name]);
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

        $followerId = $request->getBodyParam('followerId.0');


        $contact = new Contact([
            'id' => $request->getBodyParam('contactId'),
            'corporationId' => '',
            'name' => $request->getBodyParam('name'),
            'mobile' => $request->getBodyParam('mobile'),
            'position' => $request->getBodyParam('position'),
            'followerId' => $followerId,
        ]);

        if (!Plugin::getInstance()->getContacts()->saveRemoteContact($contact)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save contact.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'contact' => $contact
            ]);

            return null;
        }

        Craft::$app->getSession()->setName(Craft::t('dingtalk', 'Contact saved.'));

        return $this->redirectToPostedUrl();
    }

}