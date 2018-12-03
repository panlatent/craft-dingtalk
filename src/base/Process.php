<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;

abstract class Process extends SavableComponent implements ProcessInterface
{
    use ProcessTrait;

    public function getHandle()
    {
        return $this->handle;
    }

    public function getApprovals()
    {

    }
}