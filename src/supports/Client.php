<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\supports;

use EasyDingTalk\Application;

/**
 * Class DingTalkClient
 *
 * @package panlatent\craft\dingtalk\supports
 * @property-read \panlatent\craft\dingtalk\supports\Callback\Client $callback
 * @author Panlatent <panlatent@gmail.com>
 */
class Client extends Application
{
    /**
     * @inheritdoc
     */
    public function __construct(array $config)
    {
        $this->providers[] = Callback\ServiceProvider::class;

        parent::__construct($config);
    }
}