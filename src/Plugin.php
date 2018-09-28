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
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\ArrayHelper;
use craft\services\Dashboard;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use EasyDingTalk\Application as DingTalkClient;
use panlatent\craft\dingtalk\models\Settings;
use panlatent\craft\dingtalk\services\Departments;
use panlatent\craft\dingtalk\services\Users;
use panlatent\craft\dingtalk\widgets\DingTalk as DingTalkWidget;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package panlatent\craft\dingtalk
 * @method Settings getSettings()
 * @property-read Settings $settings
 * @property-read DingTalkClient $client
 * @property-read Departments $departments
 * @property-read Users $users
 * @author Panlatent <panlatent@gmail.com>
 */
class Plugin extends \craft\base\Plugin
{
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
    public $schemaVersion = '0.1.0';

    /**
     * @var string
     */
    public $t9nCategory = 'dingtalk';

    /**
     * @var DingTalkClient|null
     */
    private $_client;

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        $config = ArrayHelper::merge(require __DIR__ . '/config/plugins.php', $config);

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

        Event::on(Dashboard::class, Dashboard::EVENT_REGISTER_WIDGET_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = DingTalkWidget::class;
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, require __DIR__ . '/config/routes.php');
        });

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $event) {
            if ($this->getSettings()->hasDepartmentsCpSection) {
                $event->navItems[] = [
                    'label' => Craft::t('dingtalk', 'Departments'),
                    'url' => 'dingtalk/departments',
                    'icon' => '@dingtalk/icons/departments.svg',
                ];
            }
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

    public function getClient(): DingTalkClient
    {
        if ($this->_client !== null) {
            return $this->_client;
        }

        return new DingTalkClient([
            'corp_id' => $this->getSettings()->corpId,
            'corp_secret' => $this->getSettings()->corpSecret,
        ]);
    }

    /**
     * @return Departments
     */
    public function getDepartments(): Departments
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('departments');
    }

    /**
     * @return Users
     */
    public function getUsers(): Users
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('users');
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