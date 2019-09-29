<?php

/**
 * 促销活动 控制器
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class activityController extends \controllers\adminController {

    public function index() {
        $this->display('activity/index');
    }

    /**
     * 优惠券
     */
    public function coupon() {
        $m = new \core\model();
        $sql = "select *,if(now()<endTime,status,3) as status from " . $m->prefix . "coupon";
        $list = $m->getall($sql);
        $this->assign('list', $list);
        $this->display('activity/couponTemplate');
    }

    /**
     * 添加 修改 优惠券
     */
    public function couponAdd() {
        $id = $this->_get('id');
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data['name'] = $this->_post('name');
            $data['money'] = $this->_post('money');
            $data['amount'] = $this->_post('amount');
            $data['startTime'] = $this->_post('startTime');
            $data['endTime'] = $this->_post('endTime');
            $data['status'] = $this->_post('status');
            $result = null;
            if ($id) {
                $result = $m->sData($data, 'coupon', 'u', "id=$id");
            } else {
                $result = $m->sData($data, 'coupon');
            }
            if ($result) {
                header("Location: ?activity=coupon");
                exit();
            }
        } else {
            //如果是编辑
            if ($id) {
                $sql = "select * from " . $m->prefix . "coupon where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
            }
            $this->display('activity/couponTemplate');
        }
    }

    /**
     * 已经发放的优惠券
     */
    public function couponSend() {
        $m = new \core\model();
        // 清理 用户为空的优惠券  start
        $sql = "delete from `" . $m->prefix . "coupon_issue` where userId not in (select id from `" . $m->prefix . "users`)";
        $m->query($sql);
        // 清理 用户为空的优惠券  end
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;

        $sql = "select ci.*,if(now()<ci.endTime,ci.status,3) as status,u.username,u.mobile from `" . $m->prefix . "coupon_issue` ci left join `" . $m->prefix . "users` u on ci.userId=u.id limit $start,$listrows";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count(id) as count from " . $m->prefix . "coupon_issue");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));

        $this->assign('list', $list);
        $this->display('activity/couponTemplate');
    }

    // 发放优惠券的功能函数 start  //////////////////////////////////////////////////////////////////////////////////////////////
    /**
     * 给指定用户发放优惠券
     */
    public function SpecifiesUser() {
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $pagesize = $this->_get('pagesize');
        if ($pagesize) {
            $listrows = $pagesize;
        }
        $start = $p * $listrows - $listrows;

        $list = $m->getall("select * from " . $m->prefix . "users order by id limit $start,$listrows");

        //page 数据
        $count = $m->getone("select count(id) as count from " . $m->prefix . "users");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.SpecifiesUserPage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(3);
        if ($this->_get('ajax')) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign("list", $list);
            $this->display('activity/SpecifiesUser');
        }
    }

    /**
     * 给指定用户发放优惠券
     */
    public function SpecifiesUserCallback() {
        $m = new \core\model();
        $id = $this->_get('id');
        if (isset($_REQUEST['userlistcheck']) and $id) {
            $sql = "select startTime,endTime,money,amount,send from " . $m->prefix . "coupon where id=$id";
            $couponInfo = $m->getrow($sql);
            if (is_array($_REQUEST['userlistcheck']) and ! empty($_REQUEST['userlistcheck'])) {

                foreach ($_REQUEST['userlistcheck'] as $key => $value) {
                    $data = null;
                    $data = array();
                    $data['couponId'] = $id;
                    $data['userId'] = $value;
                    $data['money'] = $couponInfo['money'];
                    $data['amount'] = $couponInfo['amount'];
                    $data['startTime'] = $couponInfo['startTime'];
                    $data['endTime'] = $couponInfo['endTime'];
                    $m->sData($data, 'coupon_issue');
                }
                $num = count($_REQUEST['userlistcheck']);
                $data = null;
                $data = array();
                $data['send'] = $couponInfo['send'] + $num;
                $m->sData($data, 'coupon', 'u', "id=$id");
                echo json_encode(array('status' => 1));
                exit();
            }
        }
    }

    /**
     * 按用户等级发放
     * @param type $id   优惠券ID
     */
    public function UserRank() {
        $m = new \core\model();
        $id = $this->_get('id');
        $sql = "select id,name,alias from `" . $m->prefix . "user_rank` where status=1";
        $userRank = $m->getall($sql);
        $this->assign('userrank', $userRank);
        $this->display('activity/UserRank');
    }

    /**
     * 按用户等级发放
     * @param type $id   优惠券ID
     */
    public function UserRankCallback() {
        $m = new \core\model();
        $id = $this->_get('id');
        $w['con'] = $_POST['con']; //条件
        $w['userrank'] = $_POST['val'];  //用户等级

        $sql = "select id,username from  " . $m->prefix . "users where status=1 and userRank $w[con] $w[userrank]";
        $userlist = $m->getall($sql);

        $sql = "select startTime,endTime,money,amount,send from " . $m->prefix . "coupon where id=$id";
        $couponInfo = $m->getrow($sql);

        foreach ($userlist as $key => $value) {
            $data = null;
            $data = array();
            $data['couponId'] = $id;
            $data['userId'] = $value['id'];
            $data['money'] = $couponInfo['money'];
            $data['amount'] = $couponInfo['amount'];
            $data['startTime'] = $couponInfo['startTime'];
            $data['endTime'] = $couponInfo['endTime'];
            $m->sData($data, 'coupon_issue');
        }
        $num = count($userlist);
        $data = null;
        $data = array();
        $data['send'] = $couponInfo['send'] + $num;
        $m->sData($data, 'coupon', 'u', "id=$id");
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 给所有用户发放
     */
    public function AllUser() {
        $m = new \core\model();
        $id = $this->_get('id');
        $sql = "select count(id) from `" . $m->prefix . "users` where status=1";
        $userCount = $m->getone($sql);
        $this->assign('userCount', $userCount);
        $this->display('activity/AllUser');
    }

    /**
     * 给所有用户发放
     */
    public function AllUserCallback() {
        $m = new \core\model();
        $id = $this->_get('id');

        $sql = "select id,username from  " . $m->prefix . "users where status=1";
        $userlist = $m->getall($sql);

        $sql = "select startTime,endTime,money,amount,send from " . $m->prefix . "coupon where id=$id";
        $couponInfo = $m->getrow($sql);

        foreach ($userlist as $key => $value) {
            $data = null;
            $data = array();
            $data['couponId'] = $id;
            $data['userId'] = $value['id'];
            $data['money'] = $couponInfo['money'];
            $data['amount'] = $couponInfo['amount'];
            $data['startTime'] = $couponInfo['startTime'];
            $data['endTime'] = $couponInfo['endTime'];
            $m->sData($data, 'coupon_issue');
        }
        $num = count($userlist);
        $data = null;
        $data = array();
        $data['send'] = $couponInfo['send'] + $num;
        $m->sData($data, 'coupon', 'u', "id=$id");
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 禁用优惠券
     */
    public function couponSetstatus() {
        $m = new \core\model();
        $status = (int) $this->_get('status');
        if (isset($_REQUEST['couponlistcheck']) and $status) {
            if (is_array($_REQUEST['couponlistcheck']) and ! empty($_REQUEST['couponlistcheck'])) {
                foreach ($_REQUEST['couponlistcheck'] as $key => $value) {
                    $data['status'] = $status;
                    $m->sData($data, 'coupon', 'u', "id=$value");
                }
            }
        }
    }

    /**
     * 删除优惠券
     */
    public function couponDelete() {
        $m = new \core\model();
        if (isset($_REQUEST['couponlistcheck'])) {
            if (is_array($_REQUEST['couponlistcheck']) and ! empty($_REQUEST['couponlistcheck'])) {
                foreach ($_REQUEST['couponlistcheck'] as $key => $value) {
                    $sql = "delete from `" . $m->prefix . "coupon` where id=$value";
                    $m->query($sql);
                }
            }
        }
    }

    /**
     * 禁用 已经发放优惠券
     */
    public function ScouponSetstatus() {
        $m = new \core\model();
        $status = (int) $this->_get('status');
        if (isset($_REQUEST['couponlistcheck']) and $status) {
            if (is_array($_REQUEST['couponlistcheck']) and ! empty($_REQUEST['couponlistcheck'])) {
                foreach ($_REQUEST['couponlistcheck'] as $key => $value) {
                    $data['status'] = $status;
                    $m->sData($data, 'coupon_issue', 'u', "id=$value");
                }
            }
        }
    }

    /**
     * 删除 已经发放优惠券
     */
    public function ScouponDelete() {
        $m = new \core\model();
        if (isset($_REQUEST['couponlistcheck'])) {
            if (is_array($_REQUEST['couponlistcheck']) and ! empty($_REQUEST['couponlistcheck'])) {
                foreach ($_REQUEST['couponlistcheck'] as $key => $value) {
                    $sql = "delete from `" . $m->prefix . "coupon_issue` where id=$value";
                    $m->query($sql);
                }
            }
        }
    }

    // 发放优惠券的功能函数 end  //////////////////////////////////////////////////////////////////////////////////////////////
    // 商品促销活动 start  //////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * 商品促销活动  列表 
     */
    public function salesGoods() {
        $m = new \core\model();

        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;

        $m = new \core\model();
        $list = $m->getall("select * from " . $m->prefix . "activity order by id limit $start,$listrows");

        //page 数据
        $count = $m->getone("select count(id) as count from " . $m->prefix . "activity");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->assign("list", $list);

        $this->display('activity/salesGoods');
    }

    /**
     * 删除活动
     */
    public function salesGoodsDel() {
        $activityId = $_GET['id']? : null;
        if ($activityId) {
            $m = new \core\model();
            $data['aprice'] = 'null---';
            $data['aid'] = 'null---';
            $m->sData($data, 'activity_goods','u', "aid=$activityId");
            $sql = "delete from `" . SQLPRE . "activity` where id=$activityId";
            $m->query($sql);

            //更新商品缓存 
            $model = new \models\goods();
            $model->upgoodscache();
        }
        header("Location: ?activity=goodslist");
        exit();
    }

    /**
     * 商品促销活动   编辑
     */
    public function salesGoodsEdit() {
        $m = new \core\model();
        $id = $this->_get('id');
        if (isset($_POST['_submit_'])) {
            $data = array();
            $data['name'] = $this->_post('name');
            $data['starttime'] = $this->_post('starttime');
            $data['endtime'] = $this->_post('endtime');
            $data['salesType'] = $this->_post('salesType');
            $data['zhekou'] = $this->_post('zhekou');
            $data['jianjia'] = $this->_post('jianjia');
            $result = null;
            if ($id) {
                $result = $m->sData($data, 'activity', 'u', "id=$id");
                //更新商品促销价格 start
                $sql = "select goodsid from `" . $m->prefix . "activity_goods` where aid=$id";
                $goodslist = $m->getall($sql);
                if (is_array($goodslist) and count($goodslist) > 0) {
                    foreach ($goodslist as $key => $value) {
                        $data2 = null;
                        $data2 = array();
//                            $data2['aid'] = $id;
                        $sql = "select shopPrice from `" . $m->prefix . "goods_additional` where id=$value[goodsid]";
                        $shopPrice = $m->getone($sql);
                        if ($data['salesType'] == 1) {
                            $data2['aprice'] = floatval($shopPrice) * ( intval($data['zhekou']) / 100 );
                        } elseif ($data['salesType'] == 2) {
                            $data2_aprice = floatval($shopPrice) - floatval($data['jianjia']);
                            $data2['aprice'] = $data2_aprice <= 0 ? 0.99 : $data2_aprice;
                        }
                        $m->sData($data2, 'activity_goods', 'u', "goodsid=$value[goodsid]");
                    }
                }
                //更新商品促销价格 end
            } else {
                $result = $m->sData($data, 'activity');
            }
            
            //更新商品缓存 
            $model = new \models\goods();
            $model->upgoodscache();
            if ($result) {
                header('Location:?activity=goodslist');
                exit();
            }
        } else {
            if ($id) {
                $sql = "select * from " . $m->prefix . "activity where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
            }
            $this->display('activity/salesGoodsEdit');
        }
    }

    /**
     * 商品促销活动   参与促销的商品列表 
     */
    public function salesGoodsEditgoodslist() {
        $id = $this->_get('id');
        $this->assign('aid', $id);
        $m = new \core\model();
        $sql = "select name from " . $m->prefix . "activity where id=$id";
        $activityName = $m->getone($sql);
        $this->assign('activityName', $activityName);

        $sql = "select ag.goodsid,ag.id,ag.aprice,ag.xiangou,g.name,"
                . "ga.attributeStr,ga.shopPrice,ga.sn from `" . $m->prefix . "activity_goods` ag "
                . " left join `" . $m->prefix . "goods_additional` ga on ag.goodsid=ga.id "
                . " left join " . $m->prefix . "goods g on ga.goodsId=g.id where ag.aid=$id ";
        $list = $m->getall($sql);
        $this->assign('list', $list);

        $this->display('activity/salesGoodsEditgoodslist');
    }

    /**
     * 商品促销活动   参与促销的商品列表  手动修改促销价
     */
    public function salesGoodsEditgoodseditprice() {
        $price = $this->_get('price');
        $xiangou = $this->_get('xiangou');
        $goodsid = $this->_get('goodsid');
        $m = new \core\model();
        $data = null;
        if ($price){
            $data['aprice'] = $price;
        }
        
        if ($xiangou!==false){
            $data['xiangou'] = $xiangou;
        }
        
        if ($data) {
            $m->sData($data, 'activity_goods', 'u', "goodsid=$goodsid");
            
            
            //更新商品缓存 
            $model = new \models\goods();
            $model->uponegoodscache($goodsid);
        }
        echo json_encode(array('status' => 1));
        exit();
    }

    /**
     * 商品促销活动   参与促销的商品列表  删除商品促销活动
     */
    public function salesGoodsEditgoodsdelete() {
        $goodsid = $this->_get('goodsid');
        $aid = $this->_get('aid');
        $m = new \core\model();
        $data['aprice'] = 'null---';
        $data['aid'] = 'null---';
        $result = $m->sData($data, 'activity_goods', 'u', "goodsid=$goodsid");
        if ($result) {
            $data2['nums'] = "`nums`-1---";
            $m->sData($data2, 'activity', 'u', "id=$aid");
            
            //更新商品缓存 
            $model = new \models\goods();
            $model->uponegoodscache($goodsid);
            
            echo json_encode(array('status' => 1));
            exit();
        }
    }

    /**
     * 商品促销活动   参与促销的商品列表 - 以指定商品方式 添加商品 
     */
    public function SpecifiesGoods() {
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 7;
        $pagesize = $this->_get('pagesize');
        if ($pagesize) {
            $listrows = $pagesize;
        }
        $start = $p * $listrows - $listrows;

        $list = $m->getall("select g.name,ga.sn,ga.id,ga.attributeStr,ga.shopPrice from " . $m->prefix . "goods g "
                . "left join `" . $m->prefix . "goods_additional` ga on g.id=ga.goodsId  order by id limit $start,$listrows");

        //page 数据
        $count = $m->getone("select count(ga.id) as count from " . $m->prefix . "goods g "
                . "left join `" . $m->prefix . "goods_additional` ga on g.id=ga.goodsId");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.SpecifiesGoodsPage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(3);
        if ($this->_get('ajax')) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign("list", $list);
            $this->display('activity/SpecifiesGoods');
        }
    }

    /**
     * 商品促销活动   参与促销的商品列表 - 以指定商品方式 添加商品 - 回调函数
     */
    public function SpecifiesGoodsCallback() {

        $m = new \core\model();
        $id = $this->_get('id');
        if (isset($_REQUEST['goodslistcheck']) and $id) {
            $sql = "select * from " . $m->prefix . "activity where id=$id";
            $activityInfo = $m->getrow($sql);
            if (is_array($_REQUEST['goodslistcheck']) and ! empty($_REQUEST['goodslistcheck'])) {

                foreach ($_REQUEST['goodslistcheck'] as $key => $value) {
                    $data = null;
                    $data = array();
                    $data['aid'] = $id;
                    $sql = "select shopPrice from `" . $m->prefix . "goods_additional` where id=$value";
                    $shopPrice = $m->getone($sql);
                    if ($activityInfo['salesType'] == 1) {
                        $data['aprice'] = floatval($shopPrice) * ( floatval($activityInfo['zhekou']) / 100 );
                    } elseif ($activityInfo['salesType'] == 2) {
                        $data['aprice'] = floatval($shopPrice) - floatval($activityInfo['jianjia']);
                    }
                    $rr = $m->getone("select goodsid from `" . SQLPRE . "activity_goods` where goodsid=$value");
                    if ($rr) {
                        $m->sData($data, 'activity_goods', 'u', "goodsid=$value");
                    } else {
                        $data['goodsid'] = $value;
                        $m->sData($data, 'activity_goods');
                    }
                }
                $num = count($_REQUEST['goodslistcheck']);
                $data = null;
                $data = array();
                $data['nums'] = "`nums`+$num---";
                $m->sData($data, 'activity', 'u', "id=$id");
                
                
                //更新商品缓存 
                $model = new \models\goods();
                $model->upgoodscache();
                echo json_encode(array('status' => 1));
                exit();
            }
        }
    }

    // 商品促销活动 end  //////////////////////////////////////////////////////////////////////////////////////////////


    public function exemption() {
        
    }

}
