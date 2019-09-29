<?php
/**
 * Description of comController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */
namespace controllers;
class comController extends \core\Controller {
    
    public function __construct() {
        parent::__construct();
        
        $m = new \core\model();
        $result = s('webconfig');   //缓存是否存在
        if($result==false){
            $result_ = $m->getall('select * from '.$m->prefix."shop_config where g=1 or g=100");
            foreach ($result_ as $key => $value) {
                $result[$value['mark']] = $value['val'];
            }
            $result = s('webconfig',$result);   //缓存
        }
        $GLOBALS['config'] = $result;
        $this->assign('webconfig', $result);
        //如果有用户信息
        if( isset($_SESSION['userInfo']) and !empty($_SESSION['userInfo'])){
            $this->assign('userinfo', $_SESSION['userInfo']);
        }
    }
    
}
