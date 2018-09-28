<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;

abstract class Robot extends SavableComponent implements RobotInterface
{
    use RobotTrait;

    public static function canHandleRequest(): bool
    {
        return false;
    }



}