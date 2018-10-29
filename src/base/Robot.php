<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\base;

use craft\base\SavableComponent;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use panlatent\craft\dingtalk\records\Robot as RobotRecord;
use yii\web\Response;

abstract class Robot extends SavableComponent implements RobotInterface
{
    use RobotTrait;

    public static function canHandleRequest(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
            [['handle'], UniqueValidator::class, 'targetClass' => RobotRecord::class],
            [['handle', 'name'], 'string', 'max' => 255],
            [['name', 'handle'], 'required'],
            [
                ['handle'],
                HandleValidator::class,
                'reservedWords' => [
                    'id',
                    'dateCreated',
                    'dateUpdated',
                    'uid',
                    'title'
                ]
            ],
        ];
    }

    public function handle(Response $response)
    {

    }
}