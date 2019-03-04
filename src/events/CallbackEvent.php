<?php
/**
 * DingTalk plugin for Craft 3
 *
 * @link      https://panlatent.com/
 * @copyright Copyright (c) 2018 panlatent@gmail.com
 */

namespace panlatent\craft\dingtalk\events;

use yii\base\Event;

class CallbackEvent extends Event
{


//chat_add_member ：群会话添加人员
//chat_remove_member ：群会话删除人员
//chat_quit：群会话用户主动退群
//chat_update_owner ：群会话更换群主
//chat_update_title ：群会话更换群名称
//chat_disband ：群会话解散群
//chat_disband_microapp ：绑定了微应用的群会话，在解散时回调
}