<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use panlatent\craft\dingtalk\Plugin;
use yii\base\Component;

class Callbacks extends Component
{
    public function getCallBack()
    {
        return Plugin::$plugin->getApi()->getCallback();
    }

    public function saveCallBack()
    {

    }

    public function deleteCallBack()
    {

    }
}