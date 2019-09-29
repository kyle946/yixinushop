<?php


/**
 * 注册登录相关
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class uController extends comController {

    /**
     * 退出
     */
    public function logout() {
        unset($_SESSION['userInfo']);
        header('Location: /u/login');
        exit();
    }

    /**
     * 登录
     * @param type $userId
     */
    public function login($userId = null) {
        //如果用户已经登录
        if (loginJudbe()) {
            header('Location: /user');
            exit();
        }

        if ( isset($_REQUEST['_submit_']) ) {
            //无论登录成功还是失败都记录登录次数
            $_SERVER["SERVER_ADDR"];
            $redis = new \models\yredis();
            $name = $this->_post('username');
            $pwd = $this->_post('pwd');
            $verifystr = $this->_post('verifyStr');
            $m = new \core\model();
            $error = 0;
            $d = array();
            if (md5(strtoupper($verifystr)) == $_SESSION['YixinuVerify']) {
                if (!empty($name)) {
                    $info = $m->getrow("select id,username,password,status,nickname,curLogin from " . $m->prefix . "users where username='$name'");
                    if (!empty($info)) {
                        //如果密码正确 ，登录成功
                        if (md5($pwd) == $info['password']) {
                            $info['expiretime'] = time() + 3600*10;  //设置超时时间为1800秒
                            //更新登录时间
                            $data11['curLogin'] = 'now()';
                            $data11['lastLogin'] = $info['curLogin'];
                            $m->sData($data11, 'users', 'u', "id=$info[id]");

                            unset($info['password']);
                            $_SESSION['userInfo'] = $info;
                            $d['status'] = 'ok';
                            echo json_encode($d);
                            exit();
                        } else {
                            $error = 3;  //密码错误
                        }
                    } else {
                        $error = 2;  //用户不存在
                    }
                } else {
                    $error = 1;  //用户名为空 
                }
            } else {
                $error = 4;  //验证码错误
            }

            if ($error !== 0) {
                switch ($error) {
                    case 1:   //用户名为空 
                        $d['msg'] = '用户名为空';
                        break;
                    case 2:   //用户不存在
                        $d['msg'] = '用户不存在';
                        break;
                    case 3:   //密码错误
                        $d['msg'] = '密码错误';
                        break;
                    case 4:   //验证码错误
                        $d['msg'] = '验证码错误';
                        break;
//                    default:
//                        break;
                }
                //$d['verifyKey'] = $this->formverify();
                $d['status'] = $error;
                echo json_encode($d);
                exit();
            }
        } else {
            $type = $this->rget('type');
            //登录窗口携带的参数。
            if ($this->rget('param'))
                $this->assign('param', $this->rget('param'));
            if ($this->rget('goodsid'))
                $this->assign('goodsid', $this->rget('goodsid'));
            $this->assign('webtitle_', '用户登录 - ');
            //这是是判断是不是弹窗登录的界面 。
            if ($type != false and $type == 'm') {
                $this->display('user/loginMin');
            } else {
                $this->display('user/login');
            }
        }
    }

    /**
     * 用户注册
     */
    public function register() {
        if (isset($_REQUEST['_submit_'])) {
            $m = new \core\model();
            $verifystr = $this->_post('yzm2');
            //如果图片验证码正确
            //if (md5(strtoupper($verifystr)) == $_SESSION['YixinuVerify']) {
            if (md5($verifystr) == $_SESSION['yixinuSmsRegCode']) { //yixinuSmsRegCode
                $data['username'] = $this->_post('username');
                $data['mobile'] = $this->_post('username');
                $data['password'] = md5($this->_post('password'));
                $data['nickname'] = $this->_post('nickname');
                $data['payPoints'] = 10;
                $data['userRank'] = 1;
                $result = $m->sData($data, 'users');
                if ($result) {
                    $data['id'] = mysql_insert_id();
                    $data['expiretime'] = time() + 1800;
                    $_SESSION['userInfo'] = $data;
                    header('Location: /user');
                    exit();
                }
            } else {
                msg('验证码错误！ 请 <a href="/u/register" >返回</a> 页面重新填写！');
            }
//            $this->assign('webtitle_', '新用户注册 - ');
//            $this->assign('infoDisplay', 'block');
//            $this->assign('netname', $this->getNickname());
//            $this->display('user/register');
            exit();
        } else {
            $this->assign('webtitle_', '新用户注册 - ');
            $this->assign('netname', $this->getNickname());
            $this->display('user/register');
        }
    }

    /**
     * 检测验证码是否正确
     */
    public function checkVerify() {
        $verifystr = $this->rget('verifystr');
        if (md5(strtoupper($verifystr)) == $_SESSION['YixinuVerify']) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /**
     * 发送短信
     */
    public function sendmsg() {
        $mobile = rget('mobile');
        $bool = preg_match('/^1[1-9][0-9]{9,9}$/', $mobile);
        $arr = array();
        if ($bool == 0) {
            $arr = array('status' => 0, 'msg' => '手机号码格式不正确！');
        } else {
            $m = new \core\model();
            $sql = "select id from " . SQLPRE . "users where username='$mobile'";
            $result = $m->getone($sql);
            if (!empty($result)) {
                $arr = array('status' => 0, 'msg' => '手机号码已经注册！');
            } else {
                $res = sendMsgReg($mobile);
                if ($res === 1) {
                    $arr = array('status' => 1, 'msg' => '发送成功！');
                } else {
                    $arr = array('status' => 0, 'msg' => $res['msg']);
                }
            }
        }
        echo json_encode($arr);
        exit();
    }

    /**
     * 检测验证码是否正确  短信
     */
    public function checkVerifyMsg() {
        $verifystr = $this->rget('verifystr');
        if (md5($verifystr) == $_SESSION['yixinuSmsRegCode']) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /**
     * 检测用户名唯一性
     */
    public function checkUsername() {
        $m = new \core\model();
        $username = $this->rget('name');
        $sql = "select id from " . $m->prefix . "users where username='$username'";
        $result = $m->getone($sql);
        if (!empty($result)) {
            echo 0;
        } else {
            echo 1;
        }
    }

    /**
     * 获取昵称
     * @return type
     */
    public function getNickname() {
        $netnameArray = include CONF_PATH . 'netname.php';
        $num = rand(0, count($netnameArray));
        $netname = $netnameArray[$num];
        return $netname;
    }

    /**
     * 获取昵称 json格式
     */
    public function getNicknameJson() {
        echo json_encode(array('name' => $this->getNickname()));
    }

    /**
     * 输出验证码
     * 
     * @param type $w   图片宽度
     * @param type $h   图片高度
     * @param type $length   字符串数量，长度
     * @param type $verifyName  session数组的键名
     */
    public function verifyCode($w=90,$h=35,$length=4,$verifyName='YixinuVerify') {
        $width=90; 
        $height=40;
        $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $count_ = strlen($str);
        $char = null;
        for($i=1; $i<=$length; $i++){
            $num = rand(1, $count_);
            $char .= $str[--$num];
        }
        $_SESSION[$verifyName] = md5($char);
        $width = ($length * 10 + 10) > $width ? $length * 10 + 10 : $width;
        $im = imagecreate($width, $height);
        $r = Array(225, 255, 255, 223);
        $g = Array(225, 236, 237, 255);
        $b = Array(225, 236, 166, 125);
        $key = mt_rand(0, 3);

        $backColor = imagecolorallocate($im, $r[$key], $g[$key], $b[$key]);    //背景色（随机）
        $borderColor = imagecolorallocate($im, 100, 100, 100);                    //边框色
        imagefilledrectangle($im, 0, 0, $width - 1, $height - 1, $backColor);
        imagerectangle($im, 0, 0, $width - 1, $height - 1, $borderColor);
        $stringColor = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
        $stringColor2 = imagecolorallocate($im, mt_rand(150, 180), mt_rand(150, 180), mt_rand(166, 180));
        // 干扰
        for ($i = 0; $i < 6; $i++) {
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(50, 100), mt_rand(20, 200), 55, 44, $stringColor2);
        }
        for ($i = 0; $i < 99; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $stringColor2);
        }
        $font = IFRAME_ROOT.'/api/myyh.ttf';
        $fontSize = 18;
        $codeNX = 0;
        for ($i = 0; $i < $length; $i++) {
//            imagestring($im, 5, $i * 10 + 5, 4, $char{$i}, $stringColor);
            $codeNX  += mt_rand($fontSize*1.2, $fontSize*1.6);
            $x=$i*22;
            if( $i==0 ){ $x=10; }
            $color = imagecolorallocate($im, mt_rand(1,150), mt_rand(1,150), mt_rand(1,150));
            imagettftext($im, $fontSize, mt_rand(-40, 40), $x, $fontSize*1.6, $color, $font, $char{$i});
        }
        
        $thumb = imagecreate($w, $h);
        imagecopyresized($thumb, $im, 0, 0, 0, 0, $w, $h, $width, $height);
        header("Content-type: image/" . 'png');
        imagepng($thumb);
        //销毁
        imagedestroy($thumb);
        imagedestroy($im);
    }

    /**
     * 前台用json获取用户登录状态
     */
    public function loginJudbeJson() {
        if (loginJudbe()) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
        exit();
    }
    
    /**
     * 找回密码
     */
    public function retrievepassword() {
        $url = base64_encode(MOBILE_URL.'/u/retrievepassword');
        $this->assign('url', $url);
        $this->assign('webtitle_', '找回登录密码' );
        $this->display('user/retrievepassword');
    }
    
    public function qrcode() {
        $text = rget('text');
        if(!$text){
            $text = 'http://www.yixinu.com/';
        }else{
            $text = base64_decode($text);
        }
        include ILIB.'phpqrcode.php';
        \QRcode::png($text,false,QR_ECLEVEL_L,8,1);
        exit();
    }
    
    /**
     * 
     */
    public function business_register() {
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            $m = new \core\model();
            $data = array();
            //手机号码
            if( filter_string($_POST['username'])==false ){
                echo '手机号码包含非法字符，注册失败！';
                exit();
            }else{
                $data['business_mobile'] = $this->_post('username');
            }
            //检测手机号码唯一性
            $r = $m->getone("select business_no from ".SQLPRE."business where business_mobile=$data[business_mobile]");
            if($r){
                echo $data['business_mobile'].'已经注册过商户，你可以直接登录。';
                exit();
            }
            //密码
            $data['business_pwd'] = md5($_POST['password']);
            //商户名称
            if( filter_string($_POST['bname'])==false ){
                echo '商户名称含非法字符，注册失败！';
                exit();
            }else{
                $data['business_name'] = $this->_post('bname');
            }
            //主体信息
            if( filter_string($_POST['si'])==false ){
                echo '主体信息含非法字符，注册失败！';
                exit();
            }else{
                $data['business_subject_information'] = $this->_post('si');
            }
            //真实姓名
            if( filter_string($_POST['aname'])==false ){
                echo '真实姓名含非法字符，注册失败！';
                exit();
            }else{
                $data['business_actual_name'] = $this->_post('aname');
            }
            //身份证号码
            if( filter_string($_POST['idnumber'])==false ){
                echo '身份证号码含非法字符，注册失败！';
                exit();
            }else{
                $data['business_id_number'] = $this->_post('idnumber');
            }
            //状态
            $data['business_status'] = 2|4;
                
            if( isset($_POST['image']) ){
                $_POST['image'] = str_replace('data:image/jpeg;base64,', '', $_POST['image']);
                $_POST['image'] = str_replace('data:image/jpg;base64,', '', $_POST['image']);
                $_POST['image'] = str_replace('data:image/png;base64,', '', $_POST['image']);
                $str = base64_decode($_POST['image']);
                $info = getimagesizefromstring($str);
                if (!$info) {
                    echo json_encode(array('error' => 1, 'info' => '解码失败') );
                    exit();
                }
                switch ($info['mime']) {
                    case 'image/png':
                        $ext = '.png';
                        break;
                    case 'image/jpeg':
                        $ext = '.jpg';
                        break;
                    case 'image/gif':
                        $ext = '.gif';
                        break;
                    case 'image/pjpeg':
                        $ext = '.jpg';
                        break;
                }
                $time_ = time();
                $new_file_name = './idnumberimg/' . MD5($data['business_mobile']) . $ext; 
                $res = file_put_contents($new_file_name, $str);//返回的是字节数
            }
            $data['idnumberimg'] = MD5($data['business_mobile']). $ext;
                
            $business_no = $m->sData($data, 'business');
            if($business_no){
                $updata = array('business_no_hex'=> dechex($business_no));
                $m->sData($updata, 'business', 'u', "business_no=$business_no");
                $sql = "select * from ".SQLPRE."business where business_no=$business_no";
                $info = $m->getrow($sql);
                $_SESSION['business_info'] = $info;
                echo 1;
                exit();
            }
        }else{
            $this->display('other/business_register');
        }
    }
    
    /**
     * 检测手机号码注册唯一性
     */
    public function business_check_mobile(){
        $m = new \core\model();
        $mobile = $this->rget("mobile");
        $result = $m->getone("select business_no from ".SQLPRE."business where business_mobile=$mobile");
        if (!empty($result)) {
            echo 0;
        } else {
            echo 1;
        }
    }
    
    /**
     * 查询商户注册进度
     */
    public function business_login() {
        $m = new \core\model();
        $mobile = $this->_post("mobile");
        $pwd = $this->_post("pwd");
        $yzm = $this->_post('yzm');
        if (md5(strtoupper($yzm)) != $_SESSION['YixinuVerify']) {
            echo "<p>验证码不正确！</p>";
            exit();
        }
        $sql = "select * from `".SQLPRE."business` where `business_mobile`='$mobile'";
        $info = $m->getrow($sql);
        $errorinfo = null;
        if( md5($pwd)==$info['business_pwd'] ){
            if($info){
                $_SESSION['business_info'] = $info;
                echo 1;
            }else{
                $errorinfo .= "<p>商户不存在！</p>";
            }
        }else{
            $errorinfo .= "<p>密码错误！！</p>";
        }
        echo $errorinfo;
        exit();
    }
    
    /**
     * 商户注册成功或者登录成功后显示的界面
     */
    public function business_info() {
        if( isset($_SESSION['business_info']) && !empty($_SESSION['business_info']) ){
            $m = new \core\model();
            $business_no = $_SESSION['business_info']['business_no'];
            $business_mobile = $_SESSION['business_info']['business_mobile'];
            if( $_SERVER['REQUEST_METHOD']=='POST' ){
                $data = array();
                //商户名称
                if( filter_string($_POST['business_name'])==false ){
                    echo '商户名称含非法字符，注册失败！';
                    exit();
                }else{
                    $data['business_name'] = $this->_post('business_name');
                }
                //主体信息
                if( filter_string($_POST['business_subject_information'])==false ){
                    echo '主体信息含非法字符，注册失败！';
                    exit();
                }else{
                    $data['business_subject_information'] = $this->_post('business_subject_information');
                }
                //真实姓名
                if( filter_string($_POST['business_actual_name'])==false ){
                    echo '真实姓名含非法字符，注册失败！';
                    exit();
                }else{
                    $data['business_actual_name'] = $this->_post('business_actual_name');
                }
                //身份证号码
                if( filter_string($_POST['business_id_number'])==false ){
                    echo '身份证号码含非法字符，注册失败！';
                    exit();
                }else{
                    $data['business_id_number'] = $this->_post('business_id_number');
                }
                //状态
                $data['business_status'] = 8;

                if( isset($_POST['image']) ){
                    $_POST['image'] = str_replace('data:image/jpeg;base64,', '', $_POST['image']);
                    $_POST['image'] = str_replace('data:image/jpg;base64,', '', $_POST['image']);
                    $_POST['image'] = str_replace('data:image/png;base64,', '', $_POST['image']);
                    $str = base64_decode($_POST['image']);
                    $info = getimagesizefromstring($str);
                    if (!$info) {
                        echo json_encode(array('error' => 1, 'info' => '解码失败') );
                        exit();
                    }
                    switch ($info['mime']) {
                        case 'image/png':
                            $ext = '.png';
                            break;
                        case 'image/jpeg':
                            $ext = '.jpg';
                            break;
                        case 'image/gif':
                            $ext = '.gif';
                            break;
                        case 'image/pjpeg':
                            $ext = '.jpg';
                            break;
                    }
                    $new_file_name = './idnumberimg/' . MD5($business_mobile) . $ext; 
                    $res = file_put_contents($new_file_name, $str);//返回的是字节数
                }
                $data['idnumberimg'] = MD5($business_mobile). $ext;
                $business_no = $m->sData($data, 'business','u',"business_no=$business_no");
                echo 1; 
                exit();
                
            }else{
                $sql = "select * from `".SQLPRE."business` where `business_no`='$business_no'";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
                $this->display("other/business_info");
            }
        }else{
            header("Location: /u/business_register");
            exit();
        }
    }
    
    public function business_logout() {
        unset($_SESSION['business_info']);
        header("Location: /u/business_register");
        exit();
    }

}
