<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\supports\Callback;

use EasyDingTalk\Kernel\BaseClient;

/**
 * Class Client
 *
 * @package panlatent\craft\dingtalk\supports\Callback
 * @author Panlatent <panlatent@gmail.com>
 */
class Client extends BaseClient
{
    /**
     * 获取回调信息
     *
     * @return array|\GuzzleHttp\Psr7\Response
     */
    public function get()
    {
        return $this->httpGet('call_back/get_call_back');
    }

    /**
     * @param array $callBackTag
     * @param string $token
     * @param string $aesKey
     * @param string $url
     * @return array|\GuzzleHttp\Psr7\Response
     */
    public function register(array $callBackTag, string $token, string $aesKey, string $url)
    {
        $params = [
            'call_back_tag' => $callBackTag,
            'token' => $token,
            'aes_key' => $aesKey,
            'url' => $url,
        ];

        return $this->httpPostJson('call_back/register_call_back', $params);
    }

    /**
     * @param array $callBackTag
     * @param string $token
     * @param string $aesKey
     * @param string $url
     * @return array|\GuzzleHttp\Psr7\Response
     */
    public function update(array $callBackTag, string $token, string $aesKey, string $url)
    {
        $params = [
            'call_back_tag' => $callBackTag,
            'token' => $token,
            'aes_key' => $aesKey,
            'url' => $url,
        ];

        return $this->httpPostJson('call_back/update_call_back', $params);
    }
}