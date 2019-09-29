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

$sql1 = "delete from `" . SQLPRE . "orders` where `payStatus` = 1 and createTime < now()-INTERVAL  120 minute";
$m->query($sql1);
$sql2 = "delete from `" . SQLPRE . "order_goods` where `payStatus` = 1 and createTime < now()-INTERVAL 120 minute";
$m->query($sql2);

exit();
