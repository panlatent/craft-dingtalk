<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

return [
    'dingtalk/<departmentId:\d+>/users' => 'dingtalk/users',
    'dingtalk/robots' => 'dingtalk/robots',
    'dingtalk/robots/new' => 'dingtalk/robots/edit-robot',
    'dingtalk/robots/<robotId:\d+>' => 'dingtalk/robots/edit-robot',
];