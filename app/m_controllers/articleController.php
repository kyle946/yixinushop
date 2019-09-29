<?php

/**
 * Description of articleController
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace m_controllers;

class articleController extends comController {

    public $column;

    public function __construct() {
        parent::__construct();
        $this->column = new \models\artcolumn();
    }

    /**
     * 
     */
    public function item() {
        $id = $this->rget('id');
        $mark = $this->rget('m');

        if ($id) {
            $m = new \core\model();
            //根据栏目ID获取模型和模型对应的表名，以便读取文章内容
            $column = $row = $m->getrow('select a.id,a.name,b.mark from ' . SQLPRE . 'column a left join ' . SQLPRE . 'models b on a.model=b.id where a.mark=\'' . $mark . '\'');
            if (empty($row))
                msg('Action error ! ');
            $table = $row['mark'];
            $table2 = $table . '_field';
            $this->assign('columName', $row['name']);

            $row = null;
            $row = $m->getrow('select a.*,date(a.addtime) as addtime,b.* from `' . SQLPRE . "$table` a inner join `" . SQLPRE . "$table2` b on a.id=b.aid where a.id=$id");
            if ($row['arcrank'] == 'c') {
                msg("文章已经关闭！");
            }
            $this->assign('webtitle_', $row['title']);
            $this->assign('layout_title', ysubstr($row['title'], 11));

            //更新访问率  start
            $GoodsClickCount_name = 'ArticleClickCount' . $id;
            if (!isset($_COOKIE[$GoodsClickCount_name]) or empty($_COOKIE[$GoodsClickCount_name])) {
                setcookie($GoodsClickCount_name, $id, time() + 100);
                $data['hits'] = "hits+1---";
                $m->sData($data, $table, 'u', "id=$id");
            }
            //更新访问率  end
            //删掉样式代码  start
            $reg = "/<p\s?(.+?)>\s?(.*?)\s<\/p>/is";
            if( isset($row['content']) ){
                $row['content'] = preg_replace($reg, "<p>$2</p>", $row['content']);
            }
            //删掉样式代码  end

            $this->assign('info', $row);
            //相关阅读列表
            $o1 = $m->getall('select a.*,b.mark from `' . SQLPRE . "$table` a left join `" . SQLPRE . "column` b on a.cid=b.id  where a.arcrank='o' and a.isdelete=2 and a.id<$id order by id desc limit 2");
            $o2 = $m->getall('select a.*,b.mark from `' . SQLPRE . "$table` a left join `" . SQLPRE . "column` b on a.cid=b.id  where a.arcrank='o' and a.isdelete=2 and a.id>$id limit 2");
            $this->assign('o1', $o1);
            $this->assign('o2', $o2);

            //如果登录了就显示用户自己的评论 ，否则只显示审核通过的评论
            $userinfo = loginJudbe();
            $where = 'ac.status=1';
            if ($userinfo) {
                $where = " ( ac.status=1 or ac.userid=$userinfo[id] ) ";
            }
            //评论列表
            $comment = array();
            $sql = "select ac.*,u.nickname,if(u.avatar is null,'default.gif',u.avatar) as avatar from `" . SQLPRE . "art_comment` ac "
                    . "left join `" . SQLPRE . "users` u on ac.userid=u.id"
                    . "  where ac.cid=$column[id] and ac.artId=$id and $where order by id desc limit 3";
            $comment = $m->getall($sql);
            $this->assign('list', $comment);
        } else {
            msg('参数错误或者使用了错误的链接方式。 1005');
        }

//        $this->assign('layout_title', '文章详情'); //layout_title
        $this->display('article/item');
    }

    /**
     * 评论
     */
    public function comment() {

        $m = new \core\model();
        $userinfo = loginJudbe();
        $id = $this->rget('id');
        $mark = $this->rget('m');
        //根据栏目ID获取模型和模型对应的表名，以便读取文章内容
        $column = $m->getrow('select a.id,a.name,b.mark from ' . SQLPRE . 'column a left join ' . SQLPRE . 'models b on a.model=b.id where a.mark=\'' . $mark . '\'');

        //取出文章相关信息
        $tablename = SQLPRE . $column['mark'];
        $sql = "select title,arcrank from `" . $tablename . "` where id=$id";
        $articleinfo = $m->getrow($sql);
        //如果文章不允许评论
        if ($articleinfo['arcrank'] == 'oc') {
            msg('文章禁止评论');
        }

        //如果 是回复评论的ID
        $commentpid = rget('commentpid');
        if ($commentpid) {
            $sql = "select content from `" . SQLPRE . "art_comment` where id=$commentpid";
            $this->assign('huifuneirong', ysubstr($m->getone($sql), 10));
        }

        //如果用户已经登录 ，并且提交方式为post ； 写评论  start  ######################
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['_submit_']) && $userinfo) {
            //查看上一条评论时间 是不是已经间隔两个小时了
            $sql = "SELECT ctime FROM `" . SQLPRE . "art_comment` WHERE now() < `ctime` + INTERVAL 120 MINUTE and userid=$userinfo[id]";
            $prev = $m->getone($sql);
            if ($prev) {
                header("Location:" . $_SERVER["REQUEST_URI"]);
                exit();
            }

            //获取评论默认状态   start
            $Cstatus = $GLOBALS['config']['commentDefaultStatus'];
            if (array_search($Cstatus, array(1, 2, 3)) === false || !$Cstatus || empty($Cstatus)) {
                $Cstatus = 2;
            };
            $data['status'] = $Cstatus;
            //获取评论默认状态   end
            //评价内容过滤
            $commentContent_ = $this->_post('commentContent');
            $str_ = $GLOBALS['config']['commentfilt'];
            $order = array("\r\n", "\n", "\r");
            $str = str_replace($order, ',', $str_);
            $strArray = explode(',', $str);
            $commentContent = str_replace($strArray, '**', $commentContent_);  //过滤后的内容。

            $data['content'] = mb_substr($commentContent, 0, 500, 'utf-8');  //截取字符 500
            $data['cid'] = $column['id'];
            $data['userid'] = $userinfo['id'];
            $data['artId'] = $id;
            if ($commentpid) {
                $data['pid'] = $commentpid;
            } //回复评论的ID
            $result = $m->sData($data, 'art_comment');
            if ($result) {
                //根据栏目ID获取模型和模型对应的表名，以便更新评论次数
                $table = $m->getone('select b.mark from ' . SQLPRE . 'column a left join ' . SQLPRE . 'models b on a.model=b.id where a.id=' . $column['id']);
                $d2['comments'] = "comments+1---";
                $m->sData($d2, $table, 'u', "id=$id");
                header("Location:" . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }
        // 写评论  end  #######################################################
        //赋值标题
        $this->assign('layout_title', ysubstr($articleinfo['title'], 11));

        //page 数据
        $p = $this->rget('p') ? : 1;
        $listrows = 5;
        $start = $p * $listrows - $listrows;

        //如果登录了就显示用户自己的评论 ，否则只显示审核通过的评论
        $where = 'ac.status=1';
        if ($userinfo) {
            $where = " ( ac.status=1 or ac.userid=$userinfo[id] ) ";
        }

        //评论列表
        $sql = "select ac.*,u.nickname,if(u.avatar is null,'default.gif',u.avatar) as avatar from `" . SQLPRE . "art_comment` ac "
                . "left join `" . SQLPRE . "users` u on ac.userid=u.id"
                . "  where ac.cid=$column[id] and ac.artId=$id and $where order by id desc limit $start,$listrows";
        $list_ = $m->getall($sql);
        $list = array();
        //用循环的方式获取每一条评论的楼层，最多只能盖15层。。
        foreach ($list_ as $key => $value) {
            $floor = array();
            $pid = $value['pid'];
            if (!empty($pid)) {
                for ($i = 0; $i < 15; $i++) {
                    $sql = "select ac.content,ac.ctime,ac.pid,u.nickname from `" . SQLPRE . "art_comment` ac "
                            . "left join `" . SQLPRE . "users` u on ac.userid=u.id"
                            . "  where ac.id=$pid and $where";
                    $content = $m->getrow($sql);
                    $floor[$i] = $content;
                    if (empty($content['pid'])) {
                        break;    //如果这条评论没有上一级了则中止
                    } else {
                        $pid = $content['pid'];   //如果还有上一级评论 ，则把pid循环做为下一条评论的ID 获取
                    }
                }
            }
            $list[$key] = $value;
            $list[$key]['floor'] = array_reverse($floor);  //翻转数组，使楼层按正常顺序排列
        }

        //page 数据
        $count = $m->getone("select count(ac.id) as count from `" . SQLPRE . "art_comment` ac  where ac.cid=$column[id] and ac.artId=$id and $where");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if (judgeAjaxRequest()) {
            echo json_encode(array('data' => $list, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign('list', $list);
            $this->display('article/comment');
        }
    }

    /**
     * 招聘信息 
     */
    public function jobinfo() {
        $m = new \core\model();
        $id = rget('id');
        $sql = "select a.*,b.* from `" . SQLPRE . "channel-renshi` a left join `" . SQLPRE . "channel-renshi_field` b on a.id=b.aid where id=$id";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        $this->assign('webtitle_', '高新招聘  -  ' . $info['title'] . " - $info[didian]");
        $this->assign('layout_title', '招聘信息');
        $this->display('article/jobinfo');
    }

    /**
     * 文章列表
     */
    public function artList() {

        $isajax = judgeAjaxRequest();  //判断是否ajax访问 

        $mark = $this->rget('i');
        if (!$isajax)
            $this->assign('mark', $mark);
        //获取子栏目
        if (!$isajax)
            $this->assign('sonColumn11', $this->column->getlev2());

        $m = new \core\model();
        //栏目信息 、表名
        $row = $m->getrow('select a.id,a.tplList,b.mark,a.name from ' . SQLPRE . 'column a left join ' . SQLPRE . 'models b on a.model=b.id where a.mark=\'' . $mark . '\'');
        if (empty($row))
            msg('Action error ! ');
        if (!$isajax)
            $this->assign('webtitle_', $row['name']);
        if (!$isajax)
            $this->assign('layout_title', $row['name']);
        $table = $row['mark'];
        $template = empty($row['tplList']) ? 'article/list' : $row['tplList'];  //设置默认模板
        $colid = $row['id'];

        //获取当前栏目下的所有子栏目ID，以便到能获取到所有子栏目的文章
        $result = $m->getall('select id,name,mark,pid as parentId from ' . SQLPRE . 'column ');
        returnColumnSon($colid, $result, $arr);

        //设置获取取子栏目文章的where 条件
        $where = null;
        if (is_array($arr)) {
            foreach ($arr as $value) {
                $where .=" or a.cid=$value ";
            }
        }

        // 设置过滤的条件   start   #################################
        $list = array(
            0 => array(
                'name' => '排序',
                'list' => array(
                    0 => '不限',
                    1 => '人气最高',
                    2 => '人气最低',
                    3 => '评论最多',
                    4 => '评论最少',
                )
            ),
            1 => array(
                'name' => '发布时间',
                'list' => array(
                    0 => '不限',
                    1 => '三天内',
                    2 => '一周内',
                    3 => '一月内',
                    4 => '一年内'
                )
            ),
        );
        if (!$isajax)
            $this->assign('list2', $list);
        $sort = null;
        $where2 = null;

        //如果有过滤参数  begin
        $var = rget('t');
        if ($var) {
            list($a, $b) = str_split($var);
            if ((int) $a > 0) {
                switch ($a) {
                    case 1:
                        $sort = 'a.hits desc,';
                        break;
                    case 2:
                        $sort = 'a.hits asc,';
                        break;
                    case 3:
                        $sort = 'a.comments desc,';
                        break;
                    case 4:
                        $sort = 'a.comments asc,';
                        break;
                    default:
                        break;
                }
            }
            if ((int) $b > 0) {
                switch ($b) {
                    case '1':
                        $where2.= ' and a.addtime>now()-INTERVAL 3 day';
                        break;
                    case '2':
                        $where2.= ' and a.addtime>now()-INTERVAL 1 WEEK';
                        break;
                    case '3':
                        $where2.= ' and a.addtime>now()-INTERVAL 1 MONTH';
                        break;
                    case '4':
                        $where2.= ' and a.addtime>now()-INTERVAL 1 YEAR';
                        break;
                    default:
                        break;
                }
            }
            //如果有过滤参数  end
        } else {
            for ($i = 0; $i < count($list); $i++) {
                $var = $var . '0';
            }
        }
        if (!$isajax)
            $this->assign('link', $var);
        //  设置过滤的条件   end  #################################
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 6;  //一页6条数据
        $start = $p * $listrows - $listrows;
        //获取所有文章
        $result = $m->getall('select a.*,date(a.addtime) as addtime,b.name as colname,b.mark from `' . SQLPRE . "$table` a left join `" . SQLPRE . "column` b on a.cid=b.id  where a.arcrank !='c' and a.isdelete=2 and (a.cid=$colid $where ) $where2 order by $sort a.addtime desc limit $start,$listrows");
        //总记录数
        $count = $m->getone('select count(id) as count from `' . SQLPRE . "$table` a where a.arcrank !='c' and a.isdelete=2 and (a.cid=$colid $where ) $where2 ");
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.nextpage',
//            'parameter' => createLink('article/artList',array('t'=>$var)) . "_i_".$mark.'_'."p_00000.html",  //后面5个0是替换页码的字符
//            'method' =>'html',
        );

        $page = new \lib\page($params);
        $pageinfo = $page->show(5);
        if ($isajax) {
            //处理页面使用的数据
            foreach ($result as $k => $v) {
                $v['bgcolor'] = printColor();
                $v['link2'] = createLink('article/item', array('m' => $v['mark'], 'id' => $v['id']));
                $v['imgArea'] = '';
                if (!empty($v['picname'])) {
                    $v['imgArea'] = '<img src=" ' . IMAGE_URL . ' /' . $v['picname'] . '" />';
                } else {
                    $v['imgArea'] = '<div>' . ysubstr($v['title'], 7) . '</div>';
                }
                $v['title'] = ysubstr($v['title'], 28);
                $v['addtime'] = substr($v['addtime'], 0, 10);
                $result[$k] = $v;
            }
            echo json_encode(array('data' => $result, 'page' => $pageinfo));
        } else {
            $this->assign('pageinfo', $pageinfo);
            $this->assign('list', $result);
            $this->display('article/artlist');
        }
    }

}
