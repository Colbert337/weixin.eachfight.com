<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends CI_Controller
{
    private $wechat = 'wechat_user';

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $user = $this->session->userdata($this->wechat);
        dump(100);
    }

}
