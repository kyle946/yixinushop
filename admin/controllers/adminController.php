<?php

/**
 * 登录验证和权限检测
 *
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

/**
 * 1、记录后台所有需要验证的类和方法    y_admin_action
 * 2、记录后台所能菜单      y_admin_menu
 * 3、记录操作后台所有的功能模块，y_admin_action和y_admin_menu的mtagId字段关系此表ID    y_admin_mtag
 * 4、记录后台用户的权限，同样 mtagId 关联 y_admin_mtag 的 ID 
 * 
 * select m.*,r.roleId from y_admin_menu as m,y_admin_roleper as r where m.mtagId=r.mtagId and r.roleId=3;
 */
namespace controllers;
abstract  class adminController extends \core\Controller {
    
    public function __construct() {
        parent::__construct();
        $key = cfg('LOGIN_SESSION_KEY');
        //登录信息
        $logindata = null;
        if( isset($_SESSION[$key]) ) $logindata = $_SESSION[$key];
        //如果登录信息不存在或已经超时
        if( empty($logindata) ){
//        if( empty($logindata) or $logindata['expiretime']<time() ){
            unset($_SESSION[$key]);
            header("Location:  ?act=login");
            exit();
        }else{
            $this->assign('userinfo_name', $logindata['name']); //用户名
            $model = new \core\model("admin_menu");
            //如果接近两分钟超时，则加两分钟， 如果两分钟没有操作就会退出
//            if( time()+300 > (int)$_SESSION[$key]['expiretime'] ){
//                $_SESSION[$key]['expiretime'] = time() + 1200;
//            }

            //判断是否为创始人
            $isfounder = false;
            if( $_SESSION[$key]['comment']=='founder' && $_SESSION[$key]['roleId']==null ){
                $isfounder = true;
            }
            //如果是超级管理员
            if( $_SESSION[$key]['mark']=='admin' ){
                $isfounder = true;
            }
            $this->assign('isfounder',$isfounder);  //是不是超级管理员
            $this->assign('usermark',$_SESSION[$key]['mark']);  //管理员的角色标签
            //判断是否需要检测权限
            $ischeck = true;
            if($isfounder) $ischeck=false; 
            if(CONTROLLER_NAME === 'index' && ACTION_NAME === 'index')  $ischeck=false; 
            if(ACTION_NAME === 'exemption') $ischeck=false;
            //检测是否有权限检测执行此操作
            $sql = null;
            $sql = "select a.* from ".$model->prefix."admin_action as a,".$model->prefix."admin_roleper as r "
                    . "where r.mtagId=a.mtagId and r.roleId=".$_SESSION[$key]['roleId']." and colltroller='".CONTROLLER_NAME."' and action='".ACTION_NAME."'";
            $result = $model->_query($sql);
            if($ischeck){
                if(empty($result)){
                    msg('你没有权限进行此操作！');
                }
                $sql = null; unset($result);
            }
            
            //通过 赋值查询字符串设置 默认链接为商品列表 ，
            $querystring = matchurl(null,2);
            $this->assign('querystring', $querystring);
            
            
            //根据对应的权限加载一级菜单
            if($isfounder){
                $row = $model->getall("select * from ".$model->prefix."admin_menu where parentId=0 order by sort,id");
            }else{
                $sql = "select m.*,r.roleId from ".$model->prefix."admin_menu as m,".$model->prefix."admin_roleper as r "
                        . "where m.mtagId=r.mtagId and r.roleId=".$_SESSION[$key]['roleId']." and parentId=0 order by sort,id";
                $row = $model->_query($sql);
            }
            $this->assign('menu', $row);
            
            /**
             * 子菜单 和 父菜单 的parentId 判断   _start_
             */
            $table = $model->prefix."admin_menu";
            $parentid_ = $model->_query("select id as id_ from $table where parentId=0 and '$querystring'=url limit 1");
            if(empty($parentid_)){
                $parentid_ = $model->_query("select parentId as id_ from $table where parentId!=0 and url like '$querystring%' limit 1");
            }
            // _end_
            
            //根据对应的权限加载二级菜单
            if(!empty($parentid_)){
                $parentid = $parentid_[0]['id_'];
                if($isfounder){
                    $sql = "select * from $table where parentId=$parentid order by sort,id" ;
                }else{
                    $sql = "select m.*,r.roleId from ".$model->prefix."admin_menu as m,".$model->prefix."admin_roleper as r "
                            . "where m.mtagId=r.mtagId and r.roleId=".$_SESSION[$key]['roleId']." and parentId=$parentid order by m.sort,m.id";
                }
                $row_second = $model->_query($sql);
            }else{
                $parentid = null;
                $row_second = null;
            }
            $this->assign('parentid',$parentid);
            $this->assign('menu_second', $row_second);
            
            //读取配置
            $config__ = NULL; $config_ = NULL;
            $config_ = $model->getall("select name,mark,val from ".$model->prefix."shop_config where g=100 or g=1" );
            if(!empty($config_)):
                foreach ($config_ as $value) {
                    $config__[$value['mark']] = $value['val'];
                }
                $GLOBALS['config'] = $config__;
                $config__ = null;
            endif;
        }
    }
    
    /**
     * 免检方法，此方法不检测权限
     * 用来写一些ajax验证和加载图片之类的，这些不要检测权限的功能 
     */
    abstract protected function exemption() ;
    
}
