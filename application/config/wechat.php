<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config["wechat"] = [
    /*
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'  => true,

    /*
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'  => 'wx9f3b71e6037e062e',         // AppID
    'secret'  => '32f98b33e33e395259e3aea0a0ffec70',     // AppSecret
    'token'   => 't9HazcCJVOcB4Rh5',          // Token
    'aes_key' => 'pfVaL4o8Ra0Dy8orJAch3BGag0hpwkxBUK5HU2fwl9U',                    // EncodingAESKey

    /*
     * 日志配置
     *
     * level: 日志级别，可选为：
     * debug/info/notice/warning/error/critical/alert/emergency
     * file：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'level' => 'debug',
        'file'  => '/www/api.eachfight.com/application/logs/wechat.log',
    ],

    /*
     * OAuth 配置
     *
     * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
     * callback：OAuth授权完成后的回调页地址(如果使用中间件，则随便填写。。。)
     */
    'oauth' => [
        'scopes'   => ['snsapi_userinfo'],
        'callback' => 'api/weixin/oauthBack',
    ],

//    /**
//     * 微信支付
//     */
//    'payment' => [
//        'merchant_id'        => env('WECHAT_PAYMENT_MERCHANT_ID', '1220352101'),
//        'key'                => env('WECHAT_PAYMENT_KEY', '5d43e151ae0d308b89343e01d9ced62f'),
//        'cert_path'          => env('WECHAT_PAYMENT_CERT_PATH', app_path().'/key/wechat/apiclient_cert.pem'),  //XXX: 绝对路径！！！！
//        'key_path'           => env('WECHAT_PAYMENT_KEY_PATH', app_path().'/key/wechat/apiclient_key.pem'),       //XXX: 绝对路径！！！！
//        'notify_url'         => ''
//    ],

    'guzzle' => [
        'timeout' => 3.0, // 超时时间（秒）
        //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
    ],

    /*
     * 开发模式下的免授权模拟授权用户资料
     *
     * 当 enable_mock 为 true 则会启用模拟微信授权，用于开发时使用，开发完成请删除或者改为 false 即可
     */
    // 'enable_mock' => env('WECHAT_ENABLE_MOCK', true),
    // 'mock_user' => [
    //     "openid" =>"odh7zsgI75iT8FRh0fGlSojc9PWM",
    //     // 以下字段为 scope 为 snsapi_userinfo 时需要
    //     "nickname" => "overtrue",
    //     "sex" =>"1",
    //     "province" =>"北京",
    //     "city" =>"北京",
    //     "country" =>"中国",
    //     "headimgurl" => "http://wx.qlogo.cn/mmopen/C2rEUskXQiblFYMUl9O0G05Q6pKibg7V1WpHX6CIQaic824apriabJw4r6EWxziaSt5BATrlbx1GVzwW2qjUCqtYpDvIJLjKgP1ug/0",
    // ],
];
