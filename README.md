DintTalk plugin for Craft 3
====================================
[![Build Status](https://travis-ci.org/panlatent/craft-dingtalk.svg)](https://travis-ci.org/panlatent/craft-dingtalk)
[![Coverage Status](https://coveralls.io/repos/github/panlatent/craft-dingtalk/badge.svg?branch=master)](https://coveralls.io/github/panlatent/craft-dingtalk?branch=master)
[![Latest Stable Version](https://poser.pugx.org/panlatent/craft-dingtalk/v/stable.svg)](https://packagist.org/packages/panlatent/craft-dingtalk)
[![Total Downloads](https://poser.pugx.org/panlatent/craft-dingtalk/downloads.svg)](https://packagist.org/packages/panlatent/craft-dingtalk) 
[![Latest Unstable Version](https://poser.pugx.org/panlatent/craft-dingtalk/v/unstable.svg)](https://packagist.org/packages/panlatent/craft-dingtalk)
[![License](https://poser.pugx.org/panlatent/craft-dingtalk/license.svg)](https://packagist.org/packages/panlatent/craft-dingtalk)
[![Craft CMS](https://img.shields.io/badge/Powered_by-Craft_CMS-orange.svg?style=flat)](https://craftcms.com/)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)

Alibaba DingTalk plugin for Craft 3.

钉钉是一个阿里巴巴推出的智能移动办公平台，该插件能将 [钉钉](https://www.dingtalk.com/) 开放的能力整合至 CraftCSM 中，
并提供再次开发的基础组件。

特性
---------

+ 通讯录
    
   同步钉钉部门与用户至本地（支持离职用户），并将钉钉用户作为元素类（Element） 进行开发。
   
   同步钉钉用户的智能办公信息。
   
+ 钉钉机器人

    支持创建钉钉自定义机器人并关联至多个聊天群
    
    通过实用程序或自定义代码发送不同类型的机器人消息
    
+ 审批

Requirements
------------

This plugin requires Craft CMS 3.0 or later.

Installation
------------

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require panlatent/craft-dingtalk

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for DingTalk.

Configuration
-------------

