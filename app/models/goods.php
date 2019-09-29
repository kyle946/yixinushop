<?php

/**
 * Description of goods
 * 商品模型
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace models;

class goods extends \core\model {
    
    public function __construct() {
        parent::__construct();
        $redis = new yredis();
        $goodsuptime = $redis->get(REDIS_PRE.'goodsuptime');
        if(!$goodsuptime){
            up_goods_cache();
//            $this->upgoodscache();
        }
    }

    /**
     * 
     * @param type $thisin  价格最小值
     * @param type $thisax  价格最大值
     * @param type $level  分为level 个 级别
     */
    public function pricelevel($thisin = 1, $thisax = 10, $level = 4) {
        $z = ( $thisax - $thisin + 1 ) / $level;
        $data = null;
        for ($i = 1; $i <= $level; $i++) {
            $data[] = intval($thisin * $i) . '-' . intval($z * $i);
        }
        return $data;
    }

    /**
     * 商品分类树位置
     * @param type $id
     */
    public function goodsCatetroyPos($id) {
        $data = null;
        $info1 = null;
        $info2 = null;
        $info3 = null;
        $info1 = $this->getrow("select id,name,parentId,level from " . $this->prefix . "category where id=$id");
//        if( $info1['parentId']!=0 ){
        $data[$info1['level']] = $info1;
        $data[$info1['level']]['list'] = $this->getall("select id,name,parentId,level from " . $this->prefix . "category where parentId=$info1[parentId]");
        $info2 = $this->getrow("select id,name,parentId,level from " . $this->prefix . "category where id=$info1[parentId]");
//        }

        if (isset($info2['parentId']) and $info2['parentId'] !== NULL) {
            $data[$info2['level']] = $info2;
            $data[$info2['level']]['list'] = $this->getall("select id,name,parentId,level from " . $this->prefix . "category where parentId=$info2[parentId]");
            $info3 = $this->getrow("select id,name,parentId,level from " . $this->prefix . "category where id=$info2[parentId]");
        }

        if (isset($info3['parentId']) and $info3['parentId'] !== NULL) {
            $data[$info3['level']] = $info3;
            $data[$info3['level']]['list'] = $this->getall("select id,name,parentId,level from " . $this->prefix . "category where parentId=$info2[parentId]");
        }
        sort($data);
        return $data;
    }

    /**
     * 把规格数据加上ID，让商品详情页每 一个规格都对应一个附加表的ID，点开后成为一个独立的商品。
     * @param type $list
     * @param type $attribute
     */
    
    public function setspec($list = null, $attribute = null, $goodsid) {
        //如果两个数据都 不为空。
        if ( $list ) { 
            //开始组合
            $data = null;
            $arr11 = $this->fun1($attribute, $list);
            foreach ($arr11 as $k1=>$v1) {
                $str2 = trim($v1['str'],',');
                //如果商品不存在在就不返回对应规格的商品 (这里主要是判断商品是否下架)
                $id = $this->getone("select id from " . $this->prefix . "goods_additional where attributeStr='$str2' and goodsId=$goodsid and addStatus=1");
                if ($id){
                    $data[$id] = $v1['val'];
                }
            }
            return $data;
        }
        return false;
    }

    /**
     * 商品分类列表 
     * 
     * @param type $cate   商品分类ID
     * @param type $p  当前是第几页  
     * @param type $nums  一页显示多少条数据
     * @param array $filter 数组 ，商品列表页的搜索条件 
     * @param float $price 商品列表页的价格搜索条件
     * @param string $sort 商品列表页的排序  , priceS,priceJ,rJ,rS
     * 
     * @return array $data['type']  返回的数据类型，cate 为分类页数据，list 为 列表页数据
     * @return array $data['cateList']  分类页用：分类列表 
     * @return array $data['info']  通用：当前分类详情 
     * @return array $data['priceSection']  列表页专用：价格区间
     * @return array $data['attrurl']  列表页专用：搜索条件 的url数组
     * @return array $data['attrD']  列表页专用：分类属性
     * @return array $data['list']  列表页专用：商品列表
     * @return array $data['count']  列表页专用：总共有多少条数据
     */
    public function goodslist($cate, $p = 1, $nums = 16, $filter = null, $price = null, $sort = null) {
        if (empty($cate)):
            return false;
        endif;
        $userinfo = loginJudbe();
        $userid = $userinfo?$userinfo['id']:0;

        $returndata = array();
        $returndata = $this->goodscateinfo($cate);
        $redis = new yredis();
        //判断是分类 首页还是 列表页
        if ($returndata['info']['level'] < $GLOBALS['config']['catlevel']) {
            $returndata['type'] = 'cate';//分类页面
            
            //读取分类树
            if ($GLOBALS['config']['catlevel'] == 3) {
                $cateList = $this->goodscatecache($cate);
            } else {
                $cateList = $this->goodscatecache();
            }
            $returndata['cateList'] = $cateList;

            $sql = "select id,name from `".SQLPRE."category` where parentId=$cate order by sort";
            $cateT1 = $this->getall($sql);
            $catelistT1 = array();
            foreach ($cateT1 as $k => $cateT2) {
                $arr = array();
                $sql = "select ga.id as id from `".SQLPRE."goods` g,`".SQLPRE."goods_additional` ga "
                        . "where g.id=ga.goodsId and ga.addStatus=1 and catId = $cateT2[id] order by g.sort,ga.id desc limit 4";
                $arr['goodsData'] = $this->getall($sql);
                if( !is_array($arr['goodsData']) || count($arr['goodsData'])<=0 ){
                    unset($cateT1[$k]);
                    continue;
                }
                $arr['name'] = $cateT2['name'];
                $arr['cateid'] = $cateT2['id'];
                foreach ($arr['goodsData'] as $key => $value) {
                    $value = json_decode($redis->get(REDIS_PRE.'goods_'.$value['id']) ,1);
                    //如果没有在缓存中找到商品，直接跳过并更新缓存 
                    if( !$value ){
                        unset($arr['goodsData'][$key]);
                        up_goods_cache();
                        continue;
                    }
                    //从缓存读取库存，保存准确性
                    $value['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$value['id'])?:0; 
                    //取用户的购买记录， 用作限购判断 
                    $value['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$value['id'])?:0;
                    
                    $arr['goodsData'][$key] = $value;
                }
                $catelistT1[$k] = $arr;
            }
            $returndata['info']['list'] = $catelistT1;
            

        } else {
            $returndata['type'] = 'list';//列表页面
            $cateInfo = $returndata['info'] ;
            //品牌
            
            //url 的属性参数
            $attrurl = null;
            $where_ = null;
            if (!empty($filter) && is_array($filter) && count($filter) > 0):
                //属性
                foreach ($returndata['attrD'] as $key => $value) {
                    if (array_key_exists($key, $filter)) {
                        $attrurl[$key] = $filter[$key];
                        //属性条件 
                        $where_ .= " and a.$key='" . $attrurl[$key] . "' ";
                    }
                }
            endif;
            $tablename = $this->prefix . 'goods_add' . $returndata['tablemark'];

            //把价格属性加到where 条件 里面
            if ($price) {
                $attrurl['price'] = $price;
                list($pMin, $pMax) = explode('-', $price);
                $where_ .= $where_ . " and ( ga.shopPrice between $pMin and $pMax ) ";
                $returndata['priceMin'] = $pMin;
                $returndata['priceMax'] = $pMax;
            }

            //排序
            $orderby = " order by ga.sn asc ";
            if ($sort) {
                $attrurl['sort'] = $sort;
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
            $returndata['attrurl'] = $attrurl;

            $sql = "select ga.id as id from ".SQLPRE."goods g ,".SQLPRE.'goods_additional ga,'.$tablename.' a where '
                    . ' ga.goodsId=g.id and ga.addStatus=1 and ga.id=a.aid and g.typeId='.$cateInfo['typeId'].$where_.' '.$orderby;
            $datalist = $this->getall($sql);
            $redis->delete(REDIS_PRE.'goods_catesort_'.$cate);
            foreach ($datalist as $key => $value) {
                $redis->rPush(REDIS_PRE.'goods_catesort_'.$cate, $value['id']);  
                //必须要用 rpush才能取出原始的排序，如果用 sadd ，redis会自动进行从小到大的排序
            }
            $returndata['count'] = count($datalist);
            //page 数据
            $p = !empty($p) ? $p : 1;
            $start = $p * $nums - $nums;
            // nokey 是一个不存在的 key ，加入这个是为了保证排序不发生变化 
            $list_ = $redis->sort(REDIS_PRE.'goods_catesort_'.$cate, array('by'=>'nokey','get'=>array(REDIS_PRE.'goods_*'),'limit'=>array($start,$nums)) );
            $list = array();
            foreach ($list_ as $key => $value) {
                $tmp = json_decode($value,1);
                //如果没有在缓存中找到商品，直接跳过并更新缓存 
                if( !$tmp ){
                    unset($list_[$key]);
                    $redis->upcache();
                    continue;
                }
                //从缓存读取库存，保存准确性
                $tmp['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$tmp['id']);  
                //取用户的购买记录， 用作限购判断 
                $tmp['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$tmp['id'])?:0;
                $list[$tmp['id']] = $tmp;
            }
            $returndata['list'] = $list;
//            var_dump($list);
        }
        return $returndata;
    }
    
    
//==============================商品缓存相关==============================
    
    /**
     * 更新商品缓存 
     */
    public function upgoodscache() {
        $redis = new \models\yredis();
        $model = new \core\model();
        $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
                . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
                . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou "
                . " ga.* from " . SQLPRE . "goods as g "
                . "left join " . SQLPRE . "category as gc on g.catId=gc.id "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
                . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
                . " left join " . SQLPRE . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
//                . "left join `$tablename` as a on ga.sn=a.sn ";
        $list = $model->getall($sql);
        foreach ($list as $key => $value) {
            $redis->setex(REDIS_PRE.'goods_'.$value['id'], REDIS_TTL, json_encode($value) );
            
            //更新总库存和销量
            $redis->setex(REDIS_PRE.'goods_salesval_'.$value['id'],86400*10 , (int)$value['salesval']);  //销量
            $redis->setex(REDIS_PRE.'goods_numbers_'.$value['id'] , 86400*10 , (int)$value['numbers']);  // 库存 
        }
        //记录更新商品缓存的时间
        $redis->setex(REDIS_PRE.'goodsuptime', REDIS_TTL, time() );
    }

    /**
     * 单个商品分类缓存
     * 
     * @param type $cateid
     * @param type $GET
     * @return type
     */
    public function goodscateinfo( $cateid = null ) {
        $returndata = array();
        $redis  = new yredis();
        $cachekeyname = REDIS_PRE.'goodscate_info_'.$cateid;
        $tmpdata = $redis->get($cachekeyname);
        if($tmpdata){
            $tmpdata_ = json_decode($tmpdata,1);
            if(is_array($tmpdata_) ){
                $returndata = $tmpdata_;
                return $returndata;
            }
        }
        
        $returndata['cateid'] = $cateid;  //分类 ID
        $cateInfo = $this->getrow("select * from " . $this->prefix . "category where id=$cateid");
        //价格区间
        $priceMax = $this->getone("select max(b.shopPrice) from " . $this->prefix . "goods a left join `" . $this->prefix . "goods_additional` b on a.id=b.goodsId where a.typeId=$cateInfo[typeId]");
        $priceMin = $this->getone("select min(b.shopPrice) from " . $this->prefix . "goods a left join `" . $this->prefix . "goods_additional` b on a.id=b.goodsId where a.typeId=$cateInfo[typeId]");
        //价格区间
        $returndata['priceSection'] = array("$priceMin-$priceMax");
        $returndata['info'] = $cateInfo;

        //属性
        $attrD = $this->getall("select id,name,mark as yixinu from " . $this->prefix . "goods_attr where typeId=$cateInfo[typeId] and mainpar=1");
        //根据typeId获取表名
        $table_mark = $this->getone("select mark from " . $this->prefix . "goods_type where id=$cateInfo[typeId]");
        $returndata['tablemark'] = $table_mark;
        $tablename = $this->prefix . 'goods_add' . $table_mark;
        foreach ($attrD as $key => $value) {
            $result_ = $this->getall("select distinct $key from " . $this->prefix . "goods a left join `$tablename` b on a.id=b.goodsid where a.typeId=$cateInfo[typeId] and `$key` is not null");
            $t2 = null;
            foreach ($result_ as $k => $v) {
                if($v[$key]) $t2[] = $v[$key];
            }
            $attrD[$key]['list'] = $t2;
        }

        $returndata['attrD'] = $attrD;
        $redis->setex($cachekeyname, 120, json_encode($returndata) );
        return $returndata;
    }
    
    /**
     * 分类 树缓存 
     * @param type $cate
     */
    public function goodscatecache($cate = 0) {
        $redis = new yredis();
        $cachename = REDIS_PRE.'goodscate_'.$cate;
        $catecache = $redis->get($cachename);
        if($catecache){
            return json_decode($catecache,1);
        }else{
            //读取分类
            $row = $this->getall("select a.id,a.name,a.parentId,b.name as typeName from ".SQLPRE. "category a left join ".SQLPRE."goods_type b on a.typeId=b.id");
            $cateList = null;
            $cateList = RecursionData(0, $row);
            $redis->setex($cachename, 120, json_encode($cateList) );
            return $cateList;
        }
    }
    
    

    /**
     * 更新一个商品的缓存 ，并删除与之相关的商品分类缓存
     * 
     * @param type $goodsid  商品主表的ID
     */
//    public function uponegoodscache($goodsid = null) {
//        if ($goodsid) {
//            $redis = new \models\yredis();
//            $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
//                    . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
//                    . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou "
//                    . " from " . SQLPRE . "goods as g "
//                    . "left join " . SQLPRE . "category as gc on g.catId=gc.id "
//                    . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
//                    . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
//                    . " left join " . SQLPRE . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) "
//                    . " where ga.id=$goodsid ";
//            $res = $this->getrow($sql);
//            $redis->setex(REDIS_PRE . 'goods_' . $res['id'], REDIS_TTL, json_encode($res));
//            
//            //更新库存和销量
//            $redis->setex(REDIS_PRE.'goods_salesval_'.$goodsid,86400*10 , (int)$res['salesval']);  //销量
//            $redis->setex(REDIS_PRE.'goods_numbers_'.$goodsid , 86400*10 , (int)$res['numbers']);  // 库存 
//            
//            //删除所有商品分类的缓存
//            $arr = $redis->keys(REDIS_PRE . 'goodscate_info*');
//            foreach ($arr as $v) {
//                $redis->del($v);
//            }
//        }
//    }
    
    
    
    /**
     * 
     * @param array $arr   ,example : $arr1 = array( '阿里1G1年','PC端','阿里1千条' );
     * @param array $arr2  , example : $arr2 = array('微商城','PC端');
     * 
     * @return array  , example : Array( [0] => 阿里1G1年,微商城,阿里1千条,  [1] => 阿里1G1年,PC端,阿里1千条, )
     */
    public function fun1($arr, $arr2) {
        $str = '';
        foreach ($arr as $k => $v1) {
            $bool = null;
            foreach ($arr2 as $v2) {
                if ($v1 == $v2) {
                    $bool = 1;
                    $str .='###,';
                    continue;
                }
            }
            if ($bool) {
                continue;
            }
            $str .=$v1 . ',';
        }

        $data = array();
        foreach ($arr2 as $v) {
            $s =  str_replace('###',$v,$str);
            $data[] = array('str'=>$s,'val'=>$v);
        }
        return $data;
    }

}
