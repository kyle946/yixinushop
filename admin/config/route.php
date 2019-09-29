<?php
//判断是否开启 rewrite 模式 ，
if(URL_REWRITE){
	//'array('正则表达式'，'    控制器/操作')'
	    return ;
}else{
	//'array('正则表达式'，'控制器/操作')'
	return array(
                        array('/^act\=login/','common/login'),
                        array('/^act\=logout/','common/logout'),
             // (开发专用) 权限和菜单数据编辑
                        array('/^system\=devMenu/','dev/menu'),
                        array('/^system\=devMenu&type=addmenu/','dev/addmenu'),
                        array('/^system\=devMenu&type=editmenu/','dev/editmenu'), 
                        array('/^system\=devMenu&type=Permission/','dev/Permission'),  
                        array('/^system\=devMenu&type=addPermission/','dev/addPermission'), 
                        array('/^system\=devMenu&type=editPermission/','dev/editPermission'), 
                        array('/^system\=devMenu&type=delPermission/','dev/delPermission'),
                        array('/^system\=devMenu&type=modelaction/','dev/modelaction'),
                        array('/^system\=devMenu&type=addaction/','dev/addaction'),
            //商品管理 
                        array('/^goods\=list/','goods/goodslist'),
                        array('/^goods\=edit/','goods/goodsedit'),
                        array('/^goods\=categroyList/','goods/categroyList'),
                        array('/^goods\=categroyList\&type\=page/','goods/categroyPage'),
                        array('/^goods\=categroyList\&type\=add/','goods/categroyEdit'),
                        array('/^goods\=categroyList\&type\=edit/','goods/categroyEdit'),
                        array('/^goods\=categroyList\&type\=delete/','goods/categroyDelete'),
            //更新商品缓存   redis
                        array('/^goods\=goodscache/','goods/upgoodscache'),
            //商品类型
                        array('/^goods\=type/','goods/typelist'),
                        array('/^goods\=type\&act\=add/','goods/typeadd'),
                        array('/^goods\=type\&act\=edit/','goods/typeadd'),
                        array('/^goods\=type\&act\=delete/','goods/typedelete'),
                        array('/^goods\=type\&act\=attrdelete/','goods/attrdelete'),
                        array('/^goods\=type\&act\=typeAttrlist/','goods/typeAttrlist'),
                        array('/^goods\=type\&act\=typeAttrlistEdit/','goods/typeAttrlistEdit'),
                        array('/^goods\=type\&act\=typeAttrlistDelete/','goods/typeAttrlistDelete'),
            //品牌管理
                        array('/^goods\=brand/','goods/brandlist'),
                        array('/^goods\=brand\&type\=add/','goods/brandadd'),
                        array('/^goods\=brand\&type\=edit/','goods/brandedit'),
            //规格
                        array('/^goods\=spec/','goods/spec'),
                        array('/^goods\=spec\&type\=add/','goods/specadd'),
                        array('/^goods\=spec\&type\=edit/','goods/specedit'),
                        array('/^goods\=spec\&type\=delete/','goods/specdel'),
            //自定义数据组
                        array('/^goods\=customData/','goods/customData'),
                        array('/^goods\=customData\&type=edit/','goods/customDataEdit'),
                        array('/^goods\=customData\&type=dataedit/','goods/customdata_dataedit'), //
                        
            //商品图片管理
                        array('/^goods\=imgmanage/','goods/imgmanage'),
                        array('/^goods\=all_imgmanage/','goods/all_imgmanage'),
           //滚动图片
                        array('/^goods=rollimage\&type=edit/','goods/rollimageedit'),
                        array('/^goods=rollimage\&type=dataedit/','goods/rollimagedataedit'),
                        array('/^goods=rollimage\&type=rollimaginsertimg/','goods/rollimaginsertimg'),
                        array('/^goods=rollimage$/','goods/rollimage'),
            //订单管理
                        array('/^order\=list/','order/orderlist'),
                        array('/^order\=alist/','order/orderlist'),
                        array('/^order\=blist/','order/orderlist'),
                        array('/^order\=clist/','order/orderlist'),
                        array('/^order\=printOrder/','order/printDelivery'),   //打印订单
                        array('/^order\=printDelivery/','order/printDelivery'),  //打印配送单
                        array('/^order\=printExpress/','order/printExpress'),
            //销售统计
                        array('/^statement\=goodslist/','statement/goodslist'),
                        array('/^statement\=orderlist/','statement/orderlist'),
                        
            //用户管理            
                        array('/^user\=list/','users/userlist'),
                        array('/^user\=add/','users/add'),
                        array('/^user\=list\&type=edit/','users/add'),
                        array('/^user\=list\&type=userinfo/','users/userinfo'),
                        array('/^user\=rank/','users/ranklist'),
                        array('/^user\=rank\&type=add/','users/rankadd'),
                        array('/^user\=rank\&type=edit/','users/rankadd'),
                        array('/^user\=rank\&type=del/','users/rankdel'),
            //评论
                        array('/^user\=comment/','comment/lists'),
            //评论
                        array('/^user\=commentart/','commentart/lists'),
            //商品促销活动
                        array('/^activity\=goodslist/','activity/salesGoods'),    //商品促销 活动列表
                        array('/^activity\=goodslist\&act=[add|edit]/','activity/salesGoodsEdit'),    //商品促销活动 编辑 
                        array('/^activity\=goodslist\&act=salesGoodsEditgoodslist/','activity/salesGoodsEditgoodslist'),    //参与促销的商品 
                        array('/^activity\=goodslist\&act=delete/','activity/salesGoodsDel'),    //商品促销活动  删除
            
                        array('/^activity\=orderlist/','activity/index'),   //订单促销活动
            
                        array('/^activity\=coupon$/','activity/coupon'),   //优惠券
                        array('/^activity\=coupon&type=send/','activity/couponSend'),
                        array('/^activity\=coupon&type=add/','activity/couponAdd'),
            //系统设置
                        array('/^system\=adminuser/','system/adminuser'),
                        array('/^system\=setup$/','system/setup'),
                        array('/^system\=setup\&type\=[a-z|A-Z|0-9]+/','system/setup'),
            //管理员
                        array('/^system\=adminuser\&type\=add/','system/addAdminuser'),
                        array('/^system\=adminuser\&type\=edit/','system/editAdminuser'),
                        array('/^system\=adminuser\&type\=delete/','system/deleeAdminuser'),
            //角色管理 
                        array('/^system\=role/','system/role'),
                        array('/^system\=role\&type\=add/','system/addrole'),
                        array('/^system\=role\&type\=edit/','system/editrole'),
                        array('/^system\=role\&type\=delete/','system/deleterole'),
                        array('/^mobile\=index/','index/index'),
            //商城其它功能模块
                        array('/^system\=area/','mall/areaadmin'),  //地区列表
                        array('/^system\=delivery/','mall/delivery'),  //配送方式
                        array('/^system\=delivery\&type=add/','mall/deliveryEdit'),  //添加编辑配送方式
                        array('/^system\=delivery\&type=setexpressDoc/','mall/setexpressDoc'),  //配送方式   快递单模板
                        array('/^system\=pay/','mall/pay'),  //配送方式
                        array('/^system\=pay\&type=edit/','mall/payedit'),  //配送方式
            //文章内容
                        array('/^article\=channel$/','article/channellist'),  //
                        array('/^article\=channel\&type=add/','article/addchannel'),  //
                        array('/^article\=channel\&type=edit/','article/addchannel'),  //
                        array('/^article\=channel\&type=delete/','article/deletechannel'),  //  
                        array('/^article\=field$/','article/fieldlist'),  //
                        array('/^article\=field\&type=add/','article/addfield'),  //
                        array('/^article\=field\&type=edit/','article/addfield'),  //
                        array('/^article\=field\&type=delete/','article/deletefield'),  //
                        array('/^article\=column$/','article/columnlist'),  // 栏目 
                        array('/^article\=column\&type=add/','article/addcolumn'),  //
                        array('/^article\=column\&type=edit/','article/addcolumn'),  //
                        array('/^article\=column\&type=delete/','article/deletecolumn'),  //
                        array('/^article\=list/','article/artlist'),  //  普通文章列表
                        array('/^article\=list\&type=[add|edit]/','article/artadd'),  // 编辑普通文章
                        array('/^article\=list\&type=delete/','article/deleteArticle'),  // 删除普通文章
            
                        array('/^article\=channel\-[a-z|A-Z|0-9]+$/','article/artlist'),  //这个是添加栏目时添加的菜单 ，将他匹配到 artlist 方法
                        array('/^article\=channel\-[a-z|A-Z|0-9]+\&type=[add|edit]/','article/artadd'),  //这个是添加栏目时添加的菜单
                        array('/^article\=channel\-[a-z|A-Z|0-9]+\&type=delete/','article/deleteArticle'),  //
                        array('/^article\=channel\-renshi\&type=jianlitoudi/','article/jianlitoudi'),  // 查看投递的简历
            //进销存
                        array('/^jinxiaocun=k/','jinxiaocun/inventory'),   // 仓库库存 首页 ,库存列表 ，按批次查看
                        array('/^jinxiaocun=k\&type=inventory_nums/','jinxiaocun/inventory_nums'),   //  库存列表  按数量查看
                        array('/^jinxiaocun=k\&type=warehouse/','jinxiaocun/warehouse'),   //  仓库列表
                        array('/^jinxiaocun=warehouse/','jinxiaocun/warehouse'),   //  仓库列表
                        array('/^jinxiaocun=m/','jinxiaocun/warehouse'),   //  仓库列表
                        array('/^jinxiaocun=k\&type=warehouse_add/','jinxiaocun/warehouse_add'),   //  仓库列表  
                        array('/^jinxiaocun=k\&type=inventory_records/','jinxiaocun/inventory_records'),   // 仓库库存 首页
//                        array('/^jinxiaocun=c/','jinxiaocun/purchasing'),   // 采购  
                        array('/^jinxiaocun=c/','jinxiaocun/purchasing_order'),   // 采购  
                        array('/^jinxiaocun=c\&type=plan/','jinxiaocun/purchasing_plan'),   //  采购  
                        array('/^jinxiaocun=c\&type=plan_add/','jinxiaocun/purchasing_plan_add'),   //  采购  
                        array('/^jinxiaocun=c\&type=order/','jinxiaocun/purchasing_order'),   //  采购  
                        array('/^jinxiaocun=c\&type=order_add/','jinxiaocun/purchasing_order_add'),   // 
                        array('/^jinxiaocun=c\&type=order_count/','jinxiaocun/order_count'),    //进货统计 
            
                        array('/^jinxiaocun=g/','jinxiaocun/supplier'),   // 供应商
                        array('/^jinxiaocun=g\&type=supplier_add/','jinxiaocun/supplier_add'),   // 供应商
                        array('/^jinxiaocun=k\&type=a_sales_record/','jinxiaocun/a_sales_record'),  //销售、出入库详情
                        
                        array('/^jinxiaocun=pandian/','jinxiaocun/pandian'),   //盘点
            //商户管理
                        array('/^business=list/','business/business_list'),  //商户列表
                        array('/^business=edit/','business/edit'),  //编辑商户
                        array('/^business=edit\&type=payedit/','business/payedit'),  //收银台支付配置
                        array('/^business=cashier/','business/cashier_account'),  //收银员账号
                        array('/^business=cashier_edit/','business/cashier_edit'),  //收银员编辑
            //自动进程管理 
                        array('/^jincheng=index/','jincheng/index'),   // 
            //微信公众 号  
                        array('/^weixin=index/','weixin/index'),   // 
                        array('/^weixin=index\&type=i/','weixin/otherset'),   // 
                        array('/^weixin=index\&type=b/','weixin/createMenu'),   // 
                        array('/^system\=sendmsglog/','system/sendmsglog'),  // 
                        array('/^weixin=index\&type=c/','weixin/qrcode'),
	);
}
?>
