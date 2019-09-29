<?php

session_start();
ini_set('display_errors', 0);

/*
 * 导入  乡/镇 地区数据的执行文件
 */
/**
 * Description of dbexport2
 *
 * @author kyle 青竹丹枫 <316686606@qq.com>
 */
$dbpath = $_SESSION['install_dbpath'];
$dbusername = $_SESSION['install_dbusername'];
$dbpwd = $_SESSION['install_dbpwd'];
$dbname = $_SESSION['install_dbname'];
$conn = @mysql_connect($dbpath, $dbusername, $dbpwd);
if ($conn) {

    mysql_select_db($dbname, $conn);
    mysql_query("SET NAMES 'utf8'", $conn);


    $filepath = dirname(__FILE__);
// 写入 乡/镇 地区数据 ，因为 数据太大，不能用一行读取插入的形式完成 
    $myfile = fopen($filepath . '/t/y_area_town.sql', "r") or die("Unable to open file!");
// 输出单行直到 end-of-file
    print '开始导入地区数据 ============start ';
    $ii = 1;
    while (!feof($myfile)) {
        $ii++;
        mysql_query(fgets($myfile));
        print " $ii 行 <font style='color:green;' >数据插入成功</font> <br />";
    }
    fclose($myfile);
}

?>
