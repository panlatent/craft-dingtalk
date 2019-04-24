<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\validators;

use Craft;
use yii\validators\Validator;

/**
 * Class MobileValidator
 *
 * @package panlatent\craft\dingtalk\validators
 * @author Panlatent <panlatent@gmail.com>
 */
class MobileValidator extends Validator
{
    // Properties
    // =========================================================================

    /**
     * @var bool
     */
    public $trim = true;

    /**
     * @var string|null
     */
    public $stateCodeAttribute;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = trim($model->$attribute);

        if (preg_match('#^\+?(\d+)-(\d+)$#', $value, $match)) {
            if ($this->trim) {
                $model->$attribute = $match[2];
            }

            if (!empty($this->stateCodeAttribute)) {
                $model->{$this->stateCodeAttribute} = $match[1];
            }

            $value = $match[2];
            if ($match[1] != '86') {
                return null;
            }
        }

        if (!preg_match('#^\d{11}$#', $value)) {
            $model->addError($attribute, Craft::t('dingtalk', "Invalid mobile number: {value}", [
                'value' => $value,
            ]));
        }

        return null;
    }
}