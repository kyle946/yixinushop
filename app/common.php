<?php

/**
 * 项目函数库
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

/**
 * 判断是不是首页
 * @return boolean
 */
function isindex() {
    if (CONTROLLER_NAME == 'index' and ACTION_NAME == 'index') {
        return true;
    } else {
        false;
    }
}

/**
 * 打印 颜色 值
 * @return type
 */
function printColor() {
    $r = rand(150, 250);
    $g = rand(150, 250);
    $b = rand(10, 190);
    return "rgb($r,$g,$b)";
}

/**
 * 打印 颜色 值
 * @return type
 */
function printColor2() {
    $r = rand(90, 190);
    $g = rand(90, 190);
    $b = rand(90, 190);
    return "rgb($r,$g,$b)";
}

/**
 * 截取字符
 * @param type $str
 * @param type $int
 * @return type
 */
function ysubstr($str, $int = 15) {
    $s = null;
    if (mb_strlen($str, 'utf-8') > $int)
        $s = '…';
    return mb_substr($str, 0, $int, 'utf-8') . $s;
}

/**
 * 处理url
 * @param type $url
 * @return type
 */
function u($url) {
    //如果 URL 的最后面是 / 或者 _ 则删除
    if (substr($url, -1) === '/' || substr($url, -1) === '_') {
        $url = substr($url, 0, -1);
    }
    $template_suffix = cfg('template_suffix');  //取伪静态文件后缀
    return $url . $template_suffix;
}

/**
 * 商品列表页专用 ，取属性值解析给url 使用
 * @param type $key
 */
function goodsurlattr($data, $k = null) {
    if (!empty($k))
        unset($data[$k]);
    $str = null;
    foreach ($data as $key => $value) {
        if (!empty($value))
            $str .= $key . '_' . $value . '_';
    }
    return $str;
}

/**
 * 用户登录 判断
 * @return string
 */
function loginJudbe() {
    //如果 是在手机上登录 另作判断 
    if ( judgeMobileBrowse() == true ) {
        if (isset($_COOKIE['author_316686606'])) {
            $userinfo = unserialize(base64_decode($_COOKIE['author_316686606']));
            if (is_array($userinfo)) {
                return $userinfo;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    if (!isset($_SESSION['userInfo']) or empty($_SESSION['userInfo']) or ! is_array($_SESSION['userInfo'])) {
//    if( !isset($_SESSION['userInfo']) or empty($_SESSION['userInfo']) or !is_array($_SESSION['userInfo']) or $_SESSION['userInfo']['expiretime'] < time() ){
        if (isset($_SESSION['userInfo']))
            unset($_SESSION['userInfo']);
        return false;
    }else {
//        if( time()+120 > (int)$_SESSION['userInfo']['expiretime'] ){
//            $_SESSION['userInfo']['expiretime'] = time() + 120;
//        } 
        return $_SESSION['userInfo'];
    }
}

/**
 * 输出消息。
 * @param type $content
 */
function message($content, $title = 'error') {
    $model = new \core\Controller();
    if (isset($_SESSION['userInfo']) and ! empty($_SESSION['userInfo'])) {
        $model->assign('userinfo', $_SESSION['userInfo']);
    }
    $model->assign('title', $title);
    $model->assign('messageContent', $content);
    $model->display('other/message');
    exit();
}

/**
 * 从快递100 获取快递的物流信息 
 * @param int $id  快递单号
 * @param string $com  快递公司编码代号
 * @return array 返回PHP数组
 */
function getkuaidi($id, $com) {
    $data = gethtml("www.kuaidi100.com", "http://www.kuaidi100.com/query?type=$com&postid=$id&id=&valicode=");
    return json_decode($data, true);
}

/**
 * 返回一个栏目下所有子栏目 
 * @param type $id  父栏目ID
 * @param type $data   从数据库查出的栏目数据
 * @param type $arr   写入的数组数据
 */
function returnColumnSon($id = 0, $data, &$arr) {
    foreach ($data as $key => $value) {
        if ($value['parentId'] == $id) {
            $arr[] = $value['id'];
            returnColumnSon($value['id'], $data, $arr);
        }
    }
}

/**
 * 替换栏目类型的数字为指定字符
 * @param type $typeid
 * @return string
 */
function replaceColumnType($typeid = null) {
    $str = 'i';
    switch ((int) $typeid) {
        case 1:
            $str = 'i';
            break;
        case 2:
            $str = 'i';
            break;
        case 3:
            $str = 'c';
            break;
        case 4:
            $str = 'g';
            break;
        default:
            break;
    }
    return $str;
}

function searchColumnSpeStyle($id = null) {
    if (!isset($GLOBALS['CurColumnId']) || count($GLOBALS['CurColumnId']) == 0 || empty($id)):
        return false;
    endif;
    if (array_search($id, $GLOBALS['CurColumnId']) !== FALSE) {
        return true;
    }
}

/**
 * 微商城 商品详情页面的图片替换 
 */
function regeximage1($content) {
    $content = str_replace(array('800_', '400_'), 'thumb_', $content);
    return "<img src='/static/default.gif' xsrc='$content' init='1' ";
}

/**
 * 
 * @param type $src  12100  被替换的字符串
 * @param type $i1    2   替换第几位
 * @param type $re    3   替换成什么 ？
 * 
 * @return str    13100
 */
function replaceStr($src = 'abcdef', $i1 = 3, $re = '3') {
    $i = 1;
    $res = null;
    foreach (str_split($src) as $k => $v) {
        if ($i == $i1) {
            $v = $re;
        }
        $res = $res . $v;
        $i++;
    }
    return $res;
}

/**
 * 根据栏目ID或标签 获取 模型标签名 ，也就是表名
 * @param type $id
 * @return array  数组 ，栏目id，栏目名称，模型名称
 */
function getArticletable($id=null){
    if($id){
        $m = new \core\model();
        $sql = "select c.id,c.name,c.tplList,c.tplArticle,c.tplContent,m.mark from `".SQLPRE."column` c left join `".SQLPRE."models` m on c.model=m.id where c.id='$id' or c.mark='$id'";
        $info = $m->getrow($sql);
        if( $info ){
            return $info;
        }
    }
    return false;
}

/**
 * 查询文章评论状态 
 * @param type $id  文章ID
 * @param type $table   文章表名
 * @return bool  true  为已经 关闭评论, false  为未关闭或者参数错误
 */
function getartcommentstatus($id=null,$table=null){
    
    if( $id && $table ){
        $m = new \core\model();
        //查询文章是不是已经禁止评论  start
        $sql = "select arcrank from `".SQLPRE."$table` where id=$id";
        $arcrank = $m->getone($sql);
        if( $arcrank=='oc' ){  
            return 1;
        }else{
            return false;
        }
        //查询文章是不是已经禁止评论  end
    }else{
        return false;
    }
    
}

/**
 * 发送注册验证短信
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgReg($mobile = null) {
    if ( empty($mobile) ) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->getall('select mark as yixinu,val from '.SQLPRE.'shop_config where mark like "alidayu%"');
        if( $config['alidayusmssendstatus']['val'] == '2' ){
           return array('msg'=>'短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate1']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname1']['val'];
        //配置 end  ########################################
        
        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsRegCode'] = md5($code);
        $SmsParam = array(
            'code'=>(string)$code,
            'product'=>'[异新优商城内容系统]'
        );
        
        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `".SQLPRE."sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->getone($sql);
        if($sendtime){
           return array('msg'=>'发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '注册验证:'.$code;
        $m->sData($data, 'sendsmslog');
        //保存发送记录  end
        
        
        //如果开启了调试模式 ， 直接 返回 1
        if( $config['alidayusmssendstatus']['val'] == '3' ){
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string)$mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换
//        return 1;
        
        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if( isset($sendres['result']) ){
            return 1;
        }else{
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg']='发送太频繁或已经超限制，请稍后再试！'; break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg']='手机号码格式错误！'; break;
            }
            return $sendres;
        }
    }
}

/**
 * 发送修改密码验证短信
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgChpwd($mobile = null) {
    if ( empty($mobile) ) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->getall('select mark as yixinu,val from '.SQLPRE.'shop_config where mark like "alidayu%"');
        if( $config['alidayusmssendstatus']['val'] == '2' ){
           return array('msg'=>'短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate2']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname2']['val'];
        //配置 end  ########################################
        
        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsRegCodeChpwd'] = md5($code);
        $SmsParam = array(
            'code'=>(string)$code,
            'product'=>'[异新优商城内容系统]'
        );
        
        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `".SQLPRE."sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->getone($sql);
        if($sendtime){
           return array('msg'=>'发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '修改密码:'.$code;
        $m->sData($data, 'sendsmslog');
        //保存发送记录  end
        
        
        //如果开启了调试模式 ， 直接 返回 1
        if( $config['alidayusmssendstatus']['val'] == '3' ){
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string)$mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换
        
        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if( isset($sendres['result']) ){
            return 1;
        }else{
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg']='发送太频繁或已经超限制，请稍后再试！'; break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg']='手机号码格式错误！'; break;
            }
            return $sendres;
        }
    }
}

/**
 * 发送验证短信  变更验证
 * @param type $mobile    号码
 * @return int or array 如果发成功返回 整数 1 ，如果失败返回一个数组
 */
function sendMsgMod($mobile = null) {
    if ( empty($mobile) ) {
        return false;
    } else {
        $m = new core\model();
        //配置 start  ########################################
        //短信发送功能 开启状态
        $config = $m->getall('select mark as yixinu,val from '.SQLPRE.'shop_config where mark like "alidayu%"');
        if( $config['alidayusmssendstatus']['val'] == '2' ){
           return array('msg'=>'短信发送功能已经关闭！');
        }
        //阿里大鱼  APPKEY
        $appkey = $config['alidayuappkey']['val'];
        //阿里大鱼  AppSecret
        $secret = $config['alidayuappsecret']['val'];
        //阿里大鱼  短信模板（用户注册）
        $SmsTemplateCode = $config['alidayusmstemplate3']['val'];
        //阿里大鱼  短信签名（用户注册）
        $SmsFreeSignName = $config['alidayusmssignname3']['val'];
        //配置 end  ########################################
        
        $code = rand(110000, 999999);
        $_SESSION['yixinuSmsModcode'] = md5($code);
        $SmsParam = array(
            'code'=>(string)$code,
            'product'=>'[异新优商城内容系统]'
        );
        
        //检测5分钟内是否已经发送过了  start 
        $sql = "SELECT datetime FROM `".SQLPRE."sendsmslog` WHERE `datetime` > now()-INTERVAL 2 minute and mobile='$mobile'";
        $sendtime = $m->getone($sql);
        if($sendtime){
           return array('msg'=>'发送太频繁，请两分钟后再试。');
        }
        //检测5分钟内是否已经发送过了  end
        
        //保存发送记录  start
        $data['mobile'] = $mobile;
        $data['content'] = '身份验证:'.$code;
        $m->sData($data, 'sendsmslog');
        //保存发送记录  end
        
        
        //如果开启了调试模式 ， 直接 返回 1
        if( $config['alidayusmssendstatus']['val'] == '3' ){
            return 1;
        }
        //阿里大鱼短信发送   http://www.alidayu.com/
        include APP_PATH . 'taobaosdk/TopSdk.php';
        $c = new TopClient;
        $c->appkey = $appkey;
        $c->secretKey = $secret;
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($SmsFreeSignName);
        $req->setSmsParam(json_encode($SmsParam));
        $req->setRecNum((string)$mobile);
        $req->setSmsTemplateCode($SmsTemplateCode);
        $resp = $c->execute($req);
        //返回的数据是XML对象，需要转换
        
        $sendres = array();
        $sendres = json_decode(json_encode($resp), TRUE);
        if( isset($sendres['result']) ){
            return 1;
        }else{
            switch ($sendres['sub_code']) {
                case 'isv.BUSINESS_LIMIT_CONTROL': $sendres['msg']='发送太频繁或已经超限制，请稍后再试！'; break;
                case 'isv.MOBILE_NUMBER_ILLEGAL': $sendres['msg']='手机号码格式错误！'; break;
            }
            return $sendres;
        }
    }
}

/**
 * 取微信配置
 * @return type
 */
function get_weixin_config(){
        $m = new \core\model();
        $sql = "select val from `" . SQLPRE . "shop_config` where mark='weixinappid' ";
        $appid = $m->getone($sql);
        $sql = "select val from `" . SQLPRE . "shop_config` where mark='weixinsecret' ";
        $secret = $m->getone($sql);
        return array('appid'=>$appid,'secret'=>$secret);
}

/**
 * 微信登录，获取 OPENID
 * @param string $return_url  跳转到哪个地址
 * @return type
 */
function getOpenid($return_url=null) {
    if (isset($_COOKIE ['weixin_user_openid']) && !empty($_COOKIE ['weixin_user_openid'])) {
        return $_COOKIE ['weixin_user_openid'];
    }
    $config = get_weixin_config();
    //微信回调地址
    if(!$return_url){
        $return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
    //$login_return_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $oauth2_code = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $config['appid'] . "&redirect_uri=" . urlencode($return_url) . "&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";
    header("location:$oauth2_code");
    exit();
}

/**
 * 微信登录，获取OPENID回调地址的方法
 */
function wx_oauth() {
    if (isset($_COOKIE ['weixin_user_openid']) && !empty($_COOKIE ['weixin_user_openid'])) {
        return $_COOKIE ['weixin_user_openid'];
    }
    if (isset($_REQUEST ['code'])) {
        $config = get_weixin_config();
        $state = $_REQUEST ['state'];
        $code = $_REQUEST ['code'];
        $oauth2_code = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $config['appid'] . "&secret=" . $config['secret'] . "&code=" . $code . "&grant_type=authorization_code";
        $content = file_get_contents($oauth2_code);
        $token = @json_decode($content, true);
        if (empty($token) || !is_array($token) || empty($token ['access_token']) || empty($token ['openid'])) {
            msg('获取微信公众号授权' . $code . '失败[无法取得token以及openid], 请稍后重试！ 公众平台返回原始数据为: ' . $content);
        }
        $openid = $token ['openid'];
        setcookie('weixin_user_openid', $openid, time() + 7200);
        return $openid;
    }
}

/**
 * 微信登录，取用户信息
 */
function getWxuserInfo($openid) {
    require_once IFRAME_ROOT . '/api/jssdk.php';
    $config = get_weixin_config();
    $jssdk = new JSSDK($config['appid'], $config['secret']);
//    throw new \Exception($openid);
    $accessToken = $jssdk->getAccessToken();
    $oauth2_url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$openid&lang=zh_CN";
    $content = file_get_contents($oauth2_url);
    $info = @json_decode($content, true);
    //如果是本地调试
//    if ( islocalhost() ) {
//        $string = '{"subscribe":1,"openid":"o_mSyt2m8pP8OvyF-yEPXd-cjo11","nickname":"\u56db\u56cd","sex":1,"language":"zh_CN","city":"\u957f\u6c99","province":"\u6e56\u5357","country":"\u4e2d\u56fd","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/b2ONlmmVZRrCbLrdGoS2FO2xeucVd28ibXMw8icic3ym7B8iaKCOjxFtWIn2MI0UcIrrDQ8qW38k5gTJLr7r79ZvOY3Z6MSzgJ17\/0","subscribe_time":1480405448,"remark":"","groupid":0,"tagid_list":[]}';
//        return json_decode($string, 1);
//    }
    return $info;
}



/**
 * 更新商品缓存
 */
function up_goods_cache() {
    $redis = new \models\yredis();
    $model = new \core\model();
    $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
            . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
            . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou,business_name "
            . " from " . $model->prefix . "goods as g "
            . " left join ".SQLPRE."business b on g.business_no=b.business_no"
            . " left join " . $model->prefix . "category as gc on g.catId=gc.id "
            . " left join " . $model->prefix . "goods_additional as ga on g.id=ga.goodsId "
            . " left join `" . $model->prefix . "activity_goods` ag on ga.id=ag.goodsid "
            . " left join " . $model->prefix . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
//                . "left join `$tablename` as a on ga.sn=a.sn ";
    $list = $model->getall($sql);
    foreach ($list as $key => $value) {
        $redis->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));
        //更新库存和销量
        $redis->setex(REDIS_PRE . 'goods_salesval_' . $value['id'], 86400 * 10, (int) $value['salesval']);  //销量
        $redis->setex(REDIS_PRE . 'goods_numbers_' . $value['id'], 86400 * 10, (int) $value['numbers']);  // 库存 
    }
    
    //缓存每个仓库的商品库存
    $sql = "SELECT og.goods_id, og.goods_name, sum(nums) as n , sum(delivery_quantity) as delivery_quantity , (
                    SUM( og.nums ) - SUM( og.delivery_quantity )
                    ) AS nums, o.warehouse_name, o.warehouse_id, g.numbers
                    FROM  `".SQLPRE."purchase_order_goods` og
                    INNER JOIN  `".SQLPRE."goods_additional` g ON og.goods_id = g.id
                    INNER JOIN  `".SQLPRE."purchase_order` o ON og.purchase_order_id = o.id
                    WHERE og.status =1 and og.delivery_quantity < og.nums
                    GROUP BY og.goods_id, o.warehouse_id";
    $stores_goods = $model->getall($sql);
    foreach ($stores_goods as $key => $value) {
        $redis->setex(REDIS_PRE . 'stores_goods_' . $value['warehouse_id'].'_'.$value['goods_id'], REDIS_TTL, $value['nums']);
    }


    //删除所有商品分类的缓存
    $arr = $redis->keys(REDIS_PRE . 'goodscate_info*');
    foreach ($arr as $v) {
        $redis->del($v);
    }

    //记录更新商品缓存的时间
    $redis->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());
}

/**
 * 根据编号获取地址名称
 * @param type $proviceSn
 * @param type $citySn
 * @param type $countySn
 * @return type
 */
function get_addres_name($proviceSn, $citySn, $countySn) {
    $m = new \core\model();
    $data = array();
    $data['provice_name'] = $m->getone("select provice_name  from " . $m->prefix . "area_provice  where provice_id ='$proviceSn' ");
    $data['city_name'] = $m->getone("select city_name  from " . $m->prefix . "area_city  where city_id ='$citySn' ");
    $data['county_name'] = $m->getone("select county_name  from " . $m->prefix . "area_county  where county_id ='$countySn' ");
    return $data;
}



/**
 * 拆分订单
 */
function split_order_specify_business($goods_list = array() ){
    $m = new core\model();
    //step 1  按商户拆单
    $new_order_1 = array();
    foreach ($goods_list as $goods_id => $item ) {
        $business_no = $item['business_no'];
        $new_order_1[$business_no][$goods_id] = $item;
    }
    return $new_order_1;
}

/**
 * 根据用户收货地址 , 匹配商户最近的仓库
 */
function match_stores($lat,$lng , $business_no){
        if( $lat && $lng && $business_no ){
            $m = new core\model();
            $sql = "SELECT *, "
                    . "round(6378.138*2*asin(sqrt(pow(sin(($lat*pi()/180-lat*pi()/180)/2),2)+cos($lat*pi()/180)*cos(lat*pi()/180)*pow(sin(($lng*pi()/180-`long`*pi()/180)/2),2)))*1000) as juli "
                    . "FROM ".SQLPRE."stores where business_no=$business_no order by juli asc";
            $info = $m->getrow($sql);
            $address = get_addres_name($info['provice'],$info['city'],$info['county']);
            return array_merge($info,$address);
        }else{
            return false;
        }
}


/**
 * 扣库存 , 更新库存
 */
function buckle_stock($order_id){
    
    $m = new \core\model();
    $order_info = $m->getrow("select id,business_no,orderSn,userId,delivery_warehouse,warehouse_name from `".SQLPRE."orders` where id=$order_id");
    $d['warehouse_id'] = $order_info['delivery_warehouse'];
    $d['warehouse_name'] = $order_info['warehouse_name'];
    $d['business_no'] = $order_info['business_no'];
    $d['userId'] = $order_info['userId'];
    $d['orderSn'] = $order_info['orderSn'];
    
    $goods_list = $m->getall("select goodsid,goodsname,goodssn,goodsnum from ".SQLPRE."order_goods where orderid=$order_id");
    foreach ($goods_list as $g) {
        $goods['goods_id'] = $g['goodsid'];
        $goods['goods_name'] = $g['goodsname'];
        $goods['nums'] = $g['goodsnum'];
        buckle_stock_specify_goods($goods, $d);
    }
    
    //更新缓存
    up_goods_cache();
}

/**
 * 扣库存 , 更新库存 , 指定商品
 */
function buckle_stock_specify_goods($goods = array() , $d = array() ){
    $m = new \core\model();
    $redis = new models\yredis();
    $d2['reason'] = 2;
    $d2['type'] = 2;
    $d2['comments'] = $d['orderSn'];
    $data = array_merge($d2,$goods,$d);
    unset($data['userId']);
    unset($data['orderSn']);
    //写出库记录
    $insert_id = $m->sData($data, 'inventory_records');
    
        
    //begin 从仓库减库存
    //取出每一批采购的商品，按批次减库存
    $sql = "select og.id,og.nums,og.delivery_quantity,( og.nums - og.delivery_quantity ) as total,o.warehouse_id "
        . " from `".SQLPRE."purchase_order_goods` og "
        . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
        . " where og.goods_id=$data[goods_id] and o.warehouse_id=$data[warehouse_id] and og.delivery_quantity<og.nums order by og.id desc ";
    $goods = $m->getall($sql);
    if(!$goods): return false; endif;

    $nums_ = 0;  //用来保存实际减的库存
    foreach ($goods as $item) {
        if($item['total'] < 0){
            continue;
        }
        $nums = $data['nums']; //保存之前要减的数量 ，以免计算后原来的值会改变

        //要出库的数量减 去这一批次可出库数量 ，而不是减这一批次的库存数量
        $data['nums'] = intval($data['nums']) - intval($item['total']);

        // 写入到数据库的出库数量 ，如果大于0，表示减的数量比这一批可出库数量要大，
        // 下一批要接着减 ，所以出库数量就是这一批的可出库数量，否则就是填要减的数量
        $write_num = $data['nums']>0 ? $item['total'] : $nums;
        $m->sData(array('delivery_quantity'=>"`delivery_quantity`+$write_num---"), 'purchase_order_goods', 'u', "id=$item[id]");
        $nums_ = $nums_ + $write_num;  //把每一次减去的加起来就是总共要减的库存

        //如果减完了，直接退出
        if($data['nums']<=0){
            break;
        }
    }
    //end

    //begin 减总库存
    $d3 = array(
            "numbers"=>"`numbers`-$nums_---",//减库存
            "salesval"=>"`salesval`+$nums_---",//增加销量
          );
    $m->sData($d3, 'goods_additional', 'u',"id=$data[goods_id]");
    //end
    
    //销量和库存同时记录到redis
    $redis->incrBy(REDIS_PRE.'goods_salesval_'.$data['goods_id'],$nums_);  //销量
    $redis->decrBy(REDIS_PRE.'goods_numbers_'.$data['goods_id'],$nums_);  //库存
    
    //记录用户购买记录，用作限购的判断
    ini_set('date.timezone', 'Asia/Shanghai');
    $n1 = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$d['userId'].'-'.$data['goods_id'])?:0;
    $redis->setex( REDIS_PRE.'goods_xianglouinfo_'.$d['userId'].'-'.$data['goods_id'] 
            ,strtotime(date("Y-m-d")." 23:59:59") - time()
            ,$nums_+$n1);
                    

    //更新出库记录,更新最后实际减的库存
    $m->sData(array("nums"=>$nums_ ), 'inventory_records', 'u',"id=$insert_id");
    
    return true;
}

/**
 * 判断有没有特殊字符 
 * @param type $string
 */
function filter_string($string){
    $bool = preg_match("/^(.*?)[\<|\>|\=|\/|\.|\?|\+|\'|\!|\@|\#|\$|\%|\*](.*?)$/", $string);
    if($bool){
        return false;
    }else{
        return true;
    }
}


// ###################### 模板函数  start  ###################### 
// 
//返回template对象
function obj1() {
    return new \models\template();
}

//获取一个栏目数据
function getcolumn($id,$type='pc') {
    return obj1()->getcolumn($id,$type);
}

//获取一个商品分类
function getgoodscate($id = 0) {
    return obj1()->getgoodscate($id);
}

//获取一个栏目的文章列表
function getartlist($id = 0, $num = 10, $order = 1) {
    return obj1()->getartlist1($id, $num, $order);
}

//获取一个频道的文章列表
function getartlist2($id = 0, $num = 10, $order = 1) {
    return obj1()->getartlist2($id, null, $num, $order);
}

//获取自定义数据组
function getcustom1($id) {
    return obj1()->getcustom1($id);
}

//获取招聘职位列表
function getjobslist($num = 10) {
    return obj1()->getjobslist($num);
}

//获取一个商品分类下的所有商品数据
function getonegoodscate($id=null,$num=10){
    return obj1()->getonegoodscate($id,$num);
}

/**
 * 获取一个滚动图片组的图片数据
 * @param type $id
 * @return array  {id,rid,link,img} {id,rid,链接,图片地址}
 */
function getrollimage($id){
    return obj1()->getrollimage($id);
}

function getgoodssoncate($id){
    return obj1()->getgoodssoncate($id);
}

// ###################### 模板函数  end  ###################### 