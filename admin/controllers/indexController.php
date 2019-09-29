<?php

/**
 * Description of indexController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */
namespace controllers;
class indexController extends adminController {
    
    public function index() {
        $info = array();
        $info['os'] = PHP_OS;
        $info['version'] = PHP_VERSION;
        $info['web'] = $_SERVER['SERVER_SOFTWARE'];
        $info['yixinu'] = cfg('YixinuVersion');
        $info['iframe'] = cfg('iframeVerison');
        $info['uploadSize'] = ini_get('upload_max_filesize');
        $m = new \core\model();
        $info['dbversion'] = $m->dbversion();
        //登录信息
        $user_info = $_SESSION[LOGIN_SESSION_KEY];
        $info['lastTime'] = $user_info['lastTime'];
        $user_role = $_SESSION['user_role'];
        if( $user_role=='admin' ){
            $info['user_role'] = '超级管理员';
        }elseif($user_role=='business'){
            $info['user_role'] = '商户';
        }else{
            $info['user_role'] = $user_info['rname'];
        }
        $info['business_no'] = $_SESSION['business_no'];
        $info['business_no_hex'] = dechex($info['business_no']);
        $sql = "select business_name from `".SQLPRE."business` where business_no=$info[business_no]";
        $info['business_name'] = $m->getone($sql);
        $this->assign('info',$info);
        $this->display('index/index');
    }
    
    public function business_base_info() {
        $m = new \core\model();
        $business_no = $_SESSION['business_no'];
        $sql = "select * from ".SQLPRE."business where business_no=$business_no";
        $tmpvar['info'] = $m->getrow($sql);
        $this->assign($tmpvar);
        $this->display('index/business_base_info');
    }
    
    public function business_config() {
        $m = new \core\model();
        $business_no = $_SESSION['business_no'];
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            $business_alipay_config['alipay_account'] = $_POST['alipay_account'];
            $business_alipay_config['alipay_key'] = $_POST['alipay_key'];
            $business_alipay_config['alipay_partner'] = $_POST['alipay_partner'];
            $business_alipay_config['alipay_private_key'] = $_POST['alipay_private_key'];
            $business_alipay_config['alipay_pay_method'] = $_POST['alipay_pay_method'];
            
            $business_weixinpay_config['weixin_appid'] = $_POST['weixin_appid'];
            $business_weixinpay_config['weixin_mchid'] = $_POST['weixin_mchid'];
            $business_weixinpay_config['weixin_key'] = $_POST['weixin_key'];
            $business_weixinpay_config['weixin_secret'] = $_POST['weixin_secret'];
            
            $data['business_weixinpay_config'] = serialize($business_weixinpay_config);
            $data['business_alipay_config'] = serialize($business_alipay_config);
            
            $m->sData($data, 'business', 'u',"business_no=$business_no");
            $p_ = null;
            if ($this->_get('p')) {
                $p_ = '&p=' . $this->_get('p');
            }
            header("Location: ?c=index&a=business_config");
            exit();
            
        }else{
            $sql = "select * from ".SQLPRE."business  where business_no=$business_no";
            $info = $m->getrow($sql);
            $business_alipay_config = unserialize($info['business_alipay_config']);
            $business_weixinpay_config = unserialize($info['business_weixinpay_config']);
            $tplvar['info'] = $info;
            $tplvar['a'] = $business_alipay_config;
            $tplvar['w'] = $business_weixinpay_config;
            $this->assign($tplvar);
            $this->display('index/business_config');
        }
    }
    
    /**
     * 删除缓存数据
     */
    public function deleteCache() {
        echo '<p>正在清除……</p>';
        //后台
        foreach (scandir(CACHE_PATH) as $key => $value) {
            if(substr($value, -3)=='php'){
                @unlink(CACHE_PATH.$value);
            }
        }
        //前台
        $fPath = IFRAME_ROOT.'..'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
        foreach (scandir($fPath) as $key => $value) {
            if(substr($value, -3)=='php'){
                @unlink($fPath.$value);
            }
        }
        
        $redis = new \models\yredis();
        //删除所有缓存
        $arr = $redis->keys(REDIS_PRE . '*');
        foreach ($arr as $v) {
            $redis->del($v);
        }
        echo '<p>清除成功。</p>';
        exit();
    }
    
//    public function addmenu() {
//        $m = new \core\model();
//        if(isset($_POST['_submit_'])){
//            $table = $_POST['table'];
//            unset($_POST['table']);
//            unset($_POST['_submit_']);
//            unset($_POST['_verifyKey_']);
//            $field = null; $val = null;
//            foreach ($_POST as $key => $value) {
//                $field .= "$key,";
//                $val.="'$value',";
//            }
//            $field = substr($field, 0, -1);
//            $val = substr($val, 0, -1);
//            $sql = "insert into ".$m->prefix."$table ($field) values ($val)";
//            $result = $m->query($sql);
//            if($result){
//                msg('', '添加成功。', '', '1111', 3);
//            }
//        }
//        
//        $str = $m->_query("select * from ".$m->prefix."admin_menu");
//        $tmp1__ =  "<pre>".print_r($str,1)."</pre>";
//        $this->assign('tmp1__', $tmp1__);
//
//        $str = $m->_query("select * from ".$m->prefix."admin_mtag");
//        $tmp2__ =  "<pre>".print_r($str,1)."</pre>";
//        $this->assign('tmp2__', $tmp2__);
//        $this->display('addmenu');
//    }
    
    public function test() {
        var_dump( WEBPATH );
    }
    
    public function exemption() {
        header("Location: ".__ROOT__);
    }
    
}

?>
