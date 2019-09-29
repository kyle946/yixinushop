<?php

/**
 * 默认类  首页
 * 
 * @author kyle 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class indexController extends comController {

    public function index() {
        $this->display();
    }

    /**
     * 
     * 短信发送测试
     */
    public function test1() {
        split_order(5);
    }

    public function test2() {
        
//        header("Location: /user/index"); exit();
        
//        ini_set('date.timezone', 'Asia/Shanghai');
//        var_dump( strtotime(date("Y-m-d")." 23:59:59") ); 
//        var_dump( strtotime(date("Y-m-d")." 23:59:59") - time() );
        
        $redis = new \models\yredis();
        var_dump( $redis->get(REDIS_PRE.'goods_xianglouinfo_4_220') );
        
        var_dump( getonegoodscate(16) );
        
        //获取完整的url
        var_dump( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        var_dump( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] );
        var_dump( 'http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"] );
        var_dump( 'http://'.$_SERVER['SERVER_NAME'].$_SERVER["REQUEST_URI"] );


        $list = array(
            0 => array(
                'name' => '工作地点',
                'list' => array(
                    0 => '不限',
                    1 => '长沙',
                    2 => '上海',
                    3 => '北京'
                )
            ),
            1 => array(
                'name' => '发布时间',
                'list' => array(
                    0 => '不限',
                    1 => '三天内',
                    2 => '一周内',
                    3 => '一月内',
                    4 => '一年内'
                )
            ),
        );

        $var = rget('t');
        if ($var) {
            
        } else {
            for ($i = 0; $i < count($list); $i++) {
                $var = $var . '0';
            }
        }

        var_dump(getabc());
        var_dump($list);
        var_dump(count($list));
        var_dump(replaceStr());

        $this->assign('list', $list);
        $this->assign('link', $var);
        $this->display('index/test');
        //printf("%.2f",$coupon);
    }

    public function _empty() {
        
    }

}
