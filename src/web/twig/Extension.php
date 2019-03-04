<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\web\twig;

/**
 * Class Extension
 *
 * @package panlatent\craft\dingtalk\web\twig
 * @author Panlatent <panlatent@gmail.com>
 */
class Extension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Craft DingTalk Twig Extension';
    }
}