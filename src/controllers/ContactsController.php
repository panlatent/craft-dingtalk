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
use panlatent\craft\dingtalk\elements\User;
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
                $contact = new Contact([
                    'corporationId' => Craft::$app->getRequest()->getRequiredQueryParam('corporationId'),
                ]);
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
        $contacts = Plugin::getInstance()->getContacts();

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

        $labels = [];
        foreach (array_filter($request->getBodyParam('labels', [])) as $label) {
            $labels[] = $contacts->getLabelById($label);
        }
        $contact->labels = $labels;

        $shareDepartmentIds = $request->getBodyParam('departments');
        $shareUserIds = $request->getBodyParam('shareUsers');

        $shareDepartments = [];
        foreach ($shareDepartmentIds as $shareDepartmentId) {
            $shareDepartments[] = Plugin::getInstance()
                ->getDepartments()
                ->getDepartmentById($shareDepartmentId);
        }

        if ($shareUserIds === '') {
            $shareUserIds = [];
        }

        $contact->name = $request->getBodyParam('name');
        $contact->mobile = $request->getBodyParam('mobile');
        $contact->position = $request->getBodyParam('position');
        $contact->followerId = $followerId;
        $contact->stateCode = $request->getBodyParam('stateCode');
        $contact->companyName = $request->getBodyParam('companyName');
        $contact->address = $request->getBodyParam('address');
        $contact->remark = $request->getBodyParam('remark');
        $contact->labels = $labels;
        $contact->shareDepartments = $shareDepartments;
        $contact->shareUsers = $shareUserIds ? User::find()->id($shareUserIds)->all() : [];

        if (!$contacts->saveContact($contact)) {
            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t save contact.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'contact' => $contact,
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Contact saved.'));

        return $this->redirectToPostedUrl();
    }

    /**
     * @return Response
     */
    public function actionDeleteContact(): Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $contactId = $request->getRequiredBodyParam('contactId');

        $contact = Contact::find()->id($contactId)->one();
        if (!$contact) {
            throw new NotFoundHttpException('Contact not found');
        }

        if (!Craft::$app->getElements()->deleteElement($contact)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson(['success' => false]);
            }

            Craft::$app->getSession()->setError(Craft::t('dingtalk', 'Couldn’t delete contact.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'contact' => $contact
            ]);

            return null;
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson(['success' => true]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('dingtalk', 'Contact deleted.'));

        return $this->redirectToPostedUrl($contact);
    }
}