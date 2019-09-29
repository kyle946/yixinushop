<?php

/**
 * Description of goods
 *
 * @author 青竹丹枫 <316686606@qq.com>
 */

namespace models;

class goods {

    /**
     * 添加商品时，同时创建活动商品价格数据  
     * @param type $goodsid   商品 goods 表的ID
     * @param type $act  i为插入，u为更新
     */
    public function createActivityData($goodsid, $act = 'i') {
        $m = new \core\model();
        $redis = new yredis();
        $sql = "select id,shopPrice,salesval,numbers from `" . $m->prefix . "goods_additional` where goodsId=$goodsid";
        $list = $m->getall($sql);
        foreach ($list as $key => $value) {
            $data['goodsid'] = $value['id'];
//            $data['aprice'] = $value['shopPrice'];
            if ($act == 'i') {
                $m->sData($data, 'activity_goods');
            } elseif ($act == 'u') {
                $m->sData($data, 'activity_goods', 'u', "goodsid=$value[id]");
            }
            //更新单个商品的缓存 
            $this->uponegoodscache($value['id']);
        }
    }

    /**
     * 更新商品缓存 
     */
    public function upgoodscache() {
        $redis = new \models\yredis();
        $model = new \core\model();
        $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
                . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
                . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou"
                . " from " . $model->prefix . "goods as g "
                . "left join " . $model->prefix . "category as gc on g.catId=gc.id "
                . "left join " . $model->prefix . "goods_additional as ga on g.id=ga.goodsId "
                . " left join `" . $model->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                . " left join " . $model->prefix . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
//                . "left join `$tablename` as a on ga.sn=a.sn ";
        $list = $model->getall($sql);
        foreach ($list as $key => $value) {
            $redis->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));
            
            //更新库存和销量
            $redis->setex(REDIS_PRE.'goods_salesval_'.$value['id'],86400*10 , (int)$value['salesval']);  //销量
            $redis->setex(REDIS_PRE.'goods_numbers_'.$value['id'] , 86400*10 , (int)$value['numbers']);  // 库存 
        }
        //记录更新商品缓存的时间
        $redis->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());
    }

    /**
     * 更新一个商品的缓存 ，并删除与之相关的商品分类缓存
     * 
     * @param type $goodsid  商品主表的ID
     */
    public function uponegoodscache($goodsid = null) {
        if ($goodsid) {
            $redis = new \models\yredis();
            $model = new \core\model();
            $sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
                    . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
                    . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou "
                    . " from " . $model->prefix . "goods as g "
                    . "left join " . $model->prefix . "category as gc on g.catId=gc.id "
                    . "left join " . $model->prefix . "goods_additional as ga on g.id=ga.goodsId "
                    . " left join `" . $model->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                    . " left join " . $model->prefix . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) "
                    . " where ga.id=$goodsid ";
            $res = $model->getrow($sql);
            $redis->setex(REDIS_PRE . 'goods_' . $res['id'], REDIS_TTL, json_encode($res));
            
            //更新库存和销量
            $redis->setex(REDIS_PRE.'goods_salesval_'.$goodsid,86400*10 , (int)$res['salesval']);  //销量
            $redis->setex(REDIS_PRE.'goods_numbers_'.$goodsid , 86400*10 , (int)$res['numbers']);  // 库存 
            
            //删除所有商品分类的缓存
            $arr = $redis->keys(REDIS_PRE . 'goodscate_info*');
            foreach ($arr as $v) {
                $redis->del($v);
            }
        }
    }

}
