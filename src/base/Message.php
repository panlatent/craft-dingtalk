<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;

/**
 * Class Message
 *
 * @package panlatent\craft\dingtalk\base
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Message extends SavableComponent implements MessageInterface
{
    // Traits
    // =========================================================================

    use MessageTrait;

    // Static Methods
    // =========================================================================

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

    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->content;
    }
}