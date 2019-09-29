<?php

/**
 * 权限和菜单编辑 ，开发专用
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */
namespace controllers;

class devController extends adminController {
    
    public function menu() {
        $action = new pController();
        $m = new \core\model();
            $sql = "select * from " . $m->prefix . "admin_mtag where parentId=0";
            $main = $m->_query($sql);
            $res['main'] = $main;
            foreach ($main as $key => $value) {
                $id = $value['id'];
                $k_ = 'menu' . $id;
                $sql = null;
                $sql = "select * from " . $m->prefix . "admin_mtag where parentId=$id";
                $row = $m->_query($sql);
                $res[$k_] = $row;
            }
            $this->assign($res);
        $this->display('dev/menulist');
    }
    
    public function edit_module() {
        $m = new \core\model();
        $id = @$_REQUEST['id'];
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            $data['name'] = $this->_post('name');
            $data['parentId'] = $this->_post('parentId');
            if($id){
                $m->sData($data, 'admin_mtag','u',"id=$id");
            }else{
                $m->sData($data, 'admin_mtag');
            }
            echo 1; exit();
        }else{
            $sql = "select * from ".SQLPRE."admin_mtag where parentId=0";
            $tplvar['mtaglist'] = $m->getall($sql);
            if($id){
                $tplvar['info'] = $m->getrow("select * from ".SQLPRE."admin_mtag where id=$id");
            }
            $this->assign($tplvar);
            $this->display('dev/add_module');
        }
    }
    
    /**
     * 删除模块
     */
    public function delete_module() {
        $m = new \core\model();
        if( $_SERVER['REQUEST_METHOD']=='POST' ){
            //暂时先用粗暴的删除方法 ，后面再加上删除菜单和操作的判断 
            $id = $_POST['id'];
            $m->sDelete('admin_mtag', "id=$id");
            echo 1;  exit();
        }
    }
    
    /**
     * 弹窗的  关联菜单 
     */
    public function context_menu() {
        $m = new \core\model();
        $mtagid = $this->_get('mtagid');
        if($mtagid){
                $sql = "select m.*,t.name as mtagname from ".SQLPRE."admin_menu m "
                        . " left join ".SQLPRE."admin_mtag t on m.mtagId =t.id "
                        . " where m.mtagId=$mtagid";
                $tplvar['list'] = $m->getall($sql);
        }else{
                $row = $m->getall("select * from ".$m->prefix."admin_menu");
                $action = new pController();
                $row_ = $action->RecursionData(0,$row);
                $data=null; $data = array();
                $action->printData($row_,'',$data);
                $tplvar['list'] = $data;
        }
        $this->assign($tplvar);
        $this->display('dev/context_menu');
    }
    
    /**
     * 弹窗的  添加／修改 菜单 
     */
    public function editmenu() {
                $id = intval(@$_REQUEST['id']);
                $m = new \core\model();
                if(isset($_POST['_submit_'])){
                        $data['url'] = $this->_post("url");
                        $data['name'] = $this->_post("name");
                        $data['sort'] = intval($this->_post('sort')) != 0 ? intval($this->_post('sort')) : 150;
                        $data['g'] = intval($this->_post('g'));
                        $data['parentId'] = intval($this->_post('parentId'));
                        $data['mtagId'] = intval($this->_post('mtagId'));
                        
                        if($id){
                            $result = $m->sData($data, 'admin_menu','u',"id=$id");
                        }else{
                            $result = $m->sData($data, 'admin_menu');
                        }
                         if($result){
                             header("Location: ?c=dev&a=context_menu&mtagid=$data[mtagId]");
                             exit();
                         }
                    }else{
                            $action = new pController();
                            $sql = "select m.*,t.name as mtag from ".$m->prefix."admin_menu as m left join ".$m->prefix."admin_mtag as t on m.mtagId=t.id";
                            $row = $m->getall($sql);
                            $row_ = $action->RecursionData(0,$row);
                            $data = array();
                            $action->printData($row_,'',$data);
                            $this->assign('menulist', $data);

                            $row=null;
                            $row = $m->getall("select * from ".$m->prefix."admin_mtag");
                            $row_ = $action->RecursionData(0,$row);
                            $data=null; $data = array();
                            $action->printData($row_,'',$data);
                            $this->assign('mtaglist', $data);

                            $info = $m->getrow("select * from ".$m->prefix."admin_menu where id=$id");
                            $this->assign('info', $info);
                            $this->display('dev/editmenu');
                    }
    }
    
    /**
     * 弹窗的  删除 菜单 
     */
    public function delmenu() {
        $id = intval($_REQUEST['id']);
        $mtagid = $_REQUEST['mtagid'];
        $m = new \core\model();
        $sql = "delete from ".$m->prefix."admin_menu where id=$id";
        $result = $m->query($sql);
        if($result){
            header("Location: ?c=dev&a=context_menu&mtagid=$mtagid"); exit();
        }
    }
    
    /**
     * 弹窗的  关联菜单 
     */
    public function module_operation() {
        $m = new \core\model();
        $mtagid = $this->_get('mtagid');
        if($mtagid){
                $sql = "select m.*,t.name as mtagname from ".SQLPRE."admin_action m "
                        . " left join ".SQLPRE."admin_mtag t on m.mtagId =t.id "
                        . " where m.mtagId=$mtagid";
                $tplvar['list'] = $m->getall($sql);
        }else{
                $sql = "select m.*,t.name as mtagname from ".SQLPRE."admin_action m "
                        . " left join ".SQLPRE."admin_mtag t on m.mtagId =t.id ";
                $tplvar['list'] = $m->getall($sql);
        }
        $this->assign($tplvar);
        $this->display('dev/module_operation');
    }
    
    /**
     * 弹窗的  添加／修改 操作
     */
    public function editaction() {
                $id = intval(@$_REQUEST['id']);
                $m = new \core\model();
                if(isset($_POST['_submit_'])){
                        $data['colltroller'] = $this->_post("colltroller");
                        $data['action'] = $this->_post("action"); //comment
                        $data['comment'] = $this->_post("comment"); //comment
                        $data['mtagId'] = $_POST['mtagId'];
                        if($id){
                            $result = $m->sData($data, 'admin_action','u',"id=$id");
                        }else{
                            $result = $m->sData($data, 'admin_action');
                        }
                        header("Location: ?c=dev&a=module_operation&mtagid=$data[mtagId]");
                        exit();
                    }else{
                            $action = new pController();
                            $row=null;
                            $row = $m->getall("select * from ".$m->prefix."admin_mtag");
                            $row_ = $action->RecursionData(0,$row);
                            $data=null; $data = array();
                            $action->printData($row_,'',$data);
                            $this->assign('mtaglist', $data);
                            
                            $info = $m->getrow("select * from ".$m->prefix."admin_action where id=$id");
                            $this->assign('info', $info);
                            $this->display('dev/editaction');
                    }
    }
    
    /**
     * 弹窗的  删除 操作
     */
    public function delaction() {
        $id = intval($_REQUEST['id']);
        $mtagid = $_REQUEST['mtagid'];
        $m = new \core\model();
        $sql = "delete from ".$m->prefix."admin_action where id=$id";
        $result = $m->query($sql);
        if($result){
            header("Location: ?c=dev&a=module_operation&mtagid=$mtagid"); exit();
        }
    }
    
    /**
     * 权限
     */
    public function Permission() {
        $action = new pController();
        $m = new \core\model();
        $row = $m->_query("select * from ".$m->prefix."admin_mtag");
        $row_ = $action->RecursionData(0,$row);
        $data = array();
        $action->printData($row_,'',$data);
        $this->assign('list', $data);
        $this->display('dev/Permissionlist');
    }
    
    public function addPermission() {
        $m = new \core\model();
        if(isset($_POST['_submit_'])){
            $data['name'] = "'".$this->_post("name")."'";
            $data['parentId'] = intval($this->_post('parentId'));
            
            $field = null; $val=null;
             foreach ($data as $key => $value) {
                    $field.="$key,";  $val.="$value,";
             }
             $field = substr($field, 0,-1);
             $val = substr($val, 0,-1);
             $sql = "insert into ".$m->prefix."admin_mtag ($field) values ($val)";
             $result = $m->query($sql);
             if($result){
                 header("Location: ?system=devMenu&type=Permission");
                 exit();
             }
        }else{
                $row = $m->getall("select * from ".$m->prefix."admin_mtag where parentId=0");
                $this->assign('mtaglist', $row);
                $this->display('dev/addPermission');
        }
    }
    
    public function editPermission() {
        $id = intval($_REQUEST['id']);
        $m = new \core\model();
        if(isset($_POST['_submit_'])){
            $data['name'] = "'".$this->_post("name")."'";
            $data['parentId'] = intval($this->_post('parentId'));
            
            $val=null;
             foreach ($data as $key => $value) {
                    $val.="$key=$value,";
             }
             $val = substr($val, 0,-1);
             $sql = "update ".$m->prefix."admin_mtag set $val where id=$id";
             $result = $m->query($sql);
             if($result){
                 header("Location: ?system=devMenu&type=Permission");
                 exit();
             }
        }else{
                $info = $m->getrow("select * from ".$m->prefix."admin_mtag where id=$id");
                $this->assign('info', $info);
                            
                $row = $m->getall("select * from ".$m->prefix."admin_mtag where parentId=0");
                $this->assign('mtaglist', $row);
                $this->display('dev/addPermission');
        }
    }
    
    public function delPermission() {
        $id = intval($_REQUEST['id']);
        $m = new \core\model();
        $sql = "delete from ".$m->prefix."admin_mtag where id=$id";
        $result = $m->query($sql);
        if($result){
             header("Location: ?system=devMenu&type=Permission");
             exit();
        }
    }
    
    public function modelaction() {
        $m = new \core\model();
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 12;
        $start = $p*$listrows-$listrows;
        
        $data = $m->_query("select m.*,t.name as mtag from ".$m->prefix."admin_action as m left join ".$m->prefix."admin_mtag as t on m.mtagId=t.id order by m.colltroller  limit $start,$listrows");
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."admin_action");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        
        $this->assign('list', $data);
        $this->display('dev/modelaction');
    }
    
    public function addaction() {
        
        $m = new \core\model();
        $id = isset($_GET['id']) ? $_GET['id']:null;
        if(isset($_POST['_submit_'])){
            $data['colltroller'] = $this->_post("colltroller");
            $data['action'] = $this->_post("action");
            $data['mtagId'] = intval($this->_post('mtagId'));
            $result = null;
            if($id){
                $result = $m->sData($data, 'admin_action','u',"id=$id");
            }else{
                $result = $m->sData($data, 'admin_action');
            }
             if($result){
                 header("Location: ?system=devMenu&type=modelaction");
                 exit();
             }
             
        }else{
            $row=null;
            $action = new pController();
            $row = $m->getall("select * from ".$m->prefix."admin_mtag");
            $row_ = $action->RecursionData(0,$row);
            $data=null; $data = array();
            $action->printData($row_,'',$data);
            $this->assign('mtaglist', $data);
            if( $id ){
                $info = $m->getrow("select * from `".SQLPRE."admin_action` where id=$id");
                $this->assign('info', $info);
            }
            $this->display('dev/addaction');
        }
        
    }
    
    public function exemption() { }
    
}
