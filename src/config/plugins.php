<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

return [
    'components' => [
        'api' => [
            'class' => \panlatent\craft\dingtalk\services\Api::class
        ],
        'departments' => [
            'class' => \panlatent\craft\dingtalk\services\Departments::class,
        ],
        'messages' => [
            'class' => \panlatent\craft\dingtalk\services\Messages::class,
        ],
        'robots' => [
            'class' => \panlatent\craft\dingtalk\services\Robots::class,
        ],
        'users' => [
            'class' => \panlatent\craft\dingtalk\services\Users::class,
        ],
        'smartWorks' => [
            'class' => \panlatent\craft\dingtalk\services\SmartWorks::class,
        ],
    ]
];