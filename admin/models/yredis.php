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
        $this->connect(REDIS_ADDR,REDIS_PORT);
        //redis 服务器验证密码
        $this->auth('kyle');
    }
    
}

?>