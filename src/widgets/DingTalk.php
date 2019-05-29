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
 * Class DingTalk
 *
 * @package panlatent\craft\dingtalk\widgets
 * @author Panlatent <panlatent@gmail.com>
 */
class DingTalk extends Widget
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'DingTalk');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return Craft::t('dingtalk', 'DingTalk');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/widgets/DingTalk', [

        ]);
    }
}