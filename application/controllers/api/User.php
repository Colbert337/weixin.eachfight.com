<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('GameLevel_Model');
        $this->load->library('Sms');
    }

    public function index()
    {
        $this->session->set_userdata(['guochao'=>100]);
        dump($this->session->userdata('guochao'),$this->session->userdata($this->wechat_key));
    }

    /**
     * 获取段位价格配置
     */
    public function getGameLevel()
    {
        dump($_SESSION,$_SERVER);exit;
        $GameLevel_Model = new GameLevel_Model();
        $data = $GameLevel_Model->getGameLevel(1);
        $this->responseToJson(200, '获取成功', $data);
    }
}
