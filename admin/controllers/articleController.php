<?php

/**
 * Description of articleController
 *
 * @author kyle
 */

namespace controllers;

class articleController extends adminController {

    //频道模型的前缀，为了避免与其它表或菜单发生冲突.
    private $channelPrefix = 'channel-';

    public function addchannel() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data['name'] = $m->g($this->_post("name"));
            $data['status'] = intval($_POST['status']);

            //判断 是更新 还是添加 
            if ($this->_get("id")) {
                $id = $this->_get("id");
                $val = null;
                foreach ($data as $key => $value) {
                    $val.="$key='$value',";
                }
                $val = substr($val, 0, -1);
                $sql = "update  " . $m->prefix . "models set $val where id=$id";
                $result = $m->query($sql);
                header("Location: ?article=channel");
                exit();
            } else {

                //如果是添加数据才添加 mark
                $data['mark'] = $this->channelPrefix . $m->g($this->_post("mark"));

                //如果是添加数据 , 添加一个菜单
                $data2['url'] = "article=$data[mark]";
                $data2['name'] = $data['name'];
                $data2['sort'] = 137;  //排序
                $data2['g'] = 0;  //分组ID
                $parentid = $m->getone("select id from `" . SQLPRE . "admin_menu` where name='文章内容'");
                $data2['parentId'] = $parentid;  //上级菜单 ID
                $mtagId = $m->getone("select id from `" . SQLPRE . "admin_mtag` where name='文章内容'");
                $data2['mtagId'] = $mtagId;  //权限标签ID
                $menuid = $m->sData($data2, "admin_menu");
                //新添加的菜单ID加入models 表
                $data['menuId'] = $menuid;
                //

                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    if (is_int($value) || is_float($value)) {
                        $val.="$value,";
                    } else {
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into " . $m->prefix . "models ($field) values ($val)";
                $row = $m->query($sql);

                if ($row) {
                    $tablename = $m->prefix . $data['mark'];
                    $sql = "CREATE TABLE IF NOT EXISTS `$tablename` (
                                        `id` int(11) unsigned NOT NULL auto_increment,
                                        `cid` int(11) NOT NULL comment '栏目id',
                                        `cid2` int(11) NOT NULL comment '副栏目id',
                                        `title` varchar(255) NOT NULL comment '标题', 
                                        `picname` varchar(255) NOT NULL comment '缩略图', 
                                        `source` varchar(255) NOT NULL comment '来源', 
                                        `modifytime` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL COMMENT '修改时间',
                                        `addtime` TIMESTAMP NOT NULL comment '创建时间',
                                        `hits` MEDIUMINT(15) unsigned NOT NULL default '1' comment '点击率',
                                        `comments` MEDIUMINT(15) unsigned NOT NULL default '1' comment '评论次数',
                                        `sort` int(10) NOT NULL default '999' comment '权重 ，越小越靠前',
                                        `user` int(11) NOT NULL comment '创建者',
                                        `arcrank` varchar(10) NOT NULL default 'o' comment '阅读权限,o为全部开放,c为待审核',
                                        `isdelete` tinyint(2) unsigned NOT NULL default '2' comment '是否删除，1为已删除',

                                        PRIMARY 
                                        KEY (`id`),
                                        UNIQUE KEY `id` (`id`)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='" . $data['name'] . "'";
                    $m->query($sql);

                    $sql2 = "CREATE TABLE IF NOT EXISTS `" . $tablename . "_field` (
                                            `aid` int(11) unsigned NOT NULL default '0',
                                            PRIMARY KEY  (`aid`)
                                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 comment='" . $data['name'] . '扩展字段' . "'";
                    $m->query($sql2);

                    header("Location: ?article=channel");
                    exit();
                }
            }
        } else {
            $id = $this->_get("id");
            if ($id) {
                $result = $m->getrow("select * from " . $m->prefix . "models where id=$id");
                $this->assign('info', $result);
            }
            $this->display('article/addchannel');
        }
    }

    public function channellist() {
        $m = new \core\model();
        $result = $m->getall("select * from " . $m->prefix . "models where sys=2");
        $this->assign("list", $result);
        $this->display('article/channellist');
    }

    public function deletechannel() {
        $id = $this->_get("id");
        if ($id) {
            $m = new \core\model();
            //删除表格
            $row = $m->getrow("select * from " . $m->prefix . "models where id=$id");
            //删除菜单
            if ($row['menuId'] && $row['menuId'] != '0') {
                $m->query("delete from `" . SQLPRE . "admin_menu` where id=$row[menuId]");
            }
            //删除添加的数据表
            $m->query('drop table `' . $m->prefix . $row['mark'] . '`');
            $m->query('drop table `' . $m->prefix . $row['mark'] . '_field`');
            //删除models表的数据
            $result = $m->query("delete from " . $m->prefix . "models where id=$id");
            if ($result) {
                header("Location: ?article=channel");
                exit();
            }
        }
    }

    public function fieldlist() {
        $m = new \core\model();
        $result = $m->getall('select a.*,b.name as mname from ' . $m->prefix . "fields as a inner join " . $m->prefix . 'models as b on a.model=b.id where b.sys=2');
        $this->assign('list', $result);
        $this->display('article/fieldlist');
    }

    /**
     * 添加 编辑 字段 
     * @return boolean
     */
    public function addfield() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {

            $data['name'] = $m->g($this->_post("name"));
            $data['description'] = $m->g($this->_post("description"));
            $data['status'] = intval($this->_post("status"));

            //判断 是更新 还是添加 
            if ($this->_get("id")) {
                $id = $this->_get("id");
                $val = null;
                foreach ($data as $key => $value) {
                    $val.="$key='$value',";
                }
                $val = substr($val, 0, -1);
                $sql = "update  " . $m->prefix . "fields set $val where id=$id";
                $result = $m->query($sql);
                header("Location: ?article=field");
                exit();
            } else {
                $data['mark'] = $m->g($this->_post("mark"));
                $data['type'] = $this->_post("type");
                $data['val'] = $m->g($this->_post("val"));
                $data['model'] = intval($this->_post("model"));

                $isField = array('11', 'id', 'cid', 'cid2', 'title', 'shorttitle', 'picname', 'source', 'modifytime', 'addtime', 'hits', 'sort', 'keywords', 'description', 'user', 'author', 'arcrank', 'isdelete');
                $isArray = array_search($data['mark'], $isField);
                if ($isArray) {
                    msg('添加字段错误！', '标识ID已经存在，请更换标识ID', '?article=field&type=add', '字段 编辑', 5);
                    return false;
                }  //如果字段重复了直接返回失败

                if ($data['type'] != 'radio' or $data['type'] != 'checkbox' or $data['type'] != 'select') {
                    unset($data['value']);
                }

                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    if (is_int($value) || is_float($value)) {
                        $val.="$value,";
                    } else {
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into " . $m->prefix . "fields ($field) values ($val)";
                $row = $m->query($sql);

                if ($row) {
                    $field = $data['mark'];
                    $result = $m->getrow('select id,mark from ' . $m->prefix . 'models where id=\'' . $data['model'] . '\'');
                    $table = $m->prefix . $result['mark'] . '_field';
                    switch ($data['type']) {
                        case 'varchar':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` VARCHAR( 254 ) NULL";
                            break;
                        case 'text':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TEXT NULL";
                            break;
                        case 'html':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` LONGTEXT NULL";
                            break;
                        case 'int':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` INT( 11 ) NULL";
                            break;
                        case 'float':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` FLOAT NULL";  //TINYINT( 2 ) 
                            break;
                        case 'datetime':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TIMESTAMP NULL ";
                            break;
                        case 'image':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TEXT NULL";
                            break;
                        case 'thumb':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` VARCHAR( 254 ) NULL";
                            break;
                        case 'file':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` VARCHAR( 254 ) NULL";
                            break;
                        case 'radio':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TINYINT( 2 )  NOT NULL DEFAULT  '1'";  //TINYINT( 2 ) 
                            break;
                        case 'checkbox':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TINYINT( 2 )  NOT NULL DEFAULT  '1'";  //TINYINT( 2 ) 
                            break;
                        case 'select':
                            $sql = "ALTER TABLE  `$table` ADD  `$field` TINYINT( 2 )  NOT NULL DEFAULT  '1'";  //TINYINT( 2 ) 
                            break;
                        default:
                            break;
                    }
                    $m->query($sql);
                    header('Location:?article=field');
                    exit();
                }
            }
        } else {
            //
            if ($this->_get("id")) {
                $id = $this->_get('id');
                $row = $m->getrow('select * from ' . $m->prefix . "fields where id=$id");
                $this->assign('info', $row);
                //频道模型
                $result = $m->getall("select * from " . $m->prefix . "models where status=1 and id=" . $row['model']);
                $this->assign('list', $result);
            } else {
                //频道模型
                $result = $m->getall("select * from " . $m->prefix . "models where status=1");
                $this->assign('list', $result);
            }
            $this->display('article/addfield');
        }
    }

    /**
     * 删除文章
     */
    public function deleteArticle() {
        $m = new \core\model();
        $act = $model = $this->_get('article');
        //如果选择的是普通文章的菜单 ，则从配置文件中读取普通文章的表名
        if ($model == 'list'):
            $model = cfg('ARTICLE_MODEL_NAME');
        endif;
        $id = $this->_get('id');
        $sql = "delete from `" . $m->prefix . "$model` where id=$id";
        $m->query($sql);
        $ext_ = $model . '_field';
        $sql = "delete from `" . $m->prefix . "$ext_` where aid=$id";
        $m->query($sql);
        header("Location: ?article=$act");
        exit();
    }

    /**
     * 删除 字段
     */
    public function deletefield() {
        $id = $this->_get('id');
        if ($id) {
            $m = new \core\model();
            $row = $m->getrow('select a.*,b.mark as mmark from ' . $m->prefix . "fields as a inner join " . $m->prefix . "models as b on a.model=b.id where a.id=$id");
            $field = $row['mark'];
            $table = $m->prefix . $row['mmark'] . '_field';
            $result = $m->getrow("select $field from `$table` limit 1");
            //如果有数据则发出警告。
//            if(!empty($result)){
//                msg('删除错误！', '该字段已经添加了数据，如果删除会造成数据丢失，请先核对数据再删除。', '?article=field','字段列表', 25);
//            }else{
            $m->query("delete from " . $m->prefix . "fields where id=$id");
            $sql = "ALTER TABLE  `$table` DROP  `$field`";
            $m->query($sql);
            header('Location: ?article=field');
            exit();
//            }
        }
    }

    public function columnlist() {
        $m = new \core\model();
        $result = $m->getall("select *,pid as parentId from " . $m->prefix . "column");
        $action = new pController();
        $list_ = $action->RecursionData(0, $result);
        $list = array();
        $action->printData($list_, 0, $list);
        $this->assign('list', $list);
        $this->display('article/columnlist');
    }

    public function addcolumn() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data['name'] = $m->g($this->_post('name'));
            $data['pid'] = intval($this->_post('pid'));
            $data['type'] = intval($this->_post('type'));
            $data['model'] = intval($this->_post('model'));
            $data['location'] = $m->g($this->_post('location')); //
            $data['goodstype'] = intval($this->_post('goodstype'));
            $data['goodsactivity'] = $this->_post('goodsactivity');
            $data['url'] = $m->g($this->_post('url')); //
            $data['description'] = $m->g($this->_post('description'));
            $data['issend'] = 0;//intval($this->_post('issend'));
            $data['status'] = intval($this->_post('status'));
            $data['content'] = $_POST['content'];
            $data['tplContent'] = $m->g($this->_post('tplContent'));
            $data['tplList'] = $m->g($this->_post('tplList'));
            $data['tplArticle'] = $m->g($this->_post('tplArticle'));
            $data['seotitle'] = $m->g($this->_post('seotitle'));
            $data['keyword'] = $m->g($this->_post('keyword'));

            if ($this->_get('id')) {
                $id = $this->_get("id");
                $val = null;
                foreach ($data as $key => $value) {
                    $val.="$key='$value',";
                }
                $val = substr($val, 0, -1);
                $sql = "update  " . $m->prefix . "column set $val where id=$id";
                $result = $m->query($sql);
                if ($result) {
                    header('Location: ?article=column');
                    exit();
                }
            } else {
                $data['mark'] = $m->g($this->_post('mark'));

                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    if (is_int($value) || is_float($value)) {
                        $val.="$value,";
                    } else {
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into " . $m->prefix . "column ($field) values ($val)";
                $row = $m->query($sql);
                if ($row) {
                    if ($data['type'] == 1) {
                        $model = $data['model'];
                        $result = $m->getrow('select id,name,mark,menuId from ' . $m->prefix . "models where id=$model");
                        if ($result['menuId'] == 0) {
                            $sql = "insert into " . $m->prefix . "admin_menu (url,name,parentId,mtagId,sort,g) values ('article=" . $result['mark'] . "','" . $result['name'] . "',8,10,137,0)";
                            $m->query($sql);
                            $id = mysql_insert_id();
                            $m->query('update ' . $m->prefix . "models set menuId=$id where id=$model");
                        }
                    }

                    header('Location: ?article=column');
                    exit();
                }
            }
        } else {
            //商品分类
            $p = new pController();
            $result = $p->returnCateList();
            $this->assign('glist', $result);
            //促销活动
            $sql = "select id,name from `" . $m->prefix . "activity`";
            $galist = $m->getall($sql);
            $this->assign('galist', $galist);
            //如果是修改数据
            if ($this->_get('id')) {
                $id = $this->_get('id');
                $row = $m->getrow('select * from ' . $m->prefix . "column where id=$id");
                $this->assign('info', $row);

                //栏目
                $result = $m->getall("select id,name,pid as parentId from " . $m->prefix . "column where id <> $id and  status=1");
                $result2 = array();
                $action = new pController();
                $list_ = $action->RecursionData(0, $result);
                $action->printData($list_, 0, $result2);
                $this->assign('clist', $result2);
                
                //频道模型
                $result = $m->getall("select * from " . $m->prefix . "models where status=1 and id=" . $row['model']);
                $this->assign('mlist', $result);
            } else {
                //栏目
                $result = $m->getall("select id,name,pid as parentId from " . $m->prefix . "column where status=1");
                $result2 = array();
                $action = new pController();
                $list_ = $action->RecursionData(0, $result);
                $action->printData($list_, 0, $result2);
                $this->assign('clist', $result2);
                
                //频道模型
                $result = $m->getall("select * from " . $m->prefix . "models where status=1");
                $this->assign('mlist', $result);
            }
            $this->display('article/addcolumn');
        }
    }

    public function deletecolumn() {
        $id = $this->_get("id");
        if ($id) {
            $m = new \core\model();
            //获取栏目的基本信息
            $row = $m->getrow('select * from ' . $m->prefix . "column where id=$id");
            //删除栏目 
            $result = $m->query('delete from ' . $m->prefix . "column where id=$id");
            //如果 删除 成功
            if ($result) {
//                $result = null;
//                $result = $m->getrow("select id,model from " . $m->prefix . "column where model=" . $row['model'] . " group by model");
//                //如果与这个栏目关联的模块都已删除掉了，则删除与模块关联的菜单 并把models 里的ID改为0
//                if (empty($result)) {
//                    $row2 = $m->getrow('select id,menuId from ' . $m->prefix . "models where id=" . $row['model']);
//                    $m->query("delete from " . $m->prefix . "admin_menu where id=" . $row2['menuId']);
//                    $m->query('update ' . $m->prefix . "models set menuId=0 where id=" . $row2['id']);
//                }
            }
            header("Location: ?article=column");
            exit();
        }
    }

    //内容列表
    public function artlist() {
        $m = new \core\model();

        $model = $this->_get('article');
        //如果选择的是普通文章的菜单 ，则从配置文件中读取普通文章的表名
        if ($model == 'list'):
            $model = cfg('ARTICLE_MODEL_NAME');
        endif;
        $row = $m->getrow('select * from ' . $m->prefix . "models where mark='$model'");

        //状态为1 和 类型为栏目列表的栏目  start
        $channel = $m->getall('select  id,name,mark,pid as parentId from ' . $m->prefix . 'column where status=1 and type=1 and model=' . $row['id']);
        $action = new pController();
        if (count($channel) > 0) {
            $list_ = $action->RecursionData(0, $channel);
            $channel_ = array();
            $action->printData($list_, 0, $channel_);
            $this->assign('channelList', $channel_);
        }
        //  end

        if (!empty($row)) {

            //where 条件
            $where = ' 1 ';
            $urllink = null;
            $whereT['title'] = $this->_get('title') ? $this->_get('title') : null;
            $whereT['cid'] = $this->_get('cid') ? $this->_get('cid') : null;
            if ($whereT['title']) {
                $where .= " and a.title like '%$whereT[title]%'";
                $urllink .= "&title=$whereT[title]";
            }
            if ($whereT['cid']) {
                //获取子栏目下所有文章
                returnColumnSon($whereT['cid'], $channel, $arr);
                //设置获取取子栏目文章的where 条件
                $orwhere = null;
                if (is_array($arr)) {
                    foreach ($arr as $value) {
                        $orwhere.=" a.cid=$value or ";
                    }
                }
                $orwhere = substr($orwhere, 0, -3);
                $where .= " and ($orwhere)";
                $urllink .= "&cid=$whereT[cid]";
            }

            //page 数据
            $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
            $listrows = 15;
            $start = $p * $listrows - $listrows;

            switch ($model) {
                case 'channel-renshi':
                    $sql = 'select * from `' . SQLPRE . $row['mark'] . '` a '
                            . " left join `" . SQLPRE . "$row[mark]_field` c on a.id=c.aid "
                            . "where $where order by a.addtime desc limit $start,$listrows";
                    break;
                default:
                    $sql = 'select a.*,b.name as cname from `' . $m->prefix . $row['mark'] . '` a '
                            . 'left join ' . $m->prefix . "column b on a.cid=b.id "
                            . "where $where order by a.addtime desc limit $start,$listrows";
                    break;
            }
            $list = $m->getall($sql);
            $this->assign('list', $list);

            //page 数据
            $count = $m->getone("select count(a.id) as count from `" . $m->prefix . $row['mark'] . "` a where $where");
            $params = array(
                'total_rows' => $count,
                'now_page' => $p,
                'list_rows' => $listrows,
                'method' => 'html',
                'parameter' => '/?article=' . $this->_get('article') . $urllink . '&p=00000'
            );
            $page = new \lib\page($params);
            $this->assign('pageinfo', $page->show(4));
        }
        $this->display('article/artlist');
    }

    //内容添加编辑 
    public function artadd() {
        $m = new \core\model();
        $model = $this->_get('article');
        //如果选择的是普通文章的菜单 ，则从配置文件中读取普通文章的表名
        if ($model == 'list'):
            $model = cfg('ARTICLE_MODEL_NAME');
        endif;
        //获取扩展表字段
        $extField_ = $m->getall('select a.id,a.name,a.mark,a.type,a.val from ' . $m->prefix . 'fields a inner join ' . $m->prefix . "models b on a.model=b.id  where b.mark='$model'");

        if (isset($_POST['_submit_'])) {
            $t_ = 1;
            for ($index = 0; $index < count($extField_); $index++) {
                $field[$t_++] = $extField_[$index]['mark'];
            }
            //比对表字段，把两个表的字段分离开来
            $data = $_POST;
            $d1 = null;
            foreach ($data as $key => $value) {
                if (array_search($key, $field)) {
                    $d1[$key] = $value;
                    unset($data[$key]);
                }
            }
            unset($data['_submit_']);
            unset($data['_verifyKey_']);
            $key = cfg('LOGIN_SESSION_KEY');
            $data['user'] = $_SESSION[$key]['id'];
            $key = null;

            //如果扩展字段数据存在并且不为空
            if (!empty($d1) && is_array($d1)) {
                //根据扩展表字段类型处理数据
                $d1__ = null;
                foreach ($d1 as $key => $value) {
                    $fieldType = $m->getone('select type from ' . $m->prefix . "fields where mark='$key'");
                    switch ($fieldType) {
                        case 'image':
                            $d1__[$key] = serialize($value);
                            break;
                        default:
                            $d1__[$key] = $value;
                            break;
                    }
                }
                $d1 = $d1__;
            }

            //如果是修改数据 
            if ($this->_get('id')) {
                $id = $this->_get('id');
                $data['modifytime'] = date('Y-m-d H:i:s');
                //写入数据到主表 
                $val = null;
                foreach ($data as $key => $value) {
                    if (is_int($value) || is_float($value)) {
                        $val.="$key=$value,";
                    } else {
                        $value = $m->g($value);
                        $val.="$key='$value',";
                    }
                }
                $val = substr($val, 0, -1);
                $sql = "update `" . $m->prefix . "$model` set $val where id=$id";
//                            echo $sql; exit();
                $row = $m->query($sql);
                //写入扩展表数据
                if ($row) {
                    //如果扩展字段数据存在并且不为空
                    if (!empty($d1) && is_array($d1)) {
                        $val = null;
                        foreach ($d1 as $key => $value) {
                            if (is_int($value) || is_float($value)) {
                                $val.="$key=$value,";
                            } else {
                                $value = $m->g($value);
                                $val.="$key='$value',";
                            }
                        }
                        $val = substr($val, 0, -1);
                        $sql = "update `" . $m->prefix . "$model" . '_field`' . " set $val where aid=$id";
                        $row = $m->query($sql);
                    }
                }
            } else {
                $data['modifytime'] = $data['addtime'];
                //写入数据到主表 
                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    if (is_int($value) || is_float($value)) {
                        $val.="$value,";
                    } else {
                        $value = $m->g($value);
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into `" . $m->prefix . "$model` ($field) values ($val)";
//                    echo $sql; exit();
                $row = $m->query($sql);
                //写入扩展表数据
                if ($row) {
                    //如果扩展字段数据存在并且不为空
                    if (!empty($d1) && is_array($d1)) {
                        $id_ = mysql_insert_id();
                        $field = null;
                        $val = null;
                        foreach ($d1 as $key => $value) {
                            $field.="$key,";
                            if (is_int($value) || is_float($value)) {
                                $val.="$value,";
                            } else {
                                $value = $m->g($value);
                                $val.="'$value',";
                            }
                        }
                        $field = substr($field, 0, -1);
                        $val = substr($val, 0, -1);
                        $sql = "insert into `" . $m->prefix . "$model" . '_field`' . " (aid,$field) values ($id_,$val)";
                        $row = $m->query($sql);
                    }
                }
            }
            header('Location:?article=' . $this->_get('article'));
            exit();
            //            print_r($data);
            //            print_r($d1);
        } else {
            //扩展字段
            $this->assign("fields", $extField_);

            //状态为1 和 类型为栏目列表的栏目
//            $channel_ = $m->getall('select * from '.$m->prefix.'column where status=1 and type=1');
//            $this->assign('channel2', $channel_);

            $channel = $m->getall('select *,pid as parentId from ' . $m->prefix . 'column where status=1 and type=1');
            $action = new pController();
            $list_ = $action->RecursionData(0, $channel);
            $channel_ = array();
            $action->printData($list_, 0, $channel_);
            $this->assign('channel2', $channel_);

            //如果是修改数据
            if ($this->_get('id')) {
                $id = $this->_get('id');
                $sql = 'select * from `' . $m->prefix . "$model` where id=$id";
                $info_ = $m->getrow($sql);
                $this->assign('info', $info_);
                $extFieldValue = $m->getrow("select * from `" . $m->prefix . "$model" . '_field' . "` where aid=$id");
                $this->assign('extFieldValue', $extFieldValue);
            } else {
                //默认值
                $info['addtime'] = date('Y-m-d H:i:s');
                $info['hits'] = rand(40, 356);
                $info['sort'] = 999;
                $info['cid'] = 0;
                $info['cid2'] = 0;
                $this->assign('info', $info);
            }
            $this->display('article/artadd');
        }
    }

    /**
     * 查看投递的简历
     */
    public function jianlitoudi() {

        //page 数据
        $id = $this->_get('id');
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        $listrows = 15;
        $start = $p * $listrows - $listrows;

        $m = new \core\model();
        $list = $m->getall("select * from `" . SQLPRE . "toudijianli` where zhiweiid=$id order by id desc  limit $start,$listrows");
        $this->assign("list", $list);
        
        //page 数据
        $count = $m->getone("select count(id) as count from " . SQLPRE . "toudijianli  where zhiweiid=$id");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $sql = "select title from `".SQLPRE."channel-renshi` where id=$id ";
        $name = $m->getone($sql);
        $this->assign('name', $name);
        
        //更新未阅读的记录
        $udata['read'] = 0;
        $m->sData($udata, 'channel-renshi_field', 'u', "aid=$id");
        
        $this->display('article/jianlitoudi');
    }

    public function exemption() {
        $t = $this->_get('t');
        switch ($t) {
            //检测栏目标识唯一性。
            case 'ajaxcolumn':
                $value = $this->_get('value');
                $m = new \core\model();
                $result = $m->getrow("select id,mark from " . $m->prefix . "column where mark='$value'");
                if (!empty($result)) {
                    echo 0;
                } else {
                    echo 1;
                }
                break;
            //检测频道标识唯一性。
            case 'ajaxchannel':
                $value = $this->channelPrefix . $this->_get('value');
                $m = new \core\model();
                $result = $m->getrow("select * from " . $m->prefix . "models where mark='$value'");
                if (!empty($result)) {
                    echo 0;
                } else {
                    echo 1;
                }
                break;
            //检测字段标识唯一性。
            case 'ajaxfield':
                $value = $this->_get('value');
                $isField = array('11', 'id', 'cid', 'cid2', 'title', 'shorttitle', 'picname', 'source', 'modifytime', 'addtime', 'hits', 'sort', 'keywords', 'description', 'user', 'author', 'arcrank', 'isdelete');
                $isArray = array_search($value, $isField);
                if ($isArray) {
                    echo 0;
                } else {
                    $m = new \core\model();
                    $result = $m->getrow("select * from " . $m->prefix . "fields where mark='$value'");
                    if (!empty($result)) {
                        echo 0;
                    } else {
                        echo 1;
                    }
                }
                break;
            default:
                break;
        }
    }

}
