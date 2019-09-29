<?php

/**
 * 内容管理模块
 *
 * @author kyle 青竹丹枫 kyle <316686606@qq.com>
 */

namespace controllers;

class articleController extends comController {

    public $column;

    public function __construct() {
        parent::__construct();
        $this->column = new \models\artcolumn();
    }

    //文章列表
    public function artList() {
        $mark = $this->rget('i');
        $this->assign('mark', $mark);
        //获取子栏目
        $this->assign('sonColumn11', $this->column->getlev2());
        $m = new \core\model();

        $column = getArticletable($mark);  //取模型对应的表名
        if (empty($column))
            msg('Action error ! ');
        $table = $column['mark'];
        $template = empty($row['tplList']) ? 'article/list' : $column['tplList'];  //设置默认模板
        $colid = $column['id'];

        //获取当前栏目下的所有子栏目ID，以便到能获取到所有子栏目的文章
        $result = $m->getall('select id,name,mark,pid as parentId from ' . $m->prefix . 'column ');
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
                    1 => '人气 ↑最高',
                    2 => '人气 ↓最低',
                    3 => '评论↑最多',
                    4 => '评论↓最少',
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
        $this->assign('link', $var);
        //  设置过滤的条件   end  #################################
        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 6;  //一页6条数据
        $start = $p * $listrows - $listrows;
        //获取所有文章
        $result = $m->getall('select a.*,date(a.addtime) as addtime,b.name as colname,b.mark from `' . $m->prefix . "$table` a left join `" . $m->prefix . "column` b on a.cid=b.id  where a.arcrank!='c' and a.isdelete=2 and (a.cid=$colid $where ) $where2 order by $sort a.addtime desc limit $start,$listrows");
        //总记录数
        $count = $m->getone('select count(id) as count from `' . $m->prefix . "$table` a where a.arcrank!='c' and a.isdelete=2 and (a.cid=$colid $where ) $where2 ");
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => createLink('article/artList', array('t' => $var)) . "_i_" . $mark . '_' . "p_00000.html", //后面5个0是替换页码的字符
            'method' => 'html',
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));

        $this->assign('list', $result);
        $this->display($template);
    }

    /**
     * 读取正文内容
     */
    public function content() {
        $id = $this->rget('id');
        $mark = $this->rget('m');
        if ($id) {
            
            $m = new \core\model();
            $this->assign('sonColumn11', $this->column->getlev2());
            $column = getArticletable($mark);  //取模型对应的表名
            if (empty($column))
                msg('Action error ! ');
            $table = $column['mark'];
            $table2 = $table . '_field';
            $this->assign('columName', $column['name']);

            $row = null;
            $row = $m->getrow('select a.*,date(a.addtime) as addtime,b.* from `' . $m->prefix . "$table` a inner join `" . $m->prefix . "$table2` b on a.id=b.aid where a.id=$id");
            if ($row['arcrank'] == 'c') {
                msg("文章已经关闭！");
            }
            $this->assign('webtitle_', $row['title']);
            $this->assign('info', $row);

            //更新访问率  start
            $GoodsClickCount_name = 'ArticleClickCount' . $id;
            if (!isset($_COOKIE[$GoodsClickCount_name]) or empty($_COOKIE[$GoodsClickCount_name])) {
                setcookie($GoodsClickCount_name, $id, time() + 100);
                $data['hits'] = "hits+1---";
                $m->sData($data, $table, 'u', "id=$id");
            }
            //更新访问率  end
            //相关阅读列表
            $o1 = $m->getall('select a.*,b.mark from `' . $m->prefix . "$table` a left join `" . $m->prefix . "column` b on a.cid=b.id  where a.arcrank='o' and a.isdelete=2 and a.id<$id order by id desc limit 3");
            $o2 = $m->getall('select a.*,b.mark from `' . $m->prefix . "$table` a left join `" . $m->prefix . "column` b on a.cid=b.id  where a.arcrank='o' and a.isdelete=2 and a.id>$id limit 3");
            $this->assign('o1', $o1);
            $this->assign('o2', $o2);
//            var_dump($row);
        } else {
            msg('参数错误或者使用了错误的链接方式。 1005');
        }
        $this->display('article/content');
    }

    /**
     * ajax 获取文章内容
     */
    public function getcontent() {
        $m = new \core\model();
        $id = $this->_get('id');
        $columnmark = $this->_get('mark');
        //表名
        $table = $m->getone('select b.mark from ' . $m->prefix . 'column a left join ' . $m->prefix . 'models b on a.model=b.id where a.mark=\'' . $columnmark . '\'');
        //扩展字段表名
        $table2 = $table . '_field';
        if (!empty($id)) {
            $sql = 'select a.arcrank,b.content from `' . $m->prefix . "$table` a inner  join `" . $m->prefix . "$table2` b on a.id=b.aid where id=$id";
            $info = $m->getrow($sql);
            //如果文章已经禁止访问 
            if ($info['arcrank'] == 'c')
                echo null;
            //增加点击率
            $m->query("update `" . $m->prefix . "$table` set hits=hits+1 where id=$id");
//            header("Content-type: text/html; charset=utf-8");
            echo $info['content'];
        }
    }

    /**
     * 加载评论
     */
    public function loadComment() {
        $m = new \core\model();
        $cid = $this->_get('cid');
        $id = $this->_get('id');
        //page 数据
        $p = (isset($_GET['p']) && !empty($_GET['p'])) ? $this->_get('p') : 1;
        //每页最多只加载 5 条评论 ，原因 是为了，循环楼层时不会因为循环次数太多，导致占用服务器资源过多，引起程序加载缓慢
        $listrows = 5;
        $start = $p * $listrows - $listrows;

        //如果登录了就显示用户自己的评论 ，否则只显示审核通过的评论
        $userinfo = loginJudbe();
        $where = 'ac.status=1';
        if ($userinfo) {
            $where = " ( ac.status=1 or userid=$userinfo[id] ) ";
        }

        $sql = "select ac.*,u.nickname,if(u.avatar is null,'default.gif',u.avatar) as avatar from `" . $m->prefix . "art_comment` ac "
                . "left join `" . $m->prefix . "users` u on ac.userid=u.id"
                . "  where ac.cid=$cid and ac.artId=$id and $where order by id desc limit $start,$listrows";
        $list_ = $m->getall($sql);
        $list = array();
        //用循环的方式获取每一条评论的楼层，最多只能盖15层。。
        foreach ($list_ as $key => $value) {
            $floor = array();
            $pid = $value['pid'];
            if (!empty($pid)) {
                for ($i = 0; $i < 15; $i++) {
                    $sql = "select ac.content,ac.ctime,ac.pid,u.nickname from `" . $m->prefix . "art_comment` ac "
                            . "left join `" . $m->prefix . "users` u on ac.userid=u.id"
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
        $count = $m->getone("select count(ac.id) as count from `" . $m->prefix . "art_comment` ac  where ac.cid=$cid and ac.artId=$id and $where");

        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => 1,
            'method' => 'ajax',
            'ajax_func_name' => 'publicMethod.loadCommentPage',
        );
        $page = new \lib\page($params);
        $pageinfo = $page->show(4);
        echo json_encode(array('data' => $list, 'page' => $pageinfo));
        exit();
    }

}
