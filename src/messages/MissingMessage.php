<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\messages;

use panlatent\craft\dingtalk\base\Message;
use panlatent\craft\dingtalk\errors\MessageException;

class MissingMessage extends Message
{
    public function getRequestBody()
    {
        throw new MessageException('Missing message type');
    }
}