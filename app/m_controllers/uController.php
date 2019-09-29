<?php

/**
 * Description of uController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;

class uController extends comController {

    
    
    public $openid;
    public function __construct() {
        parent::__construct();
        $this->openid = wx_oauth();
    }

    /**
     * 登录
     */
    public function login() {
        //如果已经登录， 直接 跳转
        if ( loginJudbe() ) {
            header("Location: " . createLink('user/index'));
            exit();
        }
        
        //登录之后返回的地址，默认为首页
        $back_url = $this->rget('back'); //
        if (!empty($back_url)) {
            $back_url = base64_decode($back_url);
        } else {
            $back_url = '?v=/user/index';
        }

        if ( judgeAjaxRequest() ) {
            $name = $this->_post('uname');
            $pwd = $this->_post('pwd');
            $m = new \core\model();
            if ($this->_post('bdweixin')) {
                $verifystr = $this->_post('yzm2'); //图片验证码
                if (md5($verifystr) != $_SESSION['yixinuSmsModcode']) {
                    echo json_encode(  array('info'=>'验证码不正确！','status'=>0) );
                    exit();
                }
            } else {
                $verifystr = $this->_post('verifyStr'); //图片验证码
                if (md5($verifystr) != $_SESSION['YixinuVerify']) {
                    echo json_encode(  array('info'=>'验证码不正确！','status'=>0) );
                    exit();
                }
            }
            if (!empty($name)) {
                $sql = "select id,username,password,status,nickname,curLogin,avatar from " . SQLPRE . "users where username='$name'";
                $info = $m->getrow($sql);
                if ($info) {
                    //如果密码正确 ，登录成功
                    if (md5($pwd) == $info['password']) {
                        $info['expiretime'] = time() + 3600*10;  //设置超时时间为3600秒
                        //更新登录时间
                        $data11['curLogin'] = 'now()';
                        $data11['lastLogin'] = $info['curLogin'];
                        $m->sData($data11, 'users', 'u', "id=$info[id]");

                        unset($info['password']);
                        //如果是在手机上登录 则写cookie
                        if (judgeMobileBrowse() == true) {
                            setcookie('author_316686606', base64_encode(serialize($info)), time() + 60 * 60 * 24 * 30 * 12,'/'); //保存时间为一年
                        } else {
                            $_SESSION['userInfo'] = $info;
                        }
                        
                        //积分  begin
                        $sql ="select val from `".SQLPRE."shop_config` where mark='pointsAct3'";
                        $p1 = $m->getone($sql);
                        $pointact = new \models\other();
                        $num = intval(floatval($p1));
                        $pointact->pointsAct($info['id'], $num, 'add', '登录微商城，增加积分:'.$num );
                        //积分  end
                        
                        //如果是在微信环境下并且设置了绑定微信号, 跳转到微信登录
                        if ( judgeMicroMessenger() && $this->_post('bdweixin')) {
                            $redirect_uri = createLink('u/weixinlogin', array('b' => base64_encode('/user/index'),'bdweixin'=>1));
                            echo json_encode(  array('url'=>$redirect_uri,'status'=>1) );
                            exit();
                        } else {
                            echo json_encode(  array('url'=>$back_url,'status'=>1) );
                            exit();
                        }
                        
                    } else {
                        echo json_encode(  array('info'=>'密码错误，请重新输入！','status'=>0) );
                        exit();
                    }
                } else {
                        echo json_encode(  array('info'=>"[$name] 用户不存在，请重新输入",'status'=>0) );
                        exit();
                }
            } else {
                echo json_encode(  array('info'=>'用户名不能为空，请重新输入！','status'=>0) );
                exit();
            }
        } else {
            $this->assign('layout_title', '登录');
            $this->assign('webtitle_', '登录');
            $this->display('user/login');
        }
    }

    /**
     * 用户注册
     */
    public function register() {
        
        //如果已经登录， 直接 跳转
        if ( loginJudbe() ) {
            header("Location: " . createLink('user/index'));
            exit();
        }

        if (isset($_REQUEST['_submit_'])) {
            $m = new \core\model();
            $verifystr = $this->_post('yzm2');
            //如果图片验证码正确
            //if (md5(strtoupper($verifystr)) == $_SESSION['YixinuVerify']) {
            if (md5($verifystr) == @$_SESSION['yixinuSmsRegCode']) { //yixinuSmsRegCode
                $data['username'] = $this->_post('uname');
                $data['mobile'] = $this->_post('uname');
                $data['password'] = md5($this->_post('pwd'));
                $data['nickname'] = $this->_post('nickname');
                $data['payPoints'] = 10;
                $data['userRank'] = 1;
                
                $result = $m->sData($data, 'users');
                if ($result) {
                    $data['id'] = mysqli_insert_id($m->link);
                    //如果是在手机上登录 则写cookie
                    if (judgeMobileBrowse() == true) {
                        setcookie('author_316686606', base64_encode(serialize($data)), time() + 60 * 60 * 24 * 30 * 12,'/'); //保存时间为一年
                    } else {
                        $_SESSION['userInfo'] = $data;
                    }
                    
                    header('Location: ' . createLink('user/index'));
                    exit();
                }
            } else {
                msg('验证码错误！ 请 <a href="/u/register" >返回</a> 页面重新填写！');
            }
            exit();
        } else {
            $this->assign('webtitle_', '新用户注册 - ');
            $this->assign('layout_title', '新用户注册 - ');
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
     * 发送短信  注册用户 
     */
    public function sendmsgMod() {
        $mobile = rget('mobile');
        $bool = preg_match('/^1[1-9][0-9]{9,9}$/', $mobile);
        $arr = array();
        if ($bool == 0) {
            $arr = array('status' => 0, 'msg' => '手机号码格式不正确！');
        } else {
            $res = sendMsgMod($mobile);
            if ($res === 1) {
                $arr = array('status' => 1, 'msg' => '发送成功！');
            } else {
                $arr = array('status' => 0, 'msg' => $res['msg']);
            }
        }
        echo json_encode($arr);
        exit();
    }

    /**
     * 发送短信  注册用户 
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
        if (md5($verifystr) == @$_SESSION['yixinuSmsRegCode']) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }

    /**
     * 检测用户名唯一性
     */
    public function checkUsername() {
        $m = new \core\model();
        $username = $this->rget('name');
        $sql = "select id from " . SQLPRE . "users where username='$username'";
        $result = $m->getone($sql);
        if (!empty($result)) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
        exit();
    }

    /**
     * 找回登录密码
     */
    public function retrievepassword() {

        $m = new \core\model();
        $step = rget('s')? : 1;
        $template = 'retrievepassword_step1';
        if ($step) {
            switch ($step) {
                case 1: $template = 'retrievepassword_step1';
                    $_SESSION['retrievepasswordusername'] = null;
                    $_SESSION['retrievepassword_step'] = 1;  //验证每一个步骤是否都 已经 通过
                    break;
                case 2:
                    if (isset($_POST['_submit_']) && $_SESSION['retrievepassword_step'] === 1) {
                        $name = $this->_post('uname');
                        $verifystr = $this->_post('verifyStr');
                        if (md5($verifystr) == $_SESSION['YixinuVerify']) {
                            $sql = "select username from `" . SQLPRE . "users` where username=$name";
                            $username = $m->getone($sql);
                            if ($username) {
//                                header("Location: ".createLink('u/retrievepassword',array('s'=>2)) );
//                                exit();
                                $this->assign('username', $username);
                                $_SESSION['retrievepasswordusername'] = $username;
                                $template = 'retrievepassword_step2';
                                $_SESSION['retrievepassword_step'] = 2; //第一步已经通过
                            } else {
                                $_SESSION['retrievepasswordusername'] = NULL;
                                $_SESSION['retrievepassword_step'] = NULL;
                                msg('用户不存在!<br /><br /> 请点击 <a href="' . createLink('u/retrievepassword') . '">［返回］</a> 重新填写');
                            }
                        } else {
                            $_SESSION['retrievepasswordusername'] = NULL;
                            $_SESSION['retrievepassword_step'] = NULL;
                            msg('验证码不正确<br /><br /> 请点击 <a href="' . createLink('u/retrievepassword') . '">［返回］</a> 重新填写');
                        }
                    }
                    break;
                case 3:
                    if (isset($_POST['_submit_']) && $_SESSION['retrievepassword_step'] === 2) {
                        $verifystr = $this->_post('yzm2');
                        if (md5($verifystr) == @$_SESSION['yixinuSmsRegCodeChpwd']) {
                            $this->assign('username', $_SESSION['retrievepasswordusername']);
                            $template = 'retrievepassword_step3';
                            $_SESSION['retrievepassword_step'] = 3; //第二步已经通过
                        } else {
                            $_SESSION['retrievepasswordusername'] = NULL;
                            $_SESSION['retrievepassword_step'] = NULL;
                            msg('验证码不正确<br /><br /> 请点击 <a href="' . createLink('u/retrievepassword') . '">［返回］</a> 重新填写');
                        }
                    }
                    break;
                case 4:
                    //如果提交表单并且前面两个步骤已经通过
                    if (isset($_POST['_submit_']) && $_SESSION['retrievepassword_step'] === 3) {
                        $pwd = $this->_post('pwd');
                        $rpwd = $this->_post('rpwd');
                        if (empty($pwd) || empty($rpwd)) {
                            $_SESSION['retrievepasswordusername'] = NULL;
                            $_SESSION['retrievepassword_step'] = NULL;
                            msg('密码不能为空！<br /><br /> 请点击 <a href="' . createLink('u/retrievepassword') . '">［返回］</a> 重新填写');
                        } elseif ($pwd != $rpwd) {
                            $_SESSION['retrievepasswordusername'] = NULL;
                            $_SESSION['retrievepassword_step'] = NULL;
                            msg('两次密码输入不一致！<br /><br /> 请点击 <a href="' . createLink('u/retrievepassword') . '">［返回］</a> 重新填写');
                        } else {
                            $username = $_SESSION['retrievepasswordusername'];
                            $data['password'] = md5($pwd);
                            $res = $m->sData($data, 'users', 'u', "username='$username'");
                            if ($res) {
                                $_SESSION['retrievepasswordusername'] = NULL;
                                $_SESSION['retrievepassword_step'] = NULL;
                                setcookie('author_316686606', null);  //修改成功之后， 退出登录 。
                                msg('密码修改成功！<br /><br /> 请点击 <a href="' . createLink('u/login') . '">［返回］</a> 登录');
                            }
                        }
                    }
                    break;
            }
        }
        $this->assign('webtitle_', '找回登录密码 - ' . $step);
        $this->assign('layout_title', '找回登录密码');
        $this->display('user/' . $template);
    }

    /**
     * 发送短信  修改密码
     */
    public function sendmsgChpwd() {
        $mobile = $_SESSION['retrievepasswordusername'];
        $arr = array();
        if (!$mobile) {
            $arr = array('status' => 0, 'msg' => '非法提交！');
        } else {
            $res = sendMsgChpwd($mobile);
            if ($res === 1) {
                $arr = array('status' => 1, 'msg' => '发送成功！');
            } else {
                $arr = array('status' => 0, 'msg' => $res['msg']);
            }
        }
        echo json_encode($arr);
        exit();
    }

    /**
     * 检测 验证码是否正确  短信  修改密码 
     */
    public function checkVerifyMsg2() {
        $verifystr = $this->rget('verifystr');
        if (md5($verifystr) == @$_SESSION['yixinuSmsRegCodeChpwd']) {
            echo json_encode(array('status' => 1));
        } else {
            echo json_encode(array('status' => 0));
        }
    }

    /**
     * 登录退出 
     */
    public function logout() {
        setcookie('author_316686606', null,time()-3600,'/');
        $_SESSION['userInfo'] = null;
        header('Location: ?c=u&a=login');
        exit();
    }

    /**
     * 输出验证码
     * 
     * @param type $w   图片宽度
     * @param type $h   图片高度
     * @param type $length   字符串数量，长度
     * @param type $verifyName  session数组的键名
     */
    public function verifyCode($w = 98, $h = 33, $length = 5, $verifyName = 'YixinuVerify') {
        $width = 48;
        $height = 22;
        $str = '1234567890';
        $count_ = strlen($str);
        $char = null;
        for ($i = 1; $i <= $length; $i++) {
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
        $stringColor2 = imagecolorallocate($im, mt_rand(55, 120), mt_rand(55, 120), mt_rand(55, 120));
        // 干扰
        for ($i = 0; $i < 12; $i++) {
            imagearc($im, mt_rand(-10, $width), mt_rand(-10, $height), mt_rand(30, 300), mt_rand(20, 200), 55, 44, $stringColor2);
        }
        for ($i = 0; $i < 25; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $stringColor2);
        }
        for ($i = 0; $i < $length; $i++) {
            imagestring($im, 5, $i * 10 + 5, 4, $char{$i}, $stringColor);
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
     * 微信登录
     * 
     * @param  $_GET['b']  urlencode()  之后的字符串
     * @param  $_GET['bdweixin']   要不要把帐号和微信绑定在一起
     */
    public function weixinlogin() {
        $this->openid = getOpenid();
        $weixin_info = getWxuserInfo($this->openid);
        $m = new \core\model();
        $sql = "select id,avatar,nickname,wxopenid,status from `".SQLPRE."users` where wxopenid='".$this->openid."'";
        $d = $m->getrow($sql);
        if( $d ){
            setcookie('author_316686606', base64_encode(serialize($d)), time() + 60 * 60 * 24 * 30 * 12,'/'); //保存时间为一年
            header("Location: " . createLink('user/index') );
            exit();
        }else{
//                $data['username'] = NULL;
//                $data['mobile'] = NULL;
//                $data['password'] = NULL;
                $data['nickname'] = $weixin_info['nickname'];
                $data['payPoints'] = 10;
                $data['userRank'] = 1;
                $data['wxopenid'] = $weixin_info['openid'];
                //取头像   begin
                if( $weixin_info['headimgurl'] ){
                    $path = AVATAR_PATH;
                    $time = time();
                    getImage($weixin_info['headimgurl'], $path . '/weixintouxian_' . $time . ".jpg");
                    $thumbImage = $path . '/avatar_' . $time . ".jpg"; //缩略图文件名，这里是全路径 
                    makeThumb($path . '/weixintouxian_' . $time . ".jpg", $thumbImage);
                    //取文件名
                    $pinfo = pathinfo($thumbImage);
                    $fname = $pinfo['basename'];
                    //写入数据库
                    $data['avatar'] = $fname;
                    //  end
                }
                $result = $m->sData($data, 'users');
                //begin 更新卡号
                $card_no = mysqli_insert_id($m->link);
                $card_no = 'k'.$card_no.rand(1111,9999);
                $password = substr($card_no, -6, 6);  //取卡号后6会作为密码
                $m->sData(array('card_no'=>$card_no,'username'=>$card_no,'password'=>md5($password)), 'users', 'u', "wxopenid='".$this->openid."'");
                //end
                
                $sql = "select id,avatar,nickname,wxopenid,status from `".SQLPRE."users` where wxopenid='".$this->openid."'";
                $d = $m->getrow($sql);
                
                setcookie('author_316686606', base64_encode(serialize($d)), time() + 60 * 60 * 24 * 30 * 12,'/'); //保存时间为一年
                header("Location: " . createLink('user/index') );
                exit();
        }
                
    }
    
    
    public function address() {
        $province = $this->_get('province');
        $city = $this->_get('city');
        $district = $this->_get('district');
        $m = new \core\model();
        if( $province && $city   ){
            $sql = "select provice_id from `".SQLPRE."area_provice` where provice_name='$province'";
            $province_sn = $m->getone($sql);
            $sql = "select city_id from `".SQLPRE."area_city` where city_name='$city'";
            $city_sn = $m->getone($sql);
            $sql = "select county_id from `".SQLPRE."area_county` where county_name='$district'";
            $district_sn = $m->getone($sql);
            echo json_encode( array('p'=>$province_sn,'c'=>$city_sn,'d'=>$district_sn) );
            exit();
        }
    }

}
