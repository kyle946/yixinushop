<?php

/**
 * Description of mobileController
 *
 * @author 青竹丹枫 kyle <316686606@qq.com>
 */

namespace m_controllers;

class indexController extends comController {

    /**
     * 首页
     */
    public function index() {
        $m = new \core\model();
        $sql = "select value from `" . $m->prefix . "index_config` where `name`='floor'";
        $cateInfo['floor'] = $m->getone($sql);
        //商品列表
        if (isset($cateInfo['floor']) && is_array(unserialize($cateInfo['floor']))):
            $cateInfo['floor'] = unserialize($cateInfo['floor']);
            foreach ($cateInfo['floor'] as $key => $value) {
                $goodsid = null;
                foreach ($value['goodsid'] as $v2) {
                    $goodsid.='ga.id=' . $v2 . ' or ';
                }
                $goodsid = substr($goodsid, 0, -3);
                $sql = "select g.id as id_,g.name,ga.id, "
                        . "if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice ,a.id as aid, "
                        . "ga.attributeStr,ga.numbers,ga.sn,ga.thumb,ga.clickCount,ga.comments"
                        . " from " . $m->prefix . "goods g "
                        . " inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId "
                        . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                        . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                        . " where ($goodsid) and ga.addStatus=1";
                $goods_ = $m->getall($sql);
                $value['goodsData'] = $goods_;
                $value['key'] = $key;
                $cateInfo['list'][] = $value;
            }
        endif;

        if (!empty($cateInfo['list'])):
            //冒泡排序
            $f_ = false;
            for ($i = 1; $i < count($cateInfo['list']); $i++) {
                for ($j = 0; $j < count($cateInfo['list']) - $i; $j++) {
                    if ($cateInfo['list'][$j]['sort'] > $cateInfo['list'][$j + 1]['sort']) {
                        $temp = $cateInfo['list'][$j];
                        $cateInfo['list'][$j] = $cateInfo['list'][$j + 1];
                        $cateInfo['list'][$j + 1] = $temp;
                        $f_ = true;
                    }
                }
                if (!$f_) {
                    break;
                }
                $f_ = false;
            }
        endif;
        $this->assign('info', $cateInfo);
        $this->display('index');
    }

    /**
     * 商品详情页
     */
    public function item() {
        $m = new \core\model();
        $id = $this->rget('id');
            
        //判断商品是否已经下上架
        $sql = "select addStatus from `" . $m->prefix . "goods_additional` where id=$id";
        $status = $m->getone($sql);
        if ($status != 1 or !$status) {
            message('该商品已经下架或者已经删除！');
        }
        if (!empty($id)) {
            
            //用户 ID
            $userinfo = loginJudbe();
            $userid = $userinfo?$userinfo['id']:0;
            $goodsModel = new \models\goods();

            //更新访问率  start
            $GoodsClickCount_name = 'GoodsClickCount' . $id;
            if (!isset($_COOKIE[$GoodsClickCount_name]) or empty($_COOKIE[$GoodsClickCount_name])) {
                setcookie($GoodsClickCount_name, $id, time() + 100);
                $data['clickCount'] = "clickCount+1---";
                $m->sData($data, "goods_additional", 'u', "id=$id");
            }
            //更新访问率  end

            //根据typeId获取表名，以便读取商品类型的属性
            $typeId = $m->getone("select typeId from " . $m->prefix . "goods g inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId where ga.id=$id");
            $table_mark = $m->getone("select mark from " . $m->prefix . "goods_type where id=$typeId");
            $tablename = $m->prefix . 'goods_add' . $table_mark;

            $sql = "select g.*,ga.*, "
                    . " if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice ,a.id as aid, "
                    . " a.name as activityName, a.starttime,a.endtime,ag.xiangou,b.business_name"
                    . " from " . $m->prefix . "goods g "
                    . " inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId "
                    . " left join ".SQLPRE."business b on g.business_no=b.business_no"
                    . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                    . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                    . "left join `$tablename` gad on ga.sn=gad.sn "
                    . "where ga.id=$id";
            $info = $m->getrow($sql);
            
            $redis = new \models\yredis();
            //从缓存读取库存，保存准确性
            $info['numbers'] = $redis->get(REDIS_PRE.'goods_numbers_'.$info['id']); 
            //取用户的购买记录， 用作限购判断 
            $info['xiangouuser'] = $redis->get(REDIS_PRE.'goods_xianglouinfo_'.$userid.'-'.$info['id'])?:0;
            
            //图片
            if (!empty($info['imgs'])) {
                $info['imgs'] = unserialize($info['imgs']);
            }

            //替换掉商品介绍中的图片 ，以便 前端 使用延迟加载 
            $pattern = '/<img.+?src\=\"(.+?)\"/is';
            $info['goodsDesc'] = preg_replace_callback($pattern, function($matches){
                return regeximage1($matches[1]);
            }, $info['goodsDesc']);

            //属性
            if (!empty($info['attr'])) {
                $info['attr'] = unserialize($info['attr']);
            }
            if (!empty($info['attribute'])) {
                $info['attribute'] = unserialize($info['attribute']);
            }

            //规格 , 只有值为1 的规格才输出 
            if (!empty($info['attr'])) {
                $tvardata = $info['attr'];
                $info['attr'] = null;
                foreach ($tvardata as $key => $value) {
                    $tvar1 = explode(',', trim($value, ','));
                    $list = null;
                    foreach ($tvar1 as $k1 => $v1) {
                        if (strpos($v1, '=') == false)
                            continue;
                        list($tvar2, $tvar3) = explode('=', $v1);
                        if ((int) $tvar3 == 1) {
                            $list[++$k1] = $tvar2;
                        }
                    }
//                $info['attr'][$key]['list'] = $list;
                    $info['attr'][$key]['list'] = $goodsModel->setspec($list, $info['attribute'], $info['goodsId']);  //把goods附加表ID加入到规格属性中，以达到每 一个规格都 是一个独立商品目的。
                    $info['attr'][$key]['name'] = $m->getone("select name from " . $m->prefix . "goods_spec where id=$key");
                }
            }

            //把商品名称写的页面标题上
            $this->assign('webtitle_', $info['name']);
            
            //商品评价总数
            $sql = "select count(id) as count from `".SQLPRE."goods_comment` where goodsid=$info[goodsId] and status=1";
            $commentCount = $m->getone($sql);
            $this->assign('commentCount', $commentCount);
            $this->assign('info', $info);
        }
        $this->assign('layout_title', '商品详情');
        $this->display('item');
    }
    
    public function test() {
//        setcookie('userinfo', 'this test user cookie!',time()+86400);
        echo $_COOKIE['userinfo'];
        echo '<pre>';
//        print_r($_SERVER);
        echo '</pre>';
    }

}
