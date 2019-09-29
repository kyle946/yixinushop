<?php

/*
 */

include './model.php';
defined('REDIS_PRE') or define('REDIS_PRE', 'y_');    //redis  前缀
defined('SQLPRE') or define('SQLPRE', 'y_');    //数据库  前缀
defined('REDIS_ADDR') or define('REDIS_ADDR', '127.0.0.1');    //redis  保存的时间
defined('REDIS_PORT') or define('REDIS_PORT', 6379);    //redis  保存的时间
defined('REDIS_TTL') or define('REDIS_TTL', 86400);    //redis  保存的时间

$m = new model();
$sql = "select * from `" . SQLPRE . "jingcheng` where `actionName`='updateCache'";
$info = $m->getrow($sql);
$redis = new redis();
$redis->connect(REDIS_ADDR, REDIS_PORT);
//$redis->auth('password');
//
//更新商品缓存
$sql = "select g.id as id_,g.name as goodsname,g.status,g.catId,g.typeId,g.name2,g.goodsDesc,g.dateTime,g.attr, "
        . " ga.* , ga.shopPrice as sprice ,ac.id as aid, "
        . " if(ac.id is not null,ag.aprice,ga.shopPrice) as shopPrice,ac.starttime,ac.endtime,ag.xiangou "
        . " from " . SQLPRE . "goods as g "
        . "left join " . SQLPRE . "category as gc on g.catId=gc.id "
        . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId "
        . " left join `" . SQLPRE . "activity_goods` ag on ga.id=ag.goodsid "
        . " left join " . SQLPRE . "activity ac on ag.aid=ac.id and (ac.starttime<now() and now()<ac.endtime) ";
//                . "left join `$tablename` as a on ga.sn=a.sn ";
$list = $m->getall($sql);
foreach ($list as $key => $value) {
    $redis->setex(REDIS_PRE . 'goods_' . $value['id'], REDIS_TTL, json_encode($value));
    //更新库存和销量
    $redis->setex(REDIS_PRE . 'goods_salesval_' . $value['id'], REDIS_TTL, (int) $value['salesval']);  //销量
    $redis->setex(REDIS_PRE . 'goods_numbers_' . $value['id'], REDIS_TTL, (int) $value['numbers']);  // 库存 
}
//记录更新商品缓存的时间
$redis->setex(REDIS_PRE . 'goodsuptime', REDIS_TTL, time());

exit();
