<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk;

use Craft;
use craft\errors\DeprecationException;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Dashboard;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\Contact;
use panlatent\craft\dingtalk\elements\Employee;
use panlatent\craft\dingtalk\fields\Contacts;
use panlatent\craft\dingtalk\fields\Employees;
use panlatent\craft\dingtalk\models\Settings;
use panlatent\craft\dingtalk\plugin\Routes;
use panlatent\craft\dingtalk\plugin\Services;
use panlatent\craft\dingtalk\user\Permissions;
use panlatent\craft\dingtalk\utilities\RobotMessages;
use panlatent\craft\dingtalk\utilities\Sync;
use panlatent\craft\dingtalk\web\twig\CraftVariableBehavior;
use panlatent\craft\dingtalk\widgets\Blackboard;
use panlatent\craft\dingtalk\widgets\DingTalk as DingTalkWidget;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package panlatent\craft\dingtalk
 * @method Settings getSettings()
 * @property-read Settings $settings
 * @author Panlatent <panlatent@gmail.com>
 */
class Plugin extends \craft\base\Plugin
{
    // Traits
    // =========================================================================

    use Routes, Services;

    // Static Methods
    // =========================================================================

    /**
     * @deprecated
     * @see $dingtalk
     */
    public static function getInstance()
    {
        throw new DeprecationException();
    }

    // Properties
    // =========================================================================

    /**
     * @var static|null
     */
    public static $dingtalk;

    /**
     * @inheritdoc
     */
    public $schemaVersion = '1.0.0-alpha';

    /**
     * @inheritdoc
     */
    public $hasCpSettings = true;

    /**
     * @inheritdoc
     */
    public $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public $t9nCategory = 'dingtalk';

    // Public Methods
    // =========================================================================

    /**
     * Init.
     */
    public function init()
    {
        parent::init();
        self::$dingtalk = $this;
        Craft::setAlias('@dingtalk', $this->getBasePath());
        $this->name = Craft::t('dingtalk', 'DingTalk');

        $this->_registerCpRoutes();
        $this->_registerElementTypes();
        $this->_registerFieldTypes();
        $this->_registerPermissions();
        $this->_registerSiteRoutes();
        $this->_registerUtilities();
        $this->_registerVariables();
        $this->_registerWidgets();
        $this->_setComponents();
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): array
    {
        $ret = parent::getCpNavItem();

        if (!empty($this->getSettings()->cpSectionName)) {
            $ret['label'] = $this->getSettings()->cpSectionName;
        }

        $ret['subnav']['dashboard'] = [
            'label' => Craft::t('dingtalk', 'Dashboard'),
            'url' => 'dingtalk/dashboard'
        ];

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_CORPORATIONS)) {
            $ret['subnav']['corporations'] = [
                'label' => Craft::t('dingtalk', 'Corporations'),
                'url' => 'dingtalk/corporations'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_EMPLOYEES)) {
            $ret['subnav']['employees'] = [
                'label' => Craft::t('dingtalk', 'Employees'),
                'url' => 'dingtalk/employees'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_CONTACTS)) {
            $ret['subnav']['contacts'] = [
                'label' => Craft::t('dingtalk', 'Contacts'),
                'url' => 'dingtalk/contacts'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_APPROVALS)) {
            $ret['subnav']['approvals'] = [
                'label' => Craft::t('dingtalk', 'Approvals'),
                'url' => 'dingtalk/approvals'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_ATTENDANCES)) {
            $ret['subnav']['attendances'] = [
                'label' => Craft::t('dingtalk', 'Attendances'),
                'url' => 'dingtalk/attendances'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_ROBOTS)) {
            $ret['subnav']['robots'] = [
                'label' => Craft::t('dingtalk', 'Robots'),
                'url' => 'dingtalk/robots'
            ];
        }

        if (Craft::$app->getUser()->checkPermission(Permissions::MANAGE_SETTINGS)) {
            $ret['subnav']['settings'] = [
                'label' => Craft::t('dingtalk', 'Settings'),
                'url' => 'dingtalk/settings'
            ];
        }

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse()
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('dingtalk/settings'));
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    // Private Methods
    // =========================================================================

    /**
     * Register plugin element types.
     */
    private function _registerElementTypes()
    {
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Approval::class;
            $event->types[] = Contact::class;
            $event->types[] = Employee::class;
        });
    }

    /**
     * Register plugin fields.
     */
    private function _registerFieldTypes()
    {
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Contacts::class;
            $event->types[] = Employees::class;
        });
    }

    /**
     * Register plugin widgets.
     */
    private function _registerWidgets()
    {
        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = DingTalkWidget::class;
            $event->types[] = Blackboard::class;
        });
    }

    /**
     * Register plugin utility types.
     */
    private function _registerUtilities()
    {
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = Sync::class;
            $event->types[] = RobotMessages::class;
        });
    }

    /**
     * Register plugin user permissions.
     */
    private function _registerPermissions()
    {
        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function (RegisterUserPermissionsEvent $event) {
            $event->permissions[Craft::t('dingtalk', 'DingTalk')] = [
                Permissions::MANAGE_CONTACTS => [
                    'label' => Craft::t('dingtalk', 'View Contacts'),
                    'nested' => [
                        'syncDingTalkContacts' => [
                            'label' => Craft::t('dingtalk', 'Sync Contacts'),
                        ],
                    ],
                ],
                Permissions::MANAGE_APPROVALS => [
                    'label' => Craft::t('dingtalk', 'View Approvals'),
                    'nested' => [
                    ],
                ],
                'viewDingTalkRobots' => [
                    'label' => Craft::t('dingtalk', 'View Robots'),
                    'nested' => [
                        'manageDingTalkRobots' => [
                            'label' => Craft::t('dingtalk', 'Manage Robots'),
                        ],
                        'sendDingTalkRobotMessages' => [
                            'label' => Craft::t('dingtalk', 'Send Robot Messages'),
                        ],
                    ],
                ],

            ];
        });
    }

    /**
     * Register the plugin template variable.
     */
    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->attachBehavior('dingtalk', CraftVariableBehavior::class);
        });
    }
}