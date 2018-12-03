<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

trait ProcessTrait
{
    /**
     * @var int|null
     */
    public $fieldLayoutId;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $handle;

    /**
     * @var string|null
     */
    public $code;

    /**
     * @var int|null
     */
    public $sortOrder;
}