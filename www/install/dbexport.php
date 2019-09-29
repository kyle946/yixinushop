<?php
session_start();
ini_set('display_errors', 0); 
/*
 * 创建表 和 导入数据 
 */

/**
 * Description of dbexport
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */
    $dbpath = $_SESSION['install_dbpath'];
    $dbusername = $_SESSION['install_dbusername'];
    $dbpwd = $_SESSION['install_dbpwd'];
    $dbname = $_SESSION['install_dbname'];
    $conn = @mysql_connect($dbpath, $dbusername, $dbpwd);
    if ($conn) {
        //创始人帐号
        $adminname = $_SESSION['install_adminname'];
        $adminpwd = $_SESSION['install_adminpwd'];
        mysql_select_db($dbname, $conn);
        mysql_query("SET NAMES 'utf8'",$conn);
        //开始创建表，导入数据
        $filepath = dirname(__FILE__);
        $dirresult = scandir($filepath . '/tableStr');
        $arr = array();
        //扫描循环目录
        foreach ($dirresult as $key => $value) {
            if (preg_match("/[a-z|\_|\-|0-9]+\.sql/", $value)) {
                //如果匹配是SQL文件则创建表和导入数据
                //$arr[] = $value;
                
                //创建表结构
                $query = file_get_contents($filepath . '/tableStr/'.$value);
                if( mysql_query($query) ){
                    print "$value 表 <font style='color:green;' >创建成功</font><br />";
                }else{
                    print "$value 表 <font style='color:red;' >创建失败</font><br />";
                }
                //插入表数据
                $query = file_get_contents($filepath . '/tableData/'.$value);
                if( mysql_query($query) ){
                    print "$value 表 <font style='color:green;' >数据插入成功</font><br />";
                }else{
                    print "$value 表 <font style='color:red;' >数据插入失败</font><br />";
                }
            }
        }
        
        $sql = "update `y_admin_user` set name='$adminname',passwd=md5('$adminpwd') where comment='founder'";
        mysql_query($sql);
        $_SESSION['install_isok'] = 1;
//        var_dump($arr);
//        var_dump($dirresult);
    }else{
        echo '数据库链接失败！';
    }
