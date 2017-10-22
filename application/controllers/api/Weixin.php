<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{

    protected $wechat = NULL;

    public function __construct()
    {
        parent::__construct();
        //加载wechat配置文件
        $this->load->config("wechat");
        //实例化easywechat包
        $this->wechat = new Application(config_item("wechat"));
    }

    public function index(){
        echo "发起授权请求：<a href='./oauth'>./oauth</a><br>";
        echo "获取用户信息：<a href='./user'>./user</a><br>";
    }

    public function user(){
        $user = $this->wechat->user->get("oRGOms1oh2TtkvHK-FoQA4tnWH_U");
        var_dump($user);
    }

    //微信用户进行公众号授权
    public function oauth(){
        $response = $this->wechat->oauth->redirect();
        $response->send();
    }

    //回调地址，在此拿到用户的信息及access_token相关信息
    public function oauthBack(){
        $response = $this->wechat->oauth->user();
        var_dump($response);
    }
}
