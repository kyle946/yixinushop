<?php

/**
 * 项目函数库
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

/**
 * 打印颜色样式字符串
 * 
 * @return type
 */
function printColor() {
    $r = rand(70, 190);
    $g = rand(70, 190);
    $b = rand(70, 190);
    return "rgb($r,$g,$b)";
}

/**
 * 一级菜单 和 二级菜单 匹配函数
 * @param string $url   要匹配的url参数
 * @return null
 */
function matchurl($url = null, $type = 1) {
    if ($type == 1) {
        if (empty($url)) {
            $querystring = $_SERVER['QUERY_STRING'];
        } else {
            $querystring = $url;
        }

        preg_match("/^([a-zA-Z]+=[a-z|A-Z|\-|\_]+\&?.*)/", $querystring, $matches);
        if (!empty($querystring)):
            return $matches[1];
        else:
            return null;
        endif;
    }

    elseif ($type == 2) {
        if (empty($url)) {
            $querystring = $_SERVER['QUERY_STRING'];
        } else {
            $querystring = $url;
        }

        preg_match("/^([a-zA-Z]+=[a-z|A-Z|\-|\_]+)\&?(.*)/", $querystring, $matches);
        if (!empty($querystring)):
            return $matches[1];
        else:
            return null;
        endif;
    }
}

/**
 * 解析频道字段，根据类型输出html代码
 * @param array $array  字段  表 fields 里面的数据 
 * @param array $extFieldValue 字段 对应 的存储 的值
 */
function parseChannelFieldType($array = null, $extFieldValue = null) {
    $extVal = null;
    if (is_array($array) && !empty($array)) {
        foreach ($array as $val) {
            $mark = $val['mark'];
            $html = null;
            switch ($val['type']) {
                case 'varchar':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'text':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<textarea name="' . $mark . '" class="textarea4" >' . $extVal . '</textarea> ';
                    break;
                case 'html':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<textarea name="' . $mark . '" class="textarea3" >' . $extVal . '</textarea> '
                            . '<script>KindEditor.ready(function(K){ editor_' . $mark . '=K.create(\'textarea[name="' . $mark . '"]\', { width: 750,height: 450 ,items : [ \'source\',\'fontname\', \'fontsize\', \'|\', \'forecolor\', \'hilitecolor\', \'bold\', \'italic\', \'underline\', \'removeformat\', \'|\', \'justifyleft\', \'justifycenter\', \'justifyright\', \'insertorderedlist\', \'insertunorderedlist\', \'|\', \'emoticon    s\', \'image\',\'|\',  \'myimage\', \'link\'] }); });</script>';
                    break;
                case 'int':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'float':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'datetime':
                    if (is_array($extFieldValue))
                        $extVal = $extFieldValue[$mark];
                    $html = '<input type="text" class="input1" name="' . $mark . '" value="' . $extVal . '">';
                    break;
                case 'thumb':
                    if (is_array($extFieldValue))
                        $extVal_ = $extFieldValue[$mark];
                    if (!empty($extVal_)) {
                        $extVal = '<div class="hoverArea"><img border="0" src="' . IMAGE_URL . $extVal_ . '"><a onclick="deleteImgSingle(this)" style="color: red;">删除图片</a><input type="hidden" name="' . $mark . '" value="' . $value . '"></div>';
                    }
                    $html = '<input class="bt1" onclick="insertImage({mode:\'single\',divid:\'thumb_' . $mark . '\',inputname:\'' . $mark . '\',\'level\':\'1\'})" type="button" value="添加图片" />';
                    $html.='<div id=\'thumb_' . $mark . '\' class="ticlass">' . $extVal . '</div>';
                    break;
                case 'image':
                    if (is_array($extFieldValue)) {
                        $extVal_ = $extFieldValue[$mark];
                        if (is_array(unserialize($extVal_))) {
                            foreach (unserialize($extVal_) as $key => $value) {
                                if (!empty($value)) {
                                    $extVal .= '<li><div class="hoverArea"><img border="0" src="' . IMAGE_URL . $value . '"><a onclick="deleteImg(this)" style="color: red;">删除图片</a><input type="hidden" name="' . $mark . '[]" value="' . $value . '"></div></li>';
                                }
                            }
                        }
                    }
                    $html = '<div><input onclick="insertImage({\'divid\':\'imagelist_' . $mark . '\',\'inputname\':\'' . $mark . '[]\',\'level\':\'1\'})" type="button" class="bt1" value="添加图片" /></div>';
                    $html.='<div class="area4 clearfix"><ul class="clearfix imglist" id=\'imagelist_' . $mark . '\'><div>' . $extVal . '</div></ul></div>';
                    break;
                case 'file':
                    break;
                case 'radio':
                    break;
                case 'checkbox':
                    $value_ = $val['val'];
                    $value__ = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $value_));
                    foreach ($value__ as $v2) {
                        list($i1, $i2) = explode('=', $v2);
                        $html.='<input type="checkbox" value="' . $i1 . '"  />' . $i2;
                    }
                    break;
                    break;
                case 'select':
                    $value_ = $val['val'];
                    $value__ = explode("\n", str_replace(array("\r\n", "\n", "\r"), "\n", $value_));
                    $option_ = null;
                    foreach ($value__ as $v2) {
                        list($i1, $i2) = explode('=', $v2);
                        $option_.='<option value="' . $i1 . '" >' . $i2 . '</option>';
                    }
                    $html = '<select name="' . $mark . '" >' . $option_ . '</select>';
                    break;
                default:
                    break;
            }
            echo '<tr id="nohover" >'
            . '<td style="text-align: right;"><span class="fb">' . $val['name'] . '：</span></td>'
            . '<td>' . $html . '</td>'
            . '</tr>';
            $extVal = null;
        }
    } else {
        echo '';
    }
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

function message($content, $title = "") {
    $model = new \core\Controller();
    $key = cfg('LOGIN_SESSION_KEY');
    if (isset($_SESSION[$key]) and ! empty($_SESSION[$key])) {
        $var = $_SESSION[$key];
        $model->assign('userinfo_name', $var['name']);
    }
    $model->assign('title', $title);
    $model->assign('messageContent', $content);
    $model->display('message');
    exit();
}

/**
 * 更新商品缓存
 */
function up_goods_cache() {
    $redis = new \models\yredis();
    $model = new \core\model();
    $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
            . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
            . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou,business_name"
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
                    INNER JOIN  `".SQLPRE."goods_additional` g on og.goods_id = g.id
                    INNER JOIN  `".SQLPRE."purchase_order` o on og.purchase_order_id = o.id
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
 * 获取商品编号
 * @param type $type  参数：create创建编号，change修改编号使用状态
 * @param type $len 参数$type为create时表示需要多少个编号，参数$type为change时表示十六进制的编号
 * @return string or arr 返回一个字符串或者数组，$len大于1时返回数组，等于1时返回一个字符串。
 */
function get_goods_sn($type='create',$len=1) {
    $m = new core\model();
    if( $type=='create' ){
        $goodsSn = NULL;
        if( $len==1 ){  //如果只要一个编号
            $goodsSn_ = $m->getrow("select id from " . SQLPRE . "sn2 where `use`=0 order by id");
            if ($goodsSn_) {
                $goodsSn = dechex($goodsSn_['id']); //转成16进制
            } else {
                $goodsSn = $m->sData(array('v' => 1), 'sn2');   //创建编号
                $goodsSn = dechex($goodsSn); //转成16进制
            }
            return $goodsSn;
        }elseif($len>1){    //如果需要多个
            $goodsSn_ = $m->getall("select id from " . SQLPRE . "sn2 where `use`=0 order by id");
            if( count($goodsSn_)<$len){ //如果不够数量就创建
                for($i=0;$i<$len;$i++){
                    $m->sData(array('v' => 1), 'sn2');   //创建编号
                }
            }
            $goodsSn_arr = $m->getall("select id from " . SQLPRE . "sn2 where `use`=0 order by id limit $len");
            foreach ($goodsSn_arr as $key=>$value) {
                $goodsSn[$key] = dechex($value['id']);
            }
            return $goodsSn;
        }
    }elseif($type='change'){
        $id = hexdec($len);
        $res = $m->sData(array('use'=>1), 'sn2', 'u', "id=$id"); //保存编号使用状态
        return $res;
    }
}

/**
 * 如果是商户登录返回的商户查询的where 条件
 * @return type
 */
function is_business(){
        $role = @$_SESSION['user_role'];
        if($role=='business'){
            $business_no = @$_SESSION['business_no'];
            return $business_no;
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
