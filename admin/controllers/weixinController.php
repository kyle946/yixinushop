<?php


namespace controllers;
include IFRAME_ROOT . '/api/weixinFunction.php';

/**
 * Description of weixinController
 *
 * @author kyle
 */
class weixinController extends adminController {

    public $m;
    public $info;

    public function __construct() {
        parent::__construct();
        $this->m = new \core\model();
        $sql = "select mark as yixinu,val from `" . SQLPRE . "shop_config` where mark = 'weixinappid' or mark='weixinsecret' ";
        $info = $this->m->getall($sql);
        
        $this->info = $this->m->getall("select mark as yixinu,val from `" . SQLPRE . "config2`");
        
        defined('APPID') or define('APPID', $info['weixinappid']['val']);
        defined('SECRET') or define('SECRET', $info['weixinsecret']['val']);
        defined('WEIXINTOKENCODE') or define('WEIXINTOKENCODE', $this->info['weixinTokenCode']['val']); 
    }

    public function index() {
        $res = array();
        if( !APPID ){
            $res['info1'] = 'APPID  未定义，请先前往 系统设置 中进行设置 。';
        }
        if( !SECRET ){
            $res['info2'] = 'SECRET  未定义，请先前往 系统设置 中进行设置 。';
        }
        $this->assign($res);
        $this->display('weixin/index');
    }

    public function otherset() {
        $this->check();
        //保存设置的值
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            $mark = $this->_post('mark');
            $data['val'] = $this->_post('val')? : '0';
            $this->m->sData($data, 'config2', 'u', "mark='$mark'");
            echo json_encode(array('status' => 1));
            exit();
        }
        $list = $this->m->getall("select * from `" . SQLPRE . "config2` where g='i'");
        foreach ($list as $key => $value) {
            $list[$key] = $value;
            if (!empty($value['seval'])) {
                $list[$key]['seval'] = explode(",", $value['seval']);
            }
        }
        $this->assign('list', $list);
        $this->display('weixin/otherset');
    }
    
    /**
     * 生成菜单
     */
    public function createMenu() {
        $this->check();
        //保存设置的值 begin
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            $data = $_POST;
            foreach ($data as $key => $value) {
                if( isset($value['sub_button']) ){
                    unset($value['url']);
                    unset($value['type']);
                    foreach ($value as $k => $v) {
                        $value[$k] = $v;
                    }
                }
                $data[$key] = $value;
            }
            $data2 = array( 'button'=>$data );
            //保存到数据库
            $d['val'] = serialize($data2);
            $this->m->sData($d,'config2','u',"mark='weixinMenu'");
            //发送到微信接口并生效
            $rr = createMenu($data2);
            $rr = json_decode($rr,1);
//            echo json_encode_ex($data);exit();
            if( @$rr['errmsg']=='ok' ){
                echo json_encode(array('status'=>1,'info'=>$rr));
            }else{
                echo json_encode(array('status'=>0,'info'=>$rr));
            }
            exit();
        }
        //  end
        
        $sql = "select val from `" . SQLPRE . "config2` where mark='weixinMenu'";
        $arrStr = $this->m->getone($sql);
        $res = array();
        if( $arrStr ){
            $arr = unserialize($arrStr);
            $arr2 = isset($arr['button'])?$arr['button']:$arr;
            $res['weixinmenu'] = $arr2;
        }
        
        $this->assign($res);
        $this->display('weixin/createMenu');
    }
    
    /**
     * 创建二维码  显示窗口
     */
    public function qrcode() {
        $this->check();
        $sql = "select * from `".SQLPRE."weixinqrcode` order by id desc";
        $qrcodelist = $this->m->getall($sql);
        $this->assign('list', $qrcodelist);
        $this->display('weixin/qrcode');
        
    }
    
    /**
     * 创建二维码 提交数据
     */
    public function qrcodepost() {
        $postdata = $_POST;
        $data['type'] = 2;
        if( $postdata['type']=='yj' ) $data['type'] = 1;
        $data['param'] = $postdata['var'];
        $data['times'] = date('Y-m-d H:i:s',time() + 86400);
        $data['comment'] = $postdata['comment'];
        $action_name = 'QR_SCENE';
        if( $postdata['type']=='yj' ) $action_name='QR_LIMIT_SCENE';
        $url = createQrcode(86400, $postdata['var'], $action_name, 1);
        $data['shorturl'] = $url['shorturl'];
        $this->m->sData($data, 'weixinqrcode');
        exit();
    }
    
    public function deleteqrcode() {
        $id = $_POST['id'];
        $this->m->sDelete('weixinqrcode', "id=$id");
        exit();
    }
    
    /**
     * 检测APPID和SECRET有没有设置
     */
    public function check() {
        if( !APPID or !SECRET ){
            $this->index();
        }
    }

    public function exemption() {
        $t = rget('t');
        $data = $_POST['data'];
        switch ($t) {
            case 'base64':
                echo base64_encode($data);
                break;
            default:
                break;
        }
    }

}
