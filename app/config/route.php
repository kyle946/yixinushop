<?php
//判断是否开启 rewrite 模式
if(URL_REWRITE){
    return array(
        array('/^\/?g\/[0-9]+/','goods/category'),
        array('/^\/?item\/[0-9]+/','goods/gitem'),
        array('/^\/?a\/[0-9]+/','goods/activity'),
        array('/^\/?[i|c]+\/[a-z0-9]+/','article/artList'),
        array('/^\/?m\/[a-z0-9]+/','article/content'),
        
        //通过php压缩css文件
        //array('/^\/?static\/style\.css$/','p/parseCss'),
    );
}else{
    
    return array(
            //'array('正则表达式'，'对应的模块和操作名')'
//            array('/^.*+/','index/index'),//把所有不存在的链接都映射到首页。
    );
    
}
?>