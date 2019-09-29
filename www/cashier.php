<?php

/** 
 * 收银终端 项目接口
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */


//应用名称
define("APP_NAME",'app');

//加载框架入口文件
include ("../iframe/i.php");

//头像上传目录
define("AVATAR_PATH",IFRAME_ROOT.'../www/avatar');
//商品图片上传目录
define("IMAGE_PATH",IFRAME_ROOT.'../www/image');

//商城电脑端域名
if( $_SERVER["SERVER_ADDR"]=='127.0.0.1' ){   //判断是不是在本地环境开发调试
    define('MAIN_DOMAIN', 'demo1.yixinu.com/');    //本地环境开发调试
}else{
    define('MAIN_DOMAIN', 'demo.yixinu.com');  //线上生产环境用
}
//商品图片URL地址
define("IMAGE_URL", 'http://'.MAIN_DOMAIN.'image/');

//定义默认控制器
define ('CONTROLLER_NAME_DEFAULT', 'cashier');

//运行项目 
iframe::start();

?>
