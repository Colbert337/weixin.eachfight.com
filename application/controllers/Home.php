<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller
{
    private $wechat = 'wechat_user';

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
    }

	public function index()
	{
        $userArr = ['id'=>'10000','name'=>'2000000000'];
        $this->session->set_userdata($this->wechat, $userArr['id']);
        dump($this->session->userdata($this->wechat));exit;

        set_cookie('token',1000,time()+7200,'.eachfight.com','/');
        dump(get_cookie('token'));
        $zip = new \ZipArchive;
        $toDir = "public/zip";
        if (!file_exists($toDir)) {
            mkdir($toDir);
        }
        if ($zip->open('test.zip') === TRUE) {
            $zip->extractTo($toDir);//假设解压缩到在当前路径下images文件夹的子文件夹php
            $zip->close();//关闭处理的zip文件
        }
        var_dump($zip);
        exit;
        exit;

        $zip = new \ZipArchive;
        $zipfile = "test.tar";
        $res = $zip->open($zipfile);

        $toDir = "./public/zip";
        if (!file_exists($toDir)) {
            mkdir($toDir);
        }
        $docnum = $zip->numFiles;
        for ($i = 0; $i < $docnum; $i++) {
            $statInfo = $zip->statIndex($i);
            var_dump($statInfo);
            exit;
            if ($statInfo['crc'] == 0) {
                //新建目录
                mkdir($toDir . '/' . substr($statInfo['name'], 0, -1));
            } else {
                //拷贝文件
                copy('zip://' . $zipfile . '#' . $statInfo['name'], $toDir . '/' . $statInfo['name']);
            }
        }

        print_r(scandir($toDir));
    }

    public function test()
    {
//        var_dump(100);exit;
//        $val = '';
//        if ($val > 0 && $val < 2000) {
//            return [1 => 0.1];
//        } elseif ($val < 20000) {
//            return [2 => 0.15];
//        } elseif ($val < 200000) {
//            return [3 => 0.2];
//        } else {
//            return [4 => 0.25];
//        }

        $arrayName = array(
            '1' => array(
                'one' => 0,
                'two' => 2000,
                'bai' => 0.1,
            ),
            '2' => array(
                'one' => 2000,
                'two' => 20000,
                'bai' => 0.15,
            ),
            '3' => array(
                'one' => 20000,
                'two' => 200000,
                'bai' => 0.2,
            ),
            '4' => array(
                'one' => 200000,
                'two' => 10000000000,
                'bai' => 0.25,
            ),
        );

        $total = 500000; //$tp=4
        $tp = 1;
        foreach($arrayName as $key=>$val){
           if($total>=$val['one'] && $total<$val['two']){
               $tp = $key;
           }
        }

        $money = 0;
        for($i=$tp;$i>=1;$i--){
            if($i==$tp){
                $num = $total-$arrayName[$i]['one'];
            }else{
                $num = $arrayName[$i]['two']-$arrayName[$i]['one'];
            }
            $money += $num*$arrayName[$i]['bai'];
        }
        var_dump($money);
    }
}
