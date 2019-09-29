<?php
/**
 * 商城其他功能操作
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace models;
class other {
    
    
    /**
     * 积分操作 
     * @param type $userid  用户ID
     * @param type $num  积分值
     * @param type $act  操作，add 为增加，sub 为减
     * @param type $info  记录积分增加的原因，如：新用户注册，增加积分:10
     */
    public function pointsAct($userid = null,$num = null,$act = 'add',$info=null) {
        if( !empty($userid) and !empty($num) ){
            $m = new \core\model();
            if($act=='add'){
                $sql = "update ".$m->prefix."users set `payPoints`=`payPoints`+$num,`pointslog`='$info' where id=$userid";
                $result = $m->query($sql);
                if($result){
                    $points = $m->getone("select payPoints from ".$m->prefix."users where id=$userid");
                    //判断积分对应的等级
                    $sql = "select id,points from `".$m->prefix."user_rank` where $points>=points order by points desc";
                    $rankData = $m->getrow($sql);
                    $sql = "update `".$m->prefix."users` set userRank=$rankData[id] where id=$userid";
                    $m->query($sql);
                    return true;
                }
            }elseif($act=='sub'){
                $sql = "update ".$m->prefix."users set `payPoints`=`payPoints`-$num where id=$userid";
                $result = $m->query($sql);
                if($result){
                    $points = $m->getone("select payPoints from ".$m->prefix."users where id=$userid");
                    //判断积分对应的等级
                    $sql = "select id,points from `".$m->prefix."user_rank` where $points>=points order by points desc";
                    $rankData = $m->getrow($sql);
                    $sql = "update `".$m->prefix."users` set userRank=$rankData[id] where id=$userid";
                    $m->query($sql);
                    return true;
                }
            }
        }
        return false;
    }
    
}
