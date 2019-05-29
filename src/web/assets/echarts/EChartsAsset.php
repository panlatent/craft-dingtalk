<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\web\assets\echarts;

use craft\web\AssetBundle;

/**
 * Class EChartsAsset
 *
 * @package panlatent\craft\dingtalk\web\assets\echarts
 * @author Panlatent <panlatent@gmail.com>
 */
class EChartsAsset extends AssetBundle
{
    /**
     * @var bool
     */
    public $cdn = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->cdn) {
            $this->js[] = '//cdn.bootcss.com/echarts/4.1.0-release/echarts.min.js';
        }
    }
}