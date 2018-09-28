<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

return [
    'components' => [
        'departments' => [
            'class' => \panlatent\craft\dingtalk\services\Departments::class,
        ],
        'users' => [
            'class' => \panlatent\craft\dingtalk\services\Users::class,
        ],
    ]
];