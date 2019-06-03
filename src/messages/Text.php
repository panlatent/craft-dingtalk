<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\messages;

use Craft;
use panlatent\craft\dingtalk\base\Message;

/**
 * Class Text
 *
 * @package panlatent\craft\dingtalk\messages
 * @author Panlatent <panlatent@gmail.com>
 */
class Text extends Message
{
    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Text');
    }

    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $allowTemplateSyntax = false;

    // Public Methods
    // =========================================================================

    /**
     * @return array
     */
    public function getRequestPayload()
    {
        $content = $this->content;
        if ($this->allowTemplateSyntax) {
            $content = Craft::$app->view->renderString($content);
        }

        return [
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
            'at' => [
                'atMobiles' => $this->atMobiles,
                'isAtAll' => $this->isAtAll,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/Text', [
            'message' => $this,
        ]);
    }
}