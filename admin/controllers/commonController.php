<?php

/**
 * Description of commonController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */
namespace controllers;
class commonController extends \core\Controller {
    
    public function login() {
        if( $_SERVER["REQUEST_METHOD"]=='POST' ){
            $key = LOGIN_SESSION_KEY; //cfg('LOGIN_SESSION_KEY');
            $model = new \core\model();
            $username = $model->escapeString($_POST['username']);
            $password = $_POST['password'];
            $verifystr = $this->_post('yzm');
            //begin 首次登录不需要验证码 
            $verify_bool = false; //用来判断验证码是否验证通过
            if( isset($_SESSION['is_second_login']) && $_SESSION['is_second_login']==1 ){
                if( md5( strtoupper($verifystr) ) == $_SESSION['YixinuVerify'] ){
                    $verify_bool = true;
                }
            }else{ 
                $verify_bool = true; //通过验证
            }
            //end
            
            //无论登录是否成功，第二次登录必须输入验证码
            $_SESSION['is_second_login'] = 1;
            
            if( $verify_bool ){
                $sql = "select u.* , r.sys_default , r.mark,r.name as rname from `".SQLPRE."admin_user` u "
                        . "  left join `".SQLPRE."admin_role` r on u.roleId=r.id where u.name='$username'";
                $row = $model->getrow($sql);
                if(empty($row)){
//                    msg('用户不存在',$username.'　用户不存在！请重新登录。。','','登录页面');
                    echo json_encode( array('status'=>0,'info'=>'用户不存在',$username.'　用户不存在！请重新登录。。') );
                    exit();
                }else{
                   if(md5($password) != $row['passwd']){
//                       msg('密码错误 ','用户密码不正确，请重新输入！','','登录页面');
                        echo json_encode( array('status'=>0,'info'=>'密码不正确') );
                        exit();
                   }else{
                       //修改登录时间
                       $d2['currentTime'] = date('Y-m-d H:i:s');
                       $d2['lastTime'] = $row['currentTime'];
                       $model->sData($d2,'admin_user', 'u', "id=$row[id]");
                       
                       $row['expiretime'] = time()+1800;  //设置超时时间为1800秒
                       unset($row['passwd']);
                       $_SESSION[$key] = $row;
                       //缓存管理员关联的商户
                       $_SESSION['business_no'] = $row['business_no'];  
                       if( $row['comment']=='founder' || $row['mark']=='admin' ){   //如果是创始人或者管理员
                           $_SESSION['user_role'] = 'admin';  
                           
                           //如果管理员关联商户号登录
                           if( isset($_GET['business_no']) && !empty($_GET['business_no']) ){
                               $_SESSION['business_no'] = $_GET['business_no'];
                               $_SESSION['user_role'] = 'business';
                           }
                           
                       }elseif($row['mark']=='business'){//商户登录 
                            $_SESSION['user_role'] = 'business';  
                       }else{ //其他用户登录
                           $_SESSION['user_role'] = 0; 
                       }
                   }
                }
//                header("Location:  ?");
                echo json_encode( array('status'=>1,'info'=>'登录成功') );
                exit();
            }else{
//                msg('验证码错误',$username.'　验证码错误！请重新输入。。','','登录页面');
                echo json_encode( array('status'=>0,'info'=>'验证码错误') );
                exit();
            }
        }else{
            //如果是第二次登录，必须输入验证码
            if( isset($_SESSION['is_second_login']) && $_SESSION['is_second_login']==1 ){ 
                $this->assign('first_login', 0);
            }else{ //否则不需要输入验证码
                $this->assign('first_login', 1);
            }
            $this->display("login");
        }
    }
    
    public function logout() {
        $key = cfg('LOGIN_SESSION_KEY');
        $_SESSION[$key] = '';
        header("Location:  ?act=login");
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
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
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
}
