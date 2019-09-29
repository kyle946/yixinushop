<?php

/**
 * Description of mobileController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;

class userController extends comController {

    public $userInfo = false;

    public function __construct() {
        parent::__construct();
        $this->userInfo = loginJudbe();
        
        //如果用户没有登录
        if ($this->userInfo == false) {
            $back = base64_encode('?c=user');
            header('Location: ' . createLink('u/login', array('back' => $back)));
            exit();
        } 
    }

    public function index() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        $sql = "select u.*,ur.alias as rankName,if(u.avatar is null,'default.gif',u.avatar) as avatar from " . $m->prefix . "users u left join `" . $m->prefix . "user_rank` ur on u.userRank=ur.id  where u.id=$userId";
        $info = $m->getrow($sql);
        //右侧基本信息
        $info['money'] = $m->getone("SELECT sum(amount) FROM `" . $m->prefix . "orders` WHERE userId=$userId");  //累计消费
        $info['orderCount'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "orders` WHERE userId=$userId");  //订单数
        $info['orderdaizhifu'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "orders` WHERE payStatus=1 and userId=$userId");  //待支付
        $info['orderdaifahuo'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "orders` WHERE payStatus=2 and delStatus=1 and userId=$userId");  //待发货
        $info['orderdone'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "orders` WHERE payStatus=2 and delStatus=3 and userId=$userId");  //已经完成
        $info['goodsCount'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "users_shoppingcart` WHERE userid=$userId");  //购物车商品数
        $info['coupon'] = $m->getone("SELECT count(id) FROM `" . $m->prefix . "coupon_issue` WHERE userId=$userId");  //优惠券
        $this->assign('info', $info);
        $this->assign('webtitle_', '个人中心');
//        $this->assign('layout_title', '个人中心');
        $this->display('user/index');
    }

    /**
     * 购物车 
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
        $this->assign('webtitle_', '我的购物车');
        $this->assign('layout_title', '我的购物车');
        $this->display('user/shoppingcart');
    }

    /**
     * 地址管理
     */
    public function address() {
        $this->assign('layout_title', '地址管理');
        $addresslist = array();
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        //用户收货地址  start
        $addresslist = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from " . $m->prefix . "users_address ua "
                . "left join " . $m->prefix . "area_provice ap on ua.proviceSn=ap.provice_id "
                . "left join " . $m->prefix . "area_city ac on ua.citySn=ac.city_id  "
                . "left join " . $m->prefix . "area_county act on ua.countySn=act.county_id "
                . " where ua.userid=$userId");
        $this->assign('addresslist', $addresslist);
        $this->assign('from', $this->rget('from'));
        $this->display('user/address');
    }
    
    /**
     * 删除收货地址
     */
    public function deleteAddress() {
        $userId = $this->userInfo['id'];
        $id = $this->_get('id');
        $m = new \core\model();
        $sql = "delete from ".SQLPRE."users_address where  id=$id and userid=$userId";
        $result = $m->query($sql);
        echo json_encode(array('status'=>1));
        exit();
    }

    //修改购物车中的商品数量
    public function changeCartGoodsNum() {
        $m = new \core\model();
        $id = $m->g($this->_get('id'));
        $num = $m->g($this->_get('num'));
        if (intval($num) <= 0) {
            $sql = "delete from `" . $m->prefix . "users_shoppingcart` where id=$id";
            $m->query($sql);
            echo json_encode(array('status' => 2));
            exit();
        } else {
            $sql = "update `" . $m->prefix . "users_shoppingcart` set goodsnum=$num where id=$id";
            $m->query($sql);
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 我的优惠券
     */
    public function ticket() {

        $m = new \core\model();
        $userId = $this->userInfo['id'];

        //page 数据
        $p = $this->rget('p')? : 1;
        $listrows = 10;
        $start = $p * $listrows - $listrows;

        $sql = "select ci.*,if(now()<ci.endTime,ci.status,3) as status from `" . $m->prefix . "coupon_issue` ci where ci.userId=$userId limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from " . $m->prefix . "coupon_issue where userId=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('list', $list);
            $this->assign('pageinfo', $pageinfo);

            $this->assign('from', $this->rget('from'));
            $this->assign('total', $this->rget('total'));
            $this->assign('webtitle_', '我的优惠券');
            $this->assign('layout_title', '我的优惠券');
            $this->display('user/ticket');
        }
    }

    /**
     * 修改编辑地址
     */
    public function editAddress() {


        $m = new \core\model();
        $userId = $this->userInfo['id'];
        $id = $this->rget('id');
        //总共有多少个收货地址，如果大于12个，不能再添加
        $addressCount = $m->getone("select count(id) from " . $m->prefix . "users_address where userid=$userId");
        if ($addressCount >= 12 and $id == false) {
            message("最多只能保存 12 个收货地址 ！");
        }

        if (isset($_POST['_submit_'])) {

            $data['proviceSn'] = $this->_post('provice');   //省
            $data['citySn'] = $this->_post('city');  //市
            $data['countySn'] = $this->_post('county');  //县 / 区
            $data['townSn'] = $this->_post('town');  //乡镇
            $data['street'] = $this->_post('street');   //街道地址
//            $data['zipcode'] = $this->_post('zipcode');   //邮政编码
            $data['recipients'] = $this->_post('recipients');   //收货人姓名
            $data['mobile'] = $this->_post('mobile');   //手机号码
//            $data['phone'] = $this->_post('phone');  //电话号码
            $data['userid'] = $userId;
            $data['lat'] = $this->_post('lat');  //坐标  经纬度
            $data['lng'] = $this->_post('lng');  //坐标  经纬度
            $ad = get_addres_name($data['proviceSn'], $data['citySn'], $data['countySn']);
            //默认地址
            $data['isdefault'] = 0;
            if ($this->_post('isdefault')) {
                $data['isdefault'] = 1;
                //如果设置了默认地址 ，则清空其他的所有默认地址配置
                $result = $m->sData(array("isdefault" => 0), 'users_address', 'u', "userid=$userId");
            }

            $result = null;

            //如果是修改收货地址
            if ($id) {
                $result = $m->sData(array_merge($data, $ad), 'users_address', 'u', "id=$id and userid=$userId");
            } else {  //否则是添加收货地址
                $result = $m->sData(array_merge($data, $ad), 'users_address');
            }
            
            if ($result) {
                $from = $this->rget('from');
                if($from){ $from =  '/from/'.$from; }
                header("Location: /user/address".$from );
                exit();
            }
        }

        //省市区默认值  start
        $info['proviceSn'] = 0;
        $info['citySn'] = 0;
        $info['countySn'] = 0;
        $info['townSn'] = 0;
        $info['isdefault'] = 0;
        //省市区默认值  end
        //如果是修改地址
        if ($id) {
            $sql = "select * from " . $m->prefix . "users_address where id=$id and userid=$userId";
            $info = $m->getrow($sql);
        }
        $this->assign('info', $info);

        $this->assign('info', $info);
        $this->assign('webtitle_', '编辑收货地址');
        $this->assign('layout_title', '编辑收货地址');
        $this->display("user/editAddress");
    }

    /**
     * 订单列表 
     */
    public function orderlist() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];

        $s = $this->rget('s');
        $where = null;
        if ($s) {
            switch ($s) {
                case 1: break;
                case 2: $where = ' and o.payStatus=1';
                    break;  //待付款
                case 3: $where = ' and o.payStatus=2 and o.delStatus=1';
                    break;  //待发货
                case 4: $where = ' and o.payStatus=2 and o.delStatus>2';
                    break;  //待发货
                default:
                    break;
            }
        }
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 8;  //一页6条数据
        $start = $p * $listrows - $listrows;

        $sql = "select o.*,g.goodsname,g.goodsnum,g.goodsprice,goodsthumb,g.goodsattributeStr,g.goodsid,b.business_name "
                . "from " . $m->prefix . "orders o,`" . SQLPRE . "order_goods` g ,`".SQLPRE."business` b "
                . "where o.id=g.orderid and o.business_no=b.business_no and o.userId=$userId $where "
                . "group by o.id order by o.id desc  limit $start,$listrows";
        $result_ = $m->getall($sql);
        $result = array();
        foreach ($result_ as $key => $value) {
            switch ($value['type']) {
                case 1: $value['type'] = '普通订单';
                    break;
                case 2: $value['type'] = '团购';
                    break;
                case 3: $value['type'] = '抢购';
                    break;
                default: $value['type'] = '普通订单';
                    break;
            }
            switch ($value['delStatus']) {
                case 1: $value['delStatus'] = '未发货';
                    break;
                case 2: $value['delStatus'] = '<font class="f1">已发货</font>';
                    break;
                case 3: $value['delStatus'] = '已签收';
                    break;
                case 4: $value['delStatus'] = '拒收';
                    break;
                default: $value['delStatus'] = '无效';
                    break;
            }
            switch ($value['payStatus']) {
                case 1: $value['payStatusInfo'] = '未支付';
                    break;
                case 2: $value['payStatusInfo'] = '<font class="f1">已支付</font>';
                    break;
                case 3: $value['payStatusInfo'] = '退款';
                    break;
                default: $value['payStatusInfo'] = '无效';
                    break;
            }
            $result[$key] = $value;
        }

        //总记录数
        $sql = "select count(o.id) as count from " . $m->prefix . "orders o where o.userId=$userId $where";
        $count = $m->getone($sql);
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );

        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $result, 'page' => $pageinfo));
        } else {
            $this->assign('list', $result);
            $this->assign('pageinfo', $pageinfo);
            $this->assign('webtitle_', '我的订单');
            $this->assign('layout_title', '我的订单');
            $this->display("user/orderlist");
        }
    }

    /**
     * 订单详情  查看订单 
     */
    public function orderinfo() {
        $id = $this->rget('id');
        $sn = $this->rget('sn');
        $m = new \core\model();
        //如果使用ID查询
        if ($id) {
            $sql = "select * from " . $m->prefix . "orders where id=$id";
            $orderinfo = $m->getrow($sql);
        }
        //如果使用编号查询
        if ($sn) {
            $sql = "select * from " . $m->prefix . "orders where orderSn='$sn'";
            $orderinfo = $m->getrow($sql);
            $id = $orderinfo['id'];
        }
        if (empty($orderinfo) || count($orderinfo) <= 0) {
            message("没有找到该订单，可能已经删除！");
        }

        switch ($orderinfo['type']) {
            case 1: $orderinfo['type'] = '普通订单';
                break;
            case 2: $orderinfo['type'] = '团购';
                break;
            case 3: $orderinfo['type'] = '抢购';
                break;
            default: $orderinfo['type'] = '普通订单';
                break;
        }
        switch ($orderinfo['delStatus']) {
            case 1: $orderinfo['delStatus'] = '未发货';
                break;
            case 2: $orderinfo['delStatus'] = '<font class="f1">已发货</font>';
                break;
            case 3: $orderinfo['delStatus'] = '已签收';
                break;
            case 4: $orderinfo['delStatus'] = '拒收';
                break;
            default: $orderinfo['delStatus'] = '无效';
                break;
        }
        switch ($orderinfo['payStatus']) {
            case 1: $orderinfo['payStatusInfo'] = '未支付';
                break;
            case 2: $orderinfo['payStatusInfo'] = '<font class="f1">已支付</font>';
                break;
            case 3: $orderinfo['payStatusInfo'] = '退款';
                break;
            default: $orderinfo['payStatusInfo'] = '无效';
                break;
        }

        $orderinfo['payTypeLogo'] = $m->getone("select logo from `" . $m->prefix . "payment` where sn='$orderinfo[payType]'");
        $orderinfo['deliveryId'] = $orderinfo['deliveryMethod'];
        $orderinfo['deliveryMethod'] = $m->getone("select name from `" . $m->prefix . "deliverys` where id=$orderinfo[deliveryMethod]");
        $address = $m->getrow("select ua.id,ap.provice_name,ac.city_name,act.county_name,ua.address  from " . $m->prefix . "orders ua "
                . "left join " . $m->prefix . "area_provice ap on ua.proviceId=ap.provice_id "
                . "left join " . $m->prefix . "area_city ac on ua.cityId=ac.city_id  "
                . "left join " . $m->prefix . "area_county act on ua.countyId=act.county_id "
                . " where ua.id=$id");
        $this->assign('address', $address);
        
        //快递公司及 快递 单号
        $sql = "select ex_com from ".SQLPRE."deliverys where id=$orderinfo[deliveryId]";
        $ex_com = $m->getone($sql);
        $this->assign('ex_com', $ex_com);
        $this->assign('info', $orderinfo);

        //订单商品
        $sql = "select og.*  from " . $m->prefix . "order_goods og where orderid=$id";
        $goodslist = $m->getall($sql);
        $this->assign('goodslist', $goodslist);
        
        
        
        //支付方式  start
        $payment['alipay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='alipay01' and status=1");
        $payment['weixin01'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='weixin01' and status=1");
        $payment['transfer'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='transfer' and status=1");
        $payment['face_pay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='face_pay' and status=1");
        $this->assign('payment', $payment);
        //支付方式  end
        
        $this->assign('webtitle_', '订单详情');
        $this->assign('layout_title', '订单详情');
        $this->display('user/orderinfo');
    }
    
    /**
     * 快递100  查询快递单信息
     */
    public function getwuliuxinxi() {
        $ex = rget('ex');
        $id = rget('id');
        $excomlink = "http://wap.kuaidi100.com/wap_result.jsp?rand=20120517&id={$ex}&fromWeb=null&&postid={$id}";
        //模拟浏览器查询快递单
        $content = gethtml('http://m.kuaidi100.com', $excomlink);
        //过滤掉内容
        $content = preg_replace('/<input (.*?)>/', '', $content);
        $content = preg_replace('/<span (.*?)>/', '', $content);
        $content = preg_replace('/<a (.*?)>(.*?)<\/a>/', '', $content);
        $content = preg_replace('/输入快递单号:/', '', $content);
        $matches = array();
        preg_match("/<form action(.*)>(.*?)<\/form>/is", $content, $matches);
        $this->assign('info', $matches[0] );
        $this->assign('webtitle_', '快递查询');
        $this->assign('layout_title', '快递查询');
        $this->display('user/getwuliuxinxi');
    }

    /**
     * 买到的商品
     */
    public function BuyTheGoods() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];

        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 5;
        $start = $p * $listrows - $listrows;

        $sql = "select og.*,o.orderSn from `" . $m->prefix . "orders` o "
                . " left join `" . $m->prefix . "order_goods` og on o.id=og.orderid "
                . " where o.userId=$userId and o.payStatus=2 order by id desc limit $start,$listrows";
        $list = $m->getall($sql);


        foreach ($list as $key => $value) {
            switch ($value['delStatus']) {
                case 1: $value['delStatus'] = '未发货';
                    break;
                case 2: $value['delStatus'] = '<font class="f1">已发货</font>';
                    break;
                case 3: $value['delStatus'] = '已签收';
                    break;
                case 4: $value['delStatus'] = '拒收';
                    break;
                default: $value['delStatus'] = '无效';
                    break;
            }
            switch ($value['payStatus']) {
                case 1: $value['payStatus'] = '未支付';
                    break;
                case 2: $value['payStatus'] = '<font class="f1">已支付</font>';
                    break;
                case 3: $value['payStatus'] = '退款';
                    break;
                default: $value['payStatus'] = '无效';
                    break;
            }
            $list[$key] = $value;
        }

        //page 数据
        $count = $m->getone($sql = "select count(og.id) from `" . $m->prefix . "orders` o left join `" . $m->prefix . "order_goods` og on o.id=og.orderid where o.userId=$userId  and o.payStatus=2");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);

        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('goodslist', $list);
            $this->assign('pageinfo', $pageinfo);
            $this->assign('webtitle_', '买到的商品');
            $this->assign('layout_title', '买到的商品');
            $this->display('user/BuyTheGoods');
        }
    }

    /**
     * 个人资料
     */
    public function info() {

        $m = new \core\model();
        $userId = $this->userInfo['id'];
        if (isset($_POST['type']) && intval($_POST['type']) == 1) {
            $type = intval($_POST['type']);
            $val = $_POST['inputvalue'];
            if (empty($val) || !$val) {
                echo json_encode(array('status' => 0));
                exit();
            }
            $data = array();
            if ($type == 1) {
                $data['nickname'] = $val;
            }
            $r = $m->sData($data, 'users', 'u', "id=$userId");
            if ($r) {
                echo json_encode(array('status' => 1));
                exit();
            }
        } elseif (isset($_POST['type']) && intval($_POST['type']) == 2) {
            $path = AVATAR_PATH;
            $imageInfo = uploadImage($_FILES['imagefile'], $path, 'tmp_avatar_' . $userId);
            if (is_array($imageInfo)) {
                //生成小图
                $thumbImage = $path . '/avatar_' . $userId . "." . $imageInfo['type']; //缩略图文件名，这里是全路径 
                makeThumb($imageInfo['path'], $thumbImage);
                unlink($imageInfo['path']);
                //取文件名
                $pinfo = pathinfo($thumbImage);
                $fname = $pinfo['basename'];
                //写入数据库
                $data['avatar'] = $fname;
                $m->sData($data, 'users', 'u', "id=$userId");
                $data['path'] = $path . $fname;   //echo " 已经成功上传：".$photo_server_folder."<br />";
//                echo json_encode($data);
//                exit();
            } elseif ($imageInfo == false || $imageInfo == 403) {
                message('上传失败！');
            } elseif ($imageInfo == 401) {
                message('文件超过规定大小！');
            } elseif ($imageInfo == 402) {
                message('文件类型不符！');
            }
//            echo json_encode($data);
//            exit;
        }

        $sql = "select * from " . $m->prefix . "users where id=$userId";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        $this->assign('webtitle_', '个人资料');
        $this->assign('layout_title', '个人资料');
        $this->display('user/info');
    }

    /**
     * 用户发表商品评价
     */
    public function wcomment() {
        $userInfo = $this->userInfo;
        if ($userInfo) {

            $m = new \core\model();

            //查看上一条评论时间 是不是已经间隔两个小时了
            $sql = "SELECT ctime FROM `" . $m->prefix . "goods_comment` WHERE now() < `ctime` + INTERVAL 120 MINUTE and userid=$userInfo[id]";
            $prev = $m->getone($sql);
            if (!empty($prev) || $prev != false):
                message('亲，评论太频繁了，过两个小时再来吧！');
            endif;


            $goodsid_ = $this->rget('goodsid');
            //获取购买的商品信息，支付状态为已经支付的才可以评论
            $sql = "select og.* from `" . $m->prefix . "order_goods` og inner join " . $m->prefix . "orders o on og.orderid=o.id where og.goodsid=$goodsid_ and o.userId=$userInfo[id] and og.payStatus>=2";
            $goodsData = $m->getrow($sql);

            //如果用户没有购买过该商品
            if (empty($goodsData)) {
                message('购买商品后才可以发表评价！');
            }

            if (isset($_POST['_submit_'])) {
                //处理上传的图片
//                if( !empty($_FILES['commentimage1']['tmp_name']) ){
//                    $path = './commentImage/'.date('Ymd');
//                    $imageInfo = uploadImage($_FILES['commentimage1'],$path );
//                    if($imageInfo==401 || $imageInfo==402 || $imageInfo==403){
//                        echo json_encode(array('status'=>$imageInfo)); exit();
//                    }elseif(is_array($imageInfo)){
//                        //生成小图
//                        $thumbImage1 = $path."/600_".$imageInfo['name']; 
//                        $thumbImage2 = $path."/40_".$imageInfo['name']; 
//                        makeThumb($imageInfo['path'], $thumbImage1,600,400);
//                        makeThumb($imageInfo['path'], $thumbImage2,45,30);
//                        unlink($imageInfo['path']);
//                        $data['image1'] = $thumbImage2;
//                    }elseif($imageInfo==false){
//                        echo json_encode(array('status'=>403)); exit();
//                    }
//                }
                //获取goods表的ID
                $sql = "select goodsid from `" . $m->prefix . "goods_additional` where id=$goodsid_";
                $goodsid = $m->getone($sql); //goods表ID
                //获取评论默认状态   start
                $Cstatus = $GLOBALS['config']['commentDefaultStatus'];
                if (array_search($Cstatus, array(1, 2, 3)) === false || !$Cstatus || empty($Cstatus)) {
                    $Cstatus = 2;
                };
                $data['status'] = $Cstatus;
                //获取评论默认状态   end
                //评价内容过滤
                $commentContent_ = $this->_post('commentContent');
                $str_ = $GLOBALS['config']['commentfilt'];
                $order = array("\r\n", "\n", "\r");
                $str = str_replace($order, ',', $str_);
                $strArray = explode(',', $str);
                $commentContent = str_replace($strArray, '**', $commentContent_);  //过滤后的内容。
                $data['content'] = mb_substr($commentContent, 0, 500, 'utf-8');  //截取字符 500

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
                $m->sData($data, 'goods_additional', 'u', "id=$goodsid_");
                if ($result) {
                    
                    //只有好评才增加积分  begin
                    if( intval($data['level'])===1 ){
                        //积分
                        $sql ="select val from `".SQLPRE."shop_config` where mark='pointsAct5'";
                        $p1 = $m->getone($sql);
                        $pointact = new \models\other();
                        $num = intval(floatval($p1));
                        $pointact->pointsAct($userInfo['id'], $num, 'add',
                                '评价商品 '.$goodsData['goodsname'].$goodsData['goodsattributeStr'].'，增加积分:'.$num
                                );
                    }
                    // end
                    
                    header("Location:" . createLink('goods/comment', array('goodsid' => $goodsid, 'id' => $goodsid_)));
                    exit();
                }
            }
        } else {
            message('用户未登录！');
        }
    }

    /**
     * 我的评论  文章
     */
    public function artcomment() {
        $m = new \core\model();
        $userId = $this->userInfo['id'];
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 5;
        $start = $p * $listrows - $listrows;
        $sql = "select ac.*,c.name as columnName,c.mark from `" . $m->prefix . "art_comment` ac left join `" . $m->prefix . "column` c on ac.cid=c.id where ac.userid=$userId order by id desc  limit $start,$listrows";
        $list = $m->getall($sql);
        foreach ($list as $key => $value) {
            switch ($value['status']) {
                case 1: $value['status'] = '显示';
                    break;
                case 2: $value['status'] = '待审核';
                    break;
                case 3: $value['status'] = '关闭';
                    break;
            }
            $list[$key] = $value;
        }
        //page 数据
        $count = $m->getone("select count(id) as count from `" . $m->prefix . "art_comment` where userid=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'yixinu.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('list', $list);
            $this->assign('pageinfo', $pageinfo);
            $this->assign('layout_title', '我的评论(文章)');
            $this->assign('webtitle_', '我的评论(文章)');
            $this->display('user/artcomment');
        }
    }

    /**
     * 我的评论  商品
     */
    public function goodscomment() {

        $m = new \core\model();
        $userId = $this->userInfo['id'];
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 5;
        $start = $p * $listrows - $listrows;
        $sql = "select ac.* from `" . $m->prefix . "goods_comment` ac where ac.userid=$userId limit $start,$listrows";
        $list = $m->getall($sql);
        foreach ($list as $key => $value) {
            switch ($value['status']) {
                case 1: $value['status'] = '显示';
                    break;
                case 2: $value['status'] = '待审核';
                    break;
                case 3: $value['status'] = '关闭';
                    break;
            }
            $list[$key] = $value;
        }
        //page 数据
        $count = $m->getone("select count(id) as count from `" . $m->prefix . "goods_comment` where userid=$userId");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'yixinu.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('list', $list);
            $this->assign('pageinfo', $pageinfo);
            $this->assign('layout_title', '我的评论(商品)');
            $this->assign('webtitle_', '我的评论(商品)');
            $this->display('user/goodscomment');
        }
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
            $this->assign('layout_title', '我的积分');
            $this->assign('webtitle_', '我的积分');
        $this->display('user/integral');
    }

}
