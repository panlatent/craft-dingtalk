<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\web;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\web\Controller;
use DateTime;
use DateTimeZone;
use panlatent\craft\dingtalk\Plugin;
use yii\base\Action;
use yii\web\BadRequestHttpException;
use yii\web\JsonParser;

/**
 * Class CallbackRequestAction
 *
 * @package panlatent\craft\dingtalk\web
 * @property Controller $controller
 * @author Panlatent <panlatent@gmail.com>
 */
class CallbackRequestAction extends Action
{
    // Properties
    // =========================================================================

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        if (!isset($request->parsers['application/json'])) {
            Craft::$app->getRequest()->setBodyParams((new JsonParser())->parse(file_get_contents('php://input'), ''));
        }

        return parent::beforeRun();
    }

    /**
     * @return array
     */
    public function run()
    {
        $this->controller->requirePostRequest();
        $this->requireSignature();

        $callbacks = Plugin::$dingtalk->getCallbacks();

        $encrypt = Craft::$app->getRequest()->getRequiredBodyParam('encrypt');
        $encodingAesKey = Plugin::$dingtalk->getSettings()->callbackEncodingAesKey;

        $data = $this->_decrypt($encodingAesKey, $encrypt);

        if ($data['EventType'] === 'check_url') {
            $encrypt =$this->_encrypt($encodingAesKey, 'success');
            $timestamp = time();
            $nonce = StringHelper::randomString();

            return [
                "msg_signature" => $this->_signature([$encrypt,$timestamp,$nonce]),
                "timeStamp" => $timestamp,
                "nonce" => $nonce,
                "encrypt" => $encrypt,
            ];
        }

        $name = ArrayHelper::remove($data, 'EventType');
        $corpId = ArrayHelper::remove($data, 'CorpId');
        $timestamp = ArrayHelper::remove($data, 'TimeStamp');

        $corporation = Plugin::$dingtalk->getCorporations()->getCorporationByCorpId($corpId);

        $request = $callbacks->createRequest([
            'corporationId' => $corporation->id,
            'name' => $name,
            'data' => $data,
            'postDate' => new DateTime($timestamp/1000, new DateTimeZone('Asia/Shanghai')),
        ]);

        $callbacks->saveRequest($request);

        return [];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Require signature.
     */
    protected function requireSignature()
    {
        $request = Craft::$app->getRequest();

        $encrypt = $request->getRequiredBodyParam('encrypt');
        $signature = $request->getRequiredQueryParam('signature');
        $nonce = $request->getRequiredQueryParam('nonce');
        $timestamp = $request->getRequiredQueryParam('timestamp');

        $token = Plugin::$dingtalk->getSettings()->callbackToken;

        $validateData = [$encrypt, $token, $timestamp, $nonce];
        sort($validateData);

        if ($signature !== sha1(implode('', $validateData))) {
            throw new BadRequestHttpException();
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * @param array $data
     * @return string
     */
    private function _signature(array $data)
    {
        $data[] = Plugin::$dingtalk->getSettings()->callbackToken;
        sort($data);

        return sha1(implode('', $data));
    }

    /**
     * @param string $encodingAesKey
     * @param string $content
     * @return string
     */
    private function _encrypt(string $encodingAesKey, string $content)
    {
        $aesKey = base64_decode($encodingAesKey . '=');
        $iv = substr($aesKey, 0, 16);

        $msg = StringHelper::randomString(16) . pack('N', strlen($content)) . $content . Plugin::$dingtalk->getSettings()->getCorpId();

        $encrypt = openssl_encrypt($msg, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        $padLength = 32 - (strlen($encrypt) % 32);
        if ($padLength == 0) {
            $padLength = 32;
        }

        $padChr = chr($padLength);
        $padString = '';
        for ($index = 0; $index < $padLength; $index++) {
            $padString .= $padChr;
        }

        return base64_encode($encrypt . $padString);
    }

    /**
     * @param string $encodingAesKey
     * @param string $encrypt
     * @return mixed|null
     */
    private function _decrypt(string $encodingAesKey, string $encrypt)
    {
        $aesKey = base64_decode($encodingAesKey . '=');
        $iv = substr($aesKey, 0, 16);

        $msg = openssl_decrypt(base64_decode($encrypt), 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        $pad = ord(substr($msg, -1));
        if ($pad < 1 || $pad > 32) {
            $pad = 0;
        }

        $msg = substr($msg, 0, (strlen($msg) - $pad));

        if (strlen($msg) < 16) {
            return null;
        }

        $contentLength = unpack('N', substr($msg, 16, 4));
        $content = substr($msg, 20, $contentLength[1]);

        return Json::decodeIfJson($content);
    }
}