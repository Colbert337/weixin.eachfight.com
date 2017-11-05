<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * User_Model Model Class
 *
 * 用户Model
 * @category    Models
 * @author        guochao
 */
class User_Model extends MY_Model
{

    const TBL = 'user';

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

    /**
     * 根据openid判断用户是否注册过
     * @param string $token
     * @return bool
     */
    public function CheckRegister($token)
    {
        $this->db->select('id')->from(self::TBL)->where("token", $token);
        $query = $this->db->get();
        $num = $query->num_rows();
        $query->free_result();
        return ($num > 0) ? TRUE : FALSE;
    }

    /**
     * 根据openid获取用户信息
     * @param string $openid
     * @return bool
     */
    public function getUserByOpenid($openid)
    {
        $this->db->select('*')->from(self::TBL)->where("openid", $openid);
        $query = $this->db->get();
        $data = $query->row_array();
        $query->free_result();
        return $data;
    }

}