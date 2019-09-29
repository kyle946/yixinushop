<?php

/**
 * index
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 */
//应用名称
define("APP_NAME", 'app');
//加载框架入口文件
include ("../iframe/i.php");

//商品图片URL地址
defined('IMAGE_URL') or define("IMAGE_URL", '/image/');

//手机站地址
if ($_SERVER["SERVER_ADDR"] == '127.0.0.1') {  //判断是不是在本地环境开发调试
    defined('MOBILE_URL') or define('MOBILE_URL', 'http://m1.yixinu.com');     //本地环境开发调试
} else {
    defined('MOBILE_URL') or define('MOBILE_URL', 'http://js.yixinu.com');  //线上生产环境用
}


//判断是不是手机端访问 ，如果是直接跳转到手机端的URL
if ( judgeMobileBrowse() ) {
    
    //匹配是不是文章的跳转
    if ( preg_match("/.*\/m\/([a-z]+)\/id\_([0-9]+)\.*/", $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'],$matches) ==1 ) {
        $replace = MOBILE_URL . '/?v=/article/item/m_' . $matches[1] . '_id_' . $matches[2];
        header("Location: " . $replace);
        exit();
    } else {
        header("Location: " . MOBILE_URL);
        exit();
    }
}

//运行项目 
iframe::start();
?>
