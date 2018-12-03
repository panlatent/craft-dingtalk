<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\services;

use panlatent\craft\dingtalk\models\SmartWork;
use yii\base\Component;

/**
 * Class SmartWorks
 *
 * @package panlatent\craft\dingtalk\services
 * @author Panlatent <panlatent@gmail.com>
 */
class SmartWorks extends Component
{
    public function getAllFields()
    {
        $smartWork = new SmartWork();

        return $smartWork->attributeLabels();
    }
}