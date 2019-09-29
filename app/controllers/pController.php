<?php

/**
 * 控制器常用方法集合
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class pController {
    
    public function parseCss() {
        header('Content-Type:text/css; charset=utf-8');
        $content = file_get_contents(VIEW_PATH.'other/style.css');
//        echo $content; exit();
        //去掉模板中的html注释
        $content = preg_replace("/\/\*[^\[^i^f][\s\S]*?[^i^f^\]]\*\//","",$content);
        //删除空白行和空格，压缩代码
        $content = preg_replace("/[\n\r\n\t]\s*/"," ",$content);
        echo $content; exit();
    }

}
