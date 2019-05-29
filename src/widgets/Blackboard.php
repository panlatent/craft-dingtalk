<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\widgets;

use Craft;
use craft\base\Widget;

/**
 * Class Blackboard
 *
 * @package panlatent\craft\dingtalk\widgets
 * @author Panlatent <panlatent@gmail.com>
 */
class Blackboard extends Widget
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Blackboard');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Craft::t('dingtalk', 'Blackboard');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        Craft::$app->getUser();


        return Craft::$app->view->renderTemplate('dingtalk/_components/widgets/Blackboard', [

        ]);
    }
}