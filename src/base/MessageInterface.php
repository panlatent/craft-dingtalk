<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponentInterface;

interface MessageInterface extends SavableComponentInterface
{
    public static function hasContent(): bool;

    public static function hasTitle(): bool;

    public static function hasAts(): bool;

    public function getRequestBody();
}