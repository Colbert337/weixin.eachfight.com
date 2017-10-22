<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{

    private $wechat = 'wechat_user';

    public function __construct()
    {
        parent::__construct();
        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->helper('cookie');
    }

    //根据用户openid获取用户基本信息
    public function user(){
        $user = $this->wechat->user->get("o05NB0w96SrxDgpS6ZzOapUNq1WY");
        var_dump($user);
    }

    //微信用户进行公众号授权
    public function oauth(){
        $post = $this->input->get();
        $this->session->set_userdata('target_url', $post['target_url']??'');
        $response = $this->wechat->oauth->with(['state'=>$post['target_url']??''])->redirect();
        $response->send();

//        set_cookie('wechat_user',100,1200);
//        var_dump(get_cookie('wechat_user'));exit;
//        if(!$this->session->has_userdata($this->wechat)){
//            $response = $this->wechat->oauth->with(['state'=>100])->redirect();
//            $response->send();
//        }

    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack(){
        $response = $this->wechat->oauth->user();
        var_dump($this->wechat->oauth);
    }
}
