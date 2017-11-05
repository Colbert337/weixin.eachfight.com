<?php defined('BASEPATH') || exit('No direct script access allowed');
if (! function_exists('god_game_type')) {
    function god_game_type() {
        return array(1=>'王者荣耀');
    }
}
if (! function_exists('god_game_mode')) {
    function god_game_mode() {
        return array(1=>'排位赛','其他');
    }
}
if (! function_exists('gok_game_level')) {
    function gok_game_level() {
        return array(1=>'倔强青铜','秩序白银','荣耀黄金','尊贵铂金','永恒钻石','至尊星耀','最强王者');
    }
}
if (! function_exists('lol_game_level')) {
    function lol_game_level() {
        return array(1=>'倔强青铜','秩序白银','荣耀黄金','尊贵铂金','永恒钻石','大师','最强王者');
    }
}
if (! function_exists('limit_can_zone')) {
    function limit_can_zone() {
        return array(1=>'不限制','微信区','QQ区');
    }
}
if (! function_exists('limit_can_device')) {
    function limit_can_device() {
        return array(1=>'不限制','IOS','安卓');
    }
}
if (! function_exists('god_status')) {
    function god_status() {
        return array(1=>'正常','锁定');
    }
}
if (! function_exists('bind_status')) {
    function bind_status() {
        return array(1=>'已绑定','未绑定');
    }
}
if (! function_exists('order_pay_type')) {
    function order_pay_type() {
        return array(1=>'微信','支付宝','QQ');
    }
}
if (! function_exists('order_status')) {
    function order_status() {
        return array(
            1=>'下单待支付',
            '下单已支付',
            '未接取消申请退款',
            '已接取消申请退款',
            '订单退款中',
            '订单已退款',
            '大神抢单',
            '用户准备',
            '大神确认完成',
            '用户确认前申请退款',
            '订单完成',
            '订单关闭');
    }
}
?>