<?php

/**
 * Description of mobilecomController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;
class comController extends \core\Controller {
    
    public function __construct() {
        parent::__construct();
        
        $m = new \core\model();
        $result = s('webconfig');   //缓存是否存在
        if($result==false){
            $result_ = $m->getall('select * from '.$m->prefix."shop_config");
            foreach ($result_ as $key => $value) {
                $result[$value['mark']] = $value['val'];
            }
            $result = s('webconfig',$result);   //缓存
        }
        $GLOBALS['config'] = $result;
        $this->assign('webconfig', $result);
    }
    
}
