<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace controllers;

/**
 * Description of jingchengController
 *
 * @author kyle
 */
class jinchengController extends adminController {

    public function index() {
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;

        $m = new \core\model();
        $list = $m->getall("select *,(dtime+INTERVAL sleeps SECOND) as uptime from " . SQLPRE . "jingcheng order by id limit $start,$listrows");
        foreach ($list as $key => $value) {
            $tt = strtotime($value['uptime']);
            if ($tt > time()) {
                $value['uptime'] = $tt - time() + 5;  //加5秒钟是为了避免产生误差
            } else {
                $value['uptime'] = 0;
            }
            $list[$key] = $value;
        }

        //page 数据
        $count = $m->getone("select count(id) as count from " . SQLPRE . "jingcheng");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->assign("list", $list);
        $this->display('jincheng/index');
    }

    /**
     * 设置开启或者关闭
     */
    public function set() {
        $id = rget('id');
        $m = new \core\model();
        $sql = "select * from `" . SQLPRE . "jingcheng` where `id`=$id";
        $info = $m->getrow($sql);
        if ($info['status']) {
            $data['status'] = 0;
            $m->sData($data, 'jingcheng', 'u', "id=$id");
        } else {
            $data['status'] = 1;
            $m->sData($data, 'jingcheng', 'u', "id=$id");
        }
        $data['actionName'] = $info['actionName'];
        echo json_encode($data);
        exit();
    }

    /**
     * 删除无效的订单
     */
    public function deleteOrder() {
//        session_unset();
//        session_destroy();
//        getmypid();
        session_commit();
        set_time_limit(0); //php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
        ignore_user_abort(true); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
        $m = new \core\model();
        $sql = "select * from `" . SQLPRE . "jingcheng` where `actionName`='deleteOrder'";
        $info = $m->getrow($sql);
        while ($info['status']) {
            if ($info['status'] == 0) {
                exit();
            }
            $sql1 = "delete from `" . SQLPRE . "orders` where `payStatus` = 1 and createTime < now()-INTERVAL  120 minute";
            $m->query($sql1);
            $sql2 = "delete from `" . SQLPRE . "order_goods` where `payStatus` = 1 and createTime < now()-INTERVAL 120 minute";
            $m->query($sql2);
            
            session_id("globalShareSession2");
            //休眠时间
            sleep($info['sleeps']);
//            session_start();
        }
    }

    public function updateCache() {
//        session_unset();
//        session_destroy();
//        getmypid();
        session_commit();
        set_time_limit(0); //php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
        ignore_user_abort(true); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
        $m = new \core\model();
        $sql = "select * from `" . SQLPRE . "jingcheng` where `actionName`='updateCache'";
        $info = $m->getrow($sql);
        while ($info['status']) {
            if ($info['status'] == 0) {
                exit();
            }

            $redis = new \models\yredis();
            //更新商品缓存
            $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
                    . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
                    . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou "
                    . " from " . SQLPRE . "goods as g "
                    . "left join " . SQLPRE . "category as gc on g.catId=gc.id "
                    . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                    . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                    . " left join " . SQLPRE . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
            //                . "left join `$tablename` as a on ga.sn=a.sn ";
            $list = $m->getall($sql);
            foreach ($list as $key => $value) {
                $redis->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));
                //更新库存和销量
                $redis->setex(REDIS_PRE . 'goods_salesval_' . $value['id'], REDIS_TTL, (int) $value['salesval']);  //销量
                $redis->setex(REDIS_PRE . 'goods_numbers_' . $value['id'], REDIS_TTL, (int) $value['numbers']);  // 库存 
            }
            //记录更新商品缓存的时间
            $redis->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());

            session_id("globalShareSession");
            //休眠时间
            unset($list);
            unset($info);
            unset($sql);
            sleep($info['sleeps']);
//            session_start();
        }
    }

    public function exemption() {
        
    }

}
