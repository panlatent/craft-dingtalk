<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\web\twig;

use panlatent\craft\dingtalk\Plugin;
use yii\base\Behavior;

/**
 * Class CraftVariableBehavior
 *
 * @package panlatent\craft\dingtalk\web\twig
 * @author Panlatent <panlatent@gmail.com>
 */
class CraftVariableBehavior extends Behavior
{
    /**
     * @var Plugin
     */
    public $dingtalk;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->dingtalk = Plugin::getInstance();
    }
}