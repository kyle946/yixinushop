<?php

/**
 * 订单支付  
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class payController extends comController {

    /**
     * 收银台
     */
    public function collectMoney() {
        $userInfo = loginJudbe();
        if ($userInfo) {
            $id = rget('id');
            $m = new \core\model();
            $sql = "select o.id,o.orderSn,o.deliveryMethod,o.payType,o.amount,o.name,o.mobile, o.sortsn,"
                    . "og.goodsname,og.goodsattributeStr,og.goodssn,og.goodsnum from `" . SQLPRE . "orders` o "
                    . " left join `" . SQLPRE . "order_goods`  og on o.id=og.orderid  "
                    . " where o.id='$id' or o.orderSn='$id'";
            $orderInfo = $m->getrow($sql);
            $this->assign('info', $orderInfo);
            $this->assign('orderSn', $orderInfo['orderSn']);
            $this->assign('sortsn', $orderInfo['sortsn']);
            $this->assign('orderid', $orderInfo['id']);
            $this->assign('total', $orderInfo['amount']);
            $payment = $m->getrow("select logo,sn,config from " . SQLPRE . "payment where sn='$orderInfo[payType]'");
            //合并 config 到数组中
            $config_ = $payment['config'];
            unset($payment['config']);
            $config = unserialize($config_);
            if (is_array($config) and ! empty($config)) {
                $payment = array_merge($payment, $config);
            }
            $this->assign('payment', $payment);
            $deliveryMethod = $m->getone("select name from " . SQLPRE . "deliverys where id=$orderInfo[deliveryMethod]");
            $this->assign('deliveryMethod', $deliveryMethod);
            $minute5 = $m->getone("select `createTime` + INTERVAL 120 MINUTE AS ct from " . SQLPRE . "orders where id=$orderInfo[id]");
            $this->assign('minute5', $minute5);

            //根据支付方式显示不同的页面
            if ($payment['sn'] == 'weixinPay') {
                require_once APP_PATH . 'pay/weixinPay/WxPay.Api.php';
                require_once APP_PATH . 'pay/weixinPay/WxPay.NativePay.php';

                /**
                 * 微信扫码支付  模式二
                 * 
                 * 流程：
                 * 1、调用统一下单，取得code_url，生成二维码
                 * 2、用户扫描二维码，进行支付
                 * 3、支付完成之后，微信服务器会通知支付成功
                 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
                 */
                $input = new \WxPayUnifiedOrder();
                $input->SetBody("购买: $orderInfo[goodsname]……"); //商品或支付单简要描述
                $input->SetAttach($orderInfo['orderSn']); //附加数据，在查询API和支付通知中原样返回
                $input->SetOut_trade_no("$orderInfo[orderSn]");  //订单号
                $input->SetTotal_fee($orderInfo['amount'] * 100);  //支付金额
                $input->SetTime_start(date("YmdHis"));   //交易开始时间 
                $input->SetTime_expire(date("YmdHis", time() + 7200)); //交易过期时间 
//                $input->SetGoods_tag("test");   //设置商品标记
                $input->SetNotify_url('http://' . $_SERVER['HTTP_HOST'] . '/pay/callbackwxpay/');  //异步通知回调地址
                $input->SetTrade_type("NATIVE");  //交易类型
                $input->SetProduct_id("$orderInfo[goodssn]");  //商品ID，trade_type=NATIVE，此参数必传。此id为二维码中包含的商品ID，商户自行定义
                $notify = new \NativePay();
                $result = $notify->GetPayUrl($input);
                $url2 = null;
                if (isset($result["code_url"])) {
                    $url2 = $result["code_url"];
                } else {
                    msg($result['err_code_des']);
                }
                $this->assign('url2', base64_encode($url2));
                $this->display('goods/collectMoney');
            } elseif($payment['sn'] == 'transfer'){
                $this->display('goods/collectMoney');
            } else {
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
     * 中国银联在线支付
     */
    public function unionpay() {
        $id = $this->rget('id');
        $m = new \core\model();

        $sql = "select * from " . $m->prefix . "orders where id=$id";
        $orderinfo = $m->getrow($sql);

        $sql = "select * from `" . $m->prefix . "payment` where sn='$orderinfo[payType]'";
        $payinfo = $m->getrow($sql);

        //合并 config 到 $payinfo 数组中
        $config_ = $payinfo['config'];
        unset($payinfo['config']);
        $config = unserialize($config_);
        $payinfo = array_merge($payinfo, $config);

        $pay = new \pay\unionpay\main();
        $pay->start($orderinfo, $payinfo);
    }

    /**
     * 中国银联在线支付    接口前台通知
     */
    public function unionpayFront() {
        //如果签名不为空
        if (isset($_POST ['signature'])) {

            $pay = new \pay\unionpay\main();
            $pay->configTest();

            global $log;
            $log = new \pay\unionpay\func\PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
            include_once PAYPATH . 'func/common.php';
            include_once PAYPATH . 'func/PinBlock.php';
            include_once PAYPATH . 'func/PublicEncrypte.php';
            include_once PAYPATH . 'func/secureUtil.php';

            //如果验签成功
            if (verify($_POST)) {
                $orderSn = $_POST ['orderId']; //其他字段也可用类似方式获取
                $tradeSn = $_POST['queryId'];
                $up = new \models\uporder($orderSn, $tradeSn);
                $up->exec();
                header("Location: ?c=user&a=orderinfo&sn=$orderSn");
                exit();
            }
        }
// 字段说明 https://open.unionpay.com/ajweb/help2/api?id=45&level=3
//Array
//(
//    [accNo] => 6216***********0018
//    [accessType] => 0
//    [bizType] => 000201
//    [certId] => 3474813271258769001041842579301293446
//    [currencyCode] => 156
//    [encoding] => utf-8
//    [merId] => 777290058115993
//    [orderId] => 150709024029664
//    [queryId] => 201507090240308845448
//    [reqReserved] => 透传信息
//    [respCode] => 00
//    [respMsg] => success
//    [settleAmt] => 400
//    [settleCurrencyCode] => 156
//    [settleDate] => 0708
//    [signMethod] => 01
//    [signature] => IRsMmwL9dP2Ff11bzgRNmW3HRE30VUt9WcgLIktZX7OtjR0vtYGd5mNjNomj7SrbnoriXHIbUe7K+XuDPShawIrqdxkl+HEiSqPLzUgQLKLsoUYn2c90GCFJAO01UaWrG1ROsXK/f09Ga9SlB1h9WpN7GXOJd6lKbFA73Vye+BaZVK+H2diM4KsCNY80/xqv/83WjuGuWh/oP9aRXefUsR0ehXqh1H31J7oaJrkWOTb9KO2FlhI9Xc1uLD0P0MPaiTEFPaNQIgtRc/o5NSLDYiNR82j4uZYJiB2rnQfHyb05agXVIeKLJ8CvViyfvZrDocL/dIiKM6am6VUKbkZvTQ==
//    [traceNo] => 884544
//    [traceTime] => 0709024030
//    [txnAmt] => 400
//    [txnSubType] => 01
//    [txnTime] => 20150709024030
//    [txnType] => 01
//    [version] => 5.0.0
//)
    }

    /**
     * 中国银联在线支付    接口后台通知
     */
    public function unionpayBack() {
        //如果签名不为空
        if (isset($_POST ['signature'])) {

            $pay = new \pay\unionpay\main();
            $pay->configTest();

            global $log;
            $log = new \pay\unionpay\func\PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
            include_once PAYPATH . 'func/common.php';
            include_once PAYPATH . 'func/PinBlock.php';
            include_once PAYPATH . 'func/PublicEncrypte.php';
            include_once PAYPATH . 'func/secureUtil.php';

            //如果验签成功
            if (verify($_POST)) {
                $orderSn = $_POST ['orderId']; //其他字段也可用类似方式获取
                $tradeSn = $_POST['queryId'];
                $up = new \models\uporder($orderSn, $tradeSn);
                $up->exec();
            }
        }
    }

    /**
     * 支付宝支付
     */
    public function alipayj() {
        $userInfo = loginJudbe();
        if ($userInfo) {
            $id = rget('id');
            $m = new \core\model();
            $sql = "select o.id,o.orderSn,o.deliveryMethod,o.payType,o.amount,o.name,o.mobile, "
                    . "og.goodsname,og.goodsattributeStr,og.goodssn,og.goodsnum from `" . SQLPRE . "orders` o "
                    . " left join `" . SQLPRE . "order_goods`  og on o.id=og.orderid  "
                    . " where o.id='$id' or o.orderSn='$id'";
            $orderInfo = $m->getrow($sql);
        }else{
            msg('用户未登录 ！ 1004');
        }

        echo ' <!DOCTYPE html> <html> <head>   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">   <title>支付宝即时到账交易接口接口</title> </head>';

        require_once( APP_PATH . 'pay/alipay/alipay.config.php');
        require_once( APP_PATH . 'pay/alipay/lib/alipay_submit.class.php');
        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $orderInfo['orderSn'];
        //订单名称，必填
        $subject = '支付订单： '. $orderInfo['orderSn'];
        //付款金额，必填
        $total_fee = $orderInfo['amount'];
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
            "anti_phishing_key" => $alipay_config['anti_phishing_key'],
            "exter_invoke_ip" => $alipay_config['exter_invoke_ip'],
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "_input_charset" => trim(strtolower($alipay_config['input_charset']))
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
        require_once( APP_PATH . 'pay/alipay/alipay.config.php');
        require_once( APP_PATH . 'pay/alipay/lib/alipay_notify.class.php');
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
                header("Location: ".  createLink('user/index') );
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
        require_once( APP_PATH . 'pay/alipay/alipay.config.php');
        require_once( APP_PATH . 'pay/alipay/lib/alipay_notify.class.php');
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
