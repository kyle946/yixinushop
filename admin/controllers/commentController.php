<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of commentController
 *
 * @author Administrator
 */
namespace controllers;
class commentController extends adminController {
    
    /**
     * 商品评论列表
     */
    public function lists() {
        $m = new \core\model();
        $where = null;
        if($this->_get('s')){
            $where = " where gc.status = ".intval($this->_get('s'));
        }
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select gc.*,u.username,g.name as goodsname from `".$m->prefix."goods_comment` gc "
                . " left join ".$m->prefix."goods g on gc.goodsid=g.id "
                . " left join ".$m->prefix."users u on gc.userid=u.id $where order by id desc limit $start,$listrows";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(gc.id) as count from `".$m->prefix."goods_comment` gc $where ");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign('list', $list);
        $this->display('comment/lists');
    }
    
    /**
     * 审核通过
     */
    public function approve() {
        $m = new \core\model();
        $status = $this->_get('status') ? $this->_get('status') : 2;
        if( is_array($_REQUEST['goodscommentlist']) and !empty($_REQUEST['goodscommentlist']) ){
            foreach ($_REQUEST['goodscommentlist'] as $key => $value) {
                $data['status'] =  $status;
                $m->sData($data, 'goods_comment', 'u', "id=$value");
            }
            echo json_encode(array('status'=>1));
            exit();
        }
    }
     
    /**
     * 删除评论
     */
    public function isdelete() {
        $m = new \core\model();
        if( is_array($_REQUEST['goodscommentlist']) and !empty($_REQUEST['goodscommentlist']) ){
            foreach ($_REQUEST['goodscommentlist'] as $key => $value) {
                $m->sDelete('goods_comment', "id=$value");
            }
            echo json_encode(array('status'=>1));
            exit();
        }
    }
    
    public function exemption() {
        
    }
    
}
