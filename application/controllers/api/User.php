<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->library('Sms');

        $this->openid = $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']??'';
    }

    public function index()
    {
        dump($this->openid);
        $this->session->set_userdata(['guochao'=>100]);
        dump($this->session->userdata('guochao'),$this->session->userdata($this->wechat_key));
    }

    /**
     * 获取段位价格配置
     */
    public function getGameLevel()
    {
        log_message('info','获取到的openid'.$this->openid);

        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);
    }
}
