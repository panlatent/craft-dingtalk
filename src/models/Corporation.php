<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\models;

use Craft;
use craft\base\Model;

/**
 * Class Corporation
 *
 * @package panlatent\craft\dingtalk\models
 * @property-read bool $isNew
 * @author Panlatent <panlatent@gmail.com>
 */
class Corporation extends Model
{
    /**
     * @var int|null
     */
    public $id;

    /**
     * @var bool|null
     */
    public $primary;

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
    public $corpId;

    /**
     * @var string|null
     */
    public $corpSecret;

    /**
     * @var bool|null
     */
    public $hasUrls;

    /**
     * @var string|null
     */
    public $url;

    /**
     * @return string
     */
    public function __toString()
    {
       return (string)$this->name;
    }

    /**
     * @return bool
     */
    public function getIsNew(): bool
    {
        return !$this->id;
    }

    /**
     * @return string|null
     */
    public function getCorpId()
    {
        return Craft::parseEnv($this->corpId);
    }

    /**
     * @return string|null
     */
    public function getCorpSecret()
    {
        return Craft::parseEnv($this->corpSecret);
    }

    public function getClient()
    {

    }
}