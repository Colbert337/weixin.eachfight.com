<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * GodApply Model Class
 *
 * 认证大神操作Model
 * @category    Models
 * @author        fengchen <fengchenorz@gmail.com>
 */
class God_Model extends MY_Model
{

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

    /**
     * 根据用户id及游戏类型获取大神的信息
     * @param int $user_id
     * @param int $game_type
     * @return mixed
     */
    public function getGodInfo(int $user_id, int $game_type)
    {
        return $this->db->select('*')
            ->from(self::TBL)
            ->where(["user_id" => $user_id, 'game_type' => $game_type])
            ->get()
            ->row_array();
    }


}