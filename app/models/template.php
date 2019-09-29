<?php

/*
 * 模板里面调用的相关函数
 */

/**
 * Description of template
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace models;

class template extends \core\model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取一个栏目数据
     * @param type $id
     * @param type $type  判断是手机还是电脑  pc or mobile , 主要用在商品分类和促销活动栏目类型的读取
     * @return boolean
     */
    public function getcolumn($id = null, $type = 'pc') {
        if (!$id) {
            return false;
        }
        $result = array();
        $sql = "select * from `" . SQLPRE . "column` where (id='$id' or mark='$id') and status=1";
        $data = $this->getrow($sql);
        if (empty($data) || count($data) == 0) {
            return false;
        }
        $result['id'] = $data['id'];
        $result['name'] = $data['name'];
        $result['mark'] = $data['mark'];
        $result['tplContent'] = $data['tplContent'];
        $result['tplList'] = $data['tplList'];
        $result['tplArticle'] = $data['tplArticle'];
        switch ($data['type']) {
            case '1': //栏目列表
                if ($type == 'pc') {
                    $result['link'] = createLink('article/artList', array('i' => $data['mark']), 1);
                } else {
                    $result['link'] = createLink('article/artList', array('i' => $data['mark']));
                }
                break;
            case '2': //外部链接
                $result['link'] = $data['url'];
                break;
            case '3': //栏目内容

                if ($type == 'pc') {
                    $result['link'] = createLink('article/artList', array('i' => $data['mark']), 1);
                } else {
                    $result['link'] = createLink('article/artList', array('i' => $data['mark']));
                }
                $result['content'] = $data['content'];
                break;
            case '4': //商品分类
                if ($type == 'pc') {
                    $result['link'] = createLink('goods/category', array('g' => $data['goodstype']), 1);
                } else {
                    $result['link'] = createLink('goods/category', array('g' => $data['goodstype']));
                }
                break;
            case '5': //促销活动
                if ($type == 'pc') {
                    //$result['link'] = createLink('a/' . $data['goodsactivity'], null, 1);
                    $result['link'] = createLink('goods/activity', array('ac' => $data['goodsactivity']), 1);
                } else {
                    
                }
                break;
            default:
                $result['link'] = null;
                break;
        }
        return $result;
    }

    /**
     * 获取一个商品分类
     * @param type $id
     */
    public function getgoodscate($id) {
        if ($id !== false) {
            $m = new \core\model();
            $result = null;
            if ($id == '00') {
                $result_ = $m->getall('select * from ' . $m->prefix . "category where status=1");
                $result = RecursionData(0, $result_);
            } else {
                $result = $m->getall('select * from ' . $m->prefix . "category where id=$id and status=1");
            }
            return $result;
        } else {
            return false;
        }
    }
    
    /**
     * 获取子商品分类
     * @param type $id
     */
    public function getgoodssoncate($id) {
        if ($id !== false) {
            $m = new \core\model();
            $result = $m->getall('select * from ' . $m->prefix . "category where parentId=$id and status=1");
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 获取文章列表 
     * 
     * @param type $id  栏目的标签
     * @param type $num  获取文章的数量
     * @param type $order  排序方式,1为最新，2为人气最高
     * @return boolean
     */
    public function getartlist1($id, $num, $order) {
        $sql = "select id,model from `" . SQLPRE . "column` where mark='$id' and type=1 and status=1";
        $modelinfo = $this->getrow($sql);
        $modelid = $modelinfo['model'];
        if (empty($modelid) || count($modelid) == 0) {
            return false;
        }
        return $this->getartlist2($modelid, $modelinfo['id'], $num, $order);
    }

    /**
     * 获取文章列表
     * @param type $id  频道ID
     * @param type $field  指定要获取的字段
     */
    public function getartlist2($id, $cid = null, $num, $order) {
        $tablename = null;
        $sql = "select mark from `" . SQLPRE . "models` where id='$id' or mark='$id' ";
        $tablename = $this->getone($sql);
        $tablename_ = $tablename . '_field';

        $result = array();

        $where_ = null;
        if ($cid) {
            //获取当前栏目下的所有子栏目ID，以便到能获取到所有子栏目的文章
            $result = $this->getall('select id,name,mark,pid as parentId from ' . SQLPRE . 'column ');
            returnColumnSon($cid, $result, $arr);
            //设置获取取子栏目文章的where 条件
            $where_ = " a.cid=$cid or ";
            if (is_array($arr)) {
                foreach ($arr as $value) {
                    $where_.="a.cid=$value or ";
                }
            }
            $where_ = substr($where_, 0, -4);
        }
        $order_ = 'id desc';
        if ($order == 1) {
            $order_ = 'id desc';
        } elseif ($order == 2) {
            $order_ = 'hits desc';
        }

        $sql = "select a.*,c.mark from `" . SQLPRE . "$tablename` a left join `" . SQLPRE . "$tablename_` b on a.id=b.aid "
                . " left join `" . SQLPRE . "column` c on a.cid=c.id where  a.arcrank !='c'   and  ( $where_ ) order by $order_ limit $num";
        //因为 人事招聘表中没有普通文章表的一些字段 ，所以要加判断改SQL 
        if ($id == 'channel-renshi') {
            $sql = "select a.*,c.mark from `" . SQLPRE . "$tablename` a left join `" . SQLPRE . "$tablename_` b on a.id=b.aid "
                    . " left join `" . SQLPRE . "column` c on a.cid=c.id where  a.arcrank !='c'    order by $order_ limit $num";
        }
        $result = $this->getall($sql);
        return $result;
    }

    /**
     * 获取自定义数据组 
     * 
     * @param type $id
     */
    public function getcustom1($id) {
        $sql = "select * from `" . SQLPRE . "customdata` where id='$id' or mark='$id'";
        $info = $this->getrow($sql);
        $result = array();
        $result['info'] = $info;
        $list = array();
        if ($info['tablename'] == 'goods') {
            $sql = "select * from `" . SQLPRE . "customdata_data` where cid=$info[id]";
            $res = $this->getall($sql);
            foreach ($res as $key => $value) {
                $redis = new yredis();
                $arr1 = json_decode($redis->get(REDIS_PRE . 'goods_' . $value['dataid']), 1);
                if($arr1['addStatus']==1){
                    $list[$value['dataid']] = $arr1;
                }
            }
        } else {
            //如果 不是商品类型，则查找文章数据
            $ta = $info['tablename'];
            $list = array();
            $sql = "select a.*,c.mark from `" . SQLPRE . "customdata_data` cd "
                    . " left join `" . SQLPRE . "$ta` a on cd.dataid=a.id "
                    . " left join `" . SQLPRE . "column` c on a.cid=c.id  where cd.cid=$info[id] and a.arcrank != 'c' order by a.id desc";
            $list = $this->getall($sql);
        }
        $result['list'] = $list;
        return $result;
    }

    /**
     * 获取招聘信息列表
     */
    public function getjobslist($num) {
        //如果 不是商品类型，则查找文章数据
        $list = array();
        $sql = "select a.*,c.didian,c.jinyan,c.xinzi,c.xueli from `" . SQLPRE . "channel-renshi` a "
                . " left join `" . SQLPRE . "channel-renshi_field` c on a.id=c.aid where a.arcrank='o' and isdelete!=1 order by a.id desc limit 0,$num";
        $list = $this->getall($sql);
        return $list;
    }

    /**
     * 获取一个商品分类下的所有商品数据
     * 
     * @param type $id  商品分类ID
     * @return type
     */
    public function getonegoodscate($id,$num) {
        if (!$id) {
            return FALSE;
        }
        $redis = new yredis();
        $sql = "select id from `" . SQLPRE . "category` where id=$id or parentId=$id";
        $catedata = $this->getall($sql);
        $where = null;
        foreach ($catedata as $key => $value) {
            $where.=" g.catId=$value[id] or ";
        }
        $where = '(' . substr($where, 0, -3) . ')';
        $sql = "select ga.id from `" . SQLPRE . "goods` g ,`" . SQLPRE . "goods_additional` ga where g.id=ga.goodsId and ga.addStatus=1 and ( $where  ) limit $num ";
        $res = $this->getall($sql);
        $list = array();
        foreach ($res as $key => $value) {
            $list[$value['id']] = json_decode($redis->get(REDIS_PRE . 'goods_' . $value['id']), 1);
        }
        return $list;
    }
    
    /**
     * 获取一个滚动图片组的图片数据
     * @param type $id   表 y_rollimages 的 ID 或者 mark
     */
        public function getrollimage($id) {
        if (!$id) {
            return FALSE;
        }
        
        $sql = "select id from `".SQLPRE."rollimages` where id='$id' or mark='$id'";
        $rid = $this->getone($sql);
        
        $sql = "select * from `".SQLPRE."rollimages_img` where rid=$rid";
        $data = $this->getall($sql);
        return $data;
    }

}
