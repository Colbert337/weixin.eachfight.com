<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 认证大神操作Model
 * @category	Models
 * @author		fengchen <fengchenorz@gmail.com>
 */
class God_Model extends MY_Model {

	const TBL = 'god';
	
	/**
     * 主键：{"id"}
     * 
     * @access private
     */
	private $_unique_key = array('id');

	/**
     * 构造函数
     *
     * @access public
     * @return void
     */
    function __construct()
    {
        parent::__construct();

    }



}