<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     *微信授权登陆
     */
    public function oauth()
    {
        $url = $_SERVER['REQUEST_URI'];
        $weixin = new Weixin();
        $weixin->oauth($url);
    }
}
