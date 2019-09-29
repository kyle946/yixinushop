<?php

/**
 * 人事招聘控制器类
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;

class jobsController extends comController {

    /**
     * 列表
     */
    public function index() {

        $m = new \core\model();
        $where = NULL;

        $list = array(
            0 => array(
                'name' => '工作地点',
                'list' => array(
                    0 => '不限'
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
        $sql = "select didian from `" . SQLPRE . "channel-renshi_field` group by didian";
        $arr1 = $m->getall($sql);
        $arr2 = array();
        foreach ($arr1 as $key => $value) {
            $arr2[] = $value['didian'];
        }
        $list[0]['list'] = array_merge($list[0]['list'], $arr2);
        $this->assign('list2', $list);
        $var = rget('t');
        //如果有过滤参数  ##############################################
        if ($var) {
            list($a, $b) = str_split($var);
            if ((int) $a > 0) {
                $where.= " and b.didian like '" . $list[0]['list'][intval($a)] . "' ";
            }
            if ((int) $b > 0) {
                switch ($b) {
                    case '1':
                        $where.= ' and a.addtime>now()-INTERVAL 3 day';
                        break;
                    case '2':
                        $where.= ' and a.addtime>now()-INTERVAL 1 WEEK';
                        break;
                    case '3':
                        $where.= ' and a.addtime>now()-INTERVAL 1 MONTH';
                        break;
                    case '4':
                        $where.= ' and a.addtime>now()-INTERVAL 1 YEAR';
                        break;
                    default:
                        break;
                }
            }
            #############################################################
        } else {
            for ($i = 0; $i < count($list); $i++) {
                $var = $var . '0';
            }
        }
        $this->assign('link', $var);


        //page 数据
        $p = $this->rget('p') ? $this->rget('p') : 1;
        $listrows = 6;  //一页6条数据
        $start = $p * $listrows - $listrows;
        //获取所有文章
        $sql = 'select a.*,date(a.addtime) as addtime,b.didian,b.xinzi,b.jinyan,b.zige from `' . SQLPRE . "channel-renshi` a "
                . " left join `" . SQLPRE . "channel-renshi_field` b on a.id=b.aid "
                . " where a.arcrank='o' and a.isdelete=2 $where order by id desc limit $start,$listrows";
        $result = $m->getall($sql);
        //总记录数
        $count = $m->getone('select count(id) as count from `' . SQLPRE . "channel-renshi` a where  a.arcrank='o' and a.isdelete=2 $where");
        //分页初始化参数
        $params = array(
            'total_rows' => $count,
            'now_page' => $p,
            'list_rows' => $listrows,
            'parameter' => createLink('jobs/index') . "p_00000.html", //后面5个0是替换页码的字符
            'method' => 'html',
        );
        $page = new \lib\page($params);
        $this->assign('pageinfo', $page->show(4));
        $this->assign('webtitle_', '人才招聘');
        $this->assign('list', $result);
        $this->display('jobs/index');
    }

    /**
     * 招聘详情
     */
    public function info() {
        $m = new \core\model();
        $list = array(
            0 => array(
                'name' => '工作地点',
                'list' => array(
                    0 => '不限'
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
        $sql = "select didian from `" . SQLPRE . "channel-renshi_field` group by didian";
        $arr1 = $m->getall($sql);
        $arr2 = array();
        foreach ($arr1 as $key => $value) {
            $arr2[] = $value['didian'];
        }
        $list[0]['list'] = array_merge($list[0]['list'], $arr2);
        $this->assign('list2', $list);
        $var = null;
        for ($i = 0; $i < count($list); $i++) {
            $var = $var . '0';
        }
        $this->assign('link', $var);
        
        $id = rget('id');
        $sql = "select a.*,b.* from `".SQLPRE."channel-renshi` a left join `".SQLPRE."channel-renshi_field` b on a.id=b.aid where id=$id";
        $info = $m->getrow($sql);
        $this->assign('info', $info);
        $this->assign('webtitle_', '高新招聘  -  '.$info['title']." - $info[didian]");
        $this->display('jobs/info');
    }
    
    /**
     * 保存简历
     */
    public function saveJianli() {
        
        if($_SERVER["REQUEST_METHOD"] != 'POST'){
            $string = "Location:".createLink('jobs/index');
            header($string); exit();
        }
        
        $m = new \core\model();
        $data = array();
        $data['name'] = $m->g($this->_post('a'));
        $data['age'] = $m->g($this->_post('b'));
        $data['mobile'] = $m->g($this->_post('c'));
        $data['xueli'] = $m->g($this->_post('d'));
        $data['address'] = $m->g($this->_post('e'));
        $data['works'] = $m->g($this->_post('f'));
        $data['pingjia'] = $m->g($this->_post('g'));
        $data['zhiweiid'] = $m->g($this->_post('id'));
        $sql = "select id from `".SQLPRE."toudijianli` where mobile=$data[mobile] and zhiweiid=$data[zhiweiid]";
        $r = $m->getone($sql);
        if($r){
            message('你已经申请过了！点击 <a href="'.  createLink('jobs/index').'" >[返回]</a> 查看更多职位。');
        }
        $res = $m->sData($data, 'toudijianli');
        if($res){
            $data2['read'] = '`read`+1---';
            $m->sData($data2, 'channel-renshi_field','u',"aid=$data[zhiweiid]");
            message('申请成功，点击 <a href="'.  createLink('jobs/index').'" >[返回]</a> 查看更多职位。');
        }
    }

}
