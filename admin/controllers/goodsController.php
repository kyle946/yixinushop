<?php

/**
 * 商品管理 
 *
 * 无限级分类   http://www.luocheng.cn/article-view-235.html
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;

class goodsController extends adminController {

    /**
     * 商品列表
     */
    public function goodslist() {
        $m = new \core\model();

        $cateid = isset($_GET['cateid']) ? $_GET['cateid'] : null;
        $goodsname = isset($_GET['goodsname']) ? $_GET['goodsname'] : null;
        $numbers = isset($_GET['numbers']) ? $_GET['numbers'] : null;
        $where = null;
        if ($cateid) {
            $where = $where ? $where . ' and '  : 'where ' ;
            $sql = "select id from `" . SQLPRE . "category` where id=$cateid or parentId=$cateid";
            $catedata = $m->getall($sql);
            foreach ($catedata as $key => $value) {
                $where.=" g.catId=$value[id] or ";
            }
            $where = '(' . substr($where, 0, -3) . ')';
        }
        if ($goodsname) {
            $where = $where ? $where . ' and '  : 'where ' ;
            $where .= " g.name like '%$goodsname%' or ga.tiaoma='$goodsname' ";
        }
        if ($numbers) {
            $where = $where ? $where . ' and '  : 'where ' ;
            $where .= " ga.numbers <=0 ";
        }
        //begin 如果是商户登录
        $business = is_business();
        if( $business ){
            $where = $where ? $where . ' and '  : 'where ' ;
            $where .= 'g.business_no='.$business;
            $data['business_no'] = $business;
        }
        //end
        
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;

        $sql = "select g.business_no,b.business_name,g.id,g.name as goodsName,ga.addStatus, gc.name as cateName,ga.id as gid,  "
                . "ga.shopPrice,ga.attributeStr,ga.numbers,ga.addStatus,g.sn,ga.sn as sn2 from " . SQLPRE . "goods as g "
                . " left join ".SQLPRE."business b on g.business_no=b.business_no "
                . "left join " . SQLPRE . "goods_type as gc on g.typeId=gc.id "
                . "left join " . SQLPRE . "goods_additional as ga on g.id=ga.goodsId $where order by ga.id desc  limit $start,$listrows";
        $list = $m->getall($sql);

        //page 数据
        $count = $m->getone("select count(ga.id) as count from `" . SQLPRE . "goods` g left join `" . SQLPRE . "goods_additional` ga on g.id=ga.goodsId $where");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));


        $this->assign('categroy_list', $this->returnCateList());

        $this->assign('list', $list);
        $this->display("goods/goodslist");
    }

    /**
     * 添加商品
     */
    public function goodsedit() {
        $m = new \core\model();
        //提交数据 
        if (isset($_POST['_submit_'])) {
//            var_dump($_REQUEST);exit();
            //step 1 :
            $data['name'] = $m->g($this->_post('name'));
            $data['name2'] = $m->g($this->_post('name2'));
            $data['catId'] = floatval($this->_post('catId'));
            $typeId = $data['typeId'] = $m->getone("select typeId from " . SQLPRE . "category where id=$data[catId]");
            if (isset($_POST['imgfiles']))
                $data['imgs'] = serialize($_POST['imgfiles']);
            $data['sort'] = intval($this->_post('sort'));
            $data['chengzhong'] = intval($this->_post('chengzhong'));
            $data['weighing_code'] = $this->_post('weighing_code');
//            $data['status'] = intval($this->_post('status'));
            $data['dateTime'] = "now()";
            $data['sn'] = $_SESSION['goodssn'];
            $business = is_business();
            if($business){
                $data['business_no'] = $business;
            }

            //根据ID判断是更新还是插入
            $goodsid = null;
            $action_ = 'insert';
            if ($this->_get('id')) {
                $goodsid = $this->_get('id');
                $action_ = 'update';
            }
            //判断是修改还是添加
            if (!empty($goodsid)) { //保存主商品数据
                $result = $m->sData($data, 'goods','u',"id=$goodsid");
                if ($result) {
                    $data['id'] = $goodsid;
                }
            } else {    //添加主商品数据
                $data['sn'] = $_SESSION['goodssn'];
                $m->sData(array('use'=>1), 'sn2', 'u', "id=$data[sn]"); //保存编号使用状态
                $data['dateTime'] = 'now()';
                $result = $m->sData($data, 'goods');
                if ($result) {
                    $goodsid = $data['id'] = mysqli_insert_id($m->link);
                }
            }

            //如果已经添加或更新了主商品数据，则添加商品附加数据  商品规格
            if (!empty($data['id'])) {
                $dataAdd = null;
                $dataAdd_ = null;
                if (isset($_POST['id']) && !empty($_POST['id']))
                    $dataAdd['id'] = $_POST['id'];
                $dataAdd['sn'] = $_POST['sn'];
                $dataAdd['shopPrice'] = $_POST['shopPrice'];
                $dataAdd['marketPrice'] = $_POST['marketPrice'];
                $dataAdd['numbers'] = $_POST['numbers'];
                $dataAdd['weight'] = $_POST['weight'];
                $dataAdd['tiaoma'] = $_POST['tiaoma'];
                if (isset($_POST['imgfile']) && !empty($_POST['imgfile'])){
                    $dataAdd['imgfile'] = $_POST['imgfile'];
                }
                //循环处理数据
                foreach ($dataAdd['sn'] as $key => $value) {
                    if (isset($dataAdd['id'][$key]))
                        $dataAdd_[$key]['id'] = intval($dataAdd['id'][$key]);
                    $dataAdd_[$key]['shopPrice'] = floatval($dataAdd['shopPrice'][$key]);
                    $dataAdd_[$key]['marketPrice'] = floatval($dataAdd['marketPrice'][$key]);
                    $dataAdd_[$key]['numbers'] = intval($dataAdd['numbers'][$key]);
                    $dataAdd_[$key]['weight'] = floatval($dataAdd['weight'][$key]);
                    $dataAdd_[$key]['sn'] = $dataAdd['sn'][$key];
                    $dataAdd_[$key]['tiaoma'] = $dataAdd['tiaoma'][$key];
                    if (isset($dataAdd['imgfile'][$key])){
                        $dataAdd_[$key]['thumb'] = strip_tags(trim($dataAdd['imgfile'][$key]));
                    }else{
                        $dataAdd_[$key]['thumb'] = @$_POST['imgfiles'][0];
                    }
                    $dataAdd_[$key]['attribute'] = $_SESSION['goodsspec'][$key]['attribute'];
                    $dataAdd_[$key]['attributeStr'] = $_SESSION['goodsspec'][$key]['attributeStr'];
                }
//                var_dump($_SESSION['goodsspec']); 
//                var_dump($dataAdd_);
//                exit();
                if (isset($dataAdd_) && !empty($dataAdd_)) {
                    //循环插入数据
                    foreach ($dataAdd_ as $key => $value) {
                        //如果当前是更新数据，并且ID又不存在（表示已经重新选择了规格）
                        if ($action_ == 'update' && !isset($value['id'])) {
                            $action_ = 'insert';  //改更新为添加
                            $m->query("delete from " . SQLPRE . "goods_additional where goodsId=$goodsid");  //删除之前的规格
                        }
                        //判断是插入还是更新
                        if ($action_ == 'insert') {
                            $v = null;
                            $f = null;
                            foreach ($value as $k => $v2) {
                                $f.="$k,";
                                if (is_int($v2) || is_float($v2)) {
                                    $v.="$v2,";
                                } else {
                                    $v.="'$v2',";
                                }
                            }
                            $f = substr($f, 0, -1);
                            $v = substr($v, 0, -1);
                            $sql = "insert into " . SQLPRE . "goods_additional ($f,goodsId) values ($v," . $goodsid . ")";
                            $m->query($sql);
                            get_goods_sn('change',$dataAdd_[$key]['sn']);   //通知编号已经使用
                        } else {
                            //如果是更新数据则不需要更新这两个属性，只有重新选择了规格才需要添加。
                            unset($value['attribute']);
                            unset($value['attributeStr']);
                            $v = null;
                            $id = $value['id'];
                            unset($value['id']);
                            foreach ($value as $k => $v2) {
                                if (is_int($v2) || is_float($v2)) {
                                    $v.="$k=$v2,";
                                } else {
                                    $v.="$k='$v2',";
                                }
                            }
                            $v = substr($v, 0, -1);
                            $sql = "update " . SQLPRE . "goods_additional set $v where id=$id";
                            $m->query($sql);
                        }
                    }
                }

                if (isset($_SESSION['goodsattr']) and ! empty($_SESSION['goodsattr'])) {
                    //把选择的规格属性用数组序列化记录到数据库
                    $m->query("update " . SQLPRE . "goods set attr='" . serialize($_SESSION['goodsattr']) . "' where id=" . $goodsid);
                }
            }

            //第三步添加商品属性
            $dataAttr = array();
            if (!empty($_POST['attr'])) {
                $v = null;
                $f = null;
                //根据typeId获取表名
                $table_mark = $m->getone("select mark from " . SQLPRE . "goods_type where id=$typeId");
                $tablename = 'goods_add' . $table_mark;
                //判断是更新还是添加
                if ($action_ == 'insert') {
                    //获取附加表的ID和SN
                    $fujiabiaoId_ = $m->getall("select id,sn as yixinu from " . SQLPRE . "goods_additional where goodsId=$goodsid");
                    //循环以SN为key的数组
                    foreach ($_POST['attr'] as $attKey => $attrData) {
                        $attrData['aid'] = $fujiabiaoId_[$attKey]['id'];
                        $attrData['goodsId'] = $goodsid; 
                        $attrData['sn'] = $attKey;
                        $m->sData($attrData, $tablename);
                    }
                } else {
                    //获取附加表的ID和SN
                    $fujiabiaoId_ = $m->getall("select id,sn as yixinu from " . SQLPRE . "goods_additional where goodsId=$goodsid");
                    //循环以SN为key的数组
                    foreach ($_POST['attr'] as $attKey => $attrData) {
                        if( !isset($attrData['sn']) ){  //如果没有获取到 sn ，商品类型可能已经更改，需要重新添加数据
                            $attrData['aid'] = $fujiabiaoId_[$attKey]['id'];
                            $attrData['goodsId'] = $goodsid; 
                            $attrData['sn'] = $attKey;
                            //添加之前删除之前添加的数据
                            $m->query("delete from `".SQLPRE."$tablename` where aid='$attrData[aid]'");
                            $m->sData($attrData, $tablename);
                        }else{
                            $m->sData($attrData, $tablename,'u',"sn='$attrData[sn]'");
                        }
                    }
                }
            }

            //第四步  添加商品描述信息
            $goodsDesc = $_POST['goodsDesc'];
            $sql = "update " . SQLPRE . "goods set goodsDesc='$goodsDesc' where id=" . $goodsid;
            $m->query($sql);

            //第五步  创建空的商品促销价格数据 ， 同时 设置库存和销量的默认值
            $goodsmodel = new \models\goods();
            if ($action_ == 'insert') {
                $goodsmodel->createActivityData($goodsid, 'i');
            } else {
                $goodsmodel->createActivityData($goodsid, 'u');
            }

            $_SESSION['goodsattr'] = NULL;
            $_SESSION['goodsspec'] = NULL;
            $_SESSION['goodssn'] = null;
            //加入翻页参数是为了修改完成之后不跳转到第一页。
            $p_ = null;
            if ($this->_get('p')) {
                $p_ = '&p=' . $this->_get('p');
            }
            
            header("Location: ?goods=list$p_");

            
            
//显示页面 ###########################################################################
        } else {
            $_SESSION['goodsattr'] = NULL;
            $_SESSION['goodsspec'] = NULL;
            $_SESSION['goodssn'] = null;

            $action_ = 'insert';
            $id = null;
            if ($this->_get('id') != false) {
                $action_ = 'update';
                $id = $this->_get('id');
            }
            if ($action_ == 'update') {
                $info = $m->getrow("select * from " . SQLPRE . "goods where id=$id");
                $_SESSION['goodssn'] = $info['sn'];
                $this->assign("info", $info);
            } else {
                //商品编号，规则：年月日时分秒+两位随机数
                //$goodsSn= date("mdHi").rand(10, 99);
                
                // 商品编号  start --
                $goodsSn = NULL;
                $goodsSn_ = $m->getrow("select id from ".SQLPRE."sn2 where `use`=0 order by id");
                if( $goodsSn_ ){
                    $goodsSn = dechex($goodsSn_['id']); //转成16进制
                }else{
                    $goodsSn = $m->sData(array('v' => 1), 'sn2');   //创建编号
                    $goodsSn = dechex($goodsSn); //转成16进制
                    $m->sDelete('sn2', "id<$goodsSn");  //删除没用的编号
                    //$m->sData(array('use'=>1), 'sn2', 'u', "id=$data[sn]"); //保存编号使用状态
                }
                // end  --
                
                $_SESSION['goodssn'] = $goodsSn;
                $this->assign('sn', $goodsSn);
            }
//            $this->assign('typelist', $m->getall("select * from ".SQLPRE."goods_type"));
            $this->assign('categroy_list', $m->getall("select a.*,b.name as typeName from " . SQLPRE . "category a left join " . SQLPRE
                            . "goods_type b on a.typeId=b.id where a.level=" . $GLOBALS['config']['catlevel']));

            $attrs = null;
            $attr = null;
            if ($action_ == 'update') {
                //取类型对应的表名称 ，然后读取属性
                $table_mark = $m->getone("select mark from " . SQLPRE . "goods_type where id=" . $info['typeId']);
                $tablename = SQLPRE . 'goods_add' . $table_mark;
                //保存的属性值
                $attrs_ = $m->getall("select * from $tablename where goodsid=$id");
                //属性名称
                $attrName = $m->getall("select name,mark as yixinu,mothod,attrValue,mainpar from " . SQLPRE . "goods_attr where typeId=$info[typeId]");
                foreach ($attrs_ as $key => $value) {
                    foreach ($attrName as $k => $v) {
//                        if($v['mainpar']==1){
                        $value_[$k]['val'] = $value[$k];
                        $value_[$k]['name'] = $v['name'];
                        $value_[$k]['mothod'] = $v['mothod'];
                        $value_[$k]['mark'] = $v['yixinu'];
                        $value_[$k]['attrValue'] = $v['attrValue'];
//                        }
                    }
                    $attrs[$value['sn']]['goodsid'] = $value['goodsid'];
                    $attrs[$value['sn']]['aid'] = $value['aid'];
                    $attrs[$value['sn']]['sn'] = $value['sn'];
                    $attrs[$value['sn']]['val'] = $value_;
                }
//                var_dump($attrs);
                $this->assign('attrlist', $attrs);
                //规格
                $attr = $m->getall("select * from " . SQLPRE . "goods_additional where goodsId=$id");
                $this->assign('spec2', $attr);
            }
            $this->display("goods/goodsadd");
        }
    }
    
    
    public function categroydeletes() {

        $m = new \core\model();
        //从规格删除商品(删除选中的规格)
        if (isset($_REQUEST['listcheck11'])) {
//            echo json_encode($_REQUEST['goodslistcheck']); exit();
            if (is_array($_REQUEST['listcheck11']) and ! empty($_REQUEST['listcheck11'])) {

                foreach ($_REQUEST['listcheck11'] as $key => $value) {
                    //删除后设置该分类的商品肯定分出错，所以强行设置为其他
                    $m->sData(array('catId'=>33,'typeId'=>44), "goods", "u", "catId=$value");
                    //执行删除操作
                    $sql = "delete from `" . SQLPRE . "category` where id=$value";
                    $m->query($sql);
                }
            }
        }
    }

    /**
     * 删除商品
     */
    public function goodsdelete() {

        $m = new \core\model();
        //从规格删除商品(删除选中的规格)
        if (isset($_REQUEST['goodslistcheck'])) {
//            echo json_encode($_REQUEST['goodslistcheck']); exit();
            if (is_array($_REQUEST['goodslistcheck']) and ! empty($_REQUEST['goodslistcheck'])) {

                foreach ($_REQUEST['goodslistcheck'] as $key => $value) {
                    $sql = "select goodsId from `" . SQLPRE . "goods_additional` where id=$value";
                    $goodsId = $m->getone($sql);
                    $sql = "select count(*) from `" . SQLPRE . "goods_additional` where goodsId=$goodsId";
                    $num = $m->getone($sql);
                    //如果商品附加表只有一个，才删除主商品信息
                    if ($num <= 1) {
                        $sql = "delete from `" . SQLPRE . "goods_additional` where id=$value";
                        $m->query($sql);
                        $sql = "delete from `" . SQLPRE . "goods` where id=$goodsId";
                        $m->query($sql);
                    } else {  //如果商品附加表不止一个，则只删除商品附加表的信息
                        $sql = "delete from `" . SQLPRE . "goods_additional` where id=$value";
                        $m->query($sql);
                    }
                }
                echo json_encode(array('status' => 1));
            }
        }
        //删除包括所有规格的商品
        if (isset($_REQUEST['id'])) {

            $id = $this->_get('id');
            $p = $this->_get('p');
            if ($id) {
                //获取类型表
                $mark_ = $m->getone("select b.mark from " . SQLPRE . "goods a left join " . SQLPRE . "goods_type b on a.typeId=b.id  where a.id=$id");
                $tablename = SQLPRE . "goods_add" . $mark_;
                //删除附加表数据
                $m->query("delete from " . SQLPRE . "goods_additional where goodsId=$id");
                //删除商品数据 
                $m->query("delete from " . SQLPRE . "goods where id=$id");
                //删除商品类型属性数据
                $m->query("delete from $tablename where goodsid=$id");
                if ($p) {
                    $url = "Location: ?goods=list&p=$p";
                } else {
                    $url = "Location: ?goods=list";
                }
                header($url);
            }
        }
    }

    /**
     * 获取商品类型的属性。
     */
    public function getattrJson() {
        $m = new \core\model();
        $catid = $this->_get("catid");
        $goodsid = $this->_get("goodsid")?:null;
        $typeid = $m->getone("select typeId from " . SQLPRE . "category where id=$catid");
        $list_ = $m->getall("select * from " . SQLPRE . "goods_attr where typeId=$typeid");
        foreach ($list_ as $key => $value) {
            if ($value['mothod'] == 2) {
                $value['attrValue'] = str_replace(array("\r\n", "\n", "\r"), ",", trim($value['attrValue']));
            }
            $list[$key] = $value;
        }
        $result = null;
        //如果选择了规格属性
        if (isset($_SESSION['goodsspec']) && is_array($_SESSION['goodsspec'])) {
            foreach ($_SESSION['goodsspec'] as $key => $value) {
                $result[$key]['sn'] = $value['sn'];
                $result[$key]['attributeStr'] = $value['attributeStr'];
                $result[$key]['list'] = $list;
            }
        } elseif($goodsid){
            $sql = "select sn,attributeStr,id from `".SQLPRE."goods_additional` where goodsId=$goodsid";
            $arr22 = $m->getall($sql);
            foreach ($arr22 as $key => $value) {
                $result[$key]['sn'] = $value['sn'];
                $result[$key]['attributeStr'] = $value['attributeStr'];
                $result[$key]['list'] = $list;
            }
        } else {
            $result[1]['sn'] = $_SESSION['goodssn'];
            $result[1]['attributeStr'] = '';
            $result[1]['list'] = $list;
        }
        echo json_encode($result);
        exit();
    }

    /**
     * 商品添加中的 选择规格属性
     */
    public function selectspecattr() {
        $m = new \core\model();
        $result = $m->getall("select * from " . SQLPRE . "goods_spec where status=1");
        if (isset($_POST['_submit_'])) {
            $arr = $_POST['selectattr'];
            $sn = $_SESSION['goodssn'];
            if (!empty($arr)) {
                $d = null;
                foreach ($arr as $k => $value) {
                    preg_match_all('/([^,]*?)=1,/', $value, $matches);
                    if (!empty($matches[1])) {
                        $d[$k] = $matches[1];
                    }
                }

                $get_sn = get_goods_sn('create', 10);   //先获取10个编号临时用
                if (!empty($d)) {
                    //根据选择的单选属性算出有多少种规格组合
                    $p = new pController();
                    $d_ = $p->Permutation($d);
                    //统计个数
                    $j = 1;
                    foreach ($result as $key => $value) {
                        str_replace(array("\r\n", "\n", "\r"), "\n", $value['specValue']);
                        $t3_ = explode("\n", trim($value['specValue']));
                        $j = $j * count($t3_);
                    }
                    //如果没有选择所有属性，则加上All规格，加上All规格是为了设置其他选择的属性的价格和库存 。
                    $i = 0;
                    if ($j != count($d_)) {
//                            $var2 = $get_sn[$i];
//                            $i = 1;
//                            $spec2[$i]['sn'] = $var2;
//                            $spec2[$i]['attributeStr'] = '无规格';
//                            $spec2[$i]['attribute'] = NULL;
//                            $spec2[$i]['key'] = $i;
                    } else {
                            $i = 0;
                    }
                    //循环所有生成的规格加上编辑和属性字符串
                    $get_sn = get_goods_sn('create', count($d_));   //重新按照商品数量生成商品编号
                    foreach ($d_ as $value) {
                        $var1 = is_array($get_sn) ? $get_sn[$i] : $get_sn ; //避免出现一个的时候返回的不是数组
                        ++$i;
                        $spec2[$i]['attributeStr'] = $value;
                        $spec2[$i]['sn'] = $var1; //$sn . '_' . $i;
                        $spec2[$i]['attribute'] = serialize(explode(",", $value));
                        $spec2[$i]['key'] = $i;
                    }
                } else {
                    $var3 = $get_sn[$i];
                    $i = 1;
                    $spec2[$i]['sn'] = $var3;//$sn . '_' . $i;
                    $spec2[$i]['attributeStr'] = '无规格';
                    $spec2[$i]['attribute'] = NULL;
                    $spec2[$i]['key'] = $i;
                }
                
                //匹配等于1的(选择的规格)保存  begin
                $t2 = null;
                foreach ($arr as $k => $v) {
                    if (preg_match_all('/([^,]*?=1),/', $v, $m)) {
                        $t3 = null;
                        foreach ($m[0] as $k1 => $v1) {
                            $t3.=$v1;
                        }
                        $t2[$k] = $t3;
                    }
                }
                $_SESSION['goodsattr'] = $t2;
                //匹配等于1的(选择的规格)保存  end
                
                $_SESSION['goodsspec'] = $spec2;
                rsort($spec2);
                echo json_encode($spec2);
                exit();
            }
        } else {
            $this->assign('list', $result);
            $this->display('goods/selectspecattr');
        }
    }

    /**
     * 分类列表
     */
    public function categroyList() {
        $this->assign('categroy_list', $this->returnCateList());
        $this->display("goods/categroyList");
    }

    /**
     * 类型列表
     */
    public function typelist() {
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $_GET['p'] : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;


        $m = new \core\model();
        $sql = "select * from " . SQLPRE . "goods_type order by id limit $start,$listrows";
        $result = $m->_query($sql);


        //page 数据
        $count = $m->getone("select count(id) as count from " . SQLPRE . "goods_type");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));


        $this->assign('list', $result);
        $this->display('goods/typelist');
    }

    /**
     * 类型属性列表
     */
    public function typeAttrlist() {
        $id = $this->_get('id');
        $m = new \core\model();
        if ($id) {
            $sql = "select * from `" . SQLPRE . "goods_type` where id=$id";
            $result = $m->getrow($sql);
            $this->assign('info', $result);

            $sql = "select * from `" . SQLPRE . "goods_attr` where typeId=$id order by mainpar,id";
            $list = $m->getall($sql);
            $this->assign('list', $list);
            $this->display('goods/typeAttrlist');
        }
    }

    /**
     * 类型属性列表编辑
     */
    public function typeAttrlistEdit() {
        $id = $this->_get('id');
        $m = new \core\model();
        $attrid = $this->_get('attrid');
        if ($id) {

            $sql = "select * from `" . SQLPRE . "goods_type` where id=$id";
            $info = $m->getrow($sql);

            if (isset($_POST['_submit_'])) {
                $data['typeId'] = $id;
                $data['name'] = $this->_post('name');
                if (empty($attrid) or $attrid == false)
                    $data['mark'] = $this->_post('mark');
                $data['mothod'] = $this->_post('mothod');
                $data['attrValue'] = $this->_post('attrValue');
                $data['mainpar'] = $this->_post('mainpar');
                $result = null;
                if ($attrid) {
                    $result = $m->sData($data, 'goods_attr', 'u', "id=$attrid");
                } else {
                    $result = $m->sData($data, 'goods_attr');
                    //添加表字段 
                    $tablename = SQLPRE . 'goods_add' . $info['mark'];
                    $sql = "ALTER TABLE  `$tablename` ADD  `$data[mark]` VARCHAR( 254 ) NULL";
                    $m->query($sql);
                }

                if ($result) {
                    header('Location: ?goods=type&act=typeAttrlist&id=' . $id);
                    exit();
                }
            } else {
                $sql = "select * from `" . SQLPRE . "goods_attr` where id=$attrid";
                $attrdata = $m->getrow($sql);
                $this->assign('attrdata', $attrdata);
                $this->assign('info', $info);
                $this->display('goods/typeAttrlistEdit');
            }
        }
    }

    public function typeAttrlistDelete() {
        $id = $this->_get('id');
        $m = new \core\model();
        $attrid = $this->_get('attrid');
        if (!empty($id) and ! empty($attrid)) {
            //获取表名
            $sql = "select mark from `" . SQLPRE . "goods_type` where id=$id";
            $tablename_ = $m->getone($sql);
            $tablename = SQLPRE . 'goods_add' . $tablename_;
            //获取字段名
            $sql = "select mark from `" . SQLPRE . "goods_attr` where id=$attrid";
            $fieldName = $m->getone($sql);
            //删除字段
            $sql = "ALTER TABLE  `$tablename` DROP  `$fieldName`";
            $m->query($sql);
            //删除数据
            $sql = "delete from `" . SQLPRE . "goods_attr` where id=$attrid";
            $m->query($sql);
        }
        header('Location: ?goods=type&act=typeAttrlist&id=' . $id);
        exit();
    }

    /**
     * 类型添加/编辑
     */
    public function typeadd() {
        $m = new \core\model();
        $step = 1;
        //过滤step参数
        if (isset($_REQUEST['step'])) {
            $step = $_REQUEST['step'];
        }

        $this->assign('step', $step);
        //如果设置了清空所有保存的session数据
        if (isset($_GET['clear']) && $_GET['clear'] == 'all'):
            $_SESSION['typeadd_data'] = null;
        endif;

        //数据提交处理
        if (isset($_POST['_submit_']) || isset($_POST['_save_'])) {

            //第一步：
            if ($step == 1) {
                $data = null;
                $data['name'] = $m->g($this->_post('name'));
                $data['status'] = 1;
                if (array_search(intval($_POST['status']), array(0, 1, 2))) {
                    $data['status'] = intval($_POST['status']);
                }
                $data['virtual'] = intval($_POST['virtual']);

                //如果已经有了数据，重新获取数据并更新
                if (isset($_SESSION['typeadd_data']) && !empty($_SESSION['typeadd_data'])) {
                    $_SESSION['typeadd_data']['name'] = $data['name'];
                    $_SESSION['typeadd_data']['status'] = $data['status'];
                    $_SESSION['typeadd_data']['virtual'] = $data['virtual'];
                    $val = null;
                    $data['name'] = "'" . $data['name'] . "'";
                    foreach ($data as $key => $value) {
                        $val.="$key=$value,";
                    }
                    $val = substr($val, 0, -1);
                    $id = $_SESSION['typeadd_data']['id'];
                    $sql = "update " . SQLPRE . "goods_type set $val where id=$id";
                    $row = $m->query($sql);
                }
                //否则添加类型 
                else {
                    $data['mark'] = $m->g($this->_post('mark'));
                    $_SESSION['typeadd_data']['name'] = $data['name'];
                    $_SESSION['typeadd_data']['status'] = $data['status'];
                    $_SESSION['typeadd_data']['virtual'] = $data['virtual'];
                    $_SESSION['typeadd_data']['mark'] = $data['mark'];
                    $field = null;
                    $val = null;
                    $data['name'] = "'" . $data['name'] . "'";
                    $data['mark'] = "'" . $data['mark'] . "'";
                    foreach ($data as $key => $value) {
                        $field.="$key,";
                        $val.="$value,";
                    }
                    $field = substr($field, 0, -1);
                    $val = substr($val, 0, -1);
                    $sql = "insert into " . SQLPRE . "goods_type ($field) values ($val)";
                    $row = $m->query($sql);
                    $_SESSION['typeadd_data']['id'] = mysqli_insert_id($m->link);

//                            根据类型创建商品附加表，专门记录商品属性
                    $tablename = SQLPRE . 'goods_add' . $_SESSION['typeadd_data']['mark'];
                    $sql2 = null;
                    $sql2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "` (
                                            `goodsid` mediumint(15) unsigned NOT NULL default '0',
                                            `aid` mediumint(15) NULL,
                                            `sn` varchar(255) NULL
                                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment='" . $_SESSION['typeadd_data']['name'] . ' 属性记录表' . "'";
                    $m->query($sql2);
                }

                //如果点击了保存退出
                if (isset($_POST['_save_'])) {
                    $_SESSION['typeadd_data'] = null;
                    header("Location:?goods=type");
                    exit();
                } else {
                    header("Location:?goods=type&act=edit&step=2");
                    exit();
                }
            }

            //第二步：
            elseif ($step == 2) {
                //如果基本信息已经设置，并且不为空
                if (isset($_SESSION['typeadd_data']) && !empty($_SESSION['typeadd_data'])) {

                    //判断是添加属性数据提交，还是进入下一步
                    if (isset($_REQUEST['acttype']) && $_REQUEST['acttype'] == 1) {
                        $id = $_SESSION['typeadd_data']['id'];
                        $mark = $m->g($this->_post('mark'));

                        $data = null;
                        $data['name'] = "'" . $m->g($this->_post('name')) . "'";
                        $data['mothod'] = 1;
                        if (array_search(intval($_POST['mothod']), array(0, 1, 2, 3))) {
                            $data['mothod'] = intval($_POST['mothod']);
                        }
                        $data['attrValue'] = "'" . $this->_post('attrValue') . "\n'";
                        $data['typeId'] = $_SESSION['typeadd_data']['id'];

                        //                    $data['attrValue'] = "'".$m->escapeString(str_replace(array("\r\n", "\n", "\r"), "\n", $data['attrValue']))."'";
                        $field = null;
                        $val = null;

                        //根据ID判断是添加还是修改
                        if (isset($_GET['id']) && !empty($_GET['id'])) {
                            $id = $_GET['id'];
                            foreach ($data as $key => $value) {
                                $val.="$key=$value,";
                            }
                            $field = substr($field, 0, -1);
                            $val = substr($val, 0, -1);
                            $sql = "update " . SQLPRE . "goods_attr set $val where id=$id";
                            $row = $m->query($sql);
                        } else {
                            $data['mark'] = "'" . $mark . "'";
                            foreach ($data as $key => $value) {
                                $field.="$key,";
                                $val.="$value,";
                            }
                            $field = substr($field, 0, -1);
                            $val = substr($val, 0, -1);
                            $sql = "insert into " . SQLPRE . "goods_attr ($field) values ($val)";
                            $row = $m->query($sql);

                            //添加表字段 
                            $tablename = SQLPRE . 'goods_add' . $_SESSION['typeadd_data']['mark'];
                            $sql = "ALTER TABLE  `$tablename` ADD  `$mark` VARCHAR( 254 ) NULL";
                            $m->query($sql);
                        }
                        header("Location:?goods=type&act=edit&step=2");
                        exit();
                    }
                } else {
                    header("Location:?goods=type&act=add");
                    exit();
                }
            }
        }

        //页面显示处理
        else {
            //如果是编辑类型
            if ($step == 1 && !empty($_GET['id'])) {
                $id = $_GET['id'];
                $result = $m->getrow("select * from " . SQLPRE . "goods_type where id=$id");
                $_SESSION['typeadd_data'] = $result;
            }
            //如果进入第一步，并且之前已经添加过类型信息。
            if ($step == 1 && !empty($_SESSION['typeadd_data']))
                $this->assign('info', $_SESSION['typeadd_data']);
            //如果进入到第二步或者第三步，没有添加和修改的类型ID则跳回到第一步
            if (($step == 2 || $step == 3) && empty($_SESSION['typeadd_data']))
                header("Location:?goods=type&act=add");
            //如果进入到第二步
            if ($step == 2 && !empty($_SESSION['typeadd_data'])) {
                //读取已经添加的属性
                $result = $m->getall("select * from " . SQLPRE . "goods_attr where typeId=" . $_SESSION['typeadd_data']['id']);
                $this->assign('list', $result);
            }
            if (($step == 4)) {
                $_SESSION['typeadd_data'] = null;
                header("Location:?goods=type");
            }
            $this->display('goods/typeadd');
        }
    }

    /**
     * 删除属性
     */
    public function attrdelete() {
        $id = intval($_REQUEST['id']);
        if (!empty($id)) {
            $m = new \core\model();
            $sql = "delete from " . SQLPRE . "goods_attr where id = $id";
            $result = $m->query($sql);
            if ($result) {
                header("Location:?goods=type&act=edit&step=2");
                exit();
            }
        } else {
            msg('', '参数错误！', '?goods=type&act=edit&step=2', '商品管理／类型', 5);
        }
    }

    /**
     * 删除类型
     */
    public function typedelete() {
        $id = intval($_REQUEST['id']);
        if (!empty($id)) {
            $m = new \core\model();
            //step 1 删除后设置该分类的商品肯定分出错，所以强行设置为其他
            $m->sData(array('catId'=>33,'typeId'=>44), "goods", "u", "typeId=$id");
            $m->sData(array('typeId'=>44), "category", "u", "typeId=$id");
            //step 2 删除 与类型关联 的属性
            $sql = "delete from " . SQLPRE . "goods_attr where typeId = $id";
            $result = $m->query($sql);
            $result = null;
            //step 3 删除附加表
            $table_ = $m->getone('select mark from ' . SQLPRE . "goods_type where id=$id");
            $tablename = SQLPRE . 'goods_add' . $table_;
            $sql = 'drop table `' . $tablename . '`';
            $result = $m->query($sql);
            //step 4 删除 类型
            $sql = "delete from " . SQLPRE . "goods_type where id = $id";
            $result = $m->query($sql);
            if ($result) {
//                msg('类型已经删除','','?goods=type','商品管理／类型', 3);
                header("Location: ?goods=type");
                exit();
            }
        } else {
            msg('', '参数错误！', '?goods=type', '商品管理／类型', 5);
        }
    }

    /**
     * 品牌列表
     */
    public function brandlist() {
        $m = new \core\model();
        $sql = "select * from " . SQLPRE . "goods_brand";
        $result = $m->getall($sql);
        $this->assign('list', $result);
        $this->display('goods/brandlist');
    }

    /**
     * 添加品牌
     */
    public function brandadd() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data = null;
            $data['name'] = "'" . $m->g($this->_post('name')) . "'";
            $link = $m->g($this->_post('link'));
            if (strstr($link, 'http')) {
                $data['link'] = "'" . $link . "'";
            } else {
                $data['link'] = "'" . 'http://' . $link . "'";
            }

            //如果在页面上删除了logo(imgfile表单)，logo将默认为空，达到删除logo的效果
            $data['logo'] = "''";
            if (isset($_POST['imgfile'])) {
                $data['logo'] = "'" . $_POST['imgfile'] . "'";
            }
            $data['status'] = 1;
            if (array_search(intval($_POST['status']), array(0, 1, 2))) {
                $data['status'] = intval($_POST['status']);
            }

            $field = null;
            $val = null;
            foreach ($data as $key => $value) {
                $field.="$key,";
                $val.="$value,";
            }
            $field = substr($field, 0, -1);
            $val = substr($val, 0, -1);
            $sql = "insert into " . SQLPRE . "goods_brand ($field) values ($val)";
            $result = $m->query($sql);
            if ($result) {
                header("Location:?goods=brand");
                exit();
            }
        } else {
            $this->display('goods/brandadd');
        }
    }

    /**
     * 品牌编辑
     */
    public function brandedit() {
        $id = intval($_REQUEST['id']);
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data = null;
            $data['name'] = "'" . $m->g($this->_post('name')) . "'";
            $link = $m->g($this->_post('link'));
            if (strstr($link, 'http')) {
                $data['link'] = "'" . $link . "'";
            } else {
                $data['link'] = "'" . 'http://' . $link . "'";
            }
            //如果在页面上删除了logo(imgfile表单)，logo将默认为空，达到删除logo的效果
            $data['logo'] = "''";
            if (isset($_POST['imgfile'])) {
                $data['logo'] = "'" . $_POST['imgfile'] . "'";
            }
            $data['status'] = 1;
            if (array_search(intval($_POST['status']), array(0, 1, 2))) {
                $data['status'] = intval($_POST['status']);
            }

            $val = null;
            foreach ($data as $key => $value) {
                $val.="$key=$value,";
            }
            $val = substr($val, 0, -1);
            $sql = "update " . SQLPRE . "goods_brand set $val where id=$id";

            $result = $m->query($sql);
            if ($result) {
                header("Location:?goods=brand");
                exit();
            }
        } else {
            $result = $m->_query("select * from " . SQLPRE . "goods_brand where id=$id");
            $this->assign('info', $result[0]);
            $this->display('goods/brandadd');
        }
    }

    /**
     * 删除属性
     */
    public function branddelete() {
        $id = intval($_REQUEST['id']);
        if (!empty($id)) {
            $m = new \core\model();
            //删除后设置该分类的商品肯定分出错，所以强行设置为其他
            $m->sData(array('catId'=>33,'typeId'=>44), "goods", "u", "catId=$id");
            //
            $sql = "delete from " . SQLPRE . "goods_brand where id = $id";
            $result = $m->query($sql);
            if ($result) {
                header("Location:?goods=brand");
                exit();
            }
        } else {
            msg('', '参数错误！', '?goods=brand', '商品管理／品牌', 5);
        }
    }

    /**
     * 编辑分类 
     */
    public function categroyEdit() {
        $m = new \core\model();
        if ($this->_get('id')){
            $id = intval($_REQUEST['id']);
        }
        
        if (isset($_POST['_submit_'])) {

            $data = null;
            $data['name'] = $m->g($this->_post('name'));
            $data['catdesc'] =  $m->g($_POST['catdesc']);
            $row = preg_match("/^[0-9]{1,11}$/", $_POST['sort']);
            if (!$row) {
                msg("排序字段错误！", '排序字段必须使用整数！', '', '添加分类', 5);
            } else {
                $data['sort'] = $_POST['sort'];
            }
            $data['status'] = isset($_POST['status'])?$_POST['status']:1;
            $data['typeId'] = intval($_POST['typeId']);
            $data['parentId'] = $_POST['parentId'] ? intval($_POST['parentId']) : 0;
            //根据父类判断level
            if ($data['parentId'] == 0) {
                $data['level'] = 1;
            } else {
                $level_ = $m->getone('select level from ' . SQLPRE . "category where id=" . $data['parentId']);
                $data['level'] = $level_ + 1;
            }

            //判断是修改还是添加
            if ($this->_get('id')) {
                $row = $m->sData($data, 'category','u',"id=$id");
            } else {
                $row = $m->sData($data, 'category');
            }
            
            // 删除所有商品分类的缓存  begin
            $redis = new \models\yredis();
            $arr = $redis->keys(REDIS_PRE . 'goodscate_info*');
            foreach ($arr as $v) {
                $redis->del($v);
            }
            // end
            
            header("Location: ?goods=categroyList");
            exit();
        } else {
            if ($this->_get('id')) {
                $result = $m->_query("select * from " . SQLPRE . "category where id=$id");
                $this->assign('info', $result[0]);
            }
            $level_ = intval($GLOBALS['config']['catlevel']) - 1;
            $level = $level_ <= 1 ? 1 : $level_;
            //上级分类
            $this->assign('categroy_list', $this->returnCateList(" where level <= $level"));
            $typelist = $m->getall('select * from ' . SQLPRE . 'goods_type where status=1');
            $this->assign('type_list', $typelist);
            $this->display("goods/categroyAdd");
        }
    }

    /**
     * 删除分类 
     */
    public function categroyDelete() {
        $id = $_GET['id'];
        if (!empty($id)) {
            $m = new \core\model();
            $sql = "delete from " . SQLPRE . "category where id=$id";
            $row = $m->query($sql);
            if ($row):
                header("Location: ?goods=categroyList");
                exit();
            endif;
        }else {
            msg('', '参数错误！', '?goods=categroyList', '分类列表', 5);
        }
    }

    /**
     * 规格列表
     */
    public function spec() {

        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;


        $m = new \core\model();
        $sql = "select * from " . SQLPRE . "goods_spec order by id limit $start,$listrows";
        $list = $m->getall($sql);


        //page 数据
        $count = $m->getone("select count(id) as count from " . SQLPRE . "goods_spec");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));

        $this->assign('list', $list);
        $this->display('goods/spec');
    }

    /**
     * 添加规格
     */
    public function specadd() {
        $this->assign('type', $this->_get('type'));
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data = null;
            $data['name'] = $this->_post('name');
            $data['specValue'] = $this->_post('specValue');
            $data['comments'] = $this->_post('comments');
            $data['status'] = intval($this->_post('status'));
            $row = $m->sData($data, 'goods_spec');
            if ($row) {
                header("Location: ?goods=spec");
                exit();
            }
        } else {
            $this->display("goods/spec");
        }
    }

    //编辑规格
    public function specedit() {
        $id = floatval($_GET['id']);
        $this->assign('type', $this->_get('type'));
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data = null;
            $data['name'] = $this->_post('name');
            $data['specValue'] = $this->_post('specValue');
            $data['comments'] = $this->_post('comments');
            $data['status'] = intval($this->_post('status'));
            $row = $m->sData($data, 'goods_spec', 'u', "id=$id");
            if ($row) {
                header("Location: ?goods=spec");
                exit();
            }
        } else {
            $this->assign('info', $m->getrow("select * from " . SQLPRE . "goods_spec where id=$id"));
            $this->display("goods/spec");
        }
    }

    //删除规格
    public function specdel() {
        $id = floatval($_GET['id']);
        if (!empty($id)) {
            $m = new \core\model();
            $sql = "delete from " . SQLPRE . "goods_spec where id=$id";
            $row = $m->query($sql);
            if ($row):
                header("Location: ?goods=spec");
                exit();
            endif;
        }else {
            msg('', '参数错误！', '?goods=spec', '规格列表', 5);
        }
    }

    /**
     * 设置商品上架下架
     */
    public function setonline() {

        $status = $this->_get('status');
        $m = new \core\model();
        if (isset($_REQUEST['goodslistcheck'])) {
            if (is_array($_REQUEST['goodslistcheck']) and ! empty($_REQUEST['goodslistcheck'])) {

                foreach ($_REQUEST['goodslistcheck'] as $key => $value) {
                    $sql = "update `" . SQLPRE . "goods_additional` set addStatus=$status where id=$value";
                    $m->query($sql);
                }
            }
        }
    }

    /**
     * 更新商品缓存
     */
    public function upgoodscache() {
        $redis = new \models\yredis();
        $uptime = $redis->get(REDIS_PRE . 'goodsuptime');
        if ($uptime) {
            $this->assign('uptime', $uptime);
        } else {
            $this->assign('uptime', null);
        }
        $this->display('goods/upgoodscache');
    }

    public function upgoodscache2() {
        echo '<p>1、正在更新…… </p>';
        up_goods_cache();
        echo '<p>2、[商品缓存] 更新完成</p>';
    }

    //############### 自定义数据 相关函数      ########################################## 
    
    
    /**
     * 自定义数据组
     */
    public function customData() {
        $m = new \core\model();
        $result = array();
        $sql = "select * from `" . SQLPRE . "customdata`";
        $result = $m->getall($sql);
        $this->assign('list', $result);
        $this->display('goods/customData');
    }

    /**
     * 自定义数据 添加/编辑
     */
    public function customDataEdit() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            unset($_POST['_submit_']);
            unset($_POST['_verifyKey_']);
            $data = $_POST;
            $res = null;
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $id = $_GET['id'];
                unset($data['mark']);
                $res = $m->sData($data, 'customdata', 'u', "id=$id");
            } else {
                $res = $m->sData($data, 'customdata');
            }
            if ($res) {
                header("Location: ?goods=customData");
            }
        } else {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $id = $_GET['id'];
                $sql = "select * from `" . SQLPRE . "customdata` where id=$id";
                $info = $m->getrow($sql);
                $this->assign('info', $info);
            }
            $sql = "select id,name,mark from `" . SQLPRE . 'models` where status=1';
            $result = $m->getall($sql);
            $this->assign('model', $result);
            $this->display('goods/customDataEdit');
        }
    }

    /**
     * 自定义数据组  数据编辑
     */
    public function customdata_dataedit() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $m = new \core\model();
        if ($id) {
            $info = null;
            $sql = "select * from `" . SQLPRE . "customdata` where id=$id";
            $info = $m->getrow($sql);
            $this->assign('info', $info);

            $list = array();
            $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
            $listrows = 15;
            $start = $p * $listrows - $listrows;
            $count = 0;
            //--如果自定义数据是 商品类型，则查找商品数据
            if ($info['tablename'] == 'goods') {
                $this->assign('mode', 1);
                $sql = "select cd.id as id_,ga.id,concat(g.name,' - ',ga.attributeStr) as title from `" . SQLPRE . "customdata_data` cd "
                        . " left join `" . SQLPRE . "goods_additional` ga on cd.dataid=ga.id "
                        . " left join " . SQLPRE . "goods g on ga.goodsId=g.id where cd.cid=$id limit $start,$listrows";
                $list = $m->getall($sql);
                $count = $m->getone("select count(id) as count from " . SQLPRE . "customdata_data where cid=$id");
            } else {
                //如果 不是商品类型，则查找文章数据
                $ta = $info['tablename'];
                $sql = "select cd.id as id_,a.id,a.title from `" . SQLPRE . "customdata_data` cd "
                        . " left join `" . SQLPRE . "$ta` a on cd.dataid=a.id where cd.cid=$id order by a.id desc limit $start,$listrows";
                $list = $m->getall($sql);
                //page 数据
                $count = $m->getone("select count(id) as count from `" . SQLPRE . "customdata_data` where cid=$id");
                $this->assign('mode', 2);
            }
            $this->assign('list', $list);
            $params = array(
                'total_rows' => $count,
                'now_page' => $p,
                'list_rows' => $listrows,
            );
            $page = new \lib\page($params);
            $this->assign('pageinfo', $page->show(4));
        }
        $this->display('goods/customdata_dataedit');
    }

    /**
     * 自定义数据组  保存添加的商品数据
     */
    public function customdata_callback1() {
        $m = new \core\model();
        $id = $this->_get('id');
        if (isset($_REQUEST['goodslistcheck']) and $id) {
            if (is_array($_REQUEST['goodslistcheck']) and ! empty($_REQUEST['goodslistcheck'])) {
                foreach ($_REQUEST['goodslistcheck'] as $key => $value) {
                    $data = array();
                    $data['cid'] = $id;
                    $data['dataid'] = $value;
                    $m->sData($data, 'customdata_data');
                }
                echo json_encode(array('status' => 1));
                exit();
            }
        }
        echo json_encode(array('status' => 0));
        exit();
    }

    /**
     * 自定义数据组  添加文章数据
     */
    public function customdata_SpecifiesArticle() {

        $m = new \core\model();
        $id = $this->_get('id');
        $ta = $this->_get('ta');

        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 7;
        $pagesize = $this->_get('pagesize');
        if ($pagesize) {
            $listrows = $pagesize;
        }
        $start = $p * $listrows - $listrows;

        $list = $m->getall("select id,title from `" . SQLPRE . "$ta` order by id desc limit $start,$listrows");
        //page 数据
        $count = $m->getone("select count(id) as count from `" . SQLPRE . "$ta`");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(3);
        if (judgeAjaxRequest() && !$_GET['dis']) {  //dis变量 用来判断 是输出 json数据 还是显示 页面
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign("list", $list);
            $this->display('goods/customdata_SpecifiesArticle');
        }
    }

    /**
     * 自定义数据组  删除添加的数据
     */
    public function customdata_delete2() {
        $m = new \core\model();
        $id = $this->_get('id');
        if ($id) {
            $sql = "delete from `" . SQLPRE . 'customdata_data` where id=' . $id;
            $m->query($sql);
            echo json_encode(array('status' => 1));
            exit();
        }
        echo json_encode(array('status' => 0));
        exit();
    }
    
    /**
     * 删除自定义数据组。。
     */
    public function goodscustomDatadelete() {
        $d = $_REQUEST['goodslistcheck'];
        if($d && is_array($d) ){
            $m = new \core\model();
            foreach ($d as $id) {
                $sql = "delete from `".SQLPRE."customdata_data` where cid=$id";
                $m->query($sql);
                $sql = "delete from `".SQLPRE."customdata` where id=$id";
                $m->query($sql);
            }
        }
    }

    /**
     * 图片管理
     */
    public function imgmanage() {
        
        $business_no = dechex($_SESSION['business_no']);
        define('IMAGE_PATH_EXT', IMAGE_PATH.$business_no.DIRECTORY_SEPARATOR);
        define('IMAGE_URL_EXT', IMAGE_URL.$business_no.DIRECTORY_SEPARATOR);

        $name = isset($_GET['name']) ? $_GET['name'] : null;
        $dirData = array();
        if ($name) {
            $result = scandir(IMAGE_PATH_EXT . $_GET['name'] . DIRECTORY_SEPARATOR);
            $dirData = array();
            foreach ($result as $key => $value) {
                if ($value != '.' && $value != '..' && $value != ".svn") {
                    if (strstr($value, 'thumb')) {
                        $dirData[] = $value;
                    }
                }
            }
        } else {
            $result = scandir(IMAGE_PATH_EXT);
            $dirData = array();
            foreach ($result as $key => $value) {
                if (is_dir(IMAGE_PATH_EXT . $value) && $value != '.' && $value != '..' && $value != ".svn") {
                    $dirData[] = $value;
                }
            }
        }
        $this->assign("dirdata", $dirData);
        $this->display('goods/imgmanage');
    }

    /**
     * 图片管理
     */
    public function all_imgmanage() {
        $dir = isset($_GET['dir']) ? $_GET['dir'] : null;
        $name = isset($_GET['name']) ? $_GET['name'] : null;
        if($dir && !$name ){
            $result = scandir(IMAGE_PATH.$dir.DIRECTORY_SEPARATOR);
            $dirData = array();
            foreach ($result as $key => $value) {
                if (is_dir(IMAGE_PATH.$dir.DIRECTORY_SEPARATOR . $value) && $value != '.' && $value != '..' && $value != ".svn") {
                    $dirData[] = $value;
                }
            }
        }elseif ($name) {
            $result = scandir(IMAGE_PATH.$dir.DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR);
            $dirData = array();
            foreach ($result as $key => $value) {
                if ($value != '.' && $value != '..' && $value != ".svn") {
                    if (strstr($value, 'thumb')) {
                        $dirData[] = $value;
                    }
                }
            }
        } else {
            $result = scandir(IMAGE_PATH);
            $dirData = array();
            foreach ($result as $key => $value) {
                if (is_dir(IMAGE_PATH . $value) && $value != '.' && $value != '..' && $value != ".svn") {
                    $dirData[] = $value;
                }
            }
        }
        $this->assign("dirdata", $dirData);
        $this->display('goods/allimgmanage');
    }
    
    
    /**
     * 删除图片  
     */
    public function unlingimage() {
        
        $m = new \core\model();
        $dirname = $_GET['dirname'];
        //从规格删除商品(删除选中的规格)
        if (isset($_REQUEST['imglistdata'])) {
//            echo json_encode($_REQUEST['goodslistcheck']); exit();
            if (is_array($_REQUEST['imglistdata']) and ! empty($_REQUEST['imglistdata'])) {
                foreach ($_REQUEST['imglistdata'] as $k => $v) {
                    $n1 = str_replace('thumb_','400_',$v);
                    $n2 = str_replace('thumb_','800_',$v);
                    $n3 = str_replace('thumb_','',$v);
                    
                    @unlink(IMAGE_PATH . $dirname . DIRECTORY_SEPARATOR.$n1);
                    @unlink(IMAGE_PATH . $dirname . DIRECTORY_SEPARATOR.$n2);
                    @unlink(IMAGE_PATH . $dirname . DIRECTORY_SEPARATOR.$n3);
                    @unlink(IMAGE_PATH . $dirname . DIRECTORY_SEPARATOR.$v);
                }
            }
        }
        
    }
    
    /**
     * 滚动图片设置 
     */
    public function rollimage() {
        $m = new \core\model();
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 7;
        $start = $p * $listrows - $listrows;
        $list = $m->getall("select * from `" . SQLPRE . "rollimages` order by id desc limit $start,$listrows");
        //page 数据
        $count = $m->getone("select count(id) as count from `" . SQLPRE . "rollimages`");
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(4);
        $this->assign('pageinfo', $pageinfo);
        $this->assign("list", $list);
        $this->display('goods/rollimage');
    }
    
    
    public function rollimageedit() {
        $id = $this->_get('id');
        $m = new \core\model();
        if( isset($_POST['_submit_']) ){
            $data['title'] = $this->_post('title');
            if($id){
                $m->sData($data, 'rollimages','u',"id=$id");
            }else{
                $data['mark'] = $this->_post('mark');
                $m->sData($data, 'rollimages');
            }
            header("Location: ?goods=rollimage");
            exit();
        }else{
            $sql = "select * from `".SQLPRE."rollimages` where id=$id";
            $info = $m->getrow($sql);
            $this->assign('info', $info);
            $this->display('goods/rollimageedit');
        }
    }
    
    /**
     * 滚动图片数据编辑
     */
    public function rollimagedataedit() {
        $id = $this->_get('id');
        $m = new \core\model();
        $sql = "select * from `" . SQLPRE . "rollimages` where id=$id";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        $sql = "select * from `".SQLPRE."rollimages_img` where rid=$id";
        $list = $m->getall($sql);
        $this->assign('list', $list);
        $this->display('goods/rollimagedataedit');
    }
    
    /**
     * 插入图片 
     */
    public function rollimaginsertimg() {
        $id = $this->_get('id');
        $m = new \core\model();
        if( isset($_POST['_submit_']) ){
            $data['link'] = $_POST['link'];
            $data['img'] = $_POST['imgfile'];
            $data['rid'] = $id;
            $m->sData($data, 'rollimages_img');
            header("Location: ?goods=rollimage&type=dataedit&id=$id");
            exit();
        }else{
            $sql = "select * from `" . SQLPRE . "rollimages` where id=$id";
            $info = $m->getrow($sql);
            $this->assign('info', $info);
            $this->display('goods/rollimaginsertimg');
        }
    }
    
    /**
     * 删除滚动图片组
     */
    public function rollimagdelete() {
        $d = $_REQUEST['goodslistcheck'];
        if($d && is_array($d) ){
            $m = new \core\model();
            foreach ($d as $id) {
                $sql = "delete from `".SQLPRE."rollimages_img` where rid=$id";
                $m->query($sql);
                $sql = "delete from `".SQLPRE."rollimages` where id=$id";
                $m->query($sql);
            }
        }
    }
    
    /**
     * 
     */
    public function rollimagdelete2() {
        $id = $this->_get('id');
        $m = new \core\model();
        $sql = "delete from `" . SQLPRE . "rollimages_img` where id=$id";
        $m->query($sql);
        echo json_encode(array('status'=>1));
        exit();
    }
    
    /**
     * 检查称重条码是不是已经被占用了
     */
    public function check_weighing_code() {
        $val = $this->_get('val');
        $val = $_GET['val'];
        //如果是修改数据，则需要把自身排除，否则无法修改
        $id = $_GET['id'] ? ' and id<>'.$_GET['id']:null;
        $m = new \core\model();
        $sql = "select weighing_code from `".SQLPRE."goods` where weighing_code='$val' $id";
        $res = $m->getone($sql);
        if( $res ){
            echo 0; exit();
        }else{
            echo 1; exit();
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

    /**
     * 返回经过处理无限级分类之后的分类树结构
     * @return array
     */
    public function returnCateList($where = null) {
        $m = new \core\model();
        $row = $m->getall("select a.*,b.name as typeName from " . SQLPRE . "category a left join " . SQLPRE
                . "goods_type b on a.typeId=b.id $where");
        if (is_array($row) and count($row) > 0) {
            $row_ = $this->RecursionData(0, $row);
            if (empty($row_) || count($row_) <= 0) {
                return false;
            }
            $data = array();
            $this->printData($row_, '', $data);
            return $data;
        } else {
            return false;
        }
    }

    /**
     * 递归处理数据
     * @param type $id
     * @param type $data
     * @return type
     */
    public function RecursionData($id = 0, $data) {
        $arr = null;
        foreach ($data as $key => $value) {
            if ($value['parentId'] == $id) {
                $arr[$value['id']] = $value;
                $arr[$value['id']]['son'] = self::RecursionData($value['id'], $data);
            }
        }
        return $arr;
    }

    /**
     * 递归打印名称
     * @param type $data
     * @param string $str
     * @param type $arr
     */
    public function printData($data, $i = 0, &$arr = array()) {
        $h = 1 * $i;
        $str = '';
        for ($j = 0; $j < $h; $j++) {
            $str.='　　';
        }
        foreach ($data as $key => $value) {
            $value['name'] = '□' . $str . '&nbsp;' . $value['name'];
            $arr[] = $value;
            if (is_array($value['son'])) {
                self::printData($value['son'], ++$h, $arr);
                $h = $i = 0;
            }
            unset($value['son']);
        }
    }

    public function exemption() {
        $t = $this->_get('t');
        switch ($t) {
            case 'ajaxfield':
                $value = $this->_get('value');
                $m = new \core\model();
                $sql = "select id,mark from `" . SQLPRE . "customdata` where mark='$value'";
                $res = $m->getrow($sql);
                if (!empty($res)) {
                    echo 0;
                    exit();
                } else {
                    echo 1;
                    exit();
                }
                break;
            case 'ajaxfield2':
                $value = $this->_get('value');
                $m = new \core\model();
                $sql = "select id,mark from `" . SQLPRE . "rollimages` where mark='$value'";
                $res = $m->getrow($sql);
                if (!empty($res)) {
                    echo 0;
                    exit();
                } else {
                    echo 1;
                    exit();
                }
                break;
            default:
                break;
        }
        header("Location: " . __ROOT__);
    }

}
