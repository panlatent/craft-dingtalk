<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\processes;

use Craft;
use panlatent\craft\dingtalk\base\Process;

class BasicProcess extends Process
{
    public static function displayName(): string
    {
        return Craft::t('dingtalk', 'Basic Process');
    }
}