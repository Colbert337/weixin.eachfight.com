<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{

    private $token = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->helper('cookie');
        $this->load->helper('emoji_helper');
    }

    //微信用户进行公众号授权
    public function oauth()
    {
        dump($this->cache->redis->get('5c57852d86f82a29e548b2cfdbe1e4a9'), get_cookie('guochao'));
        exit;

        $callback = urldecode($this->input->get('url')) . '?code=200';
        if (!$this->session->has_userdata($this->wechat_key)) {
            $response = $this->wechat->oauth->with(['state' => urlencode($callback)])->redirect();
            $response->send();
        } else {
            redirect($callback);
        }
    }

    //回调地址，获取用户基本信息  第一次注册入库
    public function oauthBack()
    {
        $user = $this->wechat->user();
        dump($user->get('o05NB0w96SrxDgpS6ZzOapUNq1WY'), $user->toArray());

        $userArr = $user->toArray();
        $this->session->set_userdata([$this->wechat_key => $userArr['id']]);
        set_cookie('token', $userArr['id'], time() + 7200, '.eachfight.com', '/');
        redirect(urldecode($this->input->get('state')));
    }


    /**
     * 前端给code 授权获取用户信息 注册入库
     */
    public function weboauth()
    {
        $User_Model = new User_Model();
        $code = $this->input->get('code', true);
        $this->token = $this->input->get('token', true);
        log_message('info', 'weboauth获取到的数据token:' . $this->token);

        if (empty($code)) $this->responseToJson(502, 'code参数缺少');

        try {
            if (empty($this->token) || empty($this->cache->redis->get($this->token))) {
                $user = $this->wechat->oauth->user();
                $data = $user->getOriginal();
                //加密
                $this->token = md5($data['openid'] . 'eachfight');
                $this->cache->redis->save($this->token, md5($data['openid']), 7200);
                //存cookie
                set_cookie('guochao', '100000', 7200, '.eachfight.com', '/');
                log_message('info', '获取到的数据cookie1:' . get_cookie('guochao') . '--token--' . $this->token);
                //注册
                if (!$this->User_Model->CheckRegister($this->token)) {  //没有注册过
                    $User_Model->insert([
                        'openid' => $data['openid'],
                        'token' => $this->token,
                        'nickname' => $data['nickname'],
                        'gender' => $data['sex'],  //1时是男性，值为2时是女性，值为0时是未知
                        'headimg_url' => $data['headimgurl'],
                        'create_time' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            {
                //存cookie
                set_cookie('guochao', '100000', 7200, '.eachfight.com', '/');
                log_message('info', '获取到的数据cookie2:' . get_cookie('guochao') . '--token--' . $this->token);
            }
            $this->responseToJson(200, '登陆成功', ['token' => $this->token]);
        } catch (Exception $e) {
            $this->responseToJson(502, $e->getMessage());
        }
    }
}
