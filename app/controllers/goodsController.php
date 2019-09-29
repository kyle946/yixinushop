<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of goodsController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class goodsController extends comController {

    public function category() {

        $cate = $this->rget('g');
        $m = new \models\goods();
        $p = $this->rget('p')? : 1;
        $nums = 16;
        $data = $m->goodslist($cate, $p, $nums, $this->rget(), $this->rget('price'), $this->rget('sort'));
        $this->assign('goodsCatetroyPosData', $m->goodsCatetroyPos($cate));

        if ($data['type'] == 'cate') {
            $this->assign('cateid', $cate);
            $this->assign('cateList', $data['cateList']); //商品分类列表
            $this->assign('info', $data['info']);  //自定义的商品列表
//            var_dump($data['info']['list'][0]);
            $this->display('goods/index');
        } elseif ($data['type'] == 'list') {
//            var_dump($data['list']);

            $this->assign('info', $data['info']);
            if (isset($data['priceMin']))
                $this->assign('priceMin', $data['priceMin']);
            if (isset($data['priceMax']))
                $this->assign('priceMax', $data['priceMax']);
            if (isset($data['priceSection']))
                $this->assign('priceSection', $data['priceSection']);
            if (isset($data['attrurl']))
                $this->assign('attrurl', $data['attrurl']);
            if (isset($data['attrD']))
                $this->assign('attrD', $data['attrD']);
            $this->assign('list', $data['list']);

            $urldata = null;
            if (!empty($data['attrurl']))
                $urldata = goodsurlattr($data['attrurl']);
            //分页初始化参数
            $params = array(
                'total_rows' => $data['count'],
                'now_page' => $p,
                'list_rows' => $nums,
                'parameter' => "/g/$cate/" . $urldata . "p_00000.html", //后面5个0是替换页码的字符
                'method' => 'html',
            );
            $page = new \lib\page($params);
            $this->assign('pageinfo', $page->show(4));
            $this->display('goods/glist');
        }
    }

    /**
     * 
     */
    public function search() {
        $m = new \core\model();
        $where_ = null;
        if ($this->rget('keywords')) {
            $attrurl['keywords'] = strip_tags(trim($this->rget('keywords')));
            $where_ = " and (g.name like '%$attrurl[keywords]%' or ga.attributeStr  like '%$attrurl[keywords]%')";
            $this->assign('searchGoodsKeywords', $attrurl['keywords']);
        }
        //把价格属性加到where 条件 里面
        if ($this->rget('price')) {
            $attrurl['price'] = $this->rget('price');
            list($pMin, $pMax) = explode('-', $this->rget('price'));
            $where_ .= $where_ . " and ( ga.shopPrice between $pMin and $pMax ) ";
            $this->assign('priceMin', $pMin);
            $this->assign('priceMax', $pMax);
        }

        //排序
        $orderby = " order by ga.sn asc ";
        if ($this->rget('sort')) {
            $attrurl['sort'] = $this->rget('sort');
            switch ($attrurl['sort']) {
                case 'priceS':
                    $orderby = " order by ga.shopPrice desc ";
                    break;
                case 'priceJ':
                    $orderby = " order by ga.shopPrice asc ";
                    break;
                case 'rS':
                    $orderby = " order by ga.clickCount desc ";
                    break;
                case 'rJ':
                    $orderby = " order by ga.clickCount asc ";
                    break;
                default:
                    $orderby = " order by ga.sn asc ";
                    break;
            }
        }

        $this->assign('attrurl', $attrurl);
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 16;  //一页6条数据
        $start = $p * $listrows - $listrows;
        //总记录数
        $sql = "select count(g.id) as count from " . SQLPRE . "goods as g "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                . "where ga.addStatus=1 $where_";
        $count = $m->getone($sql);

        $sql = "select ga.id  from " . SQLPRE . "goods as g "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                . "where ga.addStatus=1 $where_ $orderby limit $start,$listrows";
        $list_ = $m->getall($sql);
        $list = array();
        $userinfo = loginJudbe();
        $userid = $userinfo?$userinfo['id']:0;
        $redis = new \models\yredis();
        foreach ($list_ as $key => $value) {
            $value = json_decode($redis->get(REDIS_PRE.'goods_'.$value['id']) ,1);
            //从缓存读取库存，保存准确性
            $value['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$value['id']); 
            //取用户的购买记录， 用作限购判断 
            $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$value['id'])?:0;
            $list[$key] = $value;
        }
//        var_dump($list);
        $this->assign('list', $list);

        $urldata = null;
        if (!empty($attrurl))
            $urldata = goodsurlattr($attrurl);
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => "/goods/search/" . $urldata . "p_00000.html", //后面5个0是替换页码的字符
            'method' => 'html',
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->display('goods/search');
    }

    /**
     * 商品详情页
     */
    public function gitem() {
        $id = $this->rget('item');
        $m = new \core\model();
        
        //判断商品是否已经下上架
        $sql = "select addStatus from `" . SQLPRE . "goods_additional` where id=$id";
        $status = $m->getone($sql);
        if ($status != 1 or !$status) {
            message('该商品已经下架或者已经删除！');
        }
        
        $goodsModel = new \models\goods();
        $redis = new \models\yredis();
        //用户 ID
        $userinfo = loginJudbe();
        $userid = $userinfo?$userinfo['id']:0;

        //更新访问率  start
        $GoodsClickCount_name = 'GoodsClickCount' . $id;
        if (!isset($_COOKIE[$GoodsClickCount_name]) or empty($_COOKIE[$GoodsClickCount_name])) {
            setcookie($GoodsClickCount_name, $id, time() + 100);
            $data['clickCount'] = "clickCount+1---";
            $m->sData($data, "goods_additional", 'u', "id=$id");
        }
        //更新访问率  end

        //根据typeId获取表名，以便读取商品类型的属性
        $typeId = $m->getone("select typeId from " . SQLPRE . "goods g inner join " . SQLPRE . "goods_additional ga on g.id=ga.goodsId where ga.id=$id");
        $table_mark = $m->getone("select mark from " . SQLPRE . "goods_type where id=$typeId");
        $tablename = SQLPRE . 'goods_add' . $table_mark;

        $sql = "select g.*,ga.*, "
                . " if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice ,a.id as aid, "
                . "a.name as activityName, a.starttime,a.endtime,ag.xiangou "
                . " from " . SQLPRE . "goods g inner join " . SQLPRE . "goods_additional ga on g.id=ga.goodsId "
                . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                . " left join " . SQLPRE . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                . "left join `$tablename` gad on ga.sn=gad.sn "
                . "where ga.id=$id";
        $info = $m->getrow($sql);
        //从缓存读取库存，保存准确性
        $info['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$info['id']); 
        //取用户的购买记录， 用作限购判断 
        $info['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$info['id'])?:0;
        
        //图片
        if (!empty($info['imgs'])) {
            $info['imgs'] = unserialize($info['imgs']);
        }

        //替换掉商品介绍中的图片 ，以便 前端 使用延迟加载 
        $pattern = '/<img.+?src\=\"(.+?)\"/is';
        $replacement = "<img src='/static/default.gif' xsrc='$1' init='1' ";
        $info['goodsDesc'] = preg_replace($pattern, $replacement, $info['goodsDesc']);

        //属性
        if (!empty($info['attr'])) {
            $info['attr'] = unserialize($info['attr']);
        }
        if (!empty($info['attribute'])) {
            $info['attribute'] = unserialize($info['attribute']);
        }

        //规格 , 只有值为1 的规格才输出 
        $attrlist = array();
        if (!empty($info['attr'])) {
            $tvardata = $info['attr'];
            $info['attr'] = null;
            foreach ($tvardata as $key => $value) {
                $tvar1 = explode(',', trim($value, ','));
                $list = null;
                foreach ($tvar1 as $k1 => $v1) {
                    if (strpos($v1, '=') == false)
                        continue;
                    list($tvar2, $tvar3) = explode('=', $v1);
                    if ((int) $tvar3 == 1) {
                        $list[++$k1] = $tvar2;
                    }
                }
                //把goods附加表ID加入到规格属性中，以达到每 一个规格都 是一个独立商品目的
                $info['attr'][$key]['list'] = $goodsModel->setspec($list, $info['attribute'], $info['goodsId']);  
                $info['attr'][$key]['name'] = $m->getone("select name from " . SQLPRE . "goods_spec where id=$key");
                if ($list) {
                    $attrlist[$key] = $list;
                } else {
                    unset($info['attr'][$key]);
                }
            }
        }

        //把商品名称写到页面标题上
        $this->assign('webtitle_', $info['name']);
        //商品分类position
        $t_ = new \models\goods();
        $this->assign('goodsCatetroyPosData', $t_->goodsCatetroyPos($info['catId']));
        //推荐组合
        $sql = "select g.name,ga.attributeStr,ga.shopPrice,ga.id,ga.thumb from " . SQLPRE . "goods g left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId where g.catId=$info[catId] order by ga.clickCount limit 5";
        $recommendList = $m->getall($sql);
        $this->assign('recommendList', $recommendList);
        //商品评价
        $commentInfo = array();
        $commentInfo['per1'] = 0;
        $commentInfo['per2'] = 0;
        $commentInfo['per3'] = 0;
        $commentInfo['lev1'] = 0;
        $commentInfo['lev2'] = 0;
        $commentInfo['lev3'] = 0;
        $commentInfo['all'] = 0;

        $sql = "select sum(comments) from `" . SQLPRE . "goods_additional` where goodsId=$info[goodsId]";
        $commentInfo['count'] = $m->getone($sql);
        $sql = "select count(id) from `" . SQLPRE . "goods_comment` where goodsid=$info[goodsId] and level=1";
        $commentInfo['lev1'] = $m->getone($sql);
        $sql = "select count(id) from `" . SQLPRE . "goods_comment` where goodsid=$info[goodsId] and level=2";
        $commentInfo['lev2'] = $m->getone($sql);
        $sql = "select count(id) from `" . SQLPRE . "goods_comment` where goodsid=$info[goodsId] and level=3";
        $commentInfo['lev3'] = $m->getone($sql);
        $commentInfo['all'] = (int) $commentInfo['lev1'] + (int) $commentInfo['lev2'] + (int) $commentInfo['lev3'];
        $sql = "select count(id) from `" . SQLPRE . "goods_comment` where goodsid=$info[goodsId] and (image1 is not null or image2 is not null)";
        $commentInfo['image'] = $m->getone($sql);
        if ((int) $commentInfo['lev1'] > 0)
            $commentInfo['per1'] = intval((int) $commentInfo['lev1'] / (int) $commentInfo['all'] * 100);
        if ((int) $commentInfo['lev2'] > 0)
            $commentInfo['per2'] = intval((int) $commentInfo['lev2'] / (int) $commentInfo['all'] * 100);
        if ((int) $commentInfo['lev3'] > 0)
            $commentInfo['per3'] = intval((int) $commentInfo['lev3'] / (int) $commentInfo['all'] * 100);
        $this->assign('commentInfo', $commentInfo);

        $this->assign('info', $info);
        $this->display("goods/gitem");
    }

    /**
     * 加载商品评价
     */
    public function loadComment() {
        $m = new \core\model();
        $goodsid = $this->_get('goodsid');
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 7;
        $start = $p * $listrows - $listrows;

        $sql = "select gc.*,u.nickname,u.avatar from `" . SQLPRE . "goods_comment` gc "
                . "left join `" . SQLPRE . "users` u on gc.userid=u.id"
                . "  where gc.goodsid=$goodsid and gc.status=1 order by id desc limit $start,$listrows";
        $list = $m->getall($sql);

        //page 数据
        $count = $m->getone("select count(id) as count from `" . SQLPRE . "goods_comment`  where goodsid=$goodsid");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.loadCommentPage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(4);
        echo json_encode(array('data' => $list, 'page' => $pageinfo));
        exit();
    }

    /**
     * 预算运费
     */
    public function budgetFreight() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $provice = $this->_post('provice');
            $city = $this->_post('city');
            $county = $this->_post('county');
            $town = $this->_post('town');
            $freightid = $this->_post('delivery');
            $nums = $this->_post('nums') ? $this->_post('nums') : 1;
            $goodsid = $this->_post('goodsid');

            $sql = "select weight from `" . SQLPRE . "goods_additional` where id=$goodsid";
            $weight_ = $m->getone($sql);
            $weight = floatval($weight_) * intval($nums);
            
            //查询是否为虚拟商品 begin
            $sql11 = "select virtual from `".SQLPRE."goods_additional` a,`".SQLPRE."goods` g,`".SQLPRE."goods_type` t where a.goodsId=g.id and g.typeId=t.id and a.id=$goodsid";
            $virtual = $m->getone($sql11);
            $total = 0;
            if( $virtual==1 ){
                $total = 0;
            }else{
                $total = $this->freightComputation($weight, $freightid, $provice, $city, $county, $town);
            }
            //  end

            $adr = null;
            if ($provice)
                $adr .= $m->getone("select `provice_name` from `" . SQLPRE . "area_provice` where `provice_id`=$provice") . ' ';
            if ($city)
                $adr .= $m->getone("select `city_name` from `" . SQLPRE . "area_city` where `city_id`='$city'") . ' ';
            if ($county)
                $adr .= $m->getone("select `county_name` from `" . SQLPRE . "area_county` where `county_id`='$county'") . ' ';
            if ($town)
                $adr .= $m->getone("select `town_name` from `" . SQLPRE . "area_town` where `town_id`='$town'");

            $delivery = $m->getone("select name from " . SQLPRE . "deliverys where id=$freightid");
            $text = '配送到 ' . $adr . ',运费' . $total . '元,(' . $delivery . ')';
            echo json_encode(array('info' => $text));
            exit();
        }else {
            $sql = "select id,name from " . SQLPRE . "deliverys where status=1";
            $deliverys = $m->getall($sql);
            $this->assign('delivery', $deliverys);
            $this->assign('goodsid', $this->_get('id'));
            $this->assign('nums', $this->_get('nums'));
            $this->display('goods/budgetFreight');
        }
    }

    /**
     * json 获取区域
     */
    public function getaddress() {

        $act = $this->_get("act");
        $id = $this->_get("id");
//        if(!$id) echo null;

        $m = new \core\model();
        $list = null;
        switch ($act) {
            case 'city':  //市
                $list = $m->getall("select * from " . SQLPRE . "area_city where province_id=$id");
                break;
            case 'county':   //区、县
                $list = $m->getall("select * from " . SQLPRE . "area_county  where city_id =$id");
                break;
            case 'town':  //街道、乡镇
                $list = $m->getall("select * from " . SQLPRE . "area_town  where county_id =$id");
                break;
            //默认返回省份
            default:
                $list = $m->getall("select * from " . SQLPRE . "area_provice");
                break;
        }
        echo json_encode($list);
    }

    /**
     * 商品加入购物车。
     */
    public function addcart() {
        $userInfo = loginJudbe();
        if ($userInfo == false) {
            echo json_encode(array('status' => 2));
            exit(); //未登录 
        }
        $m = new \core\model();
        $redis = new \models\yredis();
        $goodsid = $this->_get('goodsid');
        $num = $this->_get('num');

        //库存判断 
        $numbers = $redis->get(REDIS_PRE.'goods_numbers_'.$goodsid); 
        //库存判断：库存低于警界数，或者购买数量已经超过了库存数量
        if( $numbers<KUCUNBUZU || $num>$numbers ){
            echo json_encode(array('status' => 4));exit();  //库存不足
        }
        
        $cartData['goodsid'] = $goodsid;
        $cartData['userid'] = $userInfo['id'];
        $cartData['goodsnum'] = $num;

        //检查购物车中是否已经添加了这个商品
        $r = $m->getrow("select id,goodsnum from `" . $m->prefix . "users_shoppingcart` us where userid=$userInfo[id] and goodsid=$goodsid");
        $cartDataId = $r['id']?:0;
        $goodsnum = $r['goodsnum']?:0;
            
        //限购判断
        $xiangouuser = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userInfo['id'].'-'.$goodsid );
        $xiangou = $m->getone("select xiangou from `".SQLPRE."activity_goods` where goodsid=$goodsid and aid > 0");
        //限购判断 ： 已经购买过的数量 + 新添加的数量 + 购物车里已经添加的数量
        if(  $xiangou>0 && $xiangouuser+$num+$goodsnum >$xiangou ){
            echo json_encode(array('status' => 5));exit();  //已经超过限购件数
        }

        if ( !$cartDataId ) {
            //检查购物车中的商品是不是已经放满了。
            $goodscount = $m->getone("select count(id) as count from `" . SQLPRE . "users_shoppingcart` where userid=$userInfo[id]");
            if ($goodscount >= 12) {
                echo json_encode(array('status' => 3));
                exit(); //购物车满状态 
            }
            $m->sData($cartData, 'users_shoppingcart');
        } else {
            $cartData['goodsnum'] = "`goodsnum`+$num---";
            $m->sData($cartData, 'users_shoppingcart', 'u', "id=$cartDataId");
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 获取购物车里的商品
     */
    public function getshoppingcart() {
        $userInfo = loginJudbe();
        if ($userInfo == false) {
            echo json_encode(array('status' => 0));
            return false;
        }

        $m = new \core\model();
        $redis = new \models\yredis();
        $sql = "select goodsid,goodsnum from `" . SQLPRE . "users_shoppingcart` where userid=$userInfo[id]";
        $L = $m->getall($sql);
        foreach ($L as $k=>$value) {
            $v = json_decode($redis->get(REDIS_PRE.'goods_'.$value['goodsid']) ,1);
            if(!$v){
                unset($L[$k] );
                continue;
            }
            //从缓存读取库存，保存准确性
            $value['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$value['goodsid']); 
            //取用户的购买记录， 用作限购判断 
            $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userInfo['id'].'-'.$value['goodsid'])?:0;
            $L[$k] = array_merge($v,$value);
        }
        if( count($L)<=0 ){
            echo json_encode(array('status' => 0));
        }else{
            echo json_encode(array('data' => $L, 'status' => 1));
        }
         exit();
    }

    /**
     * 删除购物车里的商品
     */
    public function deleteshoppingcart() {
        $cartDataId = $this->_get('cartDataId');

        $userInfo = loginJudbe();
        if ($userInfo == false) {
            echo json_encode(array('status' => 0));
            return false;
        }
        $m = new \core\model();
        $res = $m->query("delete from `" . SQLPRE . "users_shoppingcart` where id=$cartDataId and userid=$userInfo[id]");
        if($res){
            echo json_encode(array('status' => 1));
            exit();
        }
    }

    /**
     * 确认订单 （订单确认页面）
     */
    public function ConfirmAnOrder() {
//        $this->display('goods/orderDone'); exit();
        //清空所有信息
        if ($this->_get('clear') == 'all') {
            $_SESSION['ConfirmAnOrderGoods'] = null;
        }
        $m = new \core\model();
        $userinfo = loginJudbe();
        if ($userinfo == false) {
            header('Location: ' . __ROOT__ . '/u/login');
            exit();
        }

        //用户收货地址  start
//        $addressid = $_SESSION['ConfirmAnOrderGoods']['addressid'];
        $adrlist = $m->getall("select ua.*,ap.provice_name,ac.city_name,act.county_name  from " . $m->prefix . "users_address ua "
                . "left join " . $m->prefix . "area_provice ap on ua.proviceSn=ap.provice_id "
                . "left join " . $m->prefix . "area_city ac on ua.citySn=ac.city_id  "
                . "left join " . $m->prefix . "area_county act on ua.countySn=act.county_id "
//                    . "left join " . $m->prefix . "area_town at on ua.townSn=at.town_id "
                . " where ua.userid=$userinfo[id]");
        $this->assign('adrlist', $adrlist);

        if( isset($_SESSION['ConfirmAnOrderGoods']['addressid']) ){
            
        }else{
            if($adrlist){
                $_SESSION['ConfirmAnOrderGoods']['addressid'] = $adrlist[0]['id'];
                $this->assign('adrlist', $adrlist);
            }
        }
        //用户收货地址  end
        
        //取收货地址
        if( isset($_SESSION['ConfirmAnOrderGoods']['addressid']) ){
            $address = $m->getrow("select * from " . $m->prefix . "users_address where id=" . $_SESSION['ConfirmAnOrderGoods']['addressid']);
        }
        
        //支付方式  start
        $payment['alipay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='alipay01' and status=1");
        $payment['weixinPay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='weixin01' and status=1");
        $payment['transfer'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='transfer' and status=1");
        $payment['face_pay'] = $m->getrow("select * from " . SQLPRE . "payment where `sn`='face_pay' and status=1");
        $this->assign('payment', $payment);
        //支付方式  end
        //选择默认 支付方式begin
        if ( empty($_SESSION['ConfirmAnOrderGoods']['paymentid']) && 
                !isset($_SESSION['ConfirmAnOrderGoods']['selectpay'])
                ) {
            $_SESSION['ConfirmAnOrderGoods']['paymentid'] = $payment['transfer']['sn'];
        }
        
        //配送方式 start
        $freightList = $m->getall("select *,tag as yixinu from " . SQLPRE . "deliverys where status=1");
        $this->assign('freighlist', $freightList);
        //配送方式 end
        
        //选择默认 配送方式
        if ( empty($_SESSION['ConfirmAnOrderGoods']['freightid']) ) {
            $_SESSION['ConfirmAnOrderGoods']['freightid'] = $freightList['delivery']['id'];
//            $_SESSION['ConfirmAnOrderGoods']['freight_name'] = $freightList['delivery']['name'];
        }
        //end 
        
        // 商品价格相关信息  start
        $goodsData = array();
        if (!isset($_SESSION['ConfirmAnOrderGoods']) or ! isset($_SESSION['ConfirmAnOrderGoods']['goods'])) {
            message('你好像还没有要购买的商品！先去逛逛吧。');
        } else {
            $goods_data_id = $_SESSION['ConfirmAnOrderGoods']['goods'];  //提交到订单的商品
            $where_2 = null;
            foreach ($goods_data_id as $key => $value) {
                $where_2 .= "ga.id = $key or ";
            }
            $where_2 = substr($where_2, 0, -3);
            $sql = "select g.business_no,g.name,ga.id as yixinu,ga.attributeStr,ga.shopPrice,ga.thumb,ga.numbers, "
                    . "if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ag.xiangou,  "
                    . " ga.weight,ga.sn from " . $m->prefix . "goods g "
                    . " left join `" . $m->prefix . "goods_additional` ga on g.id=ga.goodsId "
                    . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                    . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                    . " where $where_2 and ga.addStatus=1";
            $goods_data = $m->getall($sql); //要购买的商品数据
        }
        
        $new_order_list = array();  //拆单后新的订单数组
        $goodstotal = 0 ; //商品价格总计
        $total = 0; //应付款
        $freight = 0; //运费
        $Preferential = 0; //优惠
        $coupon = 0;
        $goods_data_2 = split_order_specify_business($goods_data);  //按商户拆分
        foreach ($goods_data_2 as $business_no => $order_goods_2) {
            //寻找最近的仓库发货
            $tmpvar1 = match_stores($address['lat'] , $address['lng'] , $business_no);
            $tmpvar2['delivery_warehouse'] = $tmpvar1['id'];
            $tmpvar2['warehouse_name'] = $tmpvar1['name'];
            $tmpvar2['warehouse_address'] = $tmpvar1['provice_name'].$tmpvar1['city_name'];//.$tmpvar1['county_name'].$tmpvar1['address'];

            if( !$business_no || empty($business_no) ): message("商品数据错误，未知的商户!"); endif;
            $new_order_info = $this->priceComputation($userinfo['id'] , $order_goods_2 , $business_no ,$tmpvar2['delivery_warehouse']);
            if ($new_order_info == FALSE):  message('订单创建失败!'); endif;
            $new_order_list[$business_no] = array_merge( $new_order_info , $tmpvar2 );

            $goodstotal += $new_order_info['goodstotal'];
            $total += $new_order_info['total'];
            $freight += $new_order_info['freight'];
        }
//        var_dump($new_order_list);
        $this->assign('order_list', $new_order_list);
        $this->assign('goodstotal', $goodstotal);
        $this->assign('total', $total);
        $this->assign('freight', $freight);
        $this->assign('Preferential', $Preferential);
        $this->assign('coupon', $coupon);
        // 商品价格相关信息  end
        
        //如果提交了订单或没提交订单    
        if (isset($_POST['_submit_'])) {
            $address = $m->getrow("select * from " . SQLPRE . "users_address where id=" . $_SESSION['ConfirmAnOrderGoods']['addressid']);
            //订单数据
            $orderSn = date('ymdHis') . rand(10, 99) . $userinfo['id'];
            $sortsn = $m->getone("SELECT count(*)+1 as c FROM `".SQLPRE."orders` WHERE `payType`='transfer' and payStatus=1");
            $data['orderSn'] = $orderSn; //date('YmdHis').$userinfo['id'].rand(1000, 9999);
            $data['sortsn'] = rand(10, 99) .$sortsn;  //短订单号
            $data['userId'] = $userinfo['id'];
            $data['payType'] = $_SESSION['ConfirmAnOrderGoods']['paymentid'];
            $data['deliveryMethod'] = $_SESSION['ConfirmAnOrderGoods']['freightid'];
//            $data['status'] = 1; //订单已经确认
            $data['payStatus'] = 1;  //未支付 
            $data['delStatus'] = 1; //未发货

            $data['name'] = $address['recipients'];
            $data['proviceId'] = $address['proviceSn'];
            $data['cityId'] = $address['citySn'];
            $data['countyId'] = $address['countySn'];
            $data['townId'] = $address['townSn'];
            $data['mobile'] = $address['mobile'];
            $data['address'] = $address['street'];
            $data['phone'] = $address['phone'];
            $data['zipcode'] = $address['zipcode'];

            $data['amount'] = $total;
            $data['goodsAmount'] = $goodstotal;
            $data['freight'] = $freight;
            $data['weight'] = $weight_;
            $data['coupon'] = $coupon;
            $data['preferential'] = $Preferential;  //用户 优惠 
            $data['usernote'] = $this->_post('comment');

            $result = $m->sData($data, 'orders');

            if ($result) {
                $orderid = mysqli_insert_id($m->link);
                //添加订单商品信息
                $goodsData = $_SESSION['ConfirmAnOrderGoods']['goods'];  //提交到订单的商品
                foreach ($goodsData as $key => $value) {
                    $sql = "select g.name,ga.attributeStr, g.business_no,"
                            . "if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , "
                            . "ga.id as yixinu,ga.thumb,ga.sn "
                            . " from " . SQLPRE . "goods g "
                            . " left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId "
                            . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                            . " left join " . SQLPRE . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                            . " where ga.id=$key";
                    $var1 = $m->getrow($sql);

                    $ordergoods['orderid'] = $orderid;
                    $ordergoods['goodsid'] = $key;
                    $ordergoods['business_no'] = $var1['business_no'];
                    $ordergoods['goodsname'] = $var1['name'];
                    $ordergoods['goodssn'] = $var1['sn'];
                    $ordergoods['goodsnum'] = $value;
                    $ordergoods['goodsprice'] = $var1['shopPrice'];
                    $ordergoods['goodsattributeStr'] = $var1['attributeStr'];
                    $ordergoods['goodsthumb'] = $var1['thumb'];
                    $ordergoods['payStatus'] = 1;
                    $ordergoods['delStatus'] = 1;

                    $v = null;
                    $k = null;
                    foreach ($ordergoods as $key2 => $value2) {
                        $k.="`$key2`,";
                        $v.="'$value2',";
                    }
                    $k = substr($k, 0, -1);
                    $v = substr($v, 0, -1);
                    $sql = "insert into " . SQLPRE . "order_goods ($k) values ($v)";
                    $m->query($sql);
                    //删除购物车内收藏的商品
                    $sql = "delete from `" . SQLPRE . "users_shoppingcart` where userid=$userinfo[id] and goodsid=$key";
                    //如果用了优惠券
                    if (isset($_SESSION['ConfirmAnOrderGoods']['couponId']) and $_SESSION['ConfirmAnOrderGoods']['couponId'] != 0) {
                        $couponId = $_SESSION['ConfirmAnOrderGoods']['couponId'];
                        $data2['orderSn'] = $orderSn;
                        $data2['status'] = 4;
                        $m->sData($data2, 'coupon_issue', 'u', "id=$couponId");
                    }
                    $m->query($sql);
                }
                //for end;

                //前往收银台
                $_SESSION['ConfirmAnOrderGoods'] = null;
                header("Location:" . createLink('pay/collectMoney', array('id' => $orderSn)));
                exit();
            }
        } else {
            if (@!empty($_SESSION['ConfirmAnOrderGoods']['addressid']))
                $this->assign('addressid', $_SESSION['ConfirmAnOrderGoods']['addressid']);
            if (@!empty($_SESSION['ConfirmAnOrderGoods']['freightid']))
                $this->assign('freightid', $_SESSION['ConfirmAnOrderGoods']['freightid']);
            if (@!empty($_SESSION['ConfirmAnOrderGoods']['paymentid']))
                $this->assign('paymentid', $_SESSION['ConfirmAnOrderGoods']['paymentid']);
            $this->display('goods/ConfirmAnOrder');
        }
    }


    /**
     * @param type $userid   用户ID
     * @param type $goodsData   提交到订单的商品
     * 订单确认页面 - 价格计算
     */
    public function priceComputation($userid , $goodsData , $business_no,$store_id) {
        $m = new \core\model();
        $redis = new \models\yredis();
        
         //查询商户信息
        $d['business'] = $m->getrow("select business_no,business_name from `".SQLPRE."business` where business_no=$business_no");
        $d['goodstotal'] = 0; //商品价格总计
        $d['total'] = 0; //应付款
        $d['freight'] = 0; //运费
        $d['Preferential'] = 0; //优惠
        
        //判断有没有商品
        if (!empty($goodsData) and is_array($goodsData) and count($goodsData) > 0) {
            if( !$goodsData or !  is_array($goodsData) or count($goodsData)<=0 ){
                message('商品已经下架或者已经删除，请重新选购。');
            }
            $weight = 0;
            $result = array();
            $goods_data_num = $_SESSION['ConfirmAnOrderGoods']['goods'];  //为了取购物车商品数据
            foreach ($goodsData as $key => $value) {
                $value['num'] = $goods_data_num[$key];
                $stores_goods_num = $redis->get(REDIS_PRE."stores_goods_".$store_id."_".$key);
                if( $value['num']>intval($stores_goods_num) ){
                    $value['quehuo'] = 1;//仓库缺货
                }
                //取用户的购买记录， 用作限购判断 
                $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$key)?:0;
                //限购判断 ： 已经购买过的数量 + 新添加的数量  > 限购数量
                if( $value['xiangou']>0 && ($value['xiangouuser']+$value['num'])>$value['xiangou'] ){
                    msg("$value[name] $value[attributeStr] <br />已超限购(每日限购：$value[xiangou] )<br />请返回 <a href='/?v=/user/shoppingcart'> 购物车 </a> 重新选择" );
                }
                //库存判断：库存低于警界数，或者购买数量已经超过了库存数量
                if( $value['numbers']<KUCUNBUZU || $value['num']>$value['numbers'] ){
                    msg("非常抱歉，$value[name] $value[attributeStr] 库存不足！<br />请返回 <a href='/?v=/user/shoppingcart'> 购物车 </a> 重新选择" );
                }
                
                $value['shopPriceTotal'] = round($value['shopPrice'], 2) * round($value['num'], 2);
                $result[$key] = $value;
                $d['goodstotal'] += round($value['shopPriceTotal'], 2);
                $weight += round($value['weight'], 2) * $value['num'];
            }
            $d['weight'] = $weight;
            //运费计算  start
            $addressid = @$_SESSION['ConfirmAnOrderGoods']['addressid'];
            $freightid = @$_SESSION['ConfirmAnOrderGoods']['freightid'];
            if (!empty($addressid) and ! empty($freightid)) {
                $info = $m->getrow("select id,proviceSn,citySn,countySn,townSn from " . $m->prefix . "users_address where id=$addressid");
                $d['freight'] = $this->freightComputation($weight, $freightid, $info['proviceSn'], $info['citySn'], $info['countySn'], $info['townSn']);
            }
            //运费计算  end
            
//            //用户等级的优惠折扣 begin
//            $sql2 = "select r.discount from ".SQLPRE."users u, ".SQLPRE."user_rank r where u.userRank=r.id and u.id=$userid";
//            $discount = $m->getone($sql2);
//            $d['Preferential'] = round($d['goodstotal'],2) - round($d['goodstotal'] * ($discount/100),2);
//            // end
            
//            //计算系统配置的 ， 订单满 多少 减运费
//            if( round($d['goodstotal'],2) > (int)$GLOBALS['config']['freightdiscount'] ){
//                $d['freight'] = 0;
//            }
            
            $d['coupon'] = $this->useCoupon($d['goodstotal'] + $d['freight'] - $d['Preferential']);
            $d['total'] = round($d['goodstotal'], 2) + round($d['freight'], 2) - round($d['Preferential'], 2) - round($d['coupon'], 2);
            $d['goodslist'] = $result;

            return $d;
        } else {
            return false;
        }
    }
    
    public function split1($goods=null) {
        if($goods){
            $m = new \core\model();
            $where = null;
            foreach ($goods as $key => $value) {
                $where .= "ga.id = $key or ";
            }
            $where = substr($where, 0, -3);
            $sql = "select ga.id,g.business_no,ga.id as yixinu  from " . SQLPRE . "goods g  left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId "
                    . " where $where and ga.addStatus=1";
            $goods_list = $m->getall($sql);
            if( !$goods_list or !  is_array($goods_list) or count($goods_list)<=0 ){
                message('商品已经下架或者已经删除，请重新选购。');
            }
            
            $split_goods_list = array();
            foreach ($goods_list as $key => $g) {
                $business_no = $g['business_no'];
                $split_goods_list[$business_no][$key] = $goods[$key];
            }
            return $split_goods_list;
            
        }
    }

    /**
     * 判断优惠券使用
     * @param type $total  价格  商品总价+运费+优惠后的费用
     */
    public function useCoupon($total) {
        if (!isset($_SESSION['ConfirmAnOrderGoods']['couponId']) or empty($_SESSION['ConfirmAnOrderGoods']['couponId'])) {
            return 0;
        } else {
            $m = new \core\model();
            $couponId = $_SESSION['ConfirmAnOrderGoods']['couponId'];
            $sql = "select money from `" . SQLPRE . "coupon_issue` where id=$couponId and status=1 and amount<=$total and orderSn is null and now()<endTime";
            $couponMoney = $m->getone($sql);
            if (empty($couponMoney)) {
                return 0;
            }
            return floatval($couponMoney);
        }
    }

    /**
     * 优惠券使用
     */
    public function useCouponHTML() {
        $m = new \core\model();
        $userinfo = loginJudbe();
        $Total = $this->priceComputation($userinfo['id']);
        $total = $Total['goodstotal'] + $Total['freight'] - $Total['Preferential'];
        if (isset($_POST['_submit_'])) {
            $selectcoupon = $this->_post('selectcoupon');
            if ($selectcoupon) {
                $couponUserid = $m->getone("select userId from `" . SQLPRE . "coupon_issue` where id=$selectcoupon and status=1");
                //判断是不是用的自己的优惠券
                if ($couponUserid == $userinfo['id']) {
                    $_SESSION['ConfirmAnOrderGoods']['couponId'] = $selectcoupon;
                }
            } else {
                unset($_SESSION['ConfirmAnOrderGoods']['couponId']);
            }
        } else {
            $couponList = null;
            $sql = "select * from `" . SQLPRE . "coupon_issue` where userId=$userinfo[id] and status=1 and amount<=$total and orderSn is null and now()<endTime";
            $couponList = $m->getall($sql);
            if (empty($couponList) or count($couponList) == 0) {
                $this->assign('msg', '没有符合条件的优惠券！');
            } else {
                $this->assign('couponList', $couponList);
            }
            $this->display('goods/useCoupon');
        }
    }

    /**
     * 订单确认页面 - 计算运费
     * @param type $weight  商品的总重量
     * @param type $freightid  选择的配送方式ID 
     */
    public function freightComputation($weight = null, $freightid = null, $provice = null, $city = null, $county = null, $town = null) {
        $total = 0;
        if (@empty($weight) or @ empty($freightid)) {
            return $total;
        }
        $m = new \core\model();
        //四个级别，从上到下只要有一个匹配到就不再往下匹配
        $lev1 = $provice . ',' . $city . ',' . $county . ',' . $town . ',';
        $lev2 = $provice . ',' . $city . ',' . $county . ',';
        $lev3 = $provice . ',' . $city . ',';
        $lev4 = $provice . ',';

        $data = null;
        $data = $m->getrow("select * from " . SQLPRE . "separatefreight where area='$lev1' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . SQLPRE . "separatefreight where area='$lev2' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . SQLPRE . "separatefreight where area='$lev3' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . SQLPRE . "separatefreight where area='$lev4' and deliverId='$freightid'");
        if ($data == null)
            $data = $m->getrow("select * from " . SQLPRE . "separatefreight where area='' and deliverId='$freightid'");

        //如果首重和续重为都 等于0  ，运费直接返回0
        if ((float) $data['secondWeight'] == 0 and (float) $data['firstWeight'] == 0) {
            return 0;
        }

        //重量如果小于首重或等 于首重，按首重价格算。
        if ($weight <= (float) $data['firstWeight']) {
            $total+=$data['firstPrice'];
        } else {
            $total+=$data['firstPrice'];  //首重价格
            if ((float) $data['firstWeight'] <= 0 and (float) $data['secondWeight'] > 0) {  //如果首重为0,续重不为0
                $q = intval(($weight - $data['firstWeight']) / $data['secondWeight']);
                $h = ($weight - $data['firstWeight']) / $data['secondWeight'];
                $total+=$q * $data['secondPrice'];  //续重价格
                if ($h > $q) {
                    $total+=$data['secondPrice'];  //续重不足一斤算一斤的
                }
            } elseif ((float) $data['secondWeight'] <= 0 and (float) $data['firstWeight'] > 0) {//如果首重大于0,续重为0
            } else {   //如果 两个重量 都 不为0
                $q = intval(($weight - $data['firstWeight']) / $data['secondWeight']);
                $h = ($weight - $data['firstWeight']) / $data['secondWeight'];
                $total+=$q * $data['secondPrice'];  //续重价格
                if ($h > $q) {
                    $total+=$data['secondPrice'];  //续重不足一斤算一斤的
                }
            }
        }
        return $total;
    }

    /**
     * 订单确认页面 - 修改商品数量
     */
    public function ConfirmAnOrderChangeGoods() {
        $userInfo = loginJudbe();
        $m = new \core\model();
        if ($userInfo == false) {
            echo json_encode(array('status' => 2));
            exit();
        }

        if (isset($_GET['type']) and $_GET['type'] == 2) { //如果是从我的购物车点进来的
            unset($_SESSION['ConfirmAnOrderGoods']['goods']);
            $id_ = explode(',', $this->_get('id'));
            $where = null;
            foreach ($id_ as $value) {
                if (!empty($value))
                    $where .= "goodsid=$value or ";
            }
            $where = substr($where, 0, -3);
            $sql = "select goodsnum,goodsid from `" . SQLPRE . "users_shoppingcart` where ($where) and (userid=$userInfo[id])";
            $result = $m->getall($sql);
//                        echo json_encode($sql); exit();
            foreach ($result as $key => $value) {
                $goodsid = $value['goodsid'];
                $num = $value['goodsnum'];
                $_SESSION['ConfirmAnOrderGoods']['goods'][$goodsid] = $num;
            }
            echo json_encode(array('status' => 1));
            exit();
        } else {  //如果是点的 立即购买 按钮或者是订单确认页面修改商品数量
            //是否要清空之前订单中的商品
            if ($this->_get('clear') == 'all')
                unset($_SESSION['ConfirmAnOrderGoods']['goods']);
            $goodsid = $this->_get('goodsid');
            $num = $this->_get('num');
            $_SESSION['ConfirmAnOrderGoods']['goods'][$goodsid] = $num;
            echo json_encode(array('status' => 1));
            exit();
        }
    }

    /**
     * 订单确认页面 - 选择收货地址
     */
    public function selectAddress() {
        $id = $this->_get('id');
        $_SESSION['ConfirmAnOrderGoods']['addressid'] = $id;
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 订单确认页面 - 选择支付方式
     */
    public function selectPayment() {
        $sn = $this->_get('sn');
        $_SESSION['ConfirmAnOrderGoods']['paymentid'] = $sn;
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 订单确认页面 - 选择配送方式
     */
    public function selectFreight() {
        $id = $this->_get('id');
        $_SESSION['ConfirmAnOrderGoods']['freightid'] = $id;
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 订单确认页面 - 添加新收货地址  提交数据
     */
    public function orderAddNewAddress() {
        $userinfo = loginJudbe();
        if ($userinfo !== false) {
            $m = new \core\model();
            //总共有多少个收货地址，如果大于12个，不能再添加
            $addressCount = $m->getone("select count(id) from " . SQLPRE . "users_address where userid=$userinfo[id]");
            if ($addressCount >= 12 and $id == false) {
                header('Location: ' . __ROOT__ . '?c=goods&a=ConfirmAnOrder');
                exit();
            }

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

            $data['userid'] = $userinfo['id'];
            $ad = get_addres_name($data['proviceSn'], $data['citySn'], $data['countySn']);
            if ( $m->sData(array_merge($data, $ad), 'users_address') ) {
                $_SESSION['ConfirmAnOrderGoods']['addressid'] = mysqli_insert_id($m->link);
                header('Location: ' . __ROOT__ . '?c=goods&a=ConfirmAnOrder');
                exit();
            }
        } else {
            header('Location: ' . __ROOT__ . '/u/login');
            exit();
        }
    }

    /**
     * 商品促销活动
     */
    public function activity() {
        $m = new \core\model();
        $id = $this->rget('ac');

        $sql = "select * from " . SQLPRE . "activity where id=$id";
        $activityInfo = $m->getrow($sql);
        $this->assign('info', $activityInfo);
        $this->assign('webtitle_', $activityInfo['name'] . ' 促销活动');
        //把价格属性加到where 条件 里面
        $where_ = " and ag.aid=$id ";
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 16;  //一页6条数据
        $start = $p * $listrows - $listrows;
        //总记录数
        $sql = "select count(g.id) as count from " . SQLPRE . "goods as g "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                . "where ga.addStatus=1 $where_";
        $count = $m->getone($sql);
        $sql = "select g.id as id_,g.name as goodsName,g.status, "
                . "ga.id, "
                . "if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice ,"
                . "ga.attributeStr,ga.numbers,ga.sn,ga.thumb,ga.attributeStr,ga.clickCount,ga.comments from " . SQLPRE . "goods as g "
                . "left join " . SQLPRE . "category as gc on g.catId=gc.id "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                . " left join " . SQLPRE . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                . "where ga.addStatus=1 $where_ limit $start,$listrows";
        $list = $m->getall($sql);
        $this->assign('list', $list);
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => "/goods/activity/ac_{$id}_p_00000.html", //后面5个0是替换页码的字符
            'method' => 'html',
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->display('goods/activity');
    }

    /**
     * 常用功能
     */
    public function _common() {
        $act = $this->_get('act');
        if (!empty($act)) {
            switch ($act) {
                //订单确认页面  ，添加新地址的HTML文件 ，用ajax 方式获取
                case 'orderAddNewAddress':
                    $this->display('goods/other/orderAddNewAddress');
                    break;
                default:
                    break;
            }
        }
    }

}
