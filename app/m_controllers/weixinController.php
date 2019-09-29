<?php

/*
 * 处理微信公众号相关
 */

namespace m_controllers;

/**
 * Description of weixinController
 *
 * @author kyle
 */
class weixinController extends comController {

    public $m;
    public $info;

    public function __construct() {
        parent::__construct();
        $this->m = new \core\model();
        $sql = "select mark as yixinu,val from `" . SQLPRE . "shop_config`";
        $info = $this->m->getall($sql);
        
        $this->info = $this->m->getall("select mark as yixinu,val from `" . SQLPRE . "config2`");
        
        defined('APPID') or define('APPID', $info['weixinappid']['val']);
        defined('SECRET') or define('SECRET', $info['weixinsecret']['val']);
        defined('WEIXINTOKENCODE') or define('WEIXINTOKENCODE', $this->info['weixinTokenCode']['val']); 
    }

    /**
     * 
     */
    public function index() {
        include IFRAME_ROOT . '/api/weixinFunction.php';
        //如果开启了微信Token验证  begin
        $weixinToken = $this->m->getone("select val from `" . SQLPRE . "config2` where mark='weixinToken'");
        if ($weixinToken) {
            if (checkSignature()) {
                $echoStr = $_GET["echostr"];
                echo $echoStr;
                exit();
            }
        }
        // end
         
        
        //如果请求为POST
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            //微信签名验证
            if (checkSignature()) {
                $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
                if (!empty($postStr)) {
                    $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                    $fromUsername = $postObj->FromUserName; //发送人  
                    $toUsername = $postObj->ToUserName; //接收人  
                    $MsgType = $postObj->MsgType; //消息类型  
                    $MsgId = $postObj->MsgId; //消息id  
                    $time = time(); //当前时间做为回复时间

                    if ($MsgType == 'event') {
                        $Event = $postObj->Event;
                        //如果已经关注
                        if ($Event == 'SCAN') {
                            sendmsg('欢迎回来！' , $fromUsername, $toUsername);
                        }
                        //如果没有关注
                        if ($Event == 'subscribe') {
                            $string = '谢谢关注';
                            $EventKey = $postObj->EventKey;
                            sendmsg($string, $fromUsername, $toUsername);
                        }
                    }

                    if ($MsgType == 'text') {
                        $content = trim($postObj->Content);
                        if (!empty($content)) {
                            $userinfo = wxuserinfo($fromUsername);
                            switch ($content) {
                                case '你好':
                                    sendmsg($userinfo['nickname'] . '，你好，谢谢关注！', $fromUsername, $toUsername);
                                    break;
                                default:
                                    sendmsg($userinfo['nickname'] . '，'.$this->info['weixinMsg1']['val'], $fromUsername, $toUsername);
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

}
