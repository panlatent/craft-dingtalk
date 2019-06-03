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
 * Class App
 *
 * @package panlatent\craft\dingtalk\base
 * @author Panlatent <panlatent@gmail.com>
 */
abstract class App extends SavableComponent implements AppInterface
{
    // Traits
    // =========================================================================

    use AppTrait;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();

        return $rules;
    }
}