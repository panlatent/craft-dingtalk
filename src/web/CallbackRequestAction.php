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
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\JsonParser;
use yii\web\Response;

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

    /**
     * @var string|null
     */
    public $corpId;

    /**
     * @var string|null
     */
    public $encodingAesKey;

    /**
     * @var string|null
     */
    public $token;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        $this->controller->requirePostRequest();

        $request = Craft::$app->getRequest();

        if (!isset($request->parsers['application/json'])) {
            $request->setBodyParams((new JsonParser())->parse(file_get_contents('php://input'), ''));
        }

        foreach (Plugin::$dingtalk->getCorporations()->getAllCorporations() as $corporation) {
            if ($corporation->getCallbackSettings()->getUrl() == $request->getHostInfo() . '/' . $request->getPathInfo()) {
                $this->corpId = $corporation->getCorpId();
                $this->encodingAesKey = $corporation->getCallbackSettings()->getAesKey();
                $this->token = $corporation->getCallbackSettings()->getToken();
                break;
            }
        }

        if ($this->corpId === null || $this->encodingAesKey === null || $this->token === null) {
            throw new InvalidConfigException();
        }

        $this->requireSignature();

        return parent::beforeRun();
    }

    /**
     * @return Response
     */
    public function run()
    {
        $callbacks = Plugin::$dingtalk->getCallbacks();

        $encrypt = Craft::$app->getRequest()->getRequiredBodyParam('encrypt');

        $data = $this->_decrypt($encrypt);
        if ($data['EventType'] === 'check_url') {
            $encrypted = $this->_encrypt('success');
            $timestamp =  Craft::$app->getRequest()->getQueryParam('timestamp', round(microtime(true) * 1000)); //  ;
            $nonce = Craft::$app->getRequest()->getQueryParam('nonce', StringHelper::randomString(8)); //;

            return $this->controller->asJson([
                'timeStamp' => $timestamp,
                'msg_signature' => $this->_signature($encrypted, $timestamp, $nonce),
                'encrypt' => $encrypted,
                'nonce' => $nonce,
            ]);
        }

        $name = ArrayHelper::remove($data, 'EventType');
        $corpId = ArrayHelper::remove($data, 'CorpId');
        $timestamp = ArrayHelper::remove($data, 'TimeStamp');

        $corporation = Plugin::$dingtalk->getCorporations()->getCorporationByCorpId($corpId);

        $request = $callbacks->createRequest([
            'corporationId' => $corporation->id,
            'name' => $name,
            'data' => $data,
            'postDate' => new DateTime($timestamp / 1000, new DateTimeZone('Asia/Shanghai')),
        ]);

        $callbacks->saveRequest($request);

        return $this->controller->asJson([]);
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

        if ($signature !== $this->_signature($encrypt, $timestamp, $nonce)) {
            throw new BadRequestHttpException('Invalid request signature');
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * @param string $encrypt
     * @param string $timestamp
     * @param string $nonce
     * @return string
     */
    private function _signature(string $encrypt, string $timestamp, string $nonce)
    {
        $data = [
            $encrypt,
            $this->token,
            $timestamp,
            $nonce,
        ];
        sort($data);

        return sha1(implode($data));
    }

    /**
     * @param string $content
     * @return string
     */
    private function _encrypt(string $content)
    {
        $aesKey = base64_decode($this->encodingAesKey . '=');
        $iv = substr($aesKey, 0, 16);

        $msg = StringHelper::randomString(16) . pack('N', strlen($content)) . $content . $this->corpId;

        $padLength = 32 - (strlen($msg) % 32);
        $padChr = chr($padLength);
        $padString = '';
        for ($index = 0; $index < $padLength; $index++) {
            $padString .= $padChr;
        }

        $encrypted = openssl_encrypt($msg . $padString, 'AES-256-CBC', $aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        return base64_encode($encrypted);
    }

    /**
     * @param string $encrypt
     * @return mixed|null
     */
    private function _decrypt(string $encrypt)
    {
        $aesKey = base64_decode($this->encodingAesKey . '=');
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