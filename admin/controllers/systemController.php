<?php

/**
 * Description of systemController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;

class systemController extends adminController {

    /**
     * 管理员
     */
    public function adminuser() {
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;
        $m = new \core\model();
        $where2 = isset($_GET['no']) ? " or u.business_no='$_GET[no]'" : null;
        if( isset($_GET['no']) ){
            $where = "u.business_no='$_GET[no]'";
        }else{
            //网站创始人
            $sql = "select * from " . $m->prefix . "admin_user where roleId is null and comment='founder' ";
            $row = $m->getrow($sql);
            $this->assign('founder', $row);
            $where = "u.comment != 'founder' or u.comment is null";
        }
        //其它用户 
        $sql = "select u.*,r.name as roleName from " . $m->prefix . "admin_user as u left join " . $m->prefix . "admin_role as r on "
                . " u.roleId=r.id where $where order by id limit $start,$listrows";
        $row = $m->_query($sql);
        $count_ = $m->_query("select count(u.id) as count from " . $m->prefix . "admin_user u  where $where");
        $count = $count_[0]['count'];
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->assign('user_list', $row);
        $this->display('adminuser');
    }

    /**
     * 添加管理员
     */
    public function addAdminuser() {
        $m = new \core\model("admin_mtag");
        if (isset($_POST['_submit_'])) {
            if (empty($_POST['name'])):
                msg('名称不能为空！添加无效， 请<a href="?system=role" >返回</a>重试。');
            endif;
            if ($_POST['passwd'] != $_POST['passwd2']) {
                msg('两次密码输入不一致， 请<a href="?system=role" >返回</a>重试。');
            }
            $name = $m->escapeString($_POST['name']);
            $status = $_POST['status'];
            $passwd = md5($_POST['passwd']);
            $role = $_POST['role'];
            $sql = "insert into " . $m->prefix . "admin_user (name,passwd,roleId,status) values ('$name','$passwd',$role,$status)";
            $row = $m->query($sql);
            if ($row) {
                header("Location: ?system=adminuser");
                exit();
            }
        } else {
            $sql = "select * from " . $m->prefix . "admin_role where status=1 order by id desc";
            $row = $m->_query($sql);
            $res['role_list'] = $row;
            $this->assign($res);
            $this->display('addadminuser');
        }
    }

    /**
     * 修改管理 员
     */
    public function editAdminuser() {
        $id = $_REQUEST['id'];
        $res = array();
        if ($this->isadmin($id)):
            $res['admin']=1;
            //msg('网站创始人不能修改！ 请点击<a href="?system=adminuser" >返回</a>。');
        endif;

        $m = new \core\model();
        $sql = "select * from " . $m->prefix . "admin_user where id=$id";
        $info = $m->getrow($sql);
        $res['info'] = $info;
        if (isset($_POST['_submit_']) && !empty($id)) {
            if (!empty($_POST['passwd']) && $res['info']['passwd'] != md5($_POST['passwdOld'])):
                msg('旧密码不正确！ 请点击<a href="?system=adminuser" >返回</a>。');
            endif;
            if (!empty($_POST['passwd']) && $_POST['passwd'] != $_POST['passwd2']) {
                msg('两次密码输入不一致！ 请点击<a href="?system=adminuser" >返回</a>。');
            }
            
            if (!empty($_POST['name'])){
                $sdata['name'] = $this->_post("name");
            }
            $sdata['status'] = $this->_post("status");
            if (!empty($_POST['passwd'])){
                $sdata['passwd'] = md5( $this->_post("passwd") );
            }
            if (!empty($_POST['role'])){
                $sdata['roleId'] = $_POST['role'];
            }
            
            $row = $m->sdata($sdata,'admin_user','u',"id=$id");
            if ($row) {
                header("Location: ?system=adminuser");
                exit();
            }
        } else {
            $sql = "select * from " . $m->prefix . "admin_role where status=1 order by id desc";
            $row = $m->_query($sql);
            $res['role_list'] = $row;
            $this->assign($res);
            $this->display('addadminuser');
        }
    }

    /**
     * 删除管理员
     */
    public function deleeAdminuser() {
        $id = $_REQUEST['id'];
        if (!empty($id)) {
            if ($this->isadmin($id)):
                msg('网站创始人不能删除！ 请点击<a href="?system=adminuser" >返回</a>。');
            endif;
            $m = new \core\model();
            $sql = "delete from " . $m->prefix . "admin_user where id=$id";
            $row = $m->query($sql);
            if ($row):
                header("Location: ?system=adminuser");
                exit();
            endif;
        }else {
            msg('参数错误！ 请点击<a href="?system=adminuser" >返回</a>。');
        }
    }

    /**
     * 角色管理，角色列表
     */
    public function role() {
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;
        $m = new \core\model("admin_role");
        $sql = "select * from " . $m->prefix . "admin_role order by id desc limit $start,$listrows";
        $row = $m->_query($sql);
        $count_ = $m->_query("select count(id) as count from " . $m->prefix . "admin_role;");
        $count = $count_[0]['count'];
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->assign('data', $row);
        $this->display('role');
    }

    /**
     * 添加角色
     */
    public function addrole() {
        $m = new \core\model("admin_mtag");
        if (isset($_POST['_submit_'])) {
            if (empty($_POST['name'])):
                msg( '名称不能为空！角色添加无效。', '?system=role&type=add', '添加角色', 5);
            endif;
            if (empty($_POST['authorfie'])):
                msg( '没有指派权限！角色添加无效。', '?system=role&type=add', '添加角色', 5);
            endif;
            $name = $m->escapeString($_POST['name']);
            $status = $_POST['status'];
            $sql = "insert into " . $m->prefix . "admin_role (name,status) values ('$name',$status)";
            $row = $m->query($sql);
            if ($row) {
                //msg('', '角色添加成功。', '', '角色管理', 3);
                $id = mysqli_insert_id($m->link);
                foreach ($_POST['authorfie'] as $key => $value) {
                    $sql = "insert into " . $m->prefix . "admin_roleper (roleId,mtagId) values ($id,$value)";
                    $m->query($sql);
                }

                header("Location: ?system=role");
                exit();
            }
        } else {
            $main = $m->where("parentId=0")->select();
            $res['main'] = $main;
            foreach ($main as $key => $value) {
                $id = $value['id'];
                $k_ = 'menu' . $id;
                $row = $m->where("parentId=$id")->select();
                $res[$k_] = $row;
            }
            $this->assign($res);
            $this->display('addrole');
        }
    }

    /**
     * 角色编辑
     */
    public function editrole() {
        $roleid = $_REQUEST['id'];
        $m = new \core\model();
        if (isset($_POST['_submit_']) && !empty($roleid)) {
            if (empty($_POST['name'])):
                msg( '名称不能为空！角色添加无效。', '?system=role&type=add', '添加角色', 5);
            endif;
            if (empty($_POST['authorfie'])):
                msg( '没有指派权限！角色添加无效。', '?system=role&type=add', '添加角色', 5);
            endif;
            $name = $m->escapeString($_POST['name']);
            $status = $_POST['status'];
            $sql = "update " . $m->prefix . "admin_role set name='$name',status=$status where id=$roleid";
            $row = $m->query($sql);
            if ($row) {
                $sql = "delete from " . $m->prefix . "admin_roleper where roleId=$roleid";
                $m->query($sql);
                foreach ($_POST['authorfie'] as $key => $value) {
                    $sql = null;
                    $sql = "insert into " . $m->prefix . "admin_roleper (roleId,mtagId) values ($roleid,$value)";
                    $m->query($sql);
                }
                header("Location: ?system=role");
                exit();
            }
        } else {
            $sql = "select * from " . $m->prefix . "admin_role where id=$roleid";
            $info = $m->_query($sql);
            $res['info'] = $info[0];
            $sql = "select a.*,b.roleId from " . $m->prefix . "admin_mtag as a left join " . $m->prefix . "admin_roleper as b on a.id=b.mtagId and roleId=$roleid where parentId=0";
            $main = $m->_query($sql);
            $res['main'] = $main;
            foreach ($main as $key => $value) {
                $id = $value['id'];
                $k_ = 'menu' . $id;
                $sql = null;
                $sql = "select a.*,b.roleId from " . $m->prefix . "admin_mtag as a left join " . $m->prefix . "admin_roleper as b on a.id=b.mtagId and roleId=$roleid where parentId=$id";
                $row = $m->_query($sql);
                $res[$k_] = $row;
            }
            $this->assign($res);
            $this->display('addrole');
        }
    }

    /**
     * 删除角色
     */
    public function deleterole() {
        $id = $_REQUEST['id'];
        if (!empty($id)) {
            $m = new \core\model();
            $sql = "delete from " . $m->prefix . "admin_roleper where roleId=$id";
            $row = $m->query($sql);
            if ($row) {
                $row = null;
                $sql = "delete from " . $m->prefix . "admin_role where id=$id";
                $row = $m->query($sql);
            }
            if ($row):
                header("Location: ?system=role");
                exit();
            endif;
        }else {
            msg('参数错误， 请<a href="?system=role" >返回</a>重试。');
        }
    }

    protected function isadmin($id) {
        $m = new \core\model();
        $sql = "select * from " . $m->prefix . "admin_user where id=$id";
        $row_ = $m->_query($sql);
        $row = $row_[0];
        unset($row_);
        if (empty($row['roleId']) && $row['comment'] == 'founder') {
            return true;
        }
        return false;
    }

    public function setup() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data = $_POST;
            unset($data['_submit_']);
            unset($data['_verifyKey_']);
            foreach ($data as $key => $value) {
                $val = $m->g(strip_tags(trim($value)));
                $sql = "update " . $m->prefix . "shop_config set val='$val' where mark='$key'";
                $m->query($sql);
            }
            $url = null;
            if ($this->_get('type') == false) {
                $url = '?system=setup';
            } else {
                $url = '?system=setup&type=' . $this->_get('type');
            }
            header('Location:' . $url);
        } else {
            $val = null;
            $type2 = $this->_get('type');
            if ($type2 == false) {
                $val = 1;
            } else {
                switch ($type2) {
                    case 'other':$val = 100;
                        break;
                    case 'base':$val = 2;
                        break;
                    case 'theme':$val = 3;
                        break;
                    case 'mail':$val = 4;
                        break;
                    case 'userpoint':$val = 5;
                        break;
                    default:
                        break;
                }
            }
            if ($val) {
                $result = $m->getall('select * from ' . $m->prefix . "shop_config where g=$val order by sort,id");
                $info = null;
                foreach ($result as $key => $value) {
                    $info[$key]['val'] = $value['val'];
                    $info[$key]['name'] = $value['name'];
                    $info[$key]['mark'] = $value['mark'];
                    $info[$key]['comment'] = $value['comment'];
                    $info[$key]['type'] = $value['type'];
                    if (!empty($value['seval']))
                        $info[$key]['seval'] = explode(",", $value['seval']);
                }
                $this->assign('list', $info);
            }
            $this->display();
        }
    }
    
    /**
     *     后台查看发送短信
     */
    public function sendmsglog(){
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p*$listrows-$listrows;
        
        $sql = "select * from `".$m->prefix."sendsmslog` order by id desc limit $start,$listrows";
        $list = $m->getall($sql);
        
        //page 数据
        $count = $m->getone("select count(*) as count from `".$m->prefix."sendsmslog` ");
        
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo',$page->show(4));
        $this->assign('list', $list);
        $this->display("sendmsglog");
    }
    
    public function sendmsgDelete(){
        $m = new \core\model();
        if( is_array($_REQUEST['allsms']) and !empty($_REQUEST['allsms']) ){
            foreach ($_REQUEST['allsms'] as $key => $value) {
                $m->sDelete('sendsmslog', "id=$value");
            }
            echo json_encode(array('status'=>1));
            exit();
        }
    }

    public function exemption() {
        header("Location: " . __ROOT__);
    }

}
