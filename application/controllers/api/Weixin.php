<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use EasyWeChat\Foundation\Application;

class Weixin extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->config("wechat");
        $this->wechat = new Application(config_item("wechat"));

        $this->load->helper('cookie');
        $this->load->helper('emoji_helper');

        $this->load->model('User_Model');
    }

    //微信用户进行公众号授权
    public function oauth()
    {
        if (!$this->session->userdata($this->wechat_key)) {
            $user = $this->wechat->oauth->user();
            $data = $user->getOriginal();

            log_message('info', '获取到的用户数据10000:' . json_encode($data));
            $this->session->set_userdata([$this->wechat_key => $data]);
        } else {
            $data = $this->session->userdata($this->wechat_key);
            log_message('info', '获取到的用户数据20000:' . json_encode($data));
        }

        dump($data, $this->session->userdata($this->wechat_key));
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
        $user = $this->wechat->oauth->user();
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
        $code = $this->input->get('code');
        if (empty($code)) $this->responseToJson(502, 'code参数缺少');

        log_message('info', '获取到的数据101:' . json_encode(get_cookie('token')));

        try {
            if (!$this->session->userdata($this->wechat_key)) {
                $user = $this->wechat->oauth->user();
                $data = $user->getOriginal();

                $this->session->set_userdata([$this->wechat_key => $data]);

                set_cookie('token', $data['openid'], time() + 7200, '.eachfight.com', '/');

                log_message('info', '获取到的数据100:' . json_encode($this->session->userdata($this->wechat_key)));
                log_message('info', '获取到的数据101:' . json_encode(get_cookie('token')));


            } else {
                $data = $this->session->userdata($this->wechat_key);
                log_message('info', '获取到的数据200:' . json_encode($this->session->userdata($this->wechat_key)));
            }

            if (!$this->User_Model->CheckRegister($data['openid'])) {  //没有注册过
                $User_Model->insert([
                    'openid' => $data['openid'],
                    'nickname' => $data['nickname'],
                    'gender' => $data['sex'],  //1时是男性，值为2时是女性，值为0时是未知
                    'headimg_url' => $data['headimgurl'],
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }

            $this->responseToJson(200, '登陆成功', $data);
        } catch (Exception $e) {
            $this->responseToJson(502, $e->getMessage());
        }
    }
}
