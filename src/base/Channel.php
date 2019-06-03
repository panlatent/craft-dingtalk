<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;
use craft\base\SavableComponentInterface;

/**
 * Class Channel
 *
 * @package panlatent\craft\dingtalk\base
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class Channel extends SavableComponent implements SavableComponentInterface
{
    // Traits
    // =========================================================================

    use ChannelTrait;


}