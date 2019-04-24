<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use craft\base\Model;
use panlatent\craft\dingtalk\elements\Contact;

/**
 * Class DepartmentCriteria
 *
 * @package panlatent\craft\dingtalk\models
 * @author Panlatent <panlatent@gmail.com>
 */
class DepartmentCriteria extends Model
{
    /**
     * @var int[]|int|null
     */
    public $corporationId;

    /**
     * @var int[]|int|null
     */
    public $dingDepartmentId;

    /**
     * @var string[]|string|null
     */
    public $name;

    /**
     * @var int[]|int|null
     */
    public $parentId;

    /**
     * @var bool|null
     */
    public $root;

    /**
     * @var Contact|int|null
     */
    public $shareContactOf;

    /**
     * @var bool|null
     */
    public $archived;

    /**
     * @var string|null
     */
    public $order;

    /**
     * @var int|null
     */
    public $offset;

    /**
     * @var int|null
     */
    public $limit;
}