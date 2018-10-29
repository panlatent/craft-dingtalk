<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponentInterface;
use yii\web\Response;

interface RobotInterface extends SavableComponentInterface
{
    public static function canHandleRequest(): bool;

    public function handle(Response $response);

    public function send(MessageInterface $message): bool;
}