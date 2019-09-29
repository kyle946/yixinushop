<?php

/**
 * Description of address
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace models;
class address {
    
    /**
     * 返回地区数据
     * @param type $act   获取的动作，分为 city,county,town，参数为空时默认返回省份数据
     * @param type $id   上级区域的编号
     */
    public function get($act=null,$id=null) { 
        
        $m = new \core\model();
        switch ($act) {
            case 'city':  //市
                $list = $m->getall("select * from ".$m->prefix."area_city where province_id=$id");
                break;
            case 'county':   //区、县
                $list = $m->getall("select * from ".$m->prefix."area_county  where city_id =$id");
                break;
            case 'town':  //街道、乡镇
                $list = $m->getall("select * from ".$m->prefix."area_town  where county_id =$id");
                break;
            //默认返回省份
            default:
                $list = $m->getall("select * from ".$m->prefix."area_provice");
                break;
        }
        return $list;
    }
}
