<?php

/**
 * Description of goods
 * redis 扩展
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace models;

class yredis extends \Redis {

    public function __construct() {
        parent::__construct();
        //连接  redis  服务器
        $this->connect(REDIS_ADDR, REDIS_PORT);
        //redis 服务器验证密码
        $this->auth('kyle');

        //begin 更新商品缓存
        $uptime = $this->get(REDIS_PRE . 'goodsuptime');
        if (!$uptime) {
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
                $this->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));

                //更新库存和销量
                $this->setex(REDIS_PRE . 'goods_salesval_' . $value['id'], 86400 * 10, (int) $value['salesval']);  //销量
                $this->setex(REDIS_PRE . 'goods_numbers_' . $value['id'], 86400 * 10, (int) $value['numbers']);  // 库存 
            }


            //删除所有商品分类的缓存
            $arr = $this->keys(REDIS_PRE . 'goodscate_info*');
            foreach ($arr as $v) {
                $this->del($v);
            }

            //记录更新商品缓存的时间
            $this->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());
        }
        //end
    }

}

?>