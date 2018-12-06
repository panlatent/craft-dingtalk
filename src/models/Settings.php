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
 * Class Settings
 *
 * @package panlatent\craft\dingtalk\models
 * @property string $corpId
 * @property string $corpSecret
 * @author Panlatent <panlatent@gmail.com>
 */
class Settings extends Model
{
    /**
     * @var bool|null Use .env file save corpId and corpSecret
     */
    public $useDotEnv;

    /**
     * @var bool|null
     */
    public $showContactsOnCpSection;

    /**
     * @var bool|null
     */
    public $showApprovalsOnCpSection;

    /**
     * @var string|null
     */
    private $_corpId;

    /**
     * @var string|null
     */
    private $_corpSecret;

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'corpId';
        $attributes[] = 'corpSecret';

        return $attributes;
    }

    public function fields()
    {
        $fields = parent::fields();

        if ($this->useDotEnv) {
            unset($fields['corpId'], $fields['corpSecret']);
        }

        return $fields;
    }

    /**
     * @return string|null
     */
    public function getCorpId()
    {
        if ($this->_corpId !== null) {
            return $this->_corpId;
        }

        return$this->_corpId = $this->useDotEnv ? getenv('DINGTALK_CORP_ID') : $this->_corpId;
    }

    /**
     * @param string|null $corpId
     */
    public function setCorpId(string $corpId)
    {
        $this->_corpId = $corpId;
    }

    /**
     * @return string|null
     */
    public function getCorpSecret()
    {
        if ($this->_corpSecret !== null) {
            return $this->_corpSecret;
        }

        return $this->_corpSecret = $this->useDotEnv ? getenv('DINGTALK_CORP_SECRET') : $this->_corpSecret;
    }

    /**
     * @param string|null $corpSecret
     */
    public function setCorpSecret(string $corpSecret)
    {
        $this->_corpSecret = $corpSecret;
    }
}