<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Comm extends CI_Controller
{
    private $wechat = 'wechat_user';

    public function index(){
        $user = $this->session->userdata($this->wechat);
        dump(100);
    }

}
