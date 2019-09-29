<?php

/**
 * 订单支付  
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;

class payController extends comController {

    /**
     * 收银台
     */
    public function collectMoney() {
        $userInfo = loginJudbe();
        if ($userInfo) {
            $id = rget('id');
            $m = new \core\model();
            $sql = "select o.*,b.business_name from `" . SQLPRE . "orders` o left join ".SQLPRE."business b on o.business_no = b.business_no  where ( o.id='$id' or o.orderSn='$id' or o.split_sn='$id' ) and userId=$userInfo[id]";
            $order_info_list = $m->getall($sql);
            if( count($order_info_list)==1 ){
                $orderInfo = $order_info_list[0];
                $this->assign('info', $orderInfo);
                $this->assign('orderSn', $orderInfo['orderSn']);
                $this->assign('total', $orderInfo['amount']);
                
                //查询支付方式的logo
                $payment = $m->getrow("select logo,sn,config from " . SQLPRE . "payment where sn='$orderInfo[payType]'");
                //合并 config 到数组中
                $config_ = $payment['config'];
                unset($payment['config']);
                $config = unserialize($config_);
                if (is_array($config) and ! empty($config)) {
                    $payment = array_merge($payment, $config);
                }
                $this->assign('payment', $payment);
                
                //根据支付方式显示不同的页面
                if ($payment['sn'] == 'weixin01') {  //如果支付方式为微信支付 
                    require_once APP_PATH . 'pay/weixinPay/WxPay.Api.php';
                    require_once APP_PATH . 'pay/weixinPay/WxPay.JsApiPay.php';
                    // 获取用户openid
                    $openId = $m->getone("select wxopenid from `".SQLPRE."users` where id=$userInfo[id]");
                    $tools = new \JsApiPay();
                    if(!$openId){
                        $openId = $tools->GetOpenid();
                    }
                    // 统一下单
                    $input = new \WxPayUnifiedOrder();
                    $input->SetBody("订单:$orderInfo[orderSn] , $orderInfo[goodsname]……"); //商品或支付单简要描述
                    $input->SetAttach($orderInfo['orderSn']); //附加数据，在查询API和支付通知中原样返回
    //                $input->SetBody("test");
    //                $input->SetAttach("test");
                    $input->SetOut_trade_no("$orderInfo[orderSn]");  //订单号
                    $input->SetTotal_fee($orderInfo['amount'] * 100);  //支付金额  分
                    $input->SetTime_start(date("YmdHis"));   //交易开始时间 
                    $input->SetTime_expire(date("YmdHis", time() + 7200)); //交易过期时间 
    //                $input->SetGoods_tag("test");   // 商品标记
                    $input->SetNotify_url('http://' . $_SERVER['HTTP_HOST'] . '/pay/callbackwxpay/');   // 异步通知回调地址
                    $input->SetTrade_type("JSAPI");  //交易类型
                    $input->SetOpenid($openId);
                    $order = \WxPayApi::unifiedOrder($input);
                    $jsApiParameters = $tools->GetJsApiParameters($order);
                    $this->assign('jsApiParameters', $jsApiParameters);
                    $this->display('goods/collectMoney');
                } else {
                    //如果不是用微信支付方式
                    $this->display('goods/orderDone');
                }
            }else{
                    $this->assign('order_list', $order_info_list);
                    $this->display('goods/orderDone');
            }

        } else {
            header("Location: " . createLink('u/login'));
            exit();
        }
    }

    /**
     * 微信支付回调地址
     */
    public function callbackwxpay() {
        require_once APP_PATH . 'pay/weixinPay/WxPay.Api.php';  
        require_once APP_PATH . 'pay/weixinPay/WxPay.Data.php'; 
        require_once APP_PATH . 'pay/weixinPay/WxPay.Notify.php';
        require_once APP_PATH . 'pay/weixinPay/notify.php';
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);
    }
    

    /**
     * 支付宝支付
     */
    public function alipaym() {
        $userInfo = loginJudbe();
        if ($userInfo) {
            $id = rget('id');
            $m = new \core\model();
            $sql = "select o.id,o.orderSn,o.deliveryMethod,o.payType,o.amount,o.name,o.mobile, "
                    . "og.goodsname,og.goodsattributeStr,og.goodssn,og.goodsnum,og.goodsid from `" . SQLPRE . "orders` o "
                    . " left join `" . SQLPRE . "order_goods`  og on o.id=og.orderid  "
                    . " where o.id='$id' or o.orderSn='$id'";
            $orderInfo = $m->getrow($sql);
        }else{
            msg('用户未登录 ！ 1004');
        }

        echo ' <!DOCTYPE html> <html> <head>   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">   <title>支付宝即时到账交易接口接口</title> </head>';

        require_once( APP_PATH . 'pay/alipaym/alipay.config.php');
        require_once( APP_PATH . 'pay/alipaym/lib/alipay_submit.class.php');
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $orderInfo['orderSn'];
        //订单名称，必填
        $subject = '支付订单： '. $orderInfo['orderSn'];
        //付款金额，必填
        $total_fee = $orderInfo['amount'];
        //收银台页面上，商品展示的超链接，必填
        $show_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/item/id_'.$orderInfo['goodsid']; //同步
        //商品描述，可空
        $body = "[yixinu] $orderInfo[goodsname],数量:$orderInfo[goodsnum]  ……";
        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => $alipay_config['service'],
            "partner" => $alipay_config['partner'],
            "seller_id" => $alipay_config['seller_id'],
            "payment_type" => $alipay_config['payment_type'],
            "notify_url" => 'http://' . $_SERVER['HTTP_HOST'] . '/pay/callbackalipay/', //异步 
            "return_url" => 'http://' . $_SERVER['HTTP_HOST'] . '/pay/alipay/', //同步
            "_input_charset" => trim(strtolower($alipay_config['input_charset'])),
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "show_url"	=> $show_url,
            "body" => $body
        );

        //建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        $html_text = $alipaySubmit->buildRequestForm($parameter, "post", "确认");
        echo $html_text;
        echo '</body></html>';
        exit();
    }

    /**
     * 支付宝  同步通知 回调
     */
    public function alipay() {
        require_once( APP_PATH . 'pay/alipaym/alipay.config.php');
        require_once( APP_PATH . 'pay/alipaym/lib/alipay_notify.class.php');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyReturn();
        if ($verify_result) {//验证成功
            $out_trade_no = $_GET['out_trade_no']; //商户订单号
            $trade_no = $_GET['trade_no'];  //支付宝交易号 
            $trade_status = $_GET['trade_status'];   //交易状态
            if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                $up = new \models\uporder($out_trade_no,$trade_no);
                $up->exec();
                header("Location: ".  createLink('user/orderlist',array('s'=>1)) );  //跳转到我的订单
                exit();
            }
        } else {
            msg('支付宝 验证失败！');
        }
    }

    /**
     * 支付宝  异步通知  回调
     */
    public function callbackalipay() {
        require_once( APP_PATH . 'pay/alipaym/alipay.config.php');
        require_once( APP_PATH . 'pay/alipaym/lib/alipay_notify.class.php');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result) {//验证成功
            $out_trade_no = $_POST['out_trade_no']; //商户订单号
            $trade_no = $_POST['trade_no'];  //支付宝交易号 
            $trade_status = $_POST['trade_status'];   //交易状态
            if( $trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED' ) {
                $up = new \models\uporder($out_trade_no,$trade_no);
                $up->exec();
            }
        }
    }
    

    public function _empty() {
        message('支付方式未使用！');
    }

}
