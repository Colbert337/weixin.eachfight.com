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
    public function user()
    {
        $user = $this->wechat->user->get("o05NB0w96SrxDgpS6ZzOapUNq1WY");
        var_dump($user);
    }

    //微信用户进行公众号授权
    public function oauth()
    {
        $callback = urldecode($this->input->get('url')) . '?code=200';
        if (!$this->session->has_userdata($this->wechat)) {
            $response = $this->wechat->oauth->with(['state' => urlencode($callback)])->redirect();
            $response->send();
        } else {
            redirect($callback);
        }
    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack()
    {
//      dump($this->wechat->oauth);exit;
        $user = $this->wechat->oauth->user();
        $userArr = $user->toArray();
        $this->session->set_userdata(['wechat_user' => $userArr['id']]);
        set_cookie('token', $userArr['id'], time() + 7200, '.eachfight.com', '/');
        redirect(urldecode($this->input->get('state')));
    }

    //前端给code 授权获取用户信息 注册入库
    public function weboauth()
    {
        $user = $this->wechat->oauth->user();
        set_cookie('token', $user->id, time() + 7200, '.eachfight.com', '/');
        echo json_encode(['code' => 200, 'msg' => '数据获取成功', 'data' => $user->toArray()]);
        exit;
    }
}
