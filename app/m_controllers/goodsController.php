<?php

/**
 * Description of mobileController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;

class goodsController extends comController {

    /**
     * 订单确认页面 - 修改商品数量
     */
    public function ConfirmAnOrderChangeGoods() {

        $userInfo = loginJudbe();
        if (isset($_GET['type']) and $_GET['type'] == 2) { //如果是从我的购物车点进来的
            if ($userInfo == false) {
                echo json_encode(array('status' => 2));
                exit();
            }

            unset($_SESSION['ConfirmAnOrderGoods']['goods']);
            $id_ = explode(',', $this->_get('id'));
            $where = null;
            foreach ($id_ as $value) {
                if (!empty($value))
                    $where .= "id=$value or ";
            }
            $where = substr($where, 0, -3);
            $m = new \core\model();
            $sql = "select goodsnum,goodsid from `" . $m->prefix . "users_shoppingcart` where ($where) and (userid=$userInfo[id])";
            $result = $m->getall($sql);
            //            echo json_encode($sql); exit();
            foreach ($result as $key => $value) {
                $goodsid = $value['goodsid'];
                $num = $value['goodsnum'];
                $_SESSION['ConfirmAnOrderGoods']['goods'][$goodsid] = $num;
            }
            echo json_encode(array('status' => 1));
            exit();
        } else {  //如果是点的 立即购买 按钮或者是订单确认页面修改商品数量
            //是否要清空之前订单中的商品
            if ($this->_get('clear') == 'all')
                unset($_SESSION['ConfirmAnOrderGoods']['goods']);
            $goodsid = $this->_get('goodsid');
            $num = $this->_get('num');
            $_SESSION['ConfirmAnOrderGoods']['goods'][$goodsid] = $num;

            if ($userInfo == false) {
                echo json_encode(array('status' => 2));
            } else {
                echo json_encode(array('status' => 1));
            }
            exit();
        }
    }

    /**
     * 确认订单
     */
    public function ConfirmAnOrder() {
        //清空所有信息
        if ($this->rget('clear') == 'all') {
            $_SESSION['ConfirmAnOrderGoods'] = null;
        }
        //清空部分信息
        if ($this->rget('clear') == '1') {
            $_SESSION['ConfirmAnOrderGoods']['addressid'] = null;  //选择的地址
            $_SESSION['ConfirmAnOrderGoods']['couponId'] = null; //优惠券
//            $_SESSION['ConfirmAnOrderGoods']['freightid'] = null; //配送方式
//            $_SESSION['ConfirmAnOrderGoods']['paymentid'] = null;  //支付方式
//            $_SESSION['ConfirmAnOrderGoods']['freight_name'] = null; //配送方式
//            $_SESSION['ConfirmAnOrderGoods']['payment_name'] = null;  //支付方式
        }

        $m = new \core\model();
        $userinfo = loginJudbe();
        if ($userinfo == false) {
            header('Location: ' . createLink('u/login'));
            exit();
        }


        //用户收货地址  start
        if (@!empty($_SESSION['ConfirmAnOrderGoods']['addressid'])) {
            $addressid = $_SESSION['ConfirmAnOrderGoods']['addressid'];
            $adrlist = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from " . $m->prefix . "users_address ua "
                    . "left join " . $m->prefix . "area_provice ap on ua.proviceSn=ap.provice_id "
                    . "left join " . $m->prefix . "area_city ac on ua.citySn=ac.city_id  "
                    . "left join " . $m->prefix . "area_county act on ua.countySn=act.county_id "
//                    . "left join " . $m->prefix . "area_town at on ua.townSn=at.town_id "
                    . " where ua.userid=$userinfo[id] and ua.id=$addressid");
            $this->assign('adrlist', $adrlist);
        } else {
            $adrone = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from " . $m->prefix . "users_address ua "
                    . "left join " . $m->prefix . "area_provice ap on ua.proviceSn=ap.provice_id "
                    . "left join " . $m->prefix . "area_city ac on ua.citySn=ac.city_id  "
                    . "left join " . $m->prefix . "area_county act on ua.countySn=act.county_id "
//                    . "left join " . $m->prefix . "area_town at on ua.townSn=at.town_id "
                    . " where ua.userid=$userinfo[id] and ua.isdefault=1");
            if (empty($adrone)) {
                $adrone = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from " . $m->prefix . "users_address ua "
                        . "left join " . $m->prefix . "area_provice ap on ua.proviceSn=ap.provice_id "
                        . "left join " . $m->prefix . "area_city ac on ua.citySn=ac.city_id  "
                        . "left join " . $m->prefix . "area_county act on ua.countySn=act.county_id "
//                        . "left join " . $m->prefix . "area_town at on ua.townSn=at.town_id "
                        . " where ua.userid=$userinfo[id] limit 1");
            }
            if($adrone){
                $_SESSION['ConfirmAnOrderGoods']['addressid'] = $adrone[0]['id'];
                $this->assign('adrlist', $adrone);
            }
        }
        //用户收货地址  end
        
        //支付方式  start
        $payment['alipay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='alipay01' and status=1");
        $payment['weixin01'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='weixin01' and status=1");
        $payment['transfer'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='transfer' and status=1");
        $payment['face_pay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='face_pay' and status=1");
        $this->assign('payment', $payment);
        //支付方式  end
        
        //配送方式 start
        $freightList = $m->getall("select *,tag as yixinu from " . $m->prefix . "deliverys where status=1");
        $this->assign('freighlist', $freightList);
        //配送方式 end
        
        //取收货地址
        if( isset($_SESSION['ConfirmAnOrderGoods']['addressid']) ){
            $address = $m->getrow("select * from " . $m->prefix . "users_address where id=" . $_SESSION['ConfirmAnOrderGoods']['addressid']);
        }
        
        //选择默认 配送方式
        if ( empty($_SESSION['ConfirmAnOrderGoods']['freightid']) ) {
            $_SESSION['ConfirmAnOrderGoods']['freightid'] = $freightList['delivery']['id'];
            $_SESSION['ConfirmAnOrderGoods']['freight_name'] = $freightList['delivery']['name'];
        }
        //end 
        
        //begin 选择默认支付方式
        if ( empty($_SESSION['ConfirmAnOrderGoods']['paymentid']) && 
                !isset($_SESSION['ConfirmAnOrderGoods']['selectpay'])
                ) {
            //如果是在微信之下
            if ( judgeMicroMessenger() ) {
                $_SESSION['ConfirmAnOrderGoods']['paymentid'] = $payment['weixin01']['sn'];
                $_SESSION['ConfirmAnOrderGoods']['payment_name'] = $payment['weixin01']['title'];
            } else {
                $_SESSION['ConfirmAnOrderGoods']['paymentid'] = $payment['transfer']['sn'];
                $_SESSION['ConfirmAnOrderGoods']['payment_name'] = $payment['transfer']['title'];
            }
        }
        //end
            
        
        // 商品价格相关信息  start
        $goods_data = array();  //要购买的商品数据
        if (!isset($_SESSION['ConfirmAnOrderGoods']) or ! isset($_SESSION['ConfirmAnOrderGoods']['goods'])) {
            message('你好像还没有要购买的商品！先去逛逛吧。');
        } else {
            $goods_data_id = $_SESSION['ConfirmAnOrderGoods']['goods'];  //提交到订单的商品
            $where_2 = null;
            foreach ($goods_data_id as $key => $value) {
                $where_2 .= "ga.id = $key or ";
            }
            $where_2 = substr($where_2, 0, -3);
            $sql = "select g.business_no,g.name,ga.id as yixinu,ga.attributeStr,ga.shopPrice,ga.thumb,ga.numbers, "
                    . "if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ag.xiangou,  "
                    . " ga.weight,ga.sn from " . $m->prefix . "goods g "
                    . " left join `" . $m->prefix . "goods_additional` ga on g.id=ga.goodsId "
                    . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                    . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                    . " where $where_2 and ga.addStatus=1";
            $goods_data = $m->getall($sql); //要购买的商品数据
        }
        
        $new_order_list = array();  //拆单后新的订单数组
        $goodstotal = 0 ; //商品价格总计
        $total = 0; //应付款
        $freight = 0; //运费
        $Preferential = 0; //优惠
        $coupon = 0;
        $goods_data_2 = split_order_specify_business($goods_data);  //按商户拆分
        foreach ($goods_data_2 as $business_no => $order_goods_2) {
                //寻找最近的仓库发货
                $tmpvar1 = match_stores($address['lat'] , $address['lng'] , $business_no);
                $tmpvar2['delivery_warehouse'] = $tmpvar1['id'];
                $tmpvar2['warehouse_name'] = $tmpvar1['name'];
                $tmpvar2['warehouse_address'] = $tmpvar1['provice_name'].$tmpvar1['city_name'];//.$tmpvar1['county_name'].$tmpvar1['address'];
                
                if( !$business_no || empty($business_no) ): message("商品数据错误，未知的商户!"); endif;
                $new_order_info = $this->priceComputation($userinfo['id'] , $order_goods_2 , $business_no ,$tmpvar2['delivery_warehouse']);
                if ($new_order_info == FALSE):  message('订单创建失败!'); endif;
                $new_order_list[$business_no] = array_merge( $new_order_info , $tmpvar2 );
                
                $goodstotal += $new_order_info['goodstotal'];
                $total += $new_order_info['total'];
                $freight += $new_order_info['freight'];
        }
//        var_dump($new_order_list);
        $this->assign('order_list', $new_order_list);
        
//        $goodstotal = $Total['goodstotal']; //商品价格总计
//        $total = $Total['total']; //应付款
//        $freight = $Total['freight']; //运费
//        $weight_ = $Total['weight']; // 订单总重量
//        $Preferential = $Total['Preferential']; //优惠
//        $coupon = $Total['coupon']; //优惠券

        $this->assign('goodstotal', $goodstotal);
        $this->assign('total', $total);
        $this->assign('freight', $freight);
        $this->assign('Preferential', $Preferential);
        $this->assign('coupon', $coupon);
        // 商品价格相关信息  end
        
        //如果提交了订单或没提交订单    
        if (isset($_POST['_submit_'])) {
            if (empty($_SESSION['ConfirmAnOrderGoods']['addressid'])):  message('没有选择收货地址！'); endif;
            if ( empty($_SESSION['ConfirmAnOrderGoods']['freightid']) ):  message('没有选择配送方式！'); endif;
            
            //用户补充说明
            $comment = $_POST['comment'];  
            //订单数据
            $data['userId'] = $userinfo['id'];
            $data['payType'] = $_SESSION['ConfirmAnOrderGoods']['paymentid'];
            $data['deliveryMethod'] = $_SESSION['ConfirmAnOrderGoods']['freightid'];
            $data['pay_method_name'] = $_SESSION['ConfirmAnOrderGoods']['payment_name'];
            $data['del_method_name'] = $_SESSION['ConfirmAnOrderGoods']['freight_name'];
//            $data['status'] = 1; //订单已经确认
            $data['payStatus'] = 1;  //未支付 
            $data['delStatus'] = 1; //未发货
            $data['name'] = $address['recipients'];
            $data['proviceId'] = $address['proviceSn'];
            $data['cityId'] = $address['citySn'];
            $data['countyId'] = $address['countySn'];
            $data['townId'] = $address['townSn'];
            $data['mobile'] = $address['mobile'];
            $data['address'] = $address['street'];
            $data['phone'] = $address['phone'];
            $data['zipcode'] = $address['zipcode'];
            $data['split_sn'] = time().rand(10,99); //拆单号
            $data['split_data'] = serialize(array('goodstotal'=>$goodstotal,'total'=>$total,'freight'=>$freight));  //
            foreach ($new_order_list as $business_no => $order_data) {
                
                $orderSn = date('ymdHis') . rand(10, 99) . $userinfo['id'];
                $data['orderSn'] = $orderSn; //date('YmdHis').$userinfo['id'].rand(1000, 9999);
                $sortsn = $m->getone("SELECT count(*)+1 as c FROM `".SQLPRE."orders` WHERE `payType`='transfer' and payStatus=1");//短订单号
                $data['sortsn'] = rand(10, 99) .$sortsn;  //短订单号
                $data['amount'] = $order_data['total'];
                $data['business_no'] = $business_no;
                $data['delivery_warehouse'] = $order_data['delivery_warehouse'];
                $data['warehouse_name'] = $order_data['warehouse_name'];
                
                $data['goodsAmount'] = $order_data['goodstotal'];
                $data['freight'] = $order_data['freight'];
//                $data['weight'] = $order_data['weight_'];
                $data['coupon'] = $order_data['coupon'];
                $data['preferential'] = $order_data['Preferential'];  //用户 优惠 
                $data['usernote'] = strip_tags(trim( $comment[$business_no] ));
                $result = $m->sData($data, 'orders');
                
                $orderid = mysqli_insert_id($m->link);
                foreach ($order_data['goodslist'] as $key => $var1) {
                    $ordergoods['orderid'] = $orderid;
                    $ordergoods['goodsid'] = $key;
                    $ordergoods['business_no'] = $var1['business_no'];
                    $ordergoods['goodsname'] = $var1['name'];
                    $ordergoods['goodssn'] = $var1['sn'];
                    $ordergoods['goodsnum'] = $var1['num'];
                    $ordergoods['goodsprice'] = $var1['shopPrice'];
                    $ordergoods['goodsattributeStr'] = $var1['attributeStr'];
                    $ordergoods['goodsthumb'] = $var1['thumb'];
                    $ordergoods['payStatus'] = 1;
                    $ordergoods['delStatus'] = 1;
                    $m->sData($ordergoods, 'order_goods');
                    //删除购物车内收藏的商品
                    $sql = "delete from `" . $m->prefix . "users_shoppingcart` where userid=$userinfo[id] and goodsid=$key";
                    $m->query($sql);
                }
                //如果用了优惠券
                if (isset($_SESSION['ConfirmAnOrderGoods']['couponId']) and $_SESSION['ConfirmAnOrderGoods']['couponId'] != 0) {
                    $couponId = $_SESSION['ConfirmAnOrderGoods']['couponId'];
                    $data2['orderSn'] = $orderSn;
                    $data2['status'] = 4;
                    $m->sData($data2, 'coupon_issue', 'u', "id=$couponId");
                }
            }
            
            //前往收银台
            $_SESSION['ConfirmAnOrderGoods'] = null;
            header("Location:" . createLink('pay/collectMoney', array('id' => $data['split_sn'])));
            exit();
            //输出消息  end
            
        } else {
            $this->assign('freightid', $_SESSION['ConfirmAnOrderGoods']['freightid']);
            $this->assign('freight_name', $_SESSION['ConfirmAnOrderGoods']['freight_name']);

            $this->assign('paymentid', $_SESSION['ConfirmAnOrderGoods']['paymentid']);
            $this->assign('payment_name', $_SESSION['ConfirmAnOrderGoods']['payment_name']);
            //  end

            $this->assign('webtitle_', '确认订单'); //layout_title
            $this->assign('layout_title', '确认订单'); //layout_title
            $this->display('goods/ConfirmAnOrder');
        }
    }

    /**
     * 订单确认页面 - 价格计算
     * @param type $userid 用户ID
     * @param type $goodsData 商品
     * @param type $business_no 商户号
     * @param type $store_id 发货仓库ID
     */
    public function priceComputation($userid , $goodsData , $business_no,$store_id) {
        $m = new \core\model();
        $redis = new \models\yredis();
        
         //查询商户信息
        $d['business'] = $m->getrow("select business_no,business_name from `".SQLPRE."business` where business_no=$business_no");
        $d['goodstotal'] = 0; //商品价格总计
        $d['total'] = 0; //应付款
        $d['freight'] = 0; //运费
        $d['Preferential'] = 0; //优惠
        
        //判断有没有商品
        if (!empty($goodsData) and is_array($goodsData) and count($goodsData) > 0) {
            if( !$goodsData or !  is_array($goodsData) or count($goodsData)<=0 ){
                message('商品已经下架或者已经删除，请重新选购。');
            }
            $weight = 0;
            $result = array();
            $goods_data_num = $_SESSION['ConfirmAnOrderGoods']['goods'];  //为了取购物车商品数据
            foreach ($goodsData as $key => $value) {
                $value['num'] = $goods_data_num[$key];
                $stores_goods_num = $redis->get(REDIS_PRE."stores_goods_".$store_id."_".$key);
                if( $value['num']>intval($stores_goods_num) ){
                    $value['quehuo'] = 1;//仓库缺货
                }
                //取用户的购买记录， 用作限购判断 
                $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$key)?:0;
                //限购判断 ： 已经购买过的数量 + 新添加的数量  > 限购数量
                if( $value['xiangou']>0 && ($value['xiangouuser']+$value['num'])>$value['xiangou'] ){
                    msg("$value[name] $value[attributeStr] <br />已超限购(每日限购：$value[xiangou] )<br />请返回 <a href='/?v=/user/shoppingcart'> 购物车 </a> 重新选择" );
                }
                
                //库存判断：库存低于警界数，或者购买数量已经超过了库存数量
                if( $value['numbers']<KUCUNBUZU || $value['num']>$value['numbers'] ){
                    msg("非常抱歉，$value[name] $value[attributeStr] 库存不足！<br />请返回 <a href='/?v=/user/shoppingcart'> 购物车 </a> 重新选择" );
                }
                
                $value['shopPriceTotal'] = round($value['shopPrice'], 2) * round($value['num'], 2);
                $result[$key] = $value;
                $d['goodstotal'] += round($value['shopPriceTotal'], 2);
                $weight += round($value['weight'], 2) * $value['num'];
            }
            $d['weight'] = $weight;
            //运费计算  start
            $addressid = @$_SESSION['ConfirmAnOrderGoods']['addressid'];
            $freightid = @$_SESSION['ConfirmAnOrderGoods']['freightid'];
            if (!empty($addressid) and ! empty($freightid)) {
                $info = $m->getrow("select id,proviceSn,citySn,countySn,townSn from " . $m->prefix . "users_address where id=$addressid");
                $d['freight'] = $this->freightComputation($weight, $freightid, $info['proviceSn'], $info['citySn'], $info['countySn'], $info['townSn']);
            }
            //运费计算  end
            
//            //用户等级的优惠折扣 begin
//            $sql2 = "select r.discount from ".SQLPRE."users u, ".SQLPRE."user_rank r where u.userRank=r.id and u.id=$userid";
//            $discount = $m->getone($sql2);
//            $d['Preferential'] = round($d['goodstotal'],2) - round($d['goodstotal'] * ($discount/100),2);
//            // end
            
//            //计算系统配置的 ， 订单满 多少 减运费
//            if( round($d['goodstotal'],2) > (int)$GLOBALS['config']['freightdiscount'] ){
//                $d['freight'] = 0;
//            }
            
            $d['coupon'] = $this->useCoupon($d['goodstotal'] + $d['freight'] - $d['Preferential']);
            $d['total'] = round($d['goodstotal'], 2) + round($d['freight'], 2) - round($d['Preferential'], 2) - round($d['coupon'], 2);
            $d['goodslist'] = $result;

            return $d;
        } else {
            return false;
        }
    }

    /**
     * 判断优惠券使用
     * @param type $total  价格  商品总价+运费+优惠后的费用
     */
    public function useCoupon($total) {
        if (!isset($_SESSION['ConfirmAnOrderGoods']['couponId']) or empty($_SESSION['ConfirmAnOrderGoods']['couponId'])) {
            return 0;
        } else {
            $m = new \core\model();
            $couponId = $_SESSION['ConfirmAnOrderGoods']['couponId'];
            $sql = "select money from `" . $m->prefix . "coupon_issue` where id=$couponId and status=1 and amount<=$total and orderSn is null and now()<endTime";
            $couponMoney = $m->getone($sql);
            if (empty($couponMoney)) {
                return 0;
            }
            return floatval($couponMoney);
        }
    }

    /**
     * 订单确认页面 - 计算运费
     * @param type $weight  商品的总重量
     * @param type $freightid  选择的配送方式ID 
     */
    public function freightComputation($weight = null, $freightid = null, $provice = null, $city = null, $county = null, $town = null) {
        $total = 0;
        if (@empty($weight) or @ empty($freightid)) {
            return $total;
        }
        $m = new \core\model();
        //四个级别，从上到下只要有一个匹配到就不再往下匹配
        $lev1 = $provice . ',' . $city . ',' . $county . ',' . $town . ',';
        $lev2 = $provice . ',' . $city . ',' . $county . ',';
        $lev3 = $provice . ',' . $city . ',';
        $lev4 = $provice . ',';

        $data = null;
        $data = $m->getrow("select * from " . $m->prefix . "separatefreight where area='$lev1' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . $m->prefix . "separatefreight where area='$lev2' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . $m->prefix . "separatefreight where area='$lev3' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . $m->prefix . "separatefreight where area='$lev4' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . $m->prefix . "separatefreight where area='' and deliverId='$freightid'");

        //如果首重和续重为都 等于0  ，运费直接返回0
        if ((float) $data['secondWeight'] == 0 and (float) $data['firstWeight'] == 0) {
            return 0;
        }

        //重量如果小于首重或等 于首重，按首重价格算。
        if ($weight <= (float) $data['firstWeight']) {
            $total+=$data['firstPrice'];
        } else {
            $total+=$data['firstPrice'];  //首重价格
            if ((float) $data['firstWeight'] <= 0 and (float) $data['secondWeight'] > 0) {  //如果首重为0,续重不为0
                $q = intval(($weight - $data['firstWeight']) / $data['secondWeight']);
                $h = ($weight - $data['firstWeight']) / $data['secondWeight'];
                $total+=$q * $data['secondPrice'];  //续重价格
                if ($h > $q) {
                    $total+=$data['secondPrice'];  //续重不足一斤算一斤的
                }
            } elseif ((float) $data['secondWeight'] <= 0 and (float) $data['firstWeight'] > 0) {//如果首重大于0,续重为0
            } else {   //如果 两个重量 都 不为0
                $q = intval(($weight - $data['firstWeight']) / $data['secondWeight']);
                $h = ($weight - $data['firstWeight']) / $data['secondWeight'];
                $total+=$q * $data['secondPrice'];  //续重价格
                if ($h > $q) {
                    $total+=$data['secondPrice'];  //续重不足一斤算一斤的
                }
            }
        }
        return $total;
    }

    /**
     * 商品加入购物车。
     */
    public function addcart() {
        $m = new \core\model();
        $redis = new \models\yredis();
        $userInfo = loginJudbe();
        $goodsid = $this->_get('goodsid');
        $num = $this->_get('num');
        
        //库存判断 
        $numbers = $redis->get(REDIS_PRE.'goods_numbers_'.$goodsid); 
        //库存判断：库存低于警界数，或者购买数量已经超过了库存数量
        if( $numbers<KUCUNBUZU || $num>$numbers ){
            echo json_encode(array('status' => 3));exit();  //库存不足
        }
        
        $cartData['goodsid'] = $goodsid;
        $cartData['userid'] = $userInfo['id'];
        $cartData['goodsnum'] = $num;

        //如果没有登录
        if ( $userInfo ) {
            
            //检查购物车中是否已经添加了这个商品
            $r = $m->getrow("select id,goodsnum from `" . $m->prefix . "users_shoppingcart` us where userid=$userInfo[id] and goodsid=$goodsid");
            $cartDataId = $r['id']?:0;
            $goodsnum = $r['goodsnum']?:0;
            
            //限购判断
            $xiangouuser = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userInfo['id'].'-'.$goodsid );
            $xiangou = $m->getone("select xiangou from `".SQLPRE."activity_goods` where goodsid=$goodsid and aid > 0");
            //限购判断 ： 已经购买过的数量 + 新添加的数量 + 购物车里已经添加的数量
            if(  $xiangou>0 && $xiangouuser+$num+$goodsnum >=$xiangou ){
                echo json_encode(array('status' => 4));exit();  //已经超过限购件数
            }
            
            if ( !$cartDataId ) {
                //检查购物车中的商品是不是已经放满了。
                $goodscount = $m->getone("select count(id) as count from `" . $m->prefix . "users_shoppingcart` where userid=$userInfo[id]");
                if ($goodscount >= 12) {
                    echo json_encode(array('status' => 3));
                    exit(); //购物车满状态 
                }
                $m->sData($cartData, 'users_shoppingcart');
            } else {
                $cartData['goodsnum'] = "`goodsnum`+$num---";
                $m->sData($cartData, 'users_shoppingcart', 'u', "id=$cartDataId");
            }
            echo json_encode(array('status' => 1));exit();
        }else{
            echo json_encode(array('status' => 2));exit();
        }
        
    }

    /**
     * 购物车总数
     * 
     * @param type $param
     */
    public function shopcartTotal() {
        $m = new \core\model();
        $userInfo = loginJudbe();
        $count = $m->getone("select sum(goodsnum) as count from `" . $m->prefix . "users_shoppingcart` where userid=$userInfo[id]");
        $count = $count? : 0;
        echo json_encode(array('total' => $count));
        exit();
    }

    /**
     * 购物车列表
     */
    public function shoppingcartList() {
        header("Location: /?v=/user/shoppingcart"); exit();
//        $m = new \core\model();
//        $userInfo = loginJudbe();
//        $goodslist = array();
//        if ($userInfo) {
//            $userId = $userInfo['id'];
//            $sql = "select us.*,ga.addStatus, "
//                    . "if(a.id is not null,ag.aprice,ga.shopPrice) as goodsprice"
//                    . " from `" . $m->prefix . "users_shoppingcart` us "
//                    . " left join `" . $m->prefix . "goods_additional` ga on us.goodsid=ga.id "
//                    . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
//                    . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
//                    . " where userid=$userId";
//            $goodslist = $m->getall($sql);
//        } else {
//            $goodslist = @$_SESSION['shopcartList'];
//        }
//        $this->assign('goodslist', $goodslist);
//        $this->assign('layout_title', '我的购物车');
//        $this->display('user/shoppingcart');
    }

    /**
     * 更新购物车
     */
    public function upshoppingcart() {
        $userInfo = loginJudbe();
        $m = new \core\model();
        $id = $m->g($this->_get('id'));
        $num = $m->g($this->_get('num'));
        if (intval($num) <= 0) {
            if ($userInfo) {
                $sql = "delete from `" . $m->prefix . "users_shoppingcart` where goodsid=$id and userid=$userInfo[id]";
                $m->query($sql);
                echo json_encode(array('status' => 2));
                exit();
            } else {
                $goodslist = $_SESSION['shopcartList'];
                unset($goodslist[$id]);
                $_SESSION['shopcartList'] = $goodslist;
            }
        } else {

            if ($userInfo) {
                $redis = new \models\yredis();
                //限购判断
                $xiangouuser = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userInfo['id'].'-'.$id );
                $xiangou = $m->getone("select xiangou from `".SQLPRE."activity_goods` where goodsid=$id and aid > 0");
                //限购判断 ： 已经购买过的数量 + 新添加的数量 + 购物车里已经添加的数量
                if( $xiangou>0 && $xiangouuser+$num >$xiangou ){
                    echo json_encode(array('status' => 4));exit();  //已经超过限购件数
                }

                $sql = "update `" . $m->prefix . "users_shoppingcart` set goodsnum=$num where goodsid=$id and userid=$userInfo[id]";
                $m->query($sql);
            } else {
                $goodslist = $_SESSION['shopcartList'];
                $goodslist[$id]['goodsnum'] = $num;
                $_SESSION['shopcartList'] = $goodslist;
            }
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 选择收货地址
     */
    public function selectAddress() {
        $id = $this->_get('id');
        $_SESSION['ConfirmAnOrderGoods']['addressid'] = $id;
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 选择配送方式
     */
    public function selectFreight() {
        $id = $this->_get('id');
        $name = $this->_get('name');
        $_SESSION['ConfirmAnOrderGoods']['freightid'] = $id;
        $_SESSION['ConfirmAnOrderGoods']['freight_name'] = $name;
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 选择优惠券
     */
    public function selectCoupon() {
        $m = new \core\model();
        $id = $this->rget('id');
        $userInfo = loginJudbe();
        if ($id) {
            $couponUserid = $m->getone("select userId from `" . $m->prefix . "coupon_issue` where id=$id and status=1");
            //判断是不是用的自己的优惠券
            if ($couponUserid == $userInfo['id']) {
                $_SESSION['ConfirmAnOrderGoods']['couponId'] = $id;
                echo json_encode(array('status' => 1));
            }
        } else {
            unset($_SESSION['ConfirmAnOrderGoods']['couponId']);
            echo json_encode(array('status' => 0));
        }
        exit();
    }

    /**
     * json 获取区域, 省 、市 、区信息
     */
    public function getaddress() {

        $act = $this->_get("act");
        $id = $this->_get("id");
//        if(!$id) echo null;

        $m = new \core\model();
        $list = null;
        switch ($act) {
            case 'city':  //市
                $list = $m->getall("select * from " . $m->prefix . "area_city where province_id=$id");
                break;
            case 'county':   //区、县
                $list = $m->getall("select * from " . $m->prefix . "area_county  where city_id =$id");
                break;
            case 'town':  //街道、乡镇
                $list = $m->getall("select * from " . $m->prefix . "area_town  where county_id =$id");
                break;
            //默认返回省份
            default:
                $list = $m->getall("select * from " . $m->prefix . "area_provice");
                break;
        }
        echo json_encode($list);
    }

    /**
     * 商品分类 
     */
    public function category() {
        $cate = $this->rget("id");
        if ($cate) {
            $m = new \models\goods();
            $p = $this->rget('p')? : 1;
            $nums = 4;
            $data = $m->goodslist($cate, $p, $nums, $this->rget(), $this->rget('price'), $this->rget('sort'));
            $this->assign('goodsCatetroyPosData', $m->goodsCatetroyPos($cate));

            if ($data['type'] == 'cate') {
                $this->assign('cateid', $cate);
                $this->assign('cateList', $data['cateList']);
                $this->assign('info', $data['info']);
            } elseif ($data['type'] == 'list') {

                $urldata = null;
                if (!empty($data['attrurl']))
                    $urldata = goodsurlattr($data['attrurl']);
                //分页初始化参数
                $params = array(
                    'total_rows' => $data['count'],
                    'now_page' => $p,
                    'list_rows' => $nums,
                    'parameter' => 1,
                    'method' => 'ajax',
                    'ajax_func_name' => 'publicMethod.nextpage',
                );
                $page = new \lib\page($params);
                $pageinfo = $page->show(5);

                if (judgeAjaxRequest()) {
                    echo json_encode(array('data' => $data['list'], 'page' => $pageinfo));
                    exit();
                } else {
                    $this->assign('info', $data['info']);
                    if (isset($data['priceMin']))
                        $this->assign('priceMin', $data['priceMin']);
                    if (isset($data['priceMax']))
                        $this->assign('priceMax', $data['priceMax']);
                    if (isset($data['priceSection']))
                        $this->assign('priceSection', $data['priceSection']);
                    if (isset($data['attrurl']))
                        $this->assign('attrurl', $data['attrurl']);
                    if (isset($data['attrD']))
                        $this->assign('attrD', $data['attrD']);
                    $this->assign('goodslist', $data['list']);
                    $this->assign('pageinfo', $pageinfo);
                }
            }
            $this->assign('webtitle_', '商品分类');
            $this->assign('layout_title', '商品分类');
            $this->display('goods/categoryInfo');
        } else {
            $this->assign('webtitle_', '商品分类');
            $this->assign('layout_title', '商品分类');
            $this->display('goods/category');
        }
    }

    public function search() {
        $m = new \core\model();
        $keywords = $m->g($this->_get('keywords'));
        $p = isset($_REQUEST['p'])?$_REQUEST['p']: 1;
        $listrows = 10;
        $start = $p * $listrows - $listrows;
        
        $sql = "select ga.id from `" . SQLPRE . "goods` g ,`" . SQLPRE . "goods_additional` ga where g.id=ga.goodsId and ( g.name like '%$keywords%' or ga.attributeStr like '%$keywords%' ) and ga.addStatus=1  limit $start,$listrows ";
        $res = $m->getall($sql);
        
        $sql = "select count(ga.id) as count from `" . SQLPRE . "goods` g ,`" . SQLPRE . "goods_additional` ga where g.id=ga.goodsId and ( g.name like '%$keywords%' or ga.attributeStr like '%$keywords%' ) and ga.addStatus=1 ";
        $count = $m->getone($sql);
        $list = array();
        foreach ($res as $key => $value) {
            $redis = new \models\yredis();
            $list[$value['id']] = json_decode($redis->get(REDIS_PRE . 'goods_' . $value['id']), 1);
        }

        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);

        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
            exit();
        } else {
            $this->assign('goodslist', $list);
            $this->assign('pageinfo', $pageinfo);
            $this->display('goods/search');
        }
    }

    /**
     * 商品评价
     */
    public function comment() {
        $m = new \core\model();
        $goodsid = $this->rget('goodsid');
        //page 数据
        $p = $this->rget('p') ? : 1;
        $listrows = 10;
        $start = $p * $listrows - $listrows;

        $sql = "select gc.*,u.nickname,u.avatar from `" . $m->prefix . "goods_comment` gc "
                . "left join `" . $m->prefix . "users` u on gc.userid=u.id"
                . "  where gc.goodsid=$goodsid and gc.status=1 order by id desc limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from `" . $m->prefix . "goods_comment`  where goodsid=$goodsid");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
            exit();
        } else {
            $this->assign('webtitle_', '商品评论');
            $this->assign('layout_title', '商品评论');
            $this->assign("list", $list);
            $this->assign("pageinfo", $pageinfo);
            $this->display('goods/comment');
        }
    }

    /**
     * 订单确认页面 - 选择支付方式
     */
    public function selectPayment() {
        $sn = $this->_get('sn');
        $name = $this->_get('name');
        $_SESSION['ConfirmAnOrderGoods']['paymentid'] = $sn;
        $_SESSION['ConfirmAnOrderGoods']['selectpay'] = 1;
        $_SESSION['ConfirmAnOrderGoods']['payment_name'] = $name;
        echo json_encode(array('status' => 1));
        exit();
    }

}
