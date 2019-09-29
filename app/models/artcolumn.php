<?php

/**
 * 文章栏目模块
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace models;

class artcolumn {

    //栏目数据
    public $cdata = array();
    //访问数据库的模块
    public $m;

    public function __construct() {
        $this->m = new \core\model();
        $sql = 'select id,name,mark,type,seotitle,keyword,tplList,tplArticle,url,goodstype,model,pid as parentId from ' . $this->m->prefix . "column where status=1 order by sort,id";
        $this->cdata = $this->m->getall($sql);
        $this->judgeCurColumn();
    }

    /**
     * 获取所有栏目数据，已经分好层级的
     * @return array
     */
    public function getall($id = 0) {
        return RecursionData($id, $this->cdata);
    }

    /**
     * 获取第二级栏目数据，这个方法只有在已经打开某个栏目后能使用，
     * @return type
     */
    public function getlev2() {
        $curId = end($GLOBALS['CurColumnId']);
        if ($curId) {
            return RecursionData($curId, $this->cdata);
        } else {
            return false;
        }
    }

    /**
     * 根据现在url链接判断当前的栏目ID
     * $GLOBALS['CurColumnId']  全局变量为一个数据，按顺序依次存放着上级栏目的ID
     * @return type
     */
    public function judgeCurColumn() {
        $row = null;
        $GLOBALS['CurColumnId'] = array();
        //如果栏目类型为 ：栏目列表、文章内容、商品分类
        $v1 = null;
        $bool1 = false;
        if (rget('i')) {
            $bool1 = true;
            $v1 = rget('i');
        }
        if (rget('m')) {
            $bool1 = true;
            $v1 = rget('m');
        }
        if (rget('g')) {
            $bool1 = true;
            $v1 = rget('g');
        }
        if ($bool1) {
            $row = $this->m->getrow('select id,mark,pid from ' . $this->m->prefix . "column where mark='$v1'");
        }
        $GLOBALS['CurColumnId'][] = $row['id'];    //向全局变量  CurColumnId  保存当前栏目ID
        //如果不是顶级栏目
        if ($row['pid'] != 0) {
            $GLOBALS['CurColumnId'][] = $row['pid'];    //向全局变量  CurColumnId  保存上级栏目ID
            //再查询上级栏目上是否还有上级栏目
            $row_pid = $row['pid'];
            $row = null;
            $row = $this->m->getrow('select id,pid from ' . $this->m->prefix . "column where id=$row_pid");
            if ($row['pid'] != 0):
                $GLOBALS['CurColumnId'][] = $row['pid'];   //向全局变量  CurColumnId  保存上级栏目ID
            endif;
            //再查询上级栏目上是否还有上级栏目
            $row_pid = $row['pid'];
            $row = null;
            $row = $this->m->getrow('select id,pid from ' . $this->m->prefix . "column where id=$row_pid");
            if ($row['pid'] != 0):
                $GLOBALS['CurColumnId'][] = $row['pid'];   //向全局变量  CurColumnId  保存上级栏目ID
            endif;
        }
    }

}
