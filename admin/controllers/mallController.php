<?php

/**
 * 与商城相关的功能
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

namespace controllers;

class mallController extends adminController {

    /**
     * 地区管理
     */
    public function areaadmin() {
        $m = new \core\model();
        //地区级别：1为省级，2为市级，3为县级，4为乡镇或街道
        $live = isset($_GET['live']) ? $this->_get("live") : 1;
        $address = "<a href='" . __ROOT__ . "?system=area'>中华人民共和国</a>";
        $list = null;
        switch ($live) {
            case 1:
                $list = $m->getall("select * from " . $m->prefix . "area_provice");
                break;
            case 2:
                $id = $this->_get("id");
                $list = $m->getall("select * from " . $m->prefix . "area_city where province_id=$id");
                //当前地址
                $cur1 = $m->getone("select provice_name from " . $m->prefix . "area_provice where provice_id=$id");
                $address = $address . " / <a href='" . __ROOT__ . "?system=area&live=$live&id=$id'>$cur1</a>";
                break;
            case 3:
                $id = $this->_get("id");
                $list = $m->getall("select * from " . $m->prefix . "area_county where city_id=$id");
                //当前地址
                $cur2 = $m->getrow("select city_name,province_id from " . $m->prefix . "area_city where city_id=$id");
                $cur1 = $m->getone("select provice_name from " . $m->prefix . "area_provice where provice_id=$cur2[province_id]");
                $address = $address . " / <a href='" . __ROOT__ . "?system=area&live=2&id=$cur2[province_id]'>$cur1</a>" .
                        " / <a href='" . __ROOT__ . "?system=area&live=3&id=$id'>$cur2[city_name]</a>";
                break;
            case 4:
                $id = $this->_get("id");
                $list = $m->getall("select * from " . $m->prefix . "area_town where county_id=$id");
                //当前地址
                $cur3 = $m->getrow("select county_name,city_id from " . $m->prefix . "area_county where county_id=$id");
                $cur2 = $m->getrow("select city_name,province_id from " . $m->prefix . "area_city where city_id=$cur3[city_id]");
                $cur1 = $m->getone("select provice_name from " . $m->prefix . "area_provice where provice_id=$cur2[province_id]");
                $address = $address . " / <a href='" . __ROOT__ . "?system=area&live=2&id=$cur2[province_id]'>$cur1</a>" .
                        " / <a href='" . __ROOT__ . "?system=area&live=3&id=$cur3[city_id]'>$cur2[city_name]</a> "
                        . ' / ' . $cur3['county_name'];
                break;
            default:
                $list = $m->getall("select * from " . $m->prefix . "area_provice");
                break;
        }
        $this->assign('list', $list);
        $this->assign('live', $live);
        $this->assign('address', $address);
        $this->display('mall/areaadmin');
    }

    /**
     * 配送方式
     */
    public function delivery() {
        $m = new \core\model();
        $sql = "SELECT a1.id,a1.name,a1.status,a2.deliverId,"
                . "a2.firstWeight,a2.secondWeight,a2.firstPrice,a2.secondPrice,a2.area,a2.areaName "
                . "FROM  " . $m->prefix . "deliverys  a1 LEFT JOIN " . $m->prefix . "separatefreight a2 ON a1.id = a2.deliverId ";
        $result = $m->getall($sql);
        $this->assign("list", $result);
        $this->display('mall/delivery');
    }

    /**
     * 添加编辑配送方式
     */
    public function deliveryEdit() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $data['name'] = $m->g($this->_post('name'));
            $data['description'] = $m->g($this->_post('description'));
            $data['status'] = intval($this->_post('status'));
            $data['ex_com'] = $this->_post('ex_com');
            //根据ID判断是修改还是添加数据
            if ($this->_get("id")) {
                $deliverId = $this->_get("id");
                $val = null;
                foreach ($data as $key => $value) {
                    $val.="$key='$value',";
                }
                $val = substr($val, 0, -1);
                $sql = "update  " . $m->prefix . "deliverys set $val where id=$deliverId";
                $result = $m->query($sql);
            } else {
                $field = null;
                $val = null;
                foreach ($data as $key => $value) {
                    $field.="$key,";
                    $val.="'$value',";
                }
                $field = substr($field, 0, -1);
                $val = substr($val, 0, -1);
                $sql = "insert into " . $m->prefix . "deliverys ($field) values ($val)";
                $result = $m->query($sql);
                $deliverId = mysql_insert_id();
            }

            if (isset($_POST['addressSn']) && is_array($_POST['addressSn'])) {
                //增加运费设置前先清空之前的，直接从浏览器取数据 ，以达到添加和更新都能正确保存的效果 
                $m->query("delete from " . $m->prefix . "separatefreight where deliverId=$deliverId");
                foreach ($_POST['addressSn'] as $key => $value) {
                    $data = null;
                    $data['deliverId'] = $deliverId;
                    $data['firstWeight'] = floatval(strip_tags(trim($_POST['firstweight'][$key])));
                    $data['firstPrice'] = floatval(strip_tags(trim($_POST['firstprice'][$key])));
                    $data['secondWeight'] = floatval(strip_tags(trim($_POST['secondweight'][$key])));
                    $data['secondPrice'] = floatval(strip_tags(trim($_POST['sendprice'][$key])));
                    $data['area'] = strip_tags(trim($_POST['addressSn'][$key]));
                    $data['areaName'] = strip_tags(trim($_POST['addressName'][$key]));

                    $field = null;
                    $val = null;
                    foreach ($data as $key => $value) {
                        $field.="$key,";
                        $val.="'$value',";
                    }
                    $field = substr($field, 0, -1);
                    $val = substr($val, 0, -1);
                    $sql = "insert into " . $m->prefix . "separatefreight ($field) values ($val)";
                    $result = $m->query($sql);
                }
            }
            header("Location:?system=delivery");
            exit();
        } else {
            //检测是否为编辑
            if ($this->_get("id")) {
                $id = $this->_get("id");
                $info = $m->getrow("select * from " . $m->prefix . "deliverys where id=$id");
                $this->assign("info", $info);

                $first = $m->getrow("select * from " . $m->prefix . "separatefreight where deliverId=$id and area=0");
                $deliveryList = $m->getall("select * from " . $m->prefix . "separatefreight where deliverId=$id and area<>0");
                $this->assign('first', $first);
                $this->assign("list", $deliveryList);
            }
            $this->display("mall/delivery");
        }
    }

    /**
     * 删除配送方式 指定区域运费
     * @param type $param
     */
    public function deleteDeliveryM1() {
        $m = new \core\model();
        $id = $this->_get("id");
        if ($id) {
            $result = $m->query("delete from " . $m->prefix . "separatefreight where id=$id");
            if ($result) {
                echo json_encode(array('status' => 1));
                exit();
            }
        }
    }

    /**
     * 删除配送方式 
     */
    public function deleteDeliveryM2() {
        $m = new \core\model();
        $id = $this->_get("id");
        if ($id) {
            $result = $m->query("delete from " . $m->prefix . "separatefreight where deliverId=$id");
            $result = $m->query("delete from " . $m->prefix . "deliverys where id=$id");
            if ($result) {
                header("Location:?system=delivery");
                exit();
            }
        }
    }

    /**
     * 支付方式列表
     */
    public function pay() {
        $m = new \core\model();
        $result = $m->getall("select * from " . $m->prefix . "payment");
        $this->assign('list', $result);
        $this->display('mall/pay');
    }

    /**
     * 修改 支付方式
     */
    public function payedit() {
        $id = $this->_get("id");
        $m = new \core\model();
        $paymentInfo = $m->getrow("select * from " . $m->prefix . "payment where id=$id");
        //合并 config 到数组中
        $config_ = $paymentInfo['config'];
        unset($paymentInfo['config']);
        $config = unserialize($config_);
        if (is_array($config) and ! empty($config)) {
            $paymentInfo = array_merge($paymentInfo, $config);
        }

        if (isset($_POST['_submit_'])) {

            $data = array();
            //银联
            if ($paymentInfo['sn'] == 'unionpay') {
                //银联 支付   配置   start
                //如果上传了密钥文件
                if (is_uploaded_file($_FILES['partnerKeyFile']['tmp_name'])) {
                    $upfile = $_FILES['partnerKeyFile'];
                    $pattern = "/^[a-z|A-Z|0-9|\.|-|_]{1,200}$/";
                    $bool_ = preg_match($pattern, $upfile["name"], $matches);
                    if ($bool_ == FALSE) {
                        msg('名称不合法！', '商户密钥(文件) 只能使用英文字母、数字、下划线组成。 ');
                    }
                    $max_size = 1024000;    //上传照片大小限制,默认1M
                    $size = $upfile['size'];
                    //如果文件小于1M，才保存。
                    if ($max_size > $upfile["size"]) {
                        $newfileName = 'Upload_' . $upfile["name"];
                        $data['partnerKeyFile'] = $newfileName;
                        $newfile = MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'unionpay' . DIRECTORY_SEPARATOR . 'production' . DIRECTORY_SEPARATOR . $newfileName;
                        if (move_uploaded_file($upfile['tmp_name'], $newfile)) {
                            $suc_partnerKeyFile = $m->getone("select partnerKeyFile from " . $m->prefix . "payment where id=$id");
                            if (!empty($suc_partnerKeyFile)) {
                                unlink(MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'unionpay' . DIRECTORY_SEPARATOR . 'production' . DIRECTORY_SEPARATOR . $suc_partnerKeyFile);
                            }
                        }
                    } else {
                        msg('文件大小不合法！', '文件大小必须小于1M。');
                    }
                }
                $data['partnerId'] = $m->g($this->_post("partnerId"));
                $data['partnerKey'] = $m->g($this->_post("partnerKey"));
                //银联 支付   配置   end
            } elseif ($paymentInfo['sn'] == 'weixinPay') {
                //微信支付   配置   start
                //如果上传了密钥文件
                if (is_uploaded_file($_FILES['weixinsslcert']['tmp_name'])) {
                    $upfile = $_FILES['weixinsslcert'];
                    $pattern = "/^[a-z|A-Z|0-9|\.|-|_]{1,200}$/";
                    $bool_ = preg_match($pattern, $upfile["name"], $matches);
                    if ($bool_ == FALSE) {
                        msg('名称不合法！', '商户密钥(文件) 只能使用英文字母、数字、下划线组成。 ');
                    }
                    $max_size = 1024000;    //上传照片大小限制,默认1M
                    $size = $upfile['size'];
                    //如果文件小于1M，才保存。
                    if ($max_size > $upfile["size"]) {
                        $newfileName = 'Upload_' . $upfile["name"];
                        $_POST['config']['weixinsslcert'] = $newfileName;
                        $newfile = MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . $newfileName;
                        if (move_uploaded_file($upfile['tmp_name'], $newfile)) {
                            //如果上传成功删除原来的
                            if (!empty($paymentInfo['weixinsslcert'])) {
                                unlink(MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . $paymentInfo['weixinsslcert']);
                            }
                        }
                    } else {
                        msg('文件大小不合法！', '文件大小必须小于1M。');
                    }
                } else {
                    $_POST['config']['weixinsslcert'] = $paymentInfo['weixinsslcert'];
                }

                //如果上传了密钥文件
                if (is_uploaded_file($_FILES['weixinsslkey']['tmp_name'])) {
                    $upfile = $_FILES['weixinsslkey'];
                    $pattern = "/^[a-z|A-Z|0-9|\.|-|_]{1,200}$/";
                    $bool_ = preg_match($pattern, $upfile["name"], $matches);
                    if ($bool_ == FALSE) {
                        msg('名称不合法！', '商户密钥(文件) 只能使用英文字母、数字、下划线组成。 ');
                    }
                    $max_size = 1024000;    //上传照片大小限制,默认1M
                    $size = $upfile['size'];
                    //如果文件小于1M，才保存。
                    if ($max_size > $upfile["size"]) {
                        $newfileName = 'Upload_' . $upfile["name"];
                        $_POST['config']['weixinsslkey'] = $newfileName;
                        $newfile = MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . $newfileName;
                        if (move_uploaded_file($upfile['tmp_name'], $newfile)) {
                            //如果上传成功删除原来的
                            if (!empty($paymentInfo['weixinsslkey'])) {
                                unlink(MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'cert' . DIRECTORY_SEPARATOR . $paymentInfo['weixinsslcert']);
                            }
                        }
                    } else {
                        msg('文件大小不合法！', '文件大小必须小于1M。');
                    }
                } else {
                    $_POST['config']['weixinsslkey'] = $paymentInfo['weixinsslkey'];
                }



                //生成配置文件   start
                $configfile_ = MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'WxPay.Config_.php';
                $newconfigfilename = MAIN_PATH . 'pay' . DIRECTORY_SEPARATOR . 'weixinPay' . DIRECTORY_SEPARATOR . 'WxPay.Config.php';
                $configfile = file_get_contents($configfile_);
                $configfile = str_replace('wx426b3015555a46be', $_POST['config']['weixinappid'], $configfile); //APPID
                $configfile = str_replace('1225312702', $_POST['config']['weixinmchid'], $configfile); //MCHID
                $configfile = str_replace('e10adc3949ba59abbe56e057f20f883e', $_POST['config']['weixinkey'], $configfile); //KEY
                $configfile = str_replace('01c6d59a3f9024db6336662ac95c8e74', $_POST['config']['weixinsecret'], $configfile);  //APPSECRET
                $configfile = str_replace('apiclient_cert.pem', $_POST['config']['weixinsslcert'], $configfile);  //SSLCERT_PATH
                $configfile = str_replace('apiclient_key.pem', $_POST['config']['weixinsslkey'], $configfile);  //SSLKEY_PATH
                file_put_contents($newconfigfilename, $configfile);
                //生成配置文件   end
                //同时更新到系统配置表
//                $m->sData(array('val' => $_POST['config']['weixinappid']), 'shop_config', 'u', 'mark="weixinappid"');
//                $m->sData(array('val' => $_POST['config']['weixinmchid']), 'shop_config', 'u', 'mark="weixinmchid"');
//                $m->sData(array('val' => $_POST['config']['weixinsecret']), 'shop_config', 'u', 'mark="weixinsecret"');

                //微信支付   配置   end 
            } elseif ($paymentInfo['sn'] == 'alipayj') {
                //支付宝   配置   start
                
                    //生成配置文件   begin 
                    $configfile = file_get_contents( MAIN_PATH . 'pay/alipay/alipay.config_.php' );
                    $configfile = str_replace('replacestring_partner', $_POST['config']['alipaypartner'], $configfile); //  签约帐号
                    file_put_contents(MAIN_PATH . 'pay/alipay/alipay.config.php', $configfile);
                    file_put_contents(MAIN_PATH.'pay/alipay/key/rsa_private_key.pem',$_POST['config']['alipayprivatekeytext']);//写商户私钥


                    $configfile = file_get_contents( MAIN_PATH . 'pay/alipaym/alipay.config_.php' );
                    $configfile = str_replace('replacestring_partner', $_POST['config']['alipaypartner'], $configfile); //  签约帐号
                    file_put_contents(MAIN_PATH . 'pay/alipaym/alipay.config.php', $configfile);
                    file_put_contents(MAIN_PATH.'pay/alipaym/key/rsa_private_key.pem',$_POST['config']['alipayprivatekeytext']);//写商户私钥
                    //生成配置文件   end 

                //支付宝   配置   end
            } elseif ($paymentInfo['sn'] == 'transfer') {
                //微信转账支付   start
                if( isset($_POST['imgfile']) ){
                    $_POST['config']['weixinqrcode'] = $_POST['imgfile'];
                }
                //微信转账支付   end
            }


            $data['status'] = intval($this->_post('status'));
            $data['runmode'] = intval($this->_post('runmode'));
            $data['description'] = $m->g($this->_post("description"));
            if (isset($_POST['config'])) {
                $data['config'] = serialize($_POST['config']);
            }
            $m->sData($data, 'payment', 'u', "id=$id");

            header("Location: ?system=pay&type=edit&id=$id");
            exit();
        } else {
            $this->assign('info', $paymentInfo);
            $this->display('mall/pay');
        }
    }

    /**
     * 配送方式  -  设置 快递 单模板
     */
    public function setexpressDoc() {
        $m = new \core\model();
        $id = $this->_get('id');
        $sql = "select * from " . $m->prefix . "deliverys where id=$id";
        $info = $m->getrow($sql);
        $info['ex_background'] = str_replace('thumb_', '', $info['ex_background']);
        $this->assign('info', $info);
        $this->display('mall/setexpressDoc');
    }

    /**
     * 配送方式  -  设置 快递 单模板，添加标签
     */
    public function setexpressDoc_addtag() {
        if (isset($_POST['_submit_'])) {
            $tag = $_POST['tag'];
            $width = (int) $_POST['width'] / 2;
            $height = (int) $_POST['height'] / 2;
            if (is_array($tag) and ! empty($tag)) {
                $tagData = array();
                $t_ = array();
                foreach ($tag as $key => $value) {
                    //检测是为有为选中或时间标签，根据后面的数字生成多个标签
                    if ($key == 'select1' || $key == 'timeYear' || $key == 'timeMonth' || $key == 'timeDay') {
                        $str = $key . 'Num';
                        $num = (int) $_POST[$str];
                        for ($i = 0; $i < $num; $i++) {

                            $t_['value'] = $value;
                            $t_['name'] = $key;
                            $t_['left'] = rand(10, $width);
                            $t_['top'] = rand(10, $height);
                            $tagData[] = $t_;
                        }
                    } else {   //否则只生成 一个标签
                        $t_['value'] = $value;
                        $t_['name'] = $key;
                        $t_['left'] = rand(10, $width);
                        $t_['top'] = rand(10, $height);
                        $tagData[] = $t_;
                    }
                }

                echo json_encode($tagData);
                exit();
            }
        } else {
            $this->display('mall/setexpressDoc_addtag');
        }
    }

    /**
     * 配送方式  -  设置快递单模板，保存
     */
    public function setexpressDoc_save() {
        $html = $_GET['html'];
        $id = $_GET['id'];
        $m = new \core\model();
        $sql = "update " . $m->prefix . "deliverys set expressDoc='$html' where id=$id";
        $result = $m->query($sql);
        if ($result) {
            echo json_encode(array('status' => 1));
            exit();
        }
    }

    /**
     * 配送方式 - 设置寄件人信息
     */
    public function setexpressDoc_send() {
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            $id = $this->_post('id');
            $data['senName'] = $this->_post('senName');
            $data['senMobile'] = $this->_post('senMobile');
            $data['senPhone'] = $this->_post('senPhone');
            $data['senAddress'] = $this->_post('senAddress');

            $data['proviceSn'] = $this->_post('provice');
            $data['citySn'] = $this->_post('city');
            $data['countySn'] = $this->_post('county');
            $data['townSn'] = $this->_post('town');

            $data['senProvice'] = null;
            $t_ = $this->_post('provice');
            if ($t_ != 0) {
                $sql = "select provice_name from `" . $m->prefix . "area_provice` where provice_id='$t_'";
                $data['senProvice'] = $m->getone($sql);
            }

            $data['senCity'] = null;
            $t_ = $this->_post('city');
            if ($t_ != 0) {
                $sql = "select city_name from `" . $m->prefix . "area_city` where city_id ='$t_'";
                $data['senCity'] = $m->getone($sql);
            }

            $data['senCounty'] = null;
            $t_ = $this->_post('county');
            if ($t_ != 0) {
                $sql = "select county_name from `" . $m->prefix . "area_county` where county_id ='$t_'";
                $data['senCounty'] = $m->getone($sql);
            }

            $data['senTown'] = null;
            $t_ = $this->_post('town');
            if ($t_ != 0) {
                $sql = "select town_name from `" . $m->prefix . "area_town` where town_id ='$t_'";
                $data['senTown'] = $m->getone($sql);
            }

            $data_ = serialize($data);
            $sql = "update " . $m->prefix . "deliverys set sendout='$data_' where id=$id";
            $result = $m->query($sql);
            if ($result) {
                echo json_encode(array('status' => 1));
            }
            exit();
        } else {
            $id = $this->_get('id');
            $sql = "select sendout from " . $m->prefix . "deliverys where id=$id";
            $info = $m->getone($sql);
            $info_ = null;
            if (!empty($info)) {
                $info_ = unserialize($info);
            }
            if ($this->_get('json')) {
                echo json_encode($info_);
                exit();
            } else {
                $this->assign('info', $info_);
                $this->display('mall/setexpressDoc_send');
            }
        }
    }

    /**
     * 配送方式 - 快递单模板设置 ，设置背景图片 快递单
     */
    public function setexpressDoc_setbg() {
        $id = $this->_get('id');
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {
            if (!isset($_POST['ex_background']) or empty($_POST['ex_background'])) {
                $sql = "update " . $m->prefix . "deliverys set ex_background=null where id=$id";
                $result = $m->query($sql);
                echo json_encode(array('status' => 0));
                exit();
            }
            $ex_background = $_POST['ex_background'];
            $sql = "update " . $m->prefix . "deliverys set ex_background='$ex_background' where id=$id";
            $result = $m->query($sql);
            if ($result) {
                echo json_encode(array('status' => 1));
            }
            exit();
        } else {
            $sql = "select ex_background  from " . $m->prefix . "deliverys where id=$id";
            $backgroundImage = $m->getone($sql);
            $this->assign('backgroundImage', $backgroundImage);
            $this->display('mall/setexpressDoc_setbg');
        }
    }

    /**
     * 配送方式 - 快递单模板设置 ，设置宽高
     */
    public function setexpressDoc_setweight() {
        $id = $this->_get('id');
        $m = new \core\model();
        if (isset($_POST['_submit_'])) {

            $width = $_POST['ex_width'];
            $height = $_POST['ex_height'];
            $sql = "update " . $m->prefix . "deliverys set ex_width='$width',ex_height='$height' where id=$id";
            $result = $m->query($sql);
            if ($result) {
                echo json_encode(array('status' => 1));
            }
            exit();
        } else {
            $sql = "select ex_width,ex_height  from " . $m->prefix . "deliverys where id=$id";
            $info = $m->getrow($sql);
            $this->assign('info', $info);
            $this->display('mall/setexpressDoc_setweight');
        }
    }

    /**
     * 获取地区数据，输出json格式
     * @param type $act   获取的动作，分为 city,county,town，参数为空时默认返回省份数据
     * @param type $id   上级区域的编号
     */
    public function getaddrJson($act = null, $id = null) {
        if (!$id)
            echo null;
        $address = new \models\address();
        $list = $address->get($act, $id);
        echo json_encode($list);
    }

    public function exemption() {
        $type = $this->_get("type");
        switch ($type) {
            case 'address':
                $act = $this->_get("act");
                $id = $this->_get("id");
                $this->getaddrJson($act, $id);
                break;
            case 'getsn':
                $m = new \core\model();
                $province = $this->_get('province');    //省
                $city = $this->_get('city');    //省
                $district = $this->_get('district');    //省
                $data = array();
                $data['proviceSn'] = $m->getone("select provice_id  from ".$m->prefix."area_provice  where provice_name ='$province' ");
                $data['citySn'] = $m->getone("select city_id  from ".$m->prefix."area_city  where city_name ='$city' ");
                $data['countySn'] = $m->getone("select county_id  from ".$m->prefix."area_county  where county_name ='$district' ");
                echo json_encode($data); 
                exit();
                break;
            default:
                break;
        }
    }

}
