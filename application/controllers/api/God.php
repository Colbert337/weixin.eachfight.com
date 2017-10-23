<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class God extends CI_Controller
{

    private $wechat = 'wechat_user';

    public function index()
    {
        dump($this->session->userdata());
        $user = $this->session->userdata($this->wechat);
        dump($user);
    }
}
