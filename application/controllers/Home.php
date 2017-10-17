<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
        $zip = new \ZipArchive;
        $toDir = "public/zip";
        if(!file_exists($toDir)) {
            mkdir($toDir);
        }
        if ($zip->open('test.zip') === TRUE)
        {
            $zip->extractTo($toDir);//假设解压缩到在当前路径下images文件夹的子文件夹php
            $zip->close();//关闭处理的zip文件
        }
        var_dump($zip);exit;
        exit;

        $zip = new \ZipArchive;
        $zipfile = "test.tar";
        $res = $zip->open($zipfile);

        $toDir = "./public/zip";
        if(!file_exists($toDir)) {
            mkdir($toDir);
        }
        $docnum = $zip->numFiles;
        for($i = 0; $i < $docnum; $i++) {
            $statInfo = $zip->statIndex($i);
            var_dump($statInfo);exit;
            if($statInfo['crc'] == 0) {
                //新建目录
                mkdir($toDir.'/'.substr($statInfo['name'], 0,-1));
            } else {
                //拷贝文件
                copy('zip://'.$zipfile.'#'.$statInfo['name'], $toDir.'/'.$statInfo['name']);
            }
        }

        print_r(scandir($toDir));
	}
}
