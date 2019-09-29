<?php

/**
 * 常用方法
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;
class pController {
    /**
     * 递归处理数据
     * @param type $id
     * @param type $data
     * @return type
     */
    public function RecursionData($id=0,$data) {
        $arr = null;
        foreach ($data as $key => $value) {
            if($value['parentId'] == $id){
                $arr[$value['id']] = $value;
                $arr[$value['id']]['son'] = self::RecursionData($value['id'],$data);
            }
        }
        return $arr;
    }
    
    //返回一个栏目下所有子栏目的类型 
    public function RecursionData2($id = 0, $data, &$arr) {
        foreach ($data as $key => $value) {
            if ($value['parentId'] == $id) {
                $arr[] = $value['typeId'];
                self::RecursionData($value['typeId'], $data, $arr);
            }
        }
    }
    
    /**
     * 递归打印名称
     * @param type $data
     * @param string $str
     * @param type $arr
     */
    public function printData($data,$i=0,&$arr = array()) {
        $h=1*$i;
        $str='';
        for ($j=0;$j<$h; $j++){
            $str.='　　';
        }
        foreach ($data as $key => $value) {
            $value['name'] = '□'.$str.'&nbsp;'.$value['name'];
            $arr[] = $value;
            if(is_array($value['son'])){
                self::printData($value['son'],++$h,$arr);
                $h =$i =0;
            }
            unset($value['son']);
        }
    }
    
    /**
     * 返回经过处理无限级分类之后的分类树结构
     * @return array
     */
    public function returnCateList() {
            $m = new \core\model();
            $row = $m->_query("select * from ".$m->prefix."category");
            $row_ = $this->RecursionData(0,$row);
            $data = array();
            $this->printData($row_,'',$data);
            return $data;
    }
    
    /**
     * 排列组合
     * @param type $data
     * @return string
     */
    public function Permutation($data) {
//           $data = array(
//                array('a','b','c'),
//                array(2,3,4),
//                array('H','I','J'),
//                array('K','L','M'),
//                array('e','f','g','h','k')
//            );


            $i = null;  //用做临时变量 
            $i2 = null;  //用来记录 之前叠加的 组合 
            $t = null;   //用做临时变量 ，记录之前叠加 的组合 
            $t2 = null;  //第二次循环之后，用来记录 数组第二维的数组变量

            $len_ = count($data) - 1;  //记录数据长度 ，用做输出判断
            $res = null;  //保存最后的结果

            if(count($data)==1){
                    $res = current($data);
            }else{
                
                    foreach ($data as $key => $value) {

                        foreach ($value as $k => $v) {
                            if(is_array($t)){
                                foreach ($t as $h) {
                                    $i = $i2[] = $h.','.$v;
                                    if( substr_count($i,',')==$len_ ) $res[]=$i;
//                                    if( strlen($i)==$len_ ) $res[]=$i."\n";
//                                    echo $i."\n";
                                }
                            }else{
                                $t2[] = $v;
                            }
                        }

                        if(is_array($i2)){
                            $t = $i2;
                        }else{
                            $t = $t2;
                        }
                    }
                    
            }

//            print_r($res);
            return $res;
    }
    
    /**
     * 匹配两个数组中不同的值
     * @param type $arr1  属性列表
     * @param type $arr2  保存的属性
     * @param type $kk 匹配的数组键名
     * @return type
     */
    static public function key_compare_func($arr1,$arr2,$kk='id') {
        $result = array();
        foreach ($arr1 as $key=>$value) {
            foreach ($arr2 as $v) {
                if($value["$kk"] == $v["$kk"]){
                    $result[$key] = $v;
                    break;
                }
            }
            if( empty($result[$key]) ) $result[$key] = $value;
        }
        return $result;
    }
    
    
}
