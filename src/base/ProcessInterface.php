<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponentInterface;
use panlatent\craft\dingtalk\models\Corporation;

/**
 * Interface ProcessInterface
 *
 * @package panlatent\craft\dingtalk\base
 * @author Panlatent <panlatent@gmail.com>
 */
interface ProcessInterface extends SavableComponentInterface
{
    public function getHandle();

    /**
     * @return Corporation
     */
    public function getCorporation(): Corporation;
}