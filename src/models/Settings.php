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
     * @var bool|null
     */
    public $showContactsOnCpSection;

    /**
     * @var bool|null
     */
    public $showApprovalsOnCpSection;

    public function attributes()
    {
        $attributes = parent::attributes();
        $attributes[] = 'corpId';
        $attributes[] = 'corpSecret';

        return $attributes;
    }

    /**
     * @return null|string
     */
    public function getCorpId(): string
    {
        return getenv('DINGTALK_CORP_ID');
    }

    /**
     * @param null|string $corpId
     */
    public function setCorpId(string $corpId)
    {
        Craft::$app->getConfig()->setDotEnvVar('DINGTALK_CORP_ID', $corpId);
    }

    /**
     * @return null|string
     */
    public function getCorpSecret(): string
    {
        return getenv('DINGTALK_CORP_SECRET');
    }

    /**
     * @param null|string $corpSecret
     */
    public function setCorpSecret(string $corpSecret)
    {
        Craft::$app->getConfig()->setDotEnvVar('DINGTALK_CORP_SECRET', $corpSecret);
    }
}