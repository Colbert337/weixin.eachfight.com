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
        if(!$this->session->has_userdata($this->wechat)){
            $response = $this->wechat->oauth->with(['state'=>urlencode($this->input->get('url'))])->redirect();
            $response->send();
        }else{
            redirect(urldecode($this->input->get('url')));
        }
    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack(){
        $user = $this->wechat->oauth->user();
        $userArr = $user->toArray();
        $this->session->set_userdata($this->wechat, $user->id);
        $user = $this->session->userdata($this->wechat);
        set_cookie('token',$user->id,time()+7200,'.eachfight.com','/');
        redirect(urldecode($this->input->get('state')));
    }
}
