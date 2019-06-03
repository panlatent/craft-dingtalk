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
 * Class ActionCard
 *
 * @package panlatent\craft\dingtalk\messages
 * @property-read bool $isSingle
 * @property  string $singleTitle
 * @property string $singleUrl
 * @property array $buttons
 * @author Panlatent <panlatent@gmail.com>
 */
class ActionCard extends Message
{
    /**
     * @var bool
     */
    public $hideAvatar = false;

    /**
     * @var int 0 or 1
     */
    public $btnOrientation = 0;

    /**
     * @var bool
     */
    private $_isSingle = true;

    /**
     * @var string|null
     */
    private $_singleTitle;

    /**
     * @var string|null
     */
    private $_singleUrl;

    /**
     * @var array
     */
    private $_buttons = [];

    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Action Card');
    }

    /**
     * @return array
     */
    public function getRequestPayload()
    {
        $actionCard = [
            'title' => $this->title,
            'text' => $this->content,
            'hideAvatar' => $this->hideAvatar ? 1 : 0,
            'btnOrientation' => $this->btnOrientation,
        ];

        if ($this->getIsSingle()) {
            $actionCard['singleTitle'] = $this->_singleTitle;
            $actionCard['singleURL'] = $this->_singleUrl;
        } else {
            $actionCard['btns'] = array_map(function($button) {
                return [
                    'title' => $button['title'] ?? '',
                    'actionURL' => $button['actionUrl'] ?? '',
                ];
            }, $this->_buttons);
        }

        return [
            'msgtype' => 'actionCard',
            'actionCard' => $actionCard,
        ];
    }

    /**
     * @return bool
     */
    public function getIsSingle(): bool
    {
        return $this->_isSingle;
    }

    /**
     * @return string|null
     */
    public function getSingleTitle(): string
    {
        return $this->_singleTitle;
    }

    /**
     * @param null|string $singleTitle
     */
    public function setSingleTitle(string $singleTitle)
    {
        $this->_isSingle = true;
        $this->_singleTitle = $singleTitle;
    }


    /**
     * @return string|null
     */
    public function getSingleUrl(): string
    {
        return $this->_singleUrl;
    }

    /**
     * @param null|string $singleUrl
     */
    public function setSingleUrl(string $singleUrl)
    {
        $this->_isSingle = true;
        $this->_singleUrl = $singleUrl;
    }

    /**
     * @return array
     */
    public function getButtons(): array
    {
        return $this->_buttons;
    }

    /**
     * Set buttons
     *
     * Items:
     * [
     *     "title": "内容不错",
     *     "actionUrl": "https://www.dingtalk.com/"
     * ]
     *
     * @param array|null $buttons
     * @return $this
     */
    public function setButtons(array $buttons = null)
    {
        $this->_isSingle = false;
        if ($buttons === null) {
            $this->_buttons = [];
        } else {
            foreach ($buttons as $button) {
                $this->_buttons[] = $button;
            }
        }



        return $this;
    }

    public function getSettingsHtml()
    {
        return Craft::$app->view->renderTemplate('dingtalk/_components/messages/ActionCard', [
            'message' => $this,
        ]);
    }
}