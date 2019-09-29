<?php

/**
 * Description of userController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */
namespace controllers;
class userController extends comController {
    
    public $userInfo =false;


    public $menuConfig = array(
        'index'=>'用户中心 - 首页',
        
        'order'=>'我的订单',
        'integral'=>'我的积分',
        'shoppingcart'=>'我的购物车',
        'orderinfo'=>'订单详情',
        'ticket'=>'我的优惠券',
        'BuyTheGoods' =>'买到的商品',
        
        'refund'=>'退货申请',
        'ArtComment'=>'我的评论 (文章)',
        'GoodsComment'=>'我的评论 (商品)',
        
        'money'=>'我的资金',
        
        'info'=>'个人资料',
        'consume'=>'消费记录',
        'address'=>'地址管理',
        'addNewAddress'=>'地址管理 - 编辑地址',
        'cpwd'=>'修改密码'
    );
    
    public $menul = array(
        array('home'=>'用户中心',array('index')),
        array('deal'=>'交易',array('order','BuyTheGoods','shoppingcart')),
        array('service'=>'服务',array('GoodsComment','ArtComment')),
        array('account'=>'账户管理',array('ticket','integral')),
        array('setup'=>'个人设置',array('info','address','cpwd')),
    );
    
    public function __construct() {
        parent::__construct();
        $this->userInfo = loginJudbe();
        if( $this->userInfo==false ){ 
            //判断用户是不是使用的ajax的方式请求
            if(judgeAjaxRequest()){
                echo 0; exit();
            }else{
                header('Location: /u/login');
                exit();
            }
        }
        
        $this->assign('menul', $this->menul);
        $this->assign('menuConfig', $this->menuConfig);
        
        if(array_key_exists(ACTION_NAME, $this->menuConfig) ){
            $this->assign('webtitle_', $this->menuConfig[ACTION_NAME]);
        }
    }
    
    
    /**
     * 用户中心 首页
     */
    public function index() {
        $m = new \core\model();
        //基本信息
        
        $userId = $this->userInfo['id'];
        $sql = "select u.*,ur.alias as rankName,if(u.avatar is null,'default.gif',u.avatar) as avatar from ".SQLPRE."users u left join `".SQLPRE."user_rank` ur on u.userRank=ur.id  where u.id=$userId";
        $info = $m->getrow($sql);
        unset($info['password']);
         //达到下一等级的数据
        $nextRankId = $info['userRank'] + 1;
        $sql = "select alias,id,points from `".SQLPRE."user_rank` where id=$nextRankId";
        $nextRankData = $m->getrow($sql);
        if(!empty($nextRankData)){
            $nextRank['points'] = $nextRankData['points'] - $info['payPoints'];
            $nextRank['name'] = $nextRankData['alias'];
            $this->assign('nextRank', $nextRank);
        }
        //右侧基本信息
        $info['money'] = $m->getone("SELECT sum(amount) FROM `".SQLPRE."orders` WHERE userId=$userId");  //累计消费
        $info['orderCount'] = $m->getone("SELECT count(id) FROM `".SQLPRE."orders` WHERE userId=$userId");  //订单数
        $info['goodsCount'] = $m->getone("SELECT count(id) FROM `".SQLPRE."users_shoppingcart` WHERE userid=$userId");  //购物车商品数
        $info['coupon'] = $m->getone("SELECT count(id) FROM `".SQLPRE."coupon_issue` WHERE userId=$userId");  //优惠券
        $this->assign('info', $info);
        
        //最近订单
        $sql ="select * from ".SQLPRE."orders where userId=$userId order by id desc  limit 0,5";
        $result = $m->getall($sql);
        $this->assign('orderlist', $result);
        
        $this->display('user/index');
    }
    
    /**
     * 个 人资料
     */
    public function info() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        if(isset($_POST['_submit_'])){
            $data['birthday'] = $this->_post('birthday');
            $data['nickname'] = $this->_post('nickname');
            $data['email'] = $this->_post('email');
            $data['question'] = $this->_post('question');
            $data['answer'] = $this->_post('answer');
            $data['sex'] = $this->_post('sex');
            $result = $m->sData($data, 'users','u',"id=$userId");
            if($result){
                header('Location: ?c=user&a=info'); exit();
            }
        }else{
            $sql = "select *,if(avatar is null,'default.gif',avatar) as avatar from ".SQLPRE."users where id=$userId";
            $info = $m->getrow($sql);
            $this->assign('info', $info);
            $this->display('user/info');
        }
    }
    
    /**
     * 更换头像
     */
    public function changeAvatar() {
        
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        if(isset($_POST['_submit_'])){
            
                $path = './avatar';
                $imageInfo = uploadImage($_FILES['imagefile'], $path, 'tmp_avatar_'.$userId);
                if(is_array($imageInfo) ){
                        //生成小图
                        $thumbImage = $path.'/avatar_'.$userId.".".$imageInfo['type']; //缩略图文件名，这里是全路径 
                        makeThumb($imageInfo['path'], $thumbImage);
                        unlink($imageInfo['path']);
                        //取文件名
                        $pinfo = pathinfo($thumbImage);
                        $fname = $pinfo['basename'];
                        //写入数据库
                        $data['avatar'] = $fname;
                        $m->sData($data, 'users', 'u', "id=$userId");
                        $data['path'] = $path.$fname;   //echo " 已经成功上传：".$photo_server_folder."<br />";
                        echo json_encode($data);
                        exit();
                }elseif($imageInfo==false){
                    $data['info'] = 403;
                }elseif($imageInfo==401 || $imageInfo==402 || $imageInfo==403){
                    $data['info'] = $imageInfo;
                }
                echo json_encode($data);
                exit;
                
        }else{
            $this->display('user/changeAvatar');
        }
    }
    
    /**
     * 我的优惠券
     */
    public function ticket() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select ci.*,if(now()<ci.endTime,ci.status,3) as status from `".SQLPRE."coupon_issue` ci where ci.userId=$userId limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from ".SQLPRE."coupon_issue where userId=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('user/ticket');
    }
    
    /**
     * 我的积分
     */
    public function integral() {
        $id = $this->userInfo['id'];
        $m = new \core\model();
        
        $sql = "select u.*,ur.alias as rankName,if(u.avatar is null,'default.gif',u.avatar) as avatar from ".SQLPRE."users u left join `".SQLPRE."user_rank` ur on u.userRank=ur.id  where u.id=$id";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        
        $sql = "select * from `".SQLPRE."user_rank` where status=1";
        $userrank = $m->getall($sql);
        $this->assign('list', $userrank);
        $this->display('user/integral');
    }
    
    /**
     * 买到的商品
     */
    public function BuyTheGoods() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select og.* from `".SQLPRE."orders` o "
                . " left join `".SQLPRE."order_goods` og on o.id=og.orderid "
                . " where o.userId=$userId  and o.payStatus=2 order by id desc limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone($sql = "select count(og.id) from `".SQLPRE."orders` o left join `".SQLPRE."order_goods` og on o.id=og.orderid where o.userId=$userId  and o.payStatus=2");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('user/BuyTheGoods');
    }
    
    /**
     * 收货地址管理
     */
    public function address() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        $sql = "select ua.*  from " . SQLPRE . "users_address ua where ua.userid=$userId";
        $adrlist = $m->getall($sql);
        $this->assign('list', $adrlist);
        $this->display('user/address');
    }
    
    /**
     * 添加收货地址
     */
    public function addNewAddress() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        $id = $this->rget('id');
        //总共有多少个收货地址，如果大于12个，不能再添加
        $addressCount = $m->getone("select count(id) from ".SQLPRE."users_address where userid=$userId");
        //如果是添加收货地址并且用户收货地址大于等于12个了
        if($addressCount>=12 and $id==false){
             header("Location: /user/address"); exit();
        }
        
        if(isset($_POST['_submit_'])){
            
            $data['proviceSn'] = $this->_post('provice');   //省
            $data['citySn'] = $this->_post('city');  //市
            $data['countySn'] = $this->_post('county');  //县 / 区
            $data['townSn'] = $this->_post('town');  //乡镇
            $data['street'] = $this->_post('street');   //街道地址
            $data['zipcode'] = $this->_post('zipcode');   //邮政编码
            $data['recipients'] = $this->_post('recipients');   //收货人姓名
            $data['mobile'] = $this->_post('mobile');   //手机号码
            $data['phone'] = $this->_post('phone');  //电话号码
            $data['lat'] = $this->_post('lat');  //坐标  经纬度
            $data['long'] = $this->_post('long');  //坐标  经纬度
            $data['userid']  = $userId;
            $ad = get_addres_name($data['proviceSn'], $data['citySn'], $data['countySn']);
            
            $result = null;
            
            //如果是修改收货地址
            if($id){
                $result = $m->sData(array_merge($data, $ad), 'users_address','u',"id=$id and userid=$userId");
            }else{  //否则是添加收货地址
                $result = $m->sData(array_merge($data, $ad), 'users_address');
            }
            if($result){
                header("Location: /user/address"); exit();
            }
        }else{
            //省市区默认值  start
            $info['proviceSn'] = 0;
            $info['citySn'] = 0;
            $info['countySn'] = 0;
            $info['townSn'] = 0;
            //省市区默认值  end
            
            //如果是修改地址
            if($id){
                $sql = "select * from ".SQLPRE."users_address where id=$id and userid=$userId";
                $info = $m->getrow($sql);
            }
            $this->assign('info', $info);
            $this->display('user/addNewAddress');
        }
    }
    
    /**
     * 删除收货地址
     */
    public function deleteAddress() {
        $userId = $this->userInfo['id'];
        $id = $this->rget('id');
        $m = new \core\model();
        $sql = "delete from ".SQLPRE."users_address where  id=$id and userid=$userId";
        $result = $m->query($sql);
        if($result){
            
        }
        header("Location: /user/address"); exit();
        
    }
    
    /**
     * 修改密码
     */
    public function cpwd() {
        
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        if(isset($_POST['_submit_'])){
            $data['password'] = md5($_POST['password']);
            $oldpassword = $_POST['oldpassword'];
            $verifystr = $_POST['yzm'];
             //如果图片验证码正确
            if(md5( strtoupper($verifystr) ) == $_SESSION['YixinuVerify']){
                $sql = "select password from ".SQLPRE."users where id=$userId";
                $op = $m->getone($sql);
                if($op==md5($oldpassword)){
                    $result = $m->sData($data, 'users', 'u', "id=$userId");
                    if($result){
                        $this->assign('status', 1);
                        $this->display('user/cpwd');
                        exit();
                    }
                }
            }
            $this->assign('infoDisplay', 'block');
            $this->display('user/cpwd');
        }else{
            $this->display('user/cpwd');
        }
        
    }
    
    /**
     * 购物车 列表
     */
    public function shoppingcart() {
        $m = new \core\model();
        $redis = new \models\yredis();
        $userId = $this->userInfo['id'];
        $sql = "select  id,goodsid,goodsnum from `".SQLPRE."users_shoppingcart` where userid=$userId";
        $L = $m->getall($sql);
        foreach ($L as $k=>$value) {
            $v = json_decode($redis->get(REDIS_PRE.'goods_'.$value['goodsid']) ,1);
            if(!$v){
                unset($L[$k]);
                continue;
            }
            //从缓存读取库存，保存准确性
            $value['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$value['goodsid']); 
            //取用户的购买记录， 用作限购判断 
            $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userId.'-'.$value['goodsid'])?:0;
            $L[$k] = array_merge($v,$value);
        }
//        var_dump($goodslist);
        $this->assign('goodslist', $L);
        $this->display('user/shoppingcart');
    }
    
    //修改购物车中的商品数量
    public function changeCartGoodsNum() {
        $m = new \core\model();
        $redis = new \models\yredis();
        $goodsid = $this->_get('id');
        $num = $this->_get('num');
        $userInfo = loginJudbe();
        //限购判断
        $xiangouuser = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userInfo['id'].'-'.$goodsid )?:0;
        $xiangou = $m->getone("select xiangou from `".SQLPRE."activity_goods` where goodsid=$goodsid and aid > 0")?:0;
        //限购判断 ： 已经购买过的数量 + 新添加的数量 + 购物车里已经添加的数量
        if( $xiangou>0 && $xiangouuser+$num >$xiangou ){
            echo json_encode(array('status' => 4));exit();  //已经超过限购件数
        }
                
        $sql = "update `".SQLPRE."users_shoppingcart` set goodsnum=$num where goodsid=$goodsid and userid=$userInfo[id]";
        $m->query($sql);
        echo json_encode( array('status'=>1) );
        exit();
    }
    
    /**
     * 用户订单列表
     */
    public function order() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 9;  //一页9条数据
        $start = $p*$listrows-$listrows;
        
        $sql ="select * from ".SQLPRE."orders where userId=$userId order by id desc  limit $start,$listrows";
        $result = $m->getall($sql);
        $this->assign('list', $result);
        
        //总记录数
        $sql ="select count(id) as count from ".SQLPRE."orders where userId=$userId";
        $count = $m->getone($sql);
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => "/user/order/p_00000.html",  //后面5个0是替换页码的字符
            'method' =>'html',
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->display('user/order');
    }
    
    /**
     * 订单详情  查看订单 
     */
    public function orderinfo() {
        $id = $this->_get('id');
        $sn = $this->_get('sn');
        $m = new \core\model();
        //如果使用ID查询
        if($id){
            $sql = "select * from ".SQLPRE."orders where id=$id";
            $orderinfo = $m->getrow($sql);
        }
        //如果使用编号查询
        if($sn){
            $sql = "select * from ".SQLPRE."orders where orderSn='$sn'";
            $orderinfo = $m->getrow($sql);
            $id = $orderinfo['id'];
        }
        if(empty($orderinfo) || count($orderinfo)<=0){
            message("没有找到该订单，可能已经删除！");
        }
        
        $orderinfo['payTypeLogo'] = $m->getone("select logo from `".SQLPRE."payment` where sn='$orderinfo[payType]'");
        $orderinfo['deliveryId'] = $orderinfo['deliveryMethod'];
        $orderinfo['deliveryMethod'] = $m->getone("select name from `".SQLPRE."deliverys` where id=$orderinfo[deliveryMethod]");
        $address = $m->getrow("select ua.id,ap.provice_name,ac.city_name,act.county_name  from ".SQLPRE."orders ua "
                . "left join ".SQLPRE."area_provice ap on ua.proviceId=ap.provice_id "
                . "left join ".SQLPRE."area_city ac on ua.cityId=ac.city_id  "
                . "left join ".SQLPRE."area_county act on ua.countyId=act.county_id "
                . " where ua.id=$id");
        $this->assign('address', $address);
        $this->assign('orderinfo', $orderinfo);
        
        //订单商品
        $sql = "select og.*  from ".SQLPRE."order_goods og  where orderid=$id";
        $goodslist = $m->getall($sql);
        $this->assign('goodslist', $goodslist);
        $this->display('user/orderinfo');
    }
    
    /**
     * 用户删除订单
     */
    public function deleteOrder() {
        $id = $this->rget('orderid');
        $userId = $this->userInfo['id'];
        $m = new \core\model();
        $sql = "delete from ".SQLPRE."orders where id=$id and userId=$userId";
        $result = $m->query($sql);
        if( $result ){
            $sql = "delete from  ".SQLPRE."order_goods where orderid=$id";
            $m->query($sql);
            
            echo json_encode( array('status'=>1) );
            exit();
        }
    }
    
    /**
     * 查看物流
     */
    public function viewPhysical() {
        $sn = $this->rget("sn");
        $deliveryId = $this->rget("deliveryId");
        if($sn){
            $m = new \core\model();
            $sql = "select ex_com from ".SQLPRE."deliverys where id=$deliveryId";
            $com = $m->getone($sql);
            $data = getkuaidi($sn, $com);
            if( $data['status']==200 ){
                //调整顺序   start
                $i = count($data['data']);
                $arr = array();
                foreach ($data['data'] as $key => $value) {
                    $arr[$i] = $value;
                    $i--;
                }
                sort($arr);
                $data['arr'] = $arr;
                unset($data['data']);
                //调整顺序   end
            }else{
                $data = array('arr'=>array(),'data'=>array());
            }
            $this->assign('info', $data);
            $this->display('user/viewPhysical');
        }
    }
    
    /**
     * 用户确认收货
     */
    public function takeDeliveryOfGoods() {
        $orderid = $this->rget('id');
        if($orderid){
            $m = new \core\model();
            //订单表状态
            $sql = "update ".SQLPRE."orders set delStatus=3,acceptTime=now(),completionTime=now() where id=$orderid";
            $result = $m->query($sql);
            //订单商品表中的状态
            $sql = "update `".SQLPRE."order_goods` set delStatus=3 where id=$orderid";
            $result = $m->query($sql);
            if($result){
                echo json_encode(array('status'=>1));
            }
            exit();
        }
    }
    
    /**
     * 用户发表商品评价
     */
    public function wcomment() {
        $userInfo = $userId = $this->userInfo;
        if($userInfo){
            $m = new \core\model();
            $goodsid_ = $this->rget('goodsid');
            //获取购买的商品信息，支付状态为已经支付的才可以评论
            $sql = "select og.* from `".SQLPRE."order_goods` og inner join ".SQLPRE."orders o on og.orderid=o.id where og.goodsid=$goodsid_ and o.userId=$userInfo[id] and og.payStatus>=2";
            $goodsData = $m->getrow($sql);
            
            if(isset($_POST['_submit_'])){
                //如果用户没有购买过该商品
                if( empty($goodsData) ): return false; endif;
                //处理上传的图片
                if( !empty($_FILES['commentimage1']['tmp_name']) ){
                    $path = './commentImage/'.date('Ymd');
                    $imageInfo = uploadImage($_FILES['commentimage1'],$path );
                    if($imageInfo==401 || $imageInfo==402 || $imageInfo==403){
                        echo json_encode(array('status'=>$imageInfo)); exit();
                    }elseif(is_array($imageInfo)){
                        //生成小图
                        $thumbImage1 = $path."/600_".$imageInfo['name']; 
                        $thumbImage2 = $path."/40_".$imageInfo['name']; 
                        makeThumb($imageInfo['path'], $thumbImage1,600,400);
                        makeThumb($imageInfo['path'], $thumbImage2,45,30);
                        unlink($imageInfo['path']);
                        $data['image1'] = $thumbImage2;
                    }elseif($imageInfo==false){
                        echo json_encode(array('status'=>403)); exit();
                    }
                }
                //获取goods表的ID
                $sql = "select goodsid from `".SQLPRE."goods_additional` where id=$goodsid_";
                $goodsid = $m->getone($sql); //goods表ID
                
                
                //获取评论默认状态   start
                $Cstatus = $GLOBALS['config']['commentDefaultStatus'];
                if(array_search($Cstatus, array(1,2,3))===false || !$Cstatus || empty($Cstatus)){
                    $Cstatus = 2;
                };
                $data['status'] = $Cstatus;
                //获取评论默认状态   end
            
                //评价内容过滤
                $commentContent_ = $this->_post('commentContent');
                $str_ = $GLOBALS['config']['commentfilt'];
                $order   = array("\r\n", "\n", "\r");
                $str = str_replace($order,',',$str_);
                $strArray = explode(',', $str);
                $commentContent = str_replace($strArray,'**',$commentContent_);  //过滤后的内容。
                $data['content'] = mb_substr($commentContent,0,500,'utf-8');  //截取字符 500
                
                $data['level'] = $this->_post('commentlevel');
                $data['userid'] = $userInfo['id'];
                $data['goodsid'] = $goodsid;
                $data['goodsid2'] = $goodsid_;
                $data['buytime'] = $goodsData['createTime'];
                $data['spec'] = $goodsData['goodsattributeStr'];
                $result = $m->sData($data, 'goods_comment');
                //记录评价数
                $data = null;
                $data['comments'] = "comments+1---";
                $m->sData($data, 'goods_additional','u',"id=$goodsid_");
                if($result){
                    
                    //只有好评才增加积分
                    if( intval($data['level'])===1 ){
                        //积分
                        $sql ="select val from `".SQLPRE."shop_config` where mark='pointsAct2'";
                        $p1 = $m->getone($sql);
                        $pointact = new \models\other();
                        $num = intval(floatval($p1));
                        $pointact->pointsAct($userInfo['id'], $num, 'add',
                                '评价商品 '.$goodsData['goodsname'].$goodsData['goodsattributeStr'].'，增加积分:'.$num
                                );
                    }
                    echo json_encode(array('status'=>1)); exit();
                }
            }else{
                //如果用户没有购买过该商品
                if( empty($goodsData) ):
                    $this->assign('msg', '购买商品后才可以发表评价！');
                endif;
                
                //查看上一条评论时间 是不是已经间隔两个小时了
                $sql = "SELECT ctime FROM `".SQLPRE."goods_comment` WHERE now() < `ctime` + INTERVAL 120 MINUTE and userid=$userInfo[id]";
                $prev = $m->getone($sql);
                if( !empty($prev) || $prev!=false ):
                    $this->assign('msg', '亲，评论太频繁了，过两个小时再来吧！');
                endif;
        
                $this->display('user/wcomment');
            }
        }else{
            echo 0; exit();
        }
    }
    /**
     * 文章评论提交数据
     */
    public function wcommentArt() {
        $m = new \core\model();
        $cid = $this->rget('cid'); //栏目ID
        $id = $this->rget('id');  //文章ID
        $userInfo = $userId = $this->userInfo;
        
        //查询文章是不是已经禁止评论  start
        $column_ = getArticletable($cid);
        if( getartcommentstatus($id,$column_['mark']) ){  echo 2; exit(); }
        //查询文章是不是已经禁止评论  end
        
        //查看上一条评论时间 是不是已经间隔两个小时了
        $sql = "SELECT ctime FROM `".SQLPRE."art_comment` WHERE now() < `ctime` + INTERVAL 120 MINUTE and userid=$userInfo[id]";
        $prev = $m->getone($sql);
        if( $prev ):
            echo 2; exit();
        endif;
        
        if(isset($_POST['_submit_'])){
            //获取评论默认状态   start
            $Cstatus = $GLOBALS['config']['commentDefaultStatus'];
            if(array_search($Cstatus, array(1,2,3))===false || !$Cstatus || empty($Cstatus)){
                $Cstatus = 2;
            };
            $data['status'] = $Cstatus;
            //获取评论默认状态   end
            
            //评价内容过滤
            $commentContent_ = $this->_post('commentContent');
            $str_ = $GLOBALS['config']['commentfilt'];
            $order   = array("\r\n", "\n", "\r");
            $str = str_replace($order,',',$str_);
            $strArray = explode(',', $str);
            $commentContent = str_replace($strArray,'**',$commentContent_);  //过滤后的内容。
            $data['content'] = mb_substr($commentContent,0,500,'utf-8');  //截取字符 500
                
            $data['cid'] = $cid;
            $data['userid'] = $userInfo['id'];
            $data['artId'] = $id;
            $result = $m->sData($data, 'art_comment');
            if($result){
                //根据栏目ID获取模型和模型对应的表名，以便更新评论次数
                $table = $m->getone('select b.mark from '.SQLPRE.'column a left join '.SQLPRE.'models b on a.model=b.id where a.id='.$cid);
                $d2['comments'] = "comments+1---";
                $m->sData($d2, $table, 'u', "id=$id");
                echo 1; exit();
            }
        }
    }
    
    /**
     * 回复文章评论。
     * @param type $param
     */
    public function replyComment() {
        $m = new \core\model();
        $cid = $this->rget('cid');
        $artid = $this->rget('artid');
        $id = $this->rget('id');
        $userid = $this->userInfo['id'];
        //查看上一条评论时间 是不是已经间隔一个小时了
        $sql = "SELECT ctime FROM `".SQLPRE."art_comment` WHERE now() < `ctime` + INTERVAL 60 MINUTE and userid=$userid";
        $prev = $m->getone($sql);
        if( !empty($prev) || $prev!=false ):
            echo 2; exit();
        endif;
        if(isset($_POST['_submit_'])){
            //获取评论默认状态   start
            $Cstatus = $GLOBALS['config']['commentDefaultStatus'];
            if(array_search($Cstatus, array(1,2,3))===false || !$Cstatus || empty($Cstatus)){
                $Cstatus = 2;
            };
            $data['status'] = $Cstatus;
            //获取评论默认状态   end
            
            //评价内容过滤
            $commentContent_ = $this->_post('ReplyCommentContent');
            $str_ = $GLOBALS['config']['commentfilt'];
            $order   = array("\r\n", "\n", "\r");
            $str = str_replace($order,',',$str_);
            $strArray = explode(',', $str);
            $commentContent = str_replace($strArray,'**',$commentContent_);  //过滤后的内容。
            $data['content'] = mb_substr($commentContent,0,500,'utf-8');  //截取字符 500
                
            $data['cid'] = $cid;
            $data['userid'] = $userid;
            $data['artId'] = $artid;
            $data['pid'] = $id;
            $result = $m->sData($data, 'art_comment');
            if($result){
                //根据栏目ID获取模型和模型对应的表名，以便更新评论次数
                $table = $m->getone('select b.mark from '.SQLPRE.'column a left join '.SQLPRE."models b on a.model=b.id where a.id=$cid");
                $d2['comments'] = "comments+1---";
                $m->sData($d2, $table, 'u', "id=$artid");
                echo 1; exit();
            }
        }else{
            $sql = "select content from `".SQLPRE."art_comment` where id=$id";
            $content = $m->getone($sql);
            $this->assign('content', $content);
            $this->display('article/replyComment');
        }
    }
    /**
     * 获取评论过滤字
     */
    public function commentFilt() {
        $str_ = $GLOBALS['config']['commentfilt'];
        $data = $this->_get('data');
        if(!empty($str_)){
            $order   = array("\r\n", "\n", "\r");
            $str = str_replace($order,',',$str_);
            $strArray = explode(',', $str);
            $returnData = str_replace($strArray,'**',$data);
            echo $returnData;
        }else{
            echo $data;
        }
        exit();
    }
    
    /**
     * 用户中心的文章评论
     */
    public function ArtComment() {
        
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select ac.*,c.name as columnName,c.mark from `".SQLPRE."art_comment` ac left join `".SQLPRE."column` c on ac.cid=c.id where ac.userid=$userId  limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from `".SQLPRE."art_comment` where userid=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('user/ArtComment');
    }
    
    /**
     * 用户中心的商品评论
     */
    public function GoodsComment() {
        
        $m = new \core\model();
        $userId =  $this->userInfo['id'];
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select ac.* from `".SQLPRE."goods_comment` ac where ac.userid=$userId limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from `".SQLPRE."goods_comment` where userid=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('user/GoodsComment');
    }
    
    /**
     * 修改支付方式
     */
    public function modipay() {
        
        $m = new \core\model();
        //支付方式  start
//        $payment['jdPay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='jdPay' and status=1");
//        $payment['unionpay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='unionpay' and status=1");
//        $payment['alipayw'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='alipayw' and status=1");
        $payment['alipay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='alipay01' and status=1");
        $payment['weixinPay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='weixin01' and status=1");
        $payment['transfer'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='transfer' and status=1");
        $payment['face_pay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='face_pay' and status=1");
        $this->assign('payment', $payment);
        
        $this->display('user/modipay');
    }
    
    /**
     * 修改支付方式
     */
    public function modiPaymentDone() {
        $data['payType'] = $this->_get('sn');
        $orderid = $this->_get('orderid');
        $m = new \core\model();
        
        $payStatus = $m->getone("select payStatus from ".SQLPRE."orders where id=$orderid");
        if($payStatus!=1): return false; endif;
        
        $result = $m->sData($data, 'orders', 'u', "id=$orderid");
        if($result){
            echo json_encode(array('status'=>1));
            exit();
        }
    }
    
    /**
     * 修改收货地址
     */
    public function modiAddress() {
        $m = new \core\model();
        $userId =  $this->userInfo['id'];
        
        if($this->_get('done')==1){
            $orderid =  intval($this->_get('orderid'));
            $addressid = intval($this->_get('addressid'));
            if( empty($orderid) || empty($addressid) ): return false; endif;
            $payStatus = $m->getone("select payStatus from ".SQLPRE."orders where id=$orderid");
            if($payStatus!=1): return false; endif;
            
            $address = $m->getrow("select * from ".SQLPRE."users_address where id=$addressid");
            $data['name'] = $address['recipients'] ;
            $data['proviceId'] = $address['proviceSn'];
            $data['cityId'] = $address['citySn'];
            $data['countyId'] = $address['countySn'];
            $data['townId'] = $address['townSn'];
            $data['mobile'] = $address['mobile'];
            $data['address'] = $address['street'];
            $data['phone'] = $address['phone'];
            $data['zipcode'] = $address['zipcode'];
            $result = $m->sData($data, 'orders', 'u', "id=$orderid");
            if($result){
                echo json_encode(array('status'=>1));
                exit();
            }
            
        }else{
            $adrlist = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from ".SQLPRE."users_address ua "
                . "left join ".SQLPRE."area_provice ap on ua.proviceSn=ap.provice_id "
                . "left join ".SQLPRE."area_city ac on ua.citySn=ac.city_id  "
                . "left join ".SQLPRE."area_county act on ua.countySn=act.county_id "
                . " where ua.userid=$userId");
            $this->assign('list', $adrlist);
            $this->display('user/modiAddress');
        }
    }
    
    public function _empty() {
        $this->display('user/index');
    }
    
    
}
