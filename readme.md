<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About This Project
项目采用前后端分离编写，视图页面经编译后已嵌入后端，配置后可直接使用，如需更改视图需更改[前端代码](https://github.com/poppyaddi/vue-games.git)，然后经编译后尚可使用

## Introduction
   该项目为IOS内购凭证交易平台，IOS端购买虚拟商品后将凭证拦截，凭证经动态加密后发送至服务器，服务器经动态解密后在苹果内购校验平台对凭证的有效性和时间进行验证，验证合格后存入数据库，否则按异常数据处理。
   
   项目登陆账户分为两种类型，主账户与子账户。主账户仅登陆后台，子账户仅登陆IOS端，每个主账户可拥有数个子账户。

## Project Overview
- 账户管理
    - 子账户管理
- 游戏管理
    - 游戏列表
    - 面值管理
    - 跳过面值
- 库存管理
    - 库存列表
    - 出库列表
    - 入库列表
    - 数据统计
    - 库存分配
    - 凭证迁移
    - 凭证管理
- 资金管理
    - 充值提现
    - 支付管理
    - 支付列表
- 设备管理
    - 设备授权
- 交易管理
    - 出售列表
    - 求购列表
    - 我的出售
    - 我的购买
    - 我的求购(预供货)
    - 我的求购(即时供货)
    - 我的预供
- 日志管理
    - 凭证日志
    - 登陆日志
    - 提现日志
    - 交易日志
- 系统管理
    - 用户管理
    - 用户详情
    - 角色管理
    - 菜单管理
    - 权限管理
    - 配置管理
    - 公告管理

## 配置安装
1. 下载项目
2. 终端执行`composer install`
3. 终端执行`php artisan key:generate`
4. 终端执行`php artisan jwt:secret`


