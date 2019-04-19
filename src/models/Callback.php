<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;

/**
 * Class Callback
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class Callback extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var Corporation|null
     */
    public $corporation;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var array|null
     */
    public $data;

    /**
     * @var int|null
     */
    public $postDate;
}