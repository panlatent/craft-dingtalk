<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Dashboard;
use craft\services\Elements;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use panlatent\craft\dingtalk\base\ServiceTrait;
use panlatent\craft\dingtalk\elements\Approval;
use panlatent\craft\dingtalk\elements\User;
use panlatent\craft\dingtalk\models\Settings;
use panlatent\craft\dingtalk\services\Api;
use panlatent\craft\dingtalk\services\Approvals;
use panlatent\craft\dingtalk\services\Departments;
use panlatent\craft\dingtalk\services\Messages;
use panlatent\craft\dingtalk\services\Processes;
use panlatent\craft\dingtalk\services\Robots;
use panlatent\craft\dingtalk\services\SmartWorks;
use panlatent\craft\dingtalk\services\Users;
use panlatent\craft\dingtalk\utilities\RobotMessages;
use panlatent\craft\dingtalk\utilities\SyncContacts;
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
    use ServiceTrait;

    // Static Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '0.1.10-alpha.1';

    /**
     * @var string
     */
    public $t9nCategory = 'dingtalk';

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        $components = [
            'api' => Api::class,
            'approvals' => Approvals::class,
            'departments' => Departments::class,
            'messages' => Messages::class,
            'processes' => Processes::class,
            'robots' => Robots::class,
            'smartWorks' => SmartWorks::class,
            'users' => Users::class,
        ];

        if (isset($config['components'])) {
            $components = array_merge($components, $config['components']);
        }

        $config['components'] = $components;

        parent::__construct($id, $parent, $config);
    }

    /**
     * Init.
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::setAlias('@dingtalk', $this->getBasePath());

        $this->name = Craft::t('dingtalk', 'DingTalk');

        // Register plugin's events.
        Event::on(Elements::class, Elements::EVENT_REGISTER_ELEMENT_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = User::class;
            $event->types[] = Approval::class;
        });

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = DingTalkWidget::class;
        });

        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event) {
            $event->types[] = SyncContacts::class;
            $event->types[] = RobotMessages::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'dingtalk/processes' => 'dingtalk/processes',
                'dingtalk/processes/new' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>' => 'dingtalk/processes/edit-process',
                'dingtalk/processes/<processId:\d+>/sync' => 'dingtalk/processes/edit-process-sync',
                'dingtalk/robots' => 'dingtalk/robots',
                'dingtalk/robots/new' => 'dingtalk/robots/edit-robot',
                'dingtalk/robots/<robotId:\d+>' => 'dingtalk/robots/edit-robot',
            ]);
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $event) {
            $event->roots['settings/plugins/dingtalk']  = $this->getBasePath() . '/templates/settings';
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function (RegisterCpNavItemsEvent $event) {
            $settings = $this->getSettings();

            if ($settings->showContactsOnCpSection && Craft::$app->user->checkPermission('viewDingTalkContacts')) {
                $event->navItems[] = [
                    'label' => Craft::t('dingtalk', 'Contacts'),
                    'url' => 'dingtalk/users',
                    'icon' => '@dingtalk/icons/departments.svg',
                ];
            }

            if ($settings->showApprovalsOnCpSection && Craft::$app->user->checkPermission('viewDingTalkApprovals')) {
                $event->navItems[] = [
                    'label' => Craft::t('dingtalk', 'Approvals'),
                    'url' => 'dingtalk/approvals',
                    'icon' => '@dingtalk/icons/approvals.svg',
                ];
            }

        });


        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function (RegisterUserPermissionsEvent $event) {
            $event->permissions[Craft::t('dingtalk', 'DingTalk')] = [
                'viewDingTalkContacts' => [
                    'label' => Craft::t('dingtalk', 'View Contacts'),
                    'nested' => [
                        'syncDingTalkContacts' => [
                            'label' => Craft::t('dingtalk', 'Sync Contacts'),
                        ],
                    ],
                ],
                'viewDingTalkApprovals' => [
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

        Craft::info(
            Craft::t(
                'dingtalk',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @return string
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate('dingtalk/_settings', [
            'settings' => $this->getSettings(),
        ]);
    }
}