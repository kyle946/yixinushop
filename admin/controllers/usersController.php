<?php

/**
 * Description of usersController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;
class usersController extends adminController {
    
    /**
     * 用户列表
     */
    public function userlist() {
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $where = null;
        $username = $this->_get('username');
        if( $username ){
            $where = " where u.username like '%$username%' or u.mobile like '%$username%' ";
        }
        
        $m = new \core\model();
        $list = $m->getall("select u.*,ur.name as rankname,ur.alias as rankalias from ".$m->prefix."users u left join `".$m->prefix."user_rank` ur on u.userRank=ur.id $where order by u.id limit $start,$listrows");
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."users");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign("list",$list);
        
        $this->display('user/userlist');
    }
    
    public function userinfo() {
        $id = $this->_get('id');
        $m = new \core\model();
        $sql = "select u.*,ur.name as rankname,ur.alias from ".$m->prefix."users u left join `".$m->prefix."user_rank` ur on u.userRank=ur.id where u.id=$id";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        $this->display('user/userinfo');
    }
    
    /**
     * 添加用户
     */
    public function add(){
        $m = new \core\model();
        if(isset($_POST['_submit_'])){
            $data = null;
            $data['username'] = $m->g($this->_post('username'));
            if($this->_get("id")){
                if( !empty($_POST['password']) ){
                    $data['password'] = md5($_POST['password']);
                }
            }else{
                $data['password'] = md5($_POST['password']);
            }
            $data['email'] = $m->g($this->_post('email'));
            $data['mobile'] = $m->g($this->_post('mobile'));
            $data['sex'] = $m->g($this->_post('sex'));
            $data['userRank'] = $m->g($this->_post('userRank'));
            $data['question'] = $m->g($this->_post('question'));
            $data['nickname'] = $m->g($this->_post('nickname'));
            $data['answer'] = $m->g($this->_post('answer'));
            $data['money'] = $m->g($this->_post('money'));
            $data['payPoints'] = $m->g($this->_post('payPoints'));
            $data['status'] = 1;
            if(array_search(intval($_POST['status']), array(0,1,2))){
                $data['status'] = intval($_POST['status']);
            }
            //判断 是更新 还是添加 
            if($this->_get("id")){
                $id = $this->_get("id");
                $val = null;
                foreach ($data as $key => $value) {
                    $val.="$key='$value',";
                }
                $val = substr($val, 0,-1);
                $sql = "update  ".$m->prefix."users set $val where id=$id";
                $result = $m->query($sql);
                header("Location: ?user=list");
                exit();
            }else{
                $field = null; $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    if(is_int($value) || is_float($value)){
                        $val.="$value,"; 
                    }else{
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0,-1);
                $val = substr($val, 0,-1);
                $sql = "insert into ".$m->prefix."users ($field) values ($val)";
                $row = $m->query($sql);
                if($row){
                    header("Location: ?user=list"); exit();
                }
            }
        }else{
            $ranklist = $m->getall("select id,name,discount from ".$m->prefix."user_rank where status=1");
            $this->assign("ranklist",$ranklist);
            $id = $this->_get("id");
            if($id){
                $result = $m->getrow("select * from ".$m->prefix."users where id=$id");
                $this->assign('info', $result);
            }
            $this->display("user/useradd");
        }
    }
    
    /**
     * 用户等级列表
     */
    public function ranklist() {
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $m = new \core\model();
        $list = $m->getall("select * from ".$m->prefix."user_rank order by id limit $start,$listrows");
        
        //page 数据
        $count = $m->getone("select count(id) as count from ".$m->prefix."user_rank");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign("list",$list);
        
        $this->display('ranklist');
    }
    
    /**
     * 用户等级编辑
     */
    public function rankadd() {
        $m = new \core\model();
        $id = $this->_get('id');
        if(isset($_POST['_submit_'])){
            
            $data = null;
            $data['name'] = $m->g($this->_post('name'));
            $data['alias'] = $m->g($this->_post('alias'));
            $data['points'] = $m->g($this->_post('points'));
            $data['discount'] = $m->g($this->_post('discount'));
            $data['status'] = intval($_POST['status']);
            
            $row = false;
            if($id){
                $row = $m->sData($data, 'user_rank','u',"id=$id");
            }else{
                $row = $m->sData($data, 'user_rank');
            }
            
            if($row){
                header("Location: ?user=rank"); exit();
            }
        }else{
            if($id){
                $sql = "select * from `".$m->prefix."user_rank` where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
            }
            $this->display('rankadd');
        }
    }
    
    /**
     * 等级删除
     */
    public function rankdel() {
        $id = $this->_get('id');
        $m = new \core\model();
        if($id){
            $sql = "delete from `".$m->prefix."user_rank` where id=$id";
            $result = $m->query($sql);
            if($result){
                header("Location: ?user=rank"); exit();
            }
        }
    }
    
    public function exemption() {
        
    }
    
}
