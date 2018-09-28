<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\log;

use GuzzleHttp\Client;
use Yii;
use yii\helpers\VarDumper;
use yii\log\Logger;
use yii\log\Target;
use yii\web\HttpException;

class DingTalkTarget extends Target
{
    /**
     * @var string|null
     */
    public $webHook;
    /**
     * @var array
     */
    public $at = [];
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var string|null
     */
    public $content;
    /**
     * @var array
     */
    public $templateOptions = [];
    /**
     * @var bool
     */
    public $isAtAll = false;
    /**
     * @var Client
     */
    private $_client;

    public function export()
    {
        $this->messages = static::filterMessages($this->messages, $this->getLevels(), $this->categories, $this->except);
        $content = '';

        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                // exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Throwable || $text instanceof \Exception) {
                    if ($text instanceof HttpException && $text->statusCode == 404) {
                        $text = (string) $text->getMessage() . ' ' . Yii::$app->request->getAbsoluteUrl();
                    } else {
                        $text = (string) $text->getMessage();
                    }
                } else {
                    $text = VarDumper::export($text);
                }
            }

            $level = $level = Logger::getLevelName($level);
            $options = $this->templateOptions + [
                '{title}' => $this->title,
                '{time}' => $this->getTime($timestamp),
                '{category}' => $category,
                '{message}' => $text,
                '{level}' => strtoupper($level),
                '{info}' => $this->getMessagePrefix($message)
            ];

            foreach ($options as &$option) {
                $option = strtr($option, $options);
            }

            $content .= strtr($this->content, $this->templateOptions + $options);
        }

        $title = strtr($this->title, $this->templateOptions + [
            '{count}' => count($this->messages),
        ]);

        $this->sendContent($title, $content);
    }

    public function sendContent($title, $content)
    {
        $this->getClient()->post($this->webHook, [
            'json' => [
                'msgtype' => 'markdown',
                'markdown' => [
                    'title' => $title,
                    'text' => $content,
                ],
                'at' => [
                    'atMobiles' => array_values($this->at),
                    'isAtAll' => $this->isAtAll,
                ],
            ],
        ]);

        if (!empty($this->at)) {
            $this->getClient()->post($this->webHook, [
                'json' => [
                    'msgtype' => 'text',
                    'text' => [
                        'content' => '麻烦 ' . implode(',', array_map(function ($name) {
                            return '@' . $name;
                        }, $this->at)) . '同学帮忙跟进一下哦 ~'
                    ],
                    'at' => [
                        'atMobiles' => array_values($this->at),
                        'isAtAll' => $this->isAtAll,
                    ],
                ],
            ]);
        }
    }

    public function getClient()
    {
        if ($this->_client) {
            return $this->_client;
        }

        return $this->_client = new Client();
    }
}