<?php

/**
 * Description of orderController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;
class orderController extends adminController {
    
    /**
     * 订单列表
     */
    public function orderlist() {
        $m = new \core\model();
        
        $keywords = isset($_GET['keywords'])?$_GET['keywords']:null;
        $where = null;
        if( $keywords ){
            $where = " where (o.orderSn like '%$keywords%' or o.name like '%$keywords%' or o.mobile like '%$keywords%' or o.sortsn='$keywords') ";
        }
        
        if( isset($_GET['order']) && $_GET['order']=='clist' ){
            if( $where ){
                $where .= ' and (o.payStatus=1) ';
            }else{
                $where .= ' where (o.payStatus=1) ';
            }
        }
        if( isset($_GET['order']) && $_GET['order']=='alist' ){
            if( $where ){
                $where .= ' and (o.payStatus>=2) ';
            }else{
                $where .= ' where (o.payStatus>=2) ';
            }
        }
        if( isset($_GET['order']) &&  $_GET['order']=='blist' ){
            if( $where ){
                $where .= ' and (o.delStatus>=2) ';
            }else{
                $where .= ' where (o.delStatus>=2) ';
            }
        }
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 8;
        $start = $p*$listrows-$listrows;
        
        $sql = "select o.*,ap.provice_name,ac.city_name,aco.county_name,b.business_name from ".$m->prefix."orders o "
                . " left join ".SQLPRE."business b on o.business_no=b.business_no "
                . " left join `".$m->prefix."area_provice` ap on o.proviceId=ap.provice_id "
                . " left join `".$m->prefix."area_city` ac on o.cityId=ac.city_id "
                . " left join `".$m->prefix."area_county` aco on o.countyId=aco.county_id "
                . " $where order by id desc limit $start,$listrows ";
        $orderlist = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(o.id) as count from ".$m->prefix."orders o $where");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $orderlist);
        if( isset($_SESSION['editOrderId']) and !empty($_SESSION['editOrderId']) ) $this->assign('editOrderId', $_SESSION['editOrderId']);
        $this->display('order/orderlist');
    }
    
    /**
     * 订单详情
     */
    public function orderinfo() {
        $m = new \core\model();
        $id = $this->_get('id');
        //为了方便找到编辑的位置
        $_SESSION['editOrderId'] = $id;
        //订单信息
        $sql = "select * from ".$m->prefix."orders where id=$id";
        $result = $m->getrow($sql);
        $result['paytypelogo'] = $m->getone("select logo from ".$m->prefix."payment where sn='$result[payType]'");
        $result['deltypename'] = $m->getone("select name from ".$m->prefix."deliverys where id=$result[deliveryMethod]");
        $result['deliveryId'] = $result['deliveryMethod'];
        $this->assign('info', $result);
        //收货地址
        $sql = "select ap.provice_name,ac.city_name,aco.county_name  from ".$m->prefix."orders o "
                . "left join `".$m->prefix."area_provice` ap on o.proviceId=ap.provice_id "
                . "left join `".$m->prefix."area_city` ac on o.cityId=ac.city_id "
                . "left join `".$m->prefix."area_county` aco on o.countyId=aco.county_id "
//                . "left join `".$m->prefix."area_town` at on o.townId=at.town_id "
                . " where o.id=$id ";
        $address = $m->getrow($sql);
        $this->assign('address', $address);
        $sql = "select username,nickname from ".$m->prefix."users where id=$result[userId]";
        $userinfo = $m->getrow($sql);
        $this->assign('userinfo', $userinfo);
        //商品列表
        $sql = "select * from `".$m->prefix."order_goods` where orderid = $id";
        $goodlist = $m->getall($sql);
        $this->assign('goodlist', $goodlist);
        
        $this->display('order/orderinfo');
    }
    
    /**
     * 调价
     */
    public function priceMod() {
        $m = new \core\model();
        $orderid = $this->_get('id');
        if($this->_get('pricenum')){
            //为了方便找到编辑的位置
            $_SESSION['editOrderId'] = $orderid;
        
            $price = $this->_get('pricenum');
            $amount_ = $m->getone("select amount from ".$m->prefix."orders where id=$orderid");
            $amount = (float)$amount_ + (float)$price;
            $price1 = $m->getone("select priceMod from ".$m->prefix."orders where id=$orderid");
            $price2 = (float)$price + (float)$price1;
            $sql = "update ".$m->prefix."orders set amount=$amount,priceMod=$price2 where id=$orderid";
            $m->query($sql);
        }else{
            $sql = "select amount,goodsAmount,priceMod,freight from ".$m->prefix."orders where id=$orderid";
            $info = $m->getrow($sql);
            $this->assign('info', $info);
            $this->assign('orderid', $orderid);
            $this->display('order/orderpriceMod');
        }
    }
    
    /**
     * 帮用户完成支付  , 如果支付方式为  转账或者当面付 
     */
    public function ConfirmPayment() {
        $m = new \core\model();
        $orderid = $this->_get('orderid');
        //为了方便找到编辑的位置
        $_SESSION['editOrderId'] = $orderid;
        
        $sql = "select payType,userId,amount,orderSn from ".$m->prefix."orders where id=$orderid";
        $t1 = $m->getrow($sql);
        $paymentType = $t1['payType'];
        //如果是  转账或者当面付 
        if( $paymentType=='transfer' || $paymentType=='face_pay' ){
            $sql = "update ".$m->prefix."orders set payStatus=2,`payTime`=NOW(),`sortsn`=NULL where id=$orderid";
            $paydone = $m->query($sql);
            //更新订单商品表中的支付状态
            $sql = "update `".$m->prefix."order_goods` set payStatus=2 where orderid=$orderid";
            $m->query($sql);
            if($paydone){
                //积分
                $sql ="select val from `".SQLPRE."shop_config` where mark='pointsAct'";
                $p1 = $m->getone($sql);
                $pointact = new \models\other();
                $num = intval(floatval($p1) * $t1['amount']);
                $pointact->pointsAct($t1['userId'], $num, 'add', '支付订单 '.$t1['orderSn'].'，增加积分:'.$num);
            
                echo json_encode( array('status'=>1) );
                exit();
            }
        }
        
        echo json_encode( array('status'=>0) );
    }
    
    /**
     * 打印快递单
     */
    public function printExpress() {
        $id = $this->_get('id');
        //为了方便找到编辑的位置
        $_SESSION['editOrderId'] = $id;
        
        $m = new \core\model();
        $sql = "select deliveryMethod from ".$m->prefix."orders where id=$id";
        $deliveryMethod = $m->getone($sql);
        
        //配送方式详情
        $sql = "select * from ".$m->prefix."deliverys where id=$deliveryMethod";
        $info = $m->getrow($sql);
        $info['ex_background'] = str_replace('thumb_', '', $info['ex_background']);
        $this->assign('info', $info);
        $this->display('order/printExpress');
    }
    
    public function printExpressGetjson() {
        $id = $this->_get('id');
        $m = new \core\model();
        $sql = "select * from ".$m->prefix."orders where id=$id";
        $orderInfo = $m->getrow($sql);
        
        $orderInfo['year'] = date('Y');
        $orderInfo['month'] = date('m');
        $orderInfo['day'] = date('d');
        $orderInfo['webName'] = $m->getone("select val from ".$m->prefix."shop_config where mark='webname'");
        $orderInfo['webAddress'] = $m->getone("select val from ".$m->prefix."shop_config where mark='contact'");
        $orderInfo['webPhone'] = $m->getone("select val from ".$m->prefix."shop_config where mark='telphone'");
        
        //收货地址
        $sql = "select ap.provice_name,ac.city_name,aco.county_name from ".$m->prefix."orders o "
                . "left join `".$m->prefix."area_provice` ap on o.proviceId=ap.provice_id "
                . "left join `".$m->prefix."area_city` ac on o.cityId=ac.city_id "
                . "left join `".$m->prefix."area_county` aco on o.countyId=aco.county_id "
//                . "left join `".$m->prefix."area_town` at on o.townId=at.town_id "
                . "  where o.id=$id ";
        $address = $m->getrow($sql);
        
        $sendinfo = array();
        $sql = "select sendout from ".$m->prefix."deliverys where id=$orderInfo[deliveryMethod]";
        $sendinfo_ = $m->getone($sql);
        if( !empty($sendinfo_) ){
            $sendinfo = unserialize($sendinfo_);
        }
        
        echo json_encode(array_merge($address, $orderInfo,$sendinfo));
        exit();
    }
    
    /**
     * 打印配送单
     * http://localhost/barcode
     */
    public function printDelivery() {
        
        if( $this->_get('id') and $this->_get('barcode')==false):
                $id = $this->_get('id');
                $m = new \core\model();
                //为了方便找到编辑的位置
                $_SESSION['editOrderId'] = $id;

                //订单信息
                $sql = "select * from ".$m->prefix."orders where id=$id";
                $result = $m->getrow($sql);
                $result['paytypelogo'] = $m->getone("select logo from ".$m->prefix."payment where sn='$result[payType]'");
                $result['deltypename'] = $m->getone("select name from ".$m->prefix."deliverys where id=$result[deliveryMethod]");
                $this->assign('info', $result);
                //收货地址
                $sql = "select ap.provice_name,ac.city_name,aco.county_name from ".$m->prefix."orders o "
                        . "left join `".$m->prefix."area_provice` ap on o.proviceId=ap.provice_id "
                        . "left join `".$m->prefix."area_city` ac on o.cityId=ac.city_id "
                        . "left join `".$m->prefix."area_county` aco on o.countyId=aco.county_id "
//                        . "left join `".$m->prefix."area_town` at on o.townId=at.town_id "
                        . "  where o.id=$id ";
                $address = $m->getrow($sql);
                $this->assign('address', $address);
                $sql = "select username,nickname from ".$m->prefix."users where id=$result[userId]";
                $userinfo = $m->getrow($sql);
                $this->assign('userinfo', $userinfo);
                //商品列表
                $sql = "select * from `".$m->prefix."order_goods` where orderid = $id";
                $goodlist = $m->getall($sql);
                $this->assign('goodlist', $goodlist);
                
                //判断是打印配送单还是打印订单
                if( $this->_get('order')=='printOrder' ){
                    $this->display('order/printOrder');
                }elseif( $this->_get('order')=='printDelivery' ){
                    $this->display('order/printDelivery');
                }
                
        endif;
        
        if($this->_get('barcode')):
                $text = $this->_get('text');
                $b = new \models\barcode();
                $b->index($text,10,2,20);
        endif;
    }
    
    /**
     * 发货
     */
    public function sendout() {
        $id = $this->_get('id');
        $m = new \core\model();
        //为了方便找到编辑的位置
        $_SESSION['editOrderId'] = $id;
        
        if( isset($_POST['_submit_']) ){
            $payType = $m->getone("select payType from ".SQLPRE."orders where id=$id");
            //只有当面付和微信转账的才在发货时扣库存
            if( $payType=='face_pay' || $payType=='transfer' ){
                buckle_stock($id);
            }
            $expressNo = $this->_post('expressNo');
            $sql = "update ".$m->prefix."orders set expressNo='$expressNo',delStatus=2,sendTime=now() where id=$id";
            $result = $m->query($sql);
            
            //更新订单商品表中的配送状态
            $sql = "update `".$m->prefix."order_goods` set delStatus=2 where orderid=$id";
            $m->query($sql);
            if($result){
                echo json_encode(array('status'=>1));
            }
            exit();
        }else{
            $this->assign('id', $id);
            $this->assign('orderSn', $this->_get('sn'));
            $sql = "select expressNo from ".$m->prefix."orders where id=$id";
            $expressNo = $m->getone($sql);
            $this->assign('expressNo', $expressNo);
            $this->display('order/sendout');
        }
    }
    
    /**
     * 查看物流信息
     */
    public function viewPhysical() {
        $sn = $this->rget("sn");
        $deliveryId = $this->rget("deliveryId");
        if($sn){
            $m = new \core\model();
            $sql = "select ex_com from ".$m->prefix."deliverys where id=$deliveryId";
            $com = $m->getone($sql);
            $data = getkuaidi($sn, $com);
            if( !is_array($data) or empty($data) ):
                echo '获取信息错误！';
                exit();
            endif;
            //调整顺序   start
            $i = count($data['data']);
            $arr = array();
            foreach ($data['data'] as $key => $value) {
                $arr[$i] = $value;
                $i--;
            }
            sort($arr);
            $data['arr'] = $arr;
            unset($data['data']);
            //调整顺序   end
            $this->assign('info', $data);
            $this->display('order/viewPhysical');
        }
    }
    
    /**
     * 删除订单
     */
    public function deleteOrder() {
        $orderid = $this->_get('id');
        $m = new \core\model();
        $sql = "delete from ".$m->prefix."orders where id=$orderid";
        $result = $m->query($sql);
        if( $result ){
            $sql = "delete from  ".$m->prefix."order_goods where orderid=$orderid";
            $m->query($sql);
            
            echo json_encode( array('status'=>1) );
            exit();
        }
    }
    
    public function exemption() {
        
    }
}
