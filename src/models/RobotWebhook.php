<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

/**
 * Class RobotWebhook
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class RobotWebhook extends Model
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $url;

    /**
     * @var int|null
     */
    public $rateLimit;

    /**
     * @var int|null
     */
    public $rateWindow;

    /**
     * @var bool|null
     */
    public $enabled;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'required'],
            [['name', 'url'], 'string'],
            [['url'], 'url'],
        ];
    }
}