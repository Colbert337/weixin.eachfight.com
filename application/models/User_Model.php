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
     * 根据token判断用户是否注册过
     * @param string $token
     * @return bool
     */
    public function CheckRegister($token)
    {
        $this->db->select('id', 'openid')->from(self::TBL)->where("token", $token);
        $query = $this->db->get();
        $data = $query->row_array();
        $query->free_result();
        return $data;
    }

    /**
     * 根据id获取用户信息
     * @param int $id
     * @return mixed
     */
    public function getUserById(int $id)
    {
        $this->db->select('*')->from(self::TBL)->where("id", $id);
        $query = $this->db->get();
        $data = $query->row_array();
        $query->free_result();
        return $data;
    }

}