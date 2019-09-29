<?php

/*
 * 进销存
 */

namespace controllers;

/**
 * Description of jinxiaocunController
 *
 * @author kyle
 */
class jinxiaocunController extends adminController {

    
    
    /**
     * 检测门店标记的唯一性
     */
    public function CheckStoresMark(){
        $val = $_GET['val'];
        //如果是修改数据，则需要把自身排除，否则无法修改
        $id = $_GET['id'] ? ' and id<>'.$_GET['id']:null;
        $m = new \core\model();
        $sql = "select mark from `".SQLPRE."stores` where mark='$val' $id";
        $res = $m->getone($sql);
        if( $res ){
            echo 0; exit();
        }else{
            echo 1; exit();
        }
    }
    
    /**
     * 库存管理
     */
    public function inventory_nums() {
        $m = new \core\model();
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        //如果有搜索商品或者编号
        $where = ' where og.status=1 ';
        if( isset($_GET['searchVal']) && !empty($_GET['searchVal']) ){
            $where = $where ? $where . ' and '   : 'where ' ;
            $where = " and ( og.goods_name like '%$_GET[searchVal]%' or og.goods_sn like '%$_GET[searchVal]%' ) ";
        }
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        //begin 如果是商户登录
        $business = is_business();
        if( $business ){
                $where = $where ? $where . ' and '   : 'where ' ;
                $where .= 'o.business_no='.$business;
        }
        
        $sql = "select og.*, (og.nums - og.delivery_quantity)  as nums,o.warehouse_id,o.warehouse_name,o.storage_time,o.total,o.id as orderid,g.numbers from  `".SQLPRE."purchase_order_goods` og "
                . " inner join `".SQLPRE."goods_additional` g on og.goods_id=g.id "
                . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
                . "$where order by id desc limit $start,$listrows  ";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(og.id) as count from `".SQLPRE."purchase_order_goods` og  inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id $where");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('jinxiaocun/inventory_nums');
    }
    
    /**
     * 库存管理
     */
    public function inventory() {
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        //如果有搜索商品或者编号
        $where = ' where og.status=1 ';
        if( isset($_GET['searchval']) && !empty($_GET['searchval']) ){
            $where = $where ? $where . ' and '   : 'where ' ;
            $where = " and ( og.goods_name like '%$_GET[searchval]%' or og.goods_sn like '%$_GET[searchval]%' ) ";
        }
        if( isset($_GET['warehouse_id']) && !empty($_GET['warehouse_id']) ){
            $where = $where ? $where . ' and '   : 'where ' ;
            $where = " and ( o.warehouse_id=$_GET[warehouse_id]  ) ";
        }
        //begin 如果是商户登录
        $business = is_business();
        if( $business ){
                $where = $where ? $where . ' and '   : 'where ' ;
                $where .= 'o.business_no='.$business;
        }

        $listrows = 15;
        $start = $p*$listrows-$listrows;
        $sql = "select og.*, ( sum(og.nums) - sum(og.delivery_quantity) ) as nums,o.warehouse_name,o.warehouse_id,o.storage_time,o.total,o.id as orderid,g.numbers "
        . " from  `".SQLPRE."purchase_order_goods` og "
        . " inner join `".SQLPRE."goods_additional` g on og.goods_id=g.id "
        . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
        . "$where group by og.goods_id,o.warehouse_id order by o.warehouse_id asc,og.goods_sn desc limit $start,$listrows  ";
        $list = $m->getall($sql);
        //page 数据
        $count = $m->getone("select count( distinct goods_id,o.warehouse_id ) as count from `".SQLPRE."purchase_order_goods` og "
        . "  inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id  $where ");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign('list', $list);
        
        //仓库列表
        $where2 = ' where status=1';
        if( $business ){
                $where2 = $where2 ? $where2 . ' and '   : 'where ' ;
                $where2 .= 'business_no='.$business;
        }
        $sql = "select id,name,mark from `".SQLPRE."stores` $where2";
        $warehouse = $m->getall($sql);
        $this->assign('warehouse', $warehouse);
        $this->display('jinxiaocun/inventory');
    }
    
    /**
     * 报损显示窗口
     */
    public function baosun_show_win() {
        $res = array();
        $res['w_id'] = $this->_get('w_id');
        $res['w_name'] = $this->_get('w_name');
        $res['goods_id'] = $this->_get('goods_id');
        $res['goods_name'] = $this->_get('goods_name');
        $this->assign($res);
        $this->display('jinxiaocun/baosun_show_win');
    }
    
    /**
     * 报损 提交数据,  出库流程
     */
    public function baosun_post_data() {
        $m = new \core\model();
        
        $data = array();
        $data = $_POST;
        $data['type'] = 2;
        $data['reason'] = 3;
        $business = is_business(); //商户
        if ($business): $data['business_no']=$business; endif;  //商户
        //写出库记录
        $insert_id = $m->sData($data, 'inventory_records');
        
        //begin 从仓库减库存
        //取出每一批采购的商品，按批次减库存
        $sql = "select og.id,og.nums,og.delivery_quantity,( og.nums - og.delivery_quantity ) as total,o.warehouse_id "
            . " from `".SQLPRE."purchase_order_goods` og "
            . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
            . " where og.goods_id=$data[goods_id] and o.warehouse_id=$data[warehouse_id] and og.delivery_quantity<og.nums order by og.id desc ";
        $goods = $m->getall($sql);
        if(!$goods): echo json_encode(array('msg'=>'error')); exit(); endif;
        
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
        $m->sData(array("numbers"=>"`numbers`-$nums_---" ), 'goods_additional', 'u',"id=$data[goods_id]");
        //end
        
        //更新出库记录,更新最后实际减的库存
        $m->sData(array("nums"=>$nums_ ), 'inventory_records', 'u',"id=$insert_id");
        
        //更新缓存
        up_goods_cache();
        
        echo json_encode(array('status'=>1));
        exit();
        
    }
    
    /**
     * 出入库记录
     */
    public function inventory_records() {
        
        $m = new \core\model();
        $where = null;
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        
        //搜索商品
        if( isset($_GET['searchVal']) && !empty($_GET['searchVal']) ) {
            $where = $where ? $where . ' and '   : 'where ' ;
            $where .="where goods_name like '%$_GET[searchVal]%' ";
        }
        
        //如果是查询一个商品的记录
        if( isset($_GET['goods_id']) && !empty($_GET['goods_id']) ){
            $where = $where ? $where . ' and '   : 'where ' ;
            $where .= " goods_id = $_GET[goods_id] ";
        }
        
        $listrows = 20;
        $start = $p*$listrows-$listrows;
        //begin 如果是商户登录
        $business = is_business();
        
        if( $business ){
                $where = $where ? $where . ' and '   : 'where ' ;
                $where .= 'business_no='.$business;
        }
        
        $sql = "select * from `".SQLPRE."inventory_records` $where order by id desc limit $start,$listrows  ";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(id) as count from `".SQLPRE."inventory_records` $where ");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('jinxiaocun/inventory_records');
    }
    
    /**
     * 仓库列表
     */
    public function warehouse() {
        $m = new \core\model();
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 8;
        $start = $p*$listrows-$listrows;
        
        $where = null;//' where s.type=2 ';
        //begin 如果是商户登录
        $business = is_business();
        if( $business ){
                $where = $where ? $where . ' and '   : 'where ' ;
                $where .= 's.business_no='.$business;
        }
        $sql = "select s.*,ap.provice_name,ac.city_name,aco.county_name,b.business_name from `".SQLPRE."stores` s "
                . " left join ".SQLPRE."business b on s.business_no=b.business_no "
                . " left join `".SQLPRE."area_provice` ap on s.provice=ap.provice_id "
                . "left join `".SQLPRE."area_city` ac on s.city=ac.city_id "
                . "left join `".SQLPRE."area_county` aco on s.county=aco.county_id "
                . " $where order by business_no,id limit $start,$listrows  ";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."stores");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('jinxiaocun/warehouse');
    }
    
    /**
     * 添加仓库
     */
    public function warehouse_add() {
        $m = new \core\model();
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            //删除没用的变量值
            unset($_POST['_submit_']);
            unset($_POST['_verifyKey_']);
            //取页面变量
            $data = $_POST;
//            $data['type'] = 2; //设置类型为门店
            //如果设置了缩略图
            if ( isset($_POST['imgfile']) && $_POST['imgfile'] ) {
                $data['thumb'] = $_POST['imgfile'];
                unset($data['imgfile']);
            }
            $business = is_business(); //商户
            if ($business): $data['business_no']=$business; endif;  //商户
            $res = null;
            if (isset($_GET['id']) && !empty($_GET['id'])) {  //如果是修改数据
                $id = $_GET['id'];
                $res = $m->sData($data, 'stores', 'u', "id=$id");
            } else { // 否则就是添加数据
                $res = $m->sData($data, 'stores');
            }
            if ($res) {
                header("Location: ?jinxiaocun=k&type=warehouse");
            }
        } else {
             if (isset($_GET['id']) && !empty($_GET['id'])) {
                 $id = $_GET['id'];
                $sql = "select * from `".SQLPRE."stores` where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
             }
            $this->display('jinxiaocun/warehouse_add');
        }
    }
    
    /**
     * 采购管理
     */
    public function purchasing() {
        $this->display('jinxiaocun/purchasing');
    }
    
    /**
     * 采购订单
     */
    public function purchasing_order() {
        $m = new \core\model();
        //这两行是清除添加采购订单商品的session缓存
        $sesskon_key = 'yixinu_select_goods_select_list';
        unset($_SESSION[$sesskon_key]);
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 8;
        $start = $p*$listrows-$listrows;
        
        $where = null;
        //如果是商户登录
        $business = is_business();
        if( $business ){
                $where = $where ? $where . ' and '  : 'where ' ;
                $where .= 'business_no='.$business;
        }
        
        $sql = "select *,date(`date`) as date from `".SQLPRE."purchase_order` $where order by id desc limit $start,$listrows  ";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."purchase_order $where");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('jinxiaocun/purchasing_order');
    }
    
    /**
     * 添加采购订单
     */
    public function purchasing_order_add() {
        $m = new \core\model();
        $sesskon_key = 'yixinu_select_goods_select_list';
        $business = is_business(); //商户
        if($_SERVER['REQUEST_METHOD']=='POST'){
        //begin    POST 请求
            $data = array();
            if(isset($_POST['status'])) $data['status'] = $_POST['status'];
            if(isset($_POST['principal'])) $data['principal'] = $this->_post('principal');
            if(isset($_POST['date'])) $data['date'] = $this->_post('date');
            $data['comments'] = $this->_post('comments');
            if(isset($_POST['warehouse_id'])) $data['warehouse_id'] = $this->_post('warehouse_id');
            if(isset($_POST['warehouse_name'])) $data['warehouse_name'] = $this->_post('warehouse_name'); //
            //如是是入库，写入库时间 
            if( $data['status']==1 ){
                $data['storage_time'] = date("Y-m-d H:i:s"); //
            }
            if ($business): $data['business_no']=$business; endif;  //商户
            
            if(  $this->_get("order_id") ){  //如果是修改数据
                $order_id = $this->_get("order_id"); 
                $m->sData($data, 'purchase_order','u',"id=$order_id");
            }else{
                $order_id = $m->sData($data, 'purchase_order');  //添加订单
            }
            
            //如果有修改的商品才进行操作 
            if( isset($_REQUEST['goods_id']) && is_array($_REQUEST['goods_id']) && count($_REQUEST['goods_id']) >0 ){
                $total_price = 0;
                //保存商品数据
                foreach ($_REQUEST['goods_id'] as $goods_id => $item) {

                    $data_goods['purchase_order_id'] = $order_id;
                    $data_goods['goods_id'] = $goods_id;
                    $data_goods['goods_sn'] = $_REQUEST['goods_sn'][$goods_id];
                    $data_goods['goods_name'] =  $_REQUEST['goods_name'][$goods_id];
                    $data_goods['supplier_id'] = $_REQUEST['supplier_id'][$goods_id];
                    $data_goods['package'] = $_REQUEST['package'][$goods_id];
                    $data_goods['pack_spec'] = $_REQUEST['pack_spec'][$goods_id];
                    $data_goods['nums'] = $_REQUEST['nums'][$goods_id];
                    $data_goods['purch_price'] = $_REQUEST['purch_price'][$goods_id];
                    $data_goods['price_total'] = $_REQUEST['price_total'][$goods_id];
                    $data_goods['status'] = $data['status'];

                    $total_price = $total_price + round($data_goods['price_total'],2);
                    if( $_REQUEST['ids'][$goods_id] ){  //修改
                        $id_ = $_REQUEST['ids'][$goods_id];
                        $m->sData($data_goods, 'purchase_order_goods','u',"id=$id_");
                    }else{  //添加
                        $m->sData($data_goods, 'purchase_order_goods');
                    }
                    
                    //入库增加商品总库存
                    if( $data['status']==1 ){
                        $m->sData(array("numbers"=>"`numbers`+$data_goods[nums]---" ), 'goods_additional', 'u',"id=$goods_id");
                        $data2 = array( 
                                    'goods_id'=>$goods_id,
                                    'goods_name'=>$data_goods['goods_name'],
                                    'type'=>1,
                                    'nums'=>$data_goods['nums'],
                                    'reason'=>1,
                                    'comments'=>'采购入库,订单ID：'.$order_id,
                                    'var'=>$order_id
                        );
                        if ($business): $data2['business_no']=$business; endif;
                        //入库记录
                        $m->sData($data2, 'inventory_records');
                    }
                    
                }
                //更新订单的总价格
                $m->sData(array('total'=>$total_price),'purchase_order','u',"id=$order_id");
                unset($_SESSION[$sesskon_key]);
                //如是是入库，入 库后更新商品缓存
                if( $data['status']==1 ){
                    up_goods_cache();
                }
            }
            header("Location: ?jinxiaocun=c&type=order_add&order_id=$order_id");
            exit();
            
        //end    POST 请求
        }else{
        //begin    GET 请求
                //如果是选择商品提交数据
                if( $this->_get('type')=='callback' ){
                    if ( isset($_REQUEST['goods_id']) ) {
                        $arr = isset($_SESSION[$sesskon_key])?$_SESSION[$sesskon_key]:array();
                        foreach ($_REQUEST['goods_id'] as $key => $value) {
                            if( !$value ){
                                continue;
                            }
                            $arr[$value] = $value;
                        }
                        //将所有提交过来的商品ID记录到SESSION
                        $_SESSION[$sesskon_key] = $arr;
                        echo json_encode(array('status' => 1));
                        exit();
                    }
                }

                //订单详情
                $order_info = array();
                $order_goods_list = array();
                //如果是从订单中取出
                if(  $this->_get("order_id") ){
                    $order_id = $this->_get("order_id");
                    $sql = "select *,date(`date`) as date from `".SQLPRE."purchase_order` where id=$order_id";
                    $order_info = $m->getrow($sql);
                    $sql = "select og.*  from `" . SQLPRE. "purchase_order_goods` og "
                            . " where `purchase_order_id`=$order_id ";
                    $order_goods_list = $m->getall($sql);
                }else{
                    $order_info['principal'] = '采购管理员';
                    $order_info['status'] = 2;
                    $order_info['date'] = date("Y-m-d");
                }

                //判断缓存的商品列表是不是为空
                $list = isset($_SESSION[$sesskon_key])?$_SESSION[$sesskon_key]:array();
                if(is_array($list) && count($list)>0 ){
                    $where = ' where ';
                    foreach ($list as $key => $value) {
                        $where.=" ga.id=$value or ";
                    }
                    $where = substr($where,0,-3);
                    $list = $m->getall("select concat(g.name,ga.attributeStr) as goods_name,ga.sn as goods_sn,ga.thumb,ga.id as goods_id from " . SQLPRE. "goods g "
                            . "left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId  $where  order by ga.id ");
                }
                //合并缓存和数据库中的商品
                $this->assign('select_goods_list',  array_merge($list,$order_goods_list) );
                
                //仓库列表
                $where = 'where status=1 ';
                //如果是商户登录
                $business = $_SESSION['business_no']; //is_business();
                if( $business ){
                        $where = $where ? $where . ' and '  : 'where ' ;
                        $where .= 'business_no='.$business;
                }
                $sql = "select id,name,mark from `".SQLPRE."stores` $where";
                $warehouse = $m->getall($sql);
                $this->assign('warehouse', $warehouse);

            $this->assign('info', $order_info);
            $this->display('jinxiaocun/purchasing_order_add');
            
        //end    GET 请求
        }
    }
    
    /**
     * 删除已经选择的商品缓存
     */
    public function del_purchasing_order_cache() {
        $sesskon_key = 'yixinu_select_goods_select_list';
        $list = isset($_SESSION[$sesskon_key])?$_SESSION[$sesskon_key]:array();
        $goods_id = $this->_get('goodsid');
        $order_id = $this->_get('order_id');
        if( $goods_id ){
            unset($list[$goods_id]);
            $_SESSION[$sesskon_key] = $list;
            if($order_id){
                $m = new \core\model();
                $sql = "delete from `".SQLPRE."purchase_order_goods` where `purchase_order_id`=$order_id and goods_id=$goods_id ";
                $m->query($sql);
            }
        }else{
            unset($_SESSION[$sesskon_key]);
        }
        echo json_encode(array('status' => 1));
        exit();
    }
    
    /**
     * 显示选择商品窗口,商品列表的页面显示和翻页的ajsx请求
     */
    public function show_win_select_goods() {
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $numbers = @$_GET['numbers'] ? : null ;
        $search = isset($_GET['search']) ? $_GET['search'] : '' ; 
        $listrows = 8;
        $pagesize = $this->_get('pagesize');
        if ($pagesize) {
            $listrows = $pagesize;
        }
        $start = $p * $listrows - $listrows;

        $where = '';
        if($search){
            $where = " where g.name like '%$search%' or ga.sn like '%$search%' or ga.tiaoma like '%$search%' ";
        }
        if($numbers==2){
            $where = $where ? $where.' and ' : 'where ' ;
            $where .=" ga.numbers<=0";
        }
        if($numbers==3){
            $where = $where ? $where.' and ' : 'where ' ;
            $where .=" ga.numbers<=10";
        }
        //begin 如果是商户登录
        $business = $_SESSION['business_no']; //is_business();
        if( $business ){
                $where = $where ? $where . ' and '  : 'where ' ;
                $where .= 'g.business_no='.$business;
        }
        //end
        $sql = "select g.name,ga.sn,ga.id,ga.attributeStr,ga.shopPrice,ga.thumb from " . SQLPRE . "goods g "
                . "left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId $where  order by id limit $start,$listrows";
        $list = $m->getall($sql);

        //page 数据
        $count = $m->getone("select count(ga.id) as count from " . SQLPRE . "goods g "
                . "left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId $where");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'obj.select_goods_page',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(3);
        if ($this->_get('ajax')) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign("list", $list);
            $this->display('jinxiaocun/show_win_select_goods');
        }
    }
    
    /**
     * 供应商管理
     */
    public function supplier() {
        $m = new \core\model();
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 8;
        $start = $p*$listrows-$listrows;
        
        $sql = "select s.*,ap.provice_name,ac.city_name,aco.county_name from `".SQLPRE."supplier` s "
                . " left join `".SQLPRE."area_provice` ap on s.provice=ap.provice_id "
                . "left join `".SQLPRE."area_city` ac on s.city=ac.city_id "
                . "left join `".SQLPRE."area_county` aco on s.county=aco.county_id "
                . " order by id desc limit $start,$listrows  ";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."supplier");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $list);
        $this->display('jinxiaocun/supplier');
    }
    
    /**
     * 添加供应商
     */
    public function supplier_add() {
        $m = new \core\model();
        if ($_SERVER["REQUEST_METHOD"] == 'POST') {
            //删除没用的变量值
            unset($_POST['_submit_']);
            unset($_POST['_verifyKey_']);
            //取页面变量
            $data = $_POST;
            //如果设置了缩略图
            if ( isset($_POST['imgfile']) && $_POST['imgfile'] ) {
                $data['thumb'] = $_POST['imgfile'];
                unset($data['imgfile']);
            }
            $res = null;
            if (isset($_GET['id']) && !empty($_GET['id'])) {  //如果是修改数据
                $id = $_GET['id'];
                $res = $m->sData($data, 'supplier', 'u', "id=$id");
            } else { // 否则就是添加数据
                $res = $m->sData($data, 'supplier');
            }
            if ($res) {
                header("Location: ?jinxiaocun=g&type=supplier");
            }
        } else {
             if (isset($_GET['id']) && !empty($_GET['id'])) {
                 $id = $_GET['id'];
                $sql = "select * from `".SQLPRE."supplier` where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
             }
            $this->display('jinxiaocun/supplier_add');
        }
    }
    
    /**
     * 查询一个商品的销售、库存、出入库记录
     */
    public function a_sales_record() {
        $m = new \core\model();
        $goods_id = $this->_get('goods_id');
        if (isset($_POST['_submit_'])) {
            $info['starttime'] = $_POST['starttime'];
            $info['endtime'] = $_POST['endtime'];
        }
        $info['starttime'] = empty($info['starttime']) ? date('Y-m-' . '01 00:00:00') : $info['starttime'];
        $info['endtime'] = empty($info['endtime']) ? date('Y-m-d H:i:s') : $info['endtime'];
        $info['days'] = round(( strtotime($info['endtime']) - strtotime($info['starttime']) ) / 3600 / 24);
        
        $sql = "select og.*, ( sum(og.nums) - sum(og.delivery_quantity) ) as numbers, sum(og.price_total) as price_total, sum(og.nums) as nums,"
                . "o.warehouse_name,o.warehouse_id "
                . " from  `".SQLPRE."purchase_order_goods` og "
                . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id  "
                . "where og.status=1 and og.goods_id=$goods_id  "
                . " group by og.goods_id,o.warehouse_id";
        $info['inventory'] = $m->getall($sql);
        
        $sql = "select og.*, ( sum(og.nums) - sum(og.delivery_quantity) ) as numbers, sum(og.price_total) as price_total, sum(og.nums) as nums  "
                . " from  `".SQLPRE."purchase_order_goods` og "
                . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
                . "  where og.status=1 and og.goods_id=$goods_id and date > '$info[starttime]' and date < '$info[endtime]'";
        $inventory_ = $m->getrow($sql);
        $this->assign('inventory2',$inventory_);
        
        $sql = "select sum(goodsnum) as nums from `" . SQLPRE . "order_goods` "
                . " where goodsid=$goods_id and createTime > '$info[starttime]' and createTime < '$info[endtime]' ";
        $info['nums'] = $m->getone($sql);
        
        $sql = "select sum(o.amount) as money from `" . SQLPRE . "orders` o,`" . SQLPRE . "order_goods` og  "
                . " where og.orderid=o.id and og.goodsid=$goods_id "
                . " and o.createTime > '$info[starttime]' and o.createTime < '$info[endtime]' ";
        $info['money'] = $m->getone($sql);
        
        $info['list'] = array();
        
        $sql = "select ga.*,g.* from `".SQLPRE."goods_additional` ga,`".SQLPRE."goods` g where ga.goodsId=g.id and ga.id=$goods_id";
        $goods = $m->getrow($sql);
        $this->assign('goods',$goods);
//        var_dump($info);
        
        $this->assign('info',$info);
        $this->display('jinxiaocun/a_sales_record');
    }
    
    /**
     * 盘点
     */
    public function pandian() {
        $this->display('jinxiaocun/pandian');
    }
    
    /**
     * 采购订单统计
     */
    public function order_count() {
        
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $info['starttime'] = $_POST['starttime'];
            $info['endtime'] = $_POST['endtime'];
        }
        $info['starttime'] = empty($info['starttime']) ? date('Y-m-' . '01 00:00:00') : $info['starttime'];
        $info['endtime'] = empty($info['endtime']) ? date('Y-m-d H:i:s') : $info['endtime'];
        $info['days'] = round(( strtotime($info['endtime']) - strtotime($info['starttime']) ) / 3600 / 24);
        
        
        $nums = $m->getone("select sum(numbers) from `".SQLPRE."goods_additional` ga");
        $this->assign('nums', $nums);
        $sql = "select og.*, ( sum(og.nums) - sum(og.delivery_quantity) ) as numbers, sum(og.price_total) as price_total, sum(og.nums) as nums  "
                . " from  `".SQLPRE."purchase_order_goods` og "
                . " inner join `".SQLPRE."purchase_order` o on og.purchase_order_id=o.id "
                . "  where og.status=1 and date > '$info[starttime]' and date < '$info[endtime]'";
        $inventory_ = $m->getrow($sql);
        $this->assign('inventory2',$inventory_);
        
        $this->assign('info',$info);
        $this->display('jinxiaocun/order_count');
    }

    public function exemption() {
        
    }

}
