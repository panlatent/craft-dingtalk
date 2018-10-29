<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;

abstract class Message extends SavableComponent implements MessageInterface
{
    use MessageTrait;

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitle(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasAts(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->content;
    }
}