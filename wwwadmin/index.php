<?php

/** 
 * index
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */

//应用名称
define("APP_NAME",'admin');
//加载框架入口文件
include ("../iframe/i.php");
//商品图片上传目录 
define("IMAGE_PATH",IFRAME_ROOT.'..'.DIRECTORY_SEPARATOR.'www'.DIRECTORY_SEPARATOR.'image'.DIRECTORY_SEPARATOR);

//主站域名
//商城电脑端域名
if( $_SERVER["SERVER_ADDR"]=='127.0.0.1' ){   //判断是不是在本地环境开发调试
    define('MAIN_DOMAIN', 'demo1.yixinu.com');    //本地环境开发调试
}else{
    define('MAIN_DOMAIN', 'demo.yixinu.com');  //线上生产环境用
}

//主站应用名
define('MAIN_NAME', 'app');
//主站服务器路径
define('MAIN_PATH', IFRAME_ROOT."../".MAIN_NAME.DIRECTORY_SEPARATOR);
//商品图片URL地址
define("IMAGE_URL", 'http://'.MAIN_DOMAIN.'/image/');
//运行项目 
iframe::start();

?>
