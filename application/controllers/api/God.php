<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class God extends MY_Controller
{

    private $wechat = 'wechat_user';

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    public function index_get()
    {

        $data = ['pv'=>"1000", 'uv'=>"1000", 'date'=>time()];
        $this->response($data);
    }
}
