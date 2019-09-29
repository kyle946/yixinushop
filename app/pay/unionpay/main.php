<?php

/**
 * 支付接口   中国银联
 * 
 * 公钥私钥文件说明（入网测试环境）：
 *      encrypt.cer：加密证书 （根据业务需求选用）
 *      PM_700000000000001_acp.pfx：商户私钥证书（签名）
 *      verify_sign_acp.cer：银联公钥证书（验签）
 * 
 * 公钥私钥文件说明（生产环境）：
 *      RSA2048_PROD_index_22.cer：加密证书 （根据业务需求选用）
 *      NULL：商户私钥证书（签名）（请联系业务运营中心获取两码 在CFCA网站下载）
 *      UpopRsaCert.cer：银联公钥证书（验签）
 * 
 * @link https://open.unionpay.com/ajweb/index 商家技术服务链接 
 * 
 * 网站账号信息：
 * user:xianglou2015
 * email:316686606@qq.com
 * pwd:alal2012

 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace pay\unionpay;

class main {

    public function __construct() {
        define('PAYPATH', APP_PATH . 'pay' . DIRECTORY_SEPARATOR . 'unionpay' . DIRECTORY_SEPARATOR);
    }

    /**
     * 开始支付
     * @param Array $orderinfo  订单信息 
     * @param Array $payinfo   支付方式信息
     */
    public function start($orderinfo = null,$payinfo = null) {
        if( empty($orderinfo) or empty($payinfo) ){
            message('参数错误！');
        }
        
        $runmode = $payinfo['runmode'];  //运行模式 
        
        
        // cvn2加密 1：加密 0:不加密
        define('SDK_CVN2_ENC', 0);
        // 有效期加密 1:加密 0:不加密
        define('SDK_DATE_ENC', 0);
        // 卡号加密 1：加密 0:不加密
        define('SDK_PAN_ENC', 0);

        if ($runmode == 1) {  //生产环境  
            $this->configProduction($payinfo);
        } elseif ($runmode == 2) {  //测试模式 
            $this->configTest($payinfo);
        }

//        include_once PAYPATH . 'func/PhpLog.php';
        global $log;
        $log = new func\PhpLog(SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL);
        include_once PAYPATH . 'func/common.php';
        include_once PAYPATH . 'func/PinBlock.php';
        include_once PAYPATH . 'func/PublicEncrypte.php';
        include_once PAYPATH . 'func/secureUtil.php';

//        $log->LogInfo("============处理前台请求开始===============");
        
        // 初始化日志
        $params = array(
            'version' => '5.0.0', //版本号
            'encoding' => 'utf-8', //编码方式
            'certId' => getSignCertId(), //证书ID
            'txnType' => '01', //交易类型	
            'txnSubType' => '01', //交易子类
            'bizType' => '000201', //业务类型
            'frontUrl' => SDK_FRONT_NOTIFY_URL, //前台通知地址
            'backUrl' => SDK_BACK_NOTIFY_URL, //后台通知地址	
            'signMethod' => '01', //签名方法
            'channelType' => '07', //渠道类型，07-PC，08-手机
            'accessType' => '0', //接入类型
            'merId' => $payinfo['partnerId'], //商户代码，请改自己的测试商户号  (777290058113963)
            'orderId' => $orderinfo['orderSn'], //商户订单号
            'txnTime' => date('YmdHis'), //订单发送时间
            'txnAmt' => $orderinfo['amount'] * 100, //交易金额，单位分
            'currencyCode' => '156', //交易币种
            'defaultPayType' => '0001', //默认支付方式	
            //'orderDesc' => '订单描述',  //订单描述，网关支付和wap支付暂时不起作用
            'reqReserved' => ' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
        );

        // 签名
        sign($params);
        
        // 前台请求地址
        $front_uri = SDK_FRONT_TRANS_URL;
//        $log->LogInfo ( "前台请求地址为>" . $front_uri );
        // 构造 自动提交的表单
        $html_form = create_html ( $params, $front_uri );

//        $log->LogInfo ( "-------前台交易自动提交表单>--begin----" );
//        $log->LogInfo ( $html_form );
//        $log->LogInfo ( "-------前台交易自动提交表单>--end-------" );
//        $log->LogInfo ( "============处理前台请求 结束===========" );
        
        echo $html_form;
        exit();
    }

    //正式环境配置
    public function configProduction($payinfo = null) {
        // 签名证书路径
        define('SDK_SIGN_CERT_PATH', PAYPATH . 'production' . DIRECTORY_SEPARATOR . $payinfo['partnerKeyFile']);  //   PM_700000000000001_acp.pfx
        // 签名证书密码
        define('SDK_SIGN_CERT_PWD', $payinfo['signCertPwd']);
        // 密码加密证书（这条用不到的请随便配）
        define('SDK_ENCRYPT_CERT_PATH', PAYPATH . 'production' . DIRECTORY_SEPARATOR . 'verify_sign_acp.cer');
        // 验签证书路径（请配到文件夹，不要配到具体文件）
        define('SDK_VERIFY_CERT_DIR', PAYPATH . 'production' . DIRECTORY_SEPARATOR);
        // 前台请求地址
        define('SDK_FRONT_TRANS_URL', 'https://gateway.95516.com/gateway/api/frontTransReq.do');
        // 后台请求地址
        define('SDK_BACK_TRANS_URL', 'https://gateway.95516.com/gateway/api/backTransReq.do');
        // 批量交易
        define('SDK_BATCH_TRANS_URL', 'https://gateway.95516.com/gateway/api/batchTrans.do');
        // 单笔查询请求地址
        define('SDK_SINGLE_QUERY_URL', 'https://gateway.95516.com/gateway/api/queryTrans.do');
        // 文件传输请求地址
        define('SDK_FILE_QUERY_URL', 'https://filedownload.95516.com/');
        // 有卡交易地址
        define('SDK_Card_Request_Url', 'https://gateway.95516.com/gateway/api/cardTransReq.do');
        // App交易地址
        define('SDK_App_Request_Url', 'https://gateway.95516.com/gateway/api/appTransReq.do');


        // 前台通知地址
        define('SDK_FRONT_NOTIFY_URL', 'http://'.$_SERVER["HTTP_HOST"].'/pay/unionpayFront/');
        // 后台通知地址
        define('SDK_BACK_NOTIFY_URL', 'http://'.$_SERVER["HTTP_HOST"].'/pay/unionpayBack/');


        // 文件下载目录
        define('SDK_FILE_DOWN_PATH', PAYPATH . 'production' . DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR);
        // 日志目录 
        define('SDK_LOG_FILE_PATH', PAYPATH . 'production' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR);
        // 日志级别 
        define('SDK_LOG_LEVEL', 'INFO');
    }

    //测试环境配置
    public function configTest() {
        // 签名证书路径
        define('SDK_SIGN_CERT_PATH', PAYPATH . 'test' . DIRECTORY_SEPARATOR . '700000000000001_acp.pfx');
        // 签名证书密码
        define('SDK_SIGN_CERT_PWD', '000000');
        // 密码加密证书（这条用不到的请随便配）
        define('SDK_ENCRYPT_CERT_PATH', PAYPATH . 'test' . DIRECTORY_SEPARATOR . 'verify_sign_acp.cer');
        // 验签证书路径（请配到文件夹，不要配到具体文件）
        define('SDK_VERIFY_CERT_DIR', PAYPATH . 'test' . DIRECTORY_SEPARATOR);
        // 前台请求地址
        define('SDK_FRONT_TRANS_URL', 'https://101.231.204.80:5000/gateway/api/frontTransReq.do');
        // 后台请求地址
        define('SDK_BACK_TRANS_URL', 'https://101.231.204.80:5000/gateway/api/backTransReq.do');
        // 批量交易
        define('SDK_BATCH_TRANS_URL', 'https://101.231.204.80:5000/gateway/api/batchTrans.do');
        // 单笔查询请求地址
        define('SDK_SINGLE_QUERY_URL', 'https://101.231.204.80:5000/gateway/api/queryTrans.do');
        // 文件传输请求地址
        define('SDK_FILE_QUERY_URL', 'https://101.231.204.80:9080/');
        // 有卡交易地址
        define('SDK_Card_Request_Url', 'https://101.231.204.80:5000/gateway/api/cardTransReq.do');
        // App交易地址
        define('SDK_App_Request_Url', 'https://101.231.204.80:5000/gateway/api/appTransReq.do');


        // 前台通知地址
        define('SDK_FRONT_NOTIFY_URL', 'http://'.$_SERVER["HTTP_HOST"].'/pay/unionpayFront/');
        // 后台通知地址
        define('SDK_BACK_NOTIFY_URL', 'http://'.$_SERVER["HTTP_HOST"].'/pay/unionpayBack/');


        // 文件下载目录
        define('SDK_FILE_DOWN_PATH', PAYPATH . 'test' . DIRECTORY_SEPARATOR . 'file' . DIRECTORY_SEPARATOR);
        // 日志目录 
        define('SDK_LOG_FILE_PATH', PAYPATH . 'test' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR);
        // 日志级别 
        define('SDK_LOG_LEVEL', 'INFO');
    }

}
