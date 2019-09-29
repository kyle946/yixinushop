<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of statementController
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;

class statementController extends adminController {

    public function orderlist() {
        if (isset($_POST['_submit_'])) {
            $info['starttime'] = $_POST['starttime'];
            $info['endtime'] = $_POST['endtime'];
            $info['orderby'] = $_POST['orderby']; 
        }
        $info['starttime'] = empty($info['starttime']) ? date('Y-m-' . '01 00:00:00') : $info['starttime'];
        $info['endtime'] = empty($info['endtime']) ? date('Y-m-d H:i:s') : $info['endtime'];
        $info['days'] = round(( strtotime($info['endtime']) - strtotime($info['starttime']) ) / 3600 / 24);
        $info['orderby'] = empty($info['orderby'])?1:$info['orderby'];
        $info['days'] = $info['days'] ? : 1;

        $m = new \core\model();
        $sql = "select sum(amount) as money,count(id) as count from `" . SQLPRE . "orders` where createTime > '$info[starttime]' and createTime < '$info[endtime]' ";
        $r = $m->getrow($sql);
        $info['nums'] = $r['count'];
        $info['money'] = $r['money'];
        $info['avanums'] = round($r['count'] / $info['days'],2);
        $info['avamoney'] = round($r['money'] / $info['days'],2);
        $orderby = ' order by money desc ';
        if( $info['orderby']==2 ){
            $orderby = ' order by date ';
        }elseif( $info['orderby']==1 ){
            $orderby = ' order by money desc ';
        }
        $sql = "select sum(amount) as money,count(id) as count,date(createTime) as date from `" . SQLPRE . "orders` where createTime > '$info[starttime]' and createTime < '$info[endtime]'  group by date(createTime) $orderby ";
        $info['list'] = $m->getall($sql);

        $this->assign('info', $info);
        $this->display('statement/orderlist');
    }

    public function goodslist() {
        if (isset($_POST['_submit_'])) {
            $info['starttime'] = $_POST['starttime'];
            $info['endtime'] = $_POST['endtime'];
            $info['orderby'] = $_POST['orderby']; 
        }
        $info['starttime'] = empty($info['starttime']) ? date('Y-m-' . '01 00:00:00') : $info['starttime'];
        $info['endtime'] = empty($info['endtime']) ? date('Y-m-d H:i:s') : $info['endtime'];
        $info['days'] = round(( strtotime($info['endtime']) - strtotime($info['starttime']) ) / 3600 / 24);
        $info['orderby'] = empty($info['orderby'])?1:$info['orderby'];
        
        $m = new \core\model();
        $sql = "select sum(goodsnum) as nums from `" . SQLPRE . "order_goods` where createTime > '$info[starttime]' and createTime < '$info[endtime]' ";
        $info['nums'] = $m->getone($sql);
        $sql = "select sum(amount) as money from `" . SQLPRE . "orders` where createTime > '$info[starttime]' and createTime < '$info[endtime]' ";
        $info['money'] = $m->getone($sql);
        $orderby = ' order by money desc ';
        if( $info['orderby']==2 ){
            $orderby = ' order by date ';
        }elseif( $info['orderby']==1 ){
            $orderby = ' order by money desc ';
        }
        $sql = "select goodsprice*sum(goodsnum) as money,sum(goodsnum) as count,date(createTime) as date,goodsname,goodsattributeStr from `" . SQLPRE . "order_goods` where createTime > '$info[starttime]' and createTime < '$info[endtime]'  group by goodsid $orderby ";
        $info['list'] = $m->getall($sql);
        
        $this->assign('info', $info);
        $this->display('statement/goodslist');
    }

    public function exemption() {
        
    }

}
