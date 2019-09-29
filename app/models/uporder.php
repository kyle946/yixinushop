<?php

/*
 * 更新订单
 */

/**
 * 更新订单
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace models;

class uporder extends \core\model {

    public $ordersn;
    public $tradeSn;

    public function __construct($ordersn = null, $tradeSn = null) {
        parent::__construct();
        $this->ordersn = $ordersn;
        $this->tradeSn = $tradeSn;
    }

    public function exec() {

        $orderSn = $this->ordersn; //其他字段也可用类似方式获取
        $sql = "select id,payStatus,userId,amount from " . SQLPRE . "orders where orderSn='$orderSn' and payStatus<2";
        $info = $this->getrow($sql);
        $redis = new yredis();
        if ($info) {
            $orderid = $info['id'];
            if ( (int)$info['payStatus'] < 2 ) {
                $data['payStatus'] = 2;
                $data['payTime'] = 'now()';
                $data['tradeSn'] = $this->tradeSn;
                $data['sortsn'] = 'NULL---';
                $this->sData($data, 'orders', 'u', "id=$orderid");
            }

            //更新订单商品表中的支付状态
            $sql = "select payStatus from " . SQLPRE . "orders where orderid=$orderid";
            $goodspayStatus = $this->getone($sql);
            if (  (int)$goodspayStatus < 2  ) {
            
                $d2['payStatus'] = 2;
                $this->sData($d2, 'order_goods', 'u', "orderid=$orderid");
                
                //增加销量，减库存
                buckle_stock($orderid);
            }
            
            //积分 begin
            $sql ="select val from `".SQLPRE."shop_config` where mark='pointsAct'";
            $p1 = $this->getone($sql);
            $pointact = new other();
            $num = intval(floatval($p1) * $info['amount']);
            $pointact->pointsAct($info['userId'], $num, 'add', '支付订单 '.$this->ordersn.'，增加积分:'.$num);
            // end
            
            return 1;
        } else {
            return 0;
        }
    }

}
