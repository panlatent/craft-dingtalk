# Craft Dingtalk Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added
- 现在支持多个企业管理
- 添加消息模板和组，通过向已配置模板（ Twig模版 ）注入变量来生成消息内容
- 支持设置事件回调
- 支持发送工作通知
- 优化通讯录和智能人事字段
- 支持钉钉登陆（扫码、或者重定向）

### Changed
- 插件图标颜色从蓝色改为黑色
- 更新插件设置方式

### Fixed
- 修复了通讯录不能正确筛选已离职状态的人员

## [0.1.10] - 2018-12-09
### Added
- 支持创建审批流并同步审批数据作为审批（ 元素 ）

## Changed
- 支持将密钥保存在环境变量而不是存储在插件设置中
- 优化部门与通讯录同步
- 支持一个钉钉机器人可将消息推送多个群（Webhook）

## [0.1.8] or early - 2018-09-04 - 2018-11-21

### Added
- 支持将钉钉用户同步至本地并作为元素显示
- 支持设计用户字段布局，同步用户属性和智能办公字段
- 支持添加钉钉机器人
- 提供了发送钉钉消息和机器人消息所用的消息类型
