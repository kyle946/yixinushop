<?php

namespace controllers;

class cashierController extends \core\Controller {

    private $APIPWD = "www.yixinu.com";

    public function index() {
        echo '<html><body style=\'text-align:center; color:#555;margin-top:120px;\' ><h1>欢迎使用 iframe 框架</h1><p>@长沙异新优网络科技有限责任公司</p></body></html>';
    }

    /**
     * 收银员登录
     * 传入参数是否加密：是
     * 加密的JSON参数：user 用户名，pwd 密码
     * 接收加密数据的参数：data
     * 解密后的数据格式：JSON
     * 输出参数是否加密：否
     * @return string JSON格式数据, {status:1,tips:'登录成功'}
     */
    public function login() {
        $en_str = $_REQUEST['data'];
        $jsondata = aes_decrypt($en_str, $this->APIPWD);
        $postdata = json_decode($jsondata, 1);
        //默认状态
        $res = array(
            'status' => 0,
            'tips' => '登陆失败！'
        );
        $m = new \core\model();
        $user = isset($postdata['user']) ? $postdata['user'] : null;
        $pwd = isset($postdata['pwd']) ? $postdata['pwd'] : null;
        $d = $m->getrow("select * from `" . SQLPRE . "cashier_front_account` where username='$user' ");
        if (!$d) {
            $res['tips'] .= " $user 用户不存在！";
        } else {
            if ($d['status'] == 1) {
                if ($d['pwd'] == md5($pwd)) {
                    $res['data'] = aes_encrypt(json_encode($d), $this->APIPWD);
                    $res['status'] = 1;
                    $res['tips'] = '登录成功！';
                } else {
                    $res['tips'] .= " 密码错误！";
                }
            }else{
                $res['tips'] .= " 帐号已禁用，请联系异新优管理员！";
            }
        }
        echo json_encode($res);
        exit();
    }

    /**
     * 获取商品详情， 扫码添加商品
     * 传入参数是否加密：是
     * 加密的JSON参数：goods_id 商品ID、条码、编号  , user_id 用户ID
     * 接收加密数据的参数：data
     * 解密后的数据格式：JSON
     * 输出参数是否加密：否
     * @return string JSON格式数据
     */
    public function goodsinfo() {
        $en_str = $_REQUEST['data'];
        $jsondata = aes_decrypt($en_str, $this->APIPWD);
//        throw new \Exception($jsondata);
        $postdata = json_decode($jsondata, 1);
        $goods_id = isset($postdata['goods_id']) ? $postdata['goods_id'] : null;    //商品编码或者商品条码
        $weighingcode = isset($postdata['weighingcode']) ? $postdata['weighingcode'] : null;    //商品编码或者商品条码
        $m = new \core\model();
        $redis = new \models\yredis();
        if ($goods_id) {
            try {
                $goods_cache = $redis->get(REDIS_PRE.'cashier_goods_'.$goods_id);
                $info = array();
                if( isset($goods_cache) && !empty($goods_cache) ){
                    $info = json_decode($goods_cache, 1);
                }else{
                    //根据typeId获取表名，以便读取商品类型的属性
                    $typeId = $m->getone("select typeId from " . $m->prefix . "goods g inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId where ga.id='$goods_id' or ga.tiaoma='$goods_id' or ga.sn='$goods_id' ");
                    $table_mark = $m->getone("select mark from " . $m->prefix . "goods_type where id=$typeId");
                    $tablename = $m->prefix . 'goods_add' . $table_mark;

                    $sql = "select  g.id as id_ , g.name as goodsname , g.status , g.catId , g.typeId , g.name2 , g.goodsDesc , g.dateTime , g.attr, g.chengzhong"
                            . " ,ga.*, if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice , a.id as aid , "
                            . "a.name as activityName, a.starttime,a.endtime,ag.xiangou "
                            . " from " . $m->prefix . "goods g inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId "
                            . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                            . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                            . "left join `$tablename` gad on ga.sn=gad.sn "
                            . "where ga.id='$goods_id' or ga.tiaoma='$goods_id' or ga.sn='$goods_id' ";
                    $info = $m->getrow($sql);
                    $redis->setex(REDIS_PRE . 'cashier_goods_' . $info['id'], REDIS_TTL, json_encode($info));
                }
                if(!$info){
                    echo json_encode(array('status'=>0,'tips'=>'没有找到商品信息！'));
                    exit();
                }
                $info['name'] = $info['goodsname'].' '.$info['attributeStr'];
                unset($info['weight']);
                
                
                /**
                 * 取用户ID，用作限购判断
                 * 按照限购活动，是应该对用户限购，但实际到门店买单时，不可能用户已经要结账了，东西都到手了，还说此商品有限购不能结账，再说，如果用户不使用会员卡也可以绕开限制，所以限购只能由当时门店的工作人员操作。
                 * 所以，此处的代码在实际使用中并不能启作用。
                 */
                $userid =  isset($postdata['user_id']) ? $postdata['user_id'] : 0; 
                if( $userid ){
                    //取用户的购买记录， 用作限购判断 
                    $info['xiangouuser'] = $redis->get(REDIS_PRE . 'goods_xianglouinfo_' . $userid . '-' . $info['id']) ?: 0;
                }
                $redis = new \models\yredis();
                
                
                /**
                 * 从缓存读取库存，保存准确性
                 * 此处做库存判断不科学，因为客户都已经把商品拿到手到收银台结账了，怎么可能还库存不足，就算有，也是进销存系统数据错误
                 */
                $info['numbers'] = $redis->get(REDIS_PRE . 'goods_numbers_' . $info['id']);
                
                echo json_encode(array('status'=>1,'data'=>$info,'tips'=>'查询成功'));
                exit();
            } catch (Exception $exc) {
                echo json_encode(array('status'=>0,'tips'=>'服务器没有找到商品信息！'));
                exit();
            }
            
        }elseif($weighingcode){    //如果是称重商品
                $weight = isset($postdata['weight']) ? $postdata['weight'] : null;    //称重商品重量 ，单位：克
                $business_no = $postdata['no'];
                $weighingcode = intval($weighingcode);
            
                //根据typeId获取表名，以便读取商品类型的属性
                $typeId = $m->getone("select typeId from " . $m->prefix . "goods g inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId where g.business_no='$business_no' and g.weighing_code='$weighingcode' ");
                $table_mark = $m->getone("select mark from " . $m->prefix . "goods_type where id=$typeId");
                $tablename = $m->prefix . 'goods_add' . $table_mark;

                $sql = "select  g.id as id_ , g.name as goodsname , g.status , g.catId , g.typeId , g.name2 , g.goodsDesc , g.dateTime , g.attr, g.chengzhong"
                        . " ,ga.*, if(a.id is not null,ag.aprice,ga.shopPrice) as shopPrice , ga.shopPrice as sprice , a.id as aid , "
                        . "a.name as activityName, a.starttime,a.endtime,ag.xiangou "
                        . " from " . $m->prefix . "goods g inner join " . $m->prefix . "goods_additional ga on g.id=ga.goodsId "
                        . " left join `" . $m->prefix . "activity_goods` ag on ga.id=ag.goodsid "
                        . " left join " . $m->prefix . "activity a on ag.aid=a.id and (a.starttime<now() and now()<a.endtime) "
                        . "left join `$tablename` gad on ga.sn=gad.sn "
                        . "where g.business_no='$business_no' and g.weighing_code='$weighingcode' ";    //0111212345601400
                $info = $m->getrow($sql);
                $redis->setex(REDIS_PRE . 'cashier_goods_' . $info['id'], REDIS_TTL, json_encode($info));
                
                if(!$info){
                    echo json_encode(array('status'=>0,'tips'=>'没有找到商品信息！'.$weighingcode));
                    exit();
                }
                $info['name'] = $info['goodsname'].' '.$info['attributeStr'];
                $info['weight'] = $weight;  //商品重量，单位：克
                $info['tiaoma'] = $postdata['tiaoma'];
                
                echo json_encode(array('status'=>1,'data'=>$info,'tips'=>'查询成功'));
                exit();
        }else{
            echo json_encode(array('status'=>0,'tips'=>'没有输入条码！'));
            exit();
        }
    }
    
    
    /**
     * 获取会员信息
     * 传入参数是否加密：是
     * 加密的JSON参数：mobile 会员手机号码
     * 接收加密数据的参数：data
     * 解密后的数据格式：JSON
     * 输出参数是否加密：是
     * @return string JSON格式数据
     */
    public function userInfo() {
        $en_str = $_REQUEST['data'];
        $jsondata = aes_decrypt($en_str, $this->APIPWD);
        $postdata = json_decode($jsondata, 1);
        $no = isset($postdata['no']) ? $postdata['no'] : null; //手机号码或者会员卡号
        if( $no ){
            $m = new \core\model();
            $userinfo = $m->getrow("select * from ".SQLPRE."users where mobile='$no' or card_no='$no' ");
            if( !is_array($userinfo) ){
                echo json_encode(array('status'=>0,'tips'=>'该用户不存在！'));
                exit();
            }else{
                $en_jsondata = aes_encrypt(json_encode($userinfo), $this->APIPWD);  //加密数据 
                echo json_encode(array('status'=>1,'data'=>$en_jsondata,'tips'=>'查询成功'));
                exit();
            }
        }
    }
    
    public function test() {
        $en_str = "jRRO+Bs4CphBjGOn0ENIXHepXZ+163OHoybnwHSJQnHahIKdJSd/RpVF9KwinlrS0KZraGOYGhKa2IHQuYs9SG2WpT9EjjsJ0N9j6DNN70hIIjJuTdDhRNqt79vnZaff";
        var_dump($en_str);
        var_dump(aes_decrypt($en_str, $this->APIPWD));
    }

}
