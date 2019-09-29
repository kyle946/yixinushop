<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of businessController
 *
 * @author kyle
 */

namespace controllers;
class businessController extends adminController {
    
    /**
     * 商户列表 
     */
    public function business_list() {
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $where = null;
        $search_str = $this->_get('search_str');
        if( $search_str ){
            $where = " where business_no like '%$search_str%' or business_name like '%$search_str%' ";
        }
        if( isset($_GET['type']) && $_GET['type']==2 ){
            $where .= $where ? " and (business_status&2 or business_status&4 or business_status&8)" : " where business_status&2 or business_status&4 or business_status&8";
        }
        
        $m = new \core\model();
        $list = $m->getall("select * from ".$m->prefix."business $where order by business_no limit $start,$listrows");
        
        //page 数据
        $count = $m->getone("select count(business_no) as count from ".$m->prefix."business $where");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign("list",$list);
        $this->display('business/business_list');
    }
    
    /**
     * 商户编辑
     */
    public function edit() {
        $business_no = $this->_get("no");
        $m = new \core\model();
        
        //begin 查看下一个
        $tplvar = array();
        $sql2 = "select business_no from ".SQLPRE."business where (business_no>$business_no) order by business_no asc ";
        $tplvar['next_one'] = $m->getone($sql2);
        $tplvar['next_one'] = $tplvar['next_one'] ? : $business_no;
            
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            $data = $_POST;
            unset($data['_submit_']);
            unset($data['business_pwd2']);
            unset($data['next']);
            $data['business_status'] = 0;
            if( isset($data['business_pwd']) && !empty($data['business_pwd'])){
                $data['business_pwd'] = md5($data['business_pwd']);
            }else{
                unset($data['business_pwd']);
            }
            
            foreach ($_POST['business_status'] as $value) {
                $data['business_status'] = $data['business_status'] | $value;
            }
            
            if($business_no){
                $data['createTime'] = date('Y-m-d H:i:s');
                $data['business_no_hex'] = dechex($data['business_no']);
                $m->sData($data, 'business','u', "business_no=$business_no");
            }else{
                $data['createTime'] = date('Y-m-d H:i:s');
                $data['business_no_hex'] = dechex($data['business_no']);
                $business_no = $m->sData($data, 'business');
            }
            
            
            //判断要不要关闭管理员账号
            if( $data['business_status']&2 or $data['business_status']&4 or $data['business_status']&8 ){
                $m->sData(array('status'=>0), 'admin_user','u',"business_no=$business_no");
            }elseif($data['business_status']&1){
                //begin 同步管理员账号
                $admin_user = $m->getrow("select business_no,business_mobile as name,business_pwd as passwd from ".SQLPRE."business where business_no=$business_no");
                $admin_user['roleId'] = 8;// 8是y_admin_role表的ID，表未角色是商户
                $admin_user['status']=1;
                //查询管理员账号是不是已经添加过了
                $tmpvar2 = $m->getrow("select business_no,name from ".SQLPRE."admin_user where name=$admin_user[name]");
                if(!$tmpvar2){
                    $m->sData($admin_user, 'admin_user');
                }
                //end
                $m->sData(array('status'=>1), 'admin_user','u',"business_no=$business_no");
            }
            
            //如果点击的是通过审核并看下一个
            if( isset($_POST['next']) && $_POST['next']==1 ){
                header("Location: ?business=edit&no=".$tplvar['next_one']);
                exit();
            }
            
            //加入翻页参数是为了修改完成之后不跳转到第一页。
            $p_ = null;
            if ($this->_get('p')) {
                $p_ = '&p=' . $this->_get('p');
            }
            header("Location: ?business=list$p_");
            
        }else{
            $sql = "select * from ".SQLPRE."business  where business_no=$business_no";
            $tplvar['info'] = $m->getrow($sql);
            $this->assign($tplvar);
            $this->display('business/edit');
        }
    }
    
    /**
     * 收银员列表 
     */
    public function cashier_account() {
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $where = null;
        $search_str = $this->_get('search_str');
        $where = null;
        if( $search_str ){
            $where = " and u.username like '%$search_str%' or u.business_no like '%$search_str%' ";
        }
        if( isset($_GET['no']) ){
            $where.=" and u.business_no=$_GET[no]";
        }
        
        $m = new \core\model();
        $sql = "select u.*,b.business_name,s.name as store_name from ".SQLPRE."cashier_front_account u , ".SQLPRE."business b  , ".SQLPRE."stores s "
                . "  where u.business_no=b.business_no and u.stores_id=s.id $where order by u.id limit $start,$listrows";
        $list = $m->getall($sql);
        
        //page 数据
        if( $where )$where = ' where '.substr($where,4);
        $count = $m->getone("select count(id) as count from ".$m->prefix."cashier_front_account u $where");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign("list",$list);
        $this->display('business/cashier_account');
    }
    
    /**
     * 收银员编辑
     */
    public function cashier_edit() {
        $id = $this->_get('id');
        $m = new \core\model();
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            unset($_POST['_submit_']);
            unset($_POST['pwd2']);
            $data = $_POST;
            if( isset($data['pwd']) && !empty($data['pwd'])){
                $data['pwd'] = md5($data['pwd']);
            }else{
                unset($data['pwd']);
            }
//            var_dump($data); exit();
            if($id){
                $m->sData($data, 'cashier_front_account','u', "id=$id");
            }else{
                $m->sData($data, 'cashier_front_account');
            }
            
            //  加入翻页参数是为了修改完成之后不跳转到第一页。
            $p_ = null;
            if ($this->_get('p')) {
                $p_ = '&p=' . $this->_get('p');
            }
            header("Location: ?business=cashier$p_");
        }else{
            $sql = "select u.*,b.business_name,s.name as store_name from ".SQLPRE."cashier_front_account u , ".SQLPRE."business b  , ".SQLPRE."stores s "
                . "  where u.business_no=b.business_no and u.stores_id=s.id  and u.id=$id";
            $tplvar['info'] = $m->getrow($sql);
            $this->assign($tplvar);
            $this->display('business/cashier_edit');
        }
    }
    
    /**
     * 删除收银员
     */
    public function cashier_delete() {
        $id = $this->_get('id');
        $m = new \core\model();
        $m->sDelete("cashier_front_account", "id=$id");
        $p_ = null;
        if ($this->_get('p')) {
            $p_ = '&p=' . $this->_get('p');
        }
        header("Location: ?business=cashier$p_");
        exit();
    }
    
    /**
     * 收银台支付配置
     */
    public function payedit() {
        $business_no = $this->_get('no');
        $m = new \core\model();
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            
            $business_alipay_config['alipay_account'] = $_POST['alipay_account'];
            $business_alipay_config['alipay_key'] = $_POST['alipay_key'];
            $business_alipay_config['alipay_partner'] = $_POST['alipay_partner'];
            $business_alipay_config['alipay_private_key'] = $_POST['alipay_private_key'];
            $business_alipay_config['alipay_pay_method'] = $_POST['alipay_pay_method'];
            
            $business_weixinpay_config['weixin_appid'] = $_POST['weixin_appid'];
            $business_weixinpay_config['weixin_mchid'] = $_POST['weixin_mchid'];
            $business_weixinpay_config['weixin_key'] = $_POST['weixin_key'];
            $business_weixinpay_config['weixin_secret'] = $_POST['weixin_secret'];
            
            $data['business_weixinpay_config'] = serialize($business_weixinpay_config);
            $data['business_alipay_config'] = serialize($business_alipay_config);
            
            $m->sData($data, 'business', 'u',"business_no=$business_no");
            $p_ = null;
            if ($this->_get('p')) {
                $p_ = '&p=' . $this->_get('p');
            }
            header("Location: ?business=list$p_");
            exit();
            
        }else{
            $sql = "select * from ".SQLPRE."business  where business_no=$business_no";
            $info = $m->getrow($sql);
            $business_alipay_config = unserialize($info['business_alipay_config']);
            $business_weixinpay_config = unserialize($info['business_weixinpay_config']);
            $tplvar['info'] = $info;
            $tplvar['a'] = $business_alipay_config;
            $tplvar['w'] = $business_weixinpay_config;
            $this->assign($tplvar);
            $this->display('business/payedit');
        }
    }
    
    public function exemption() {
        
    }
    
}
