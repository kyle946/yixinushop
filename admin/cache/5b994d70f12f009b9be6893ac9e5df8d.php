<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html> <head> <title>YixinuSHOP商城系统后台管理</title> <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <script src="<?php echo __ROOT__; ?>static/jquery-1.8.1.min.js" type="text/javascript"></script> <link href="<?php echo __ROOT__; ?>static/style.css" rel="stylesheet" type="text/css"/> <script src="<?php echo __ROOT__; ?>static/main.js" type="text/javascript"></script> </head> <body> <div class="area_top w100" id="area_top"> <div class="menu"> <div class="logo fleft"><a href="/"  ><img src="<?php echo __ROOT__; ?>static/logo_2.png" /></a></div> <?php if(isset($menu) && is_array($menu)):foreach($menu as $key=>$v){ ?>                    <?php if($querystring == $v['url']  or $parentid == $v['id']): ?> <div class='link fleft'><a style="color:#fff; background-color: #00C1DE" href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a> </div> <?php else: ?> <div class='link fleft'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a></div> <?php endif; ?> <?php } endif; ?> <div class="link fright" ><a href="<?php echo __ROOT__; ?>?act=logout">退出登录(<?php if(isset($userinfo_name)):echo $userinfo_name; endif; ?>)</a></div> <?php if(@$isfounder){ ?> <div class="link fright" ><a href="javascript:void(0)" onclick='delete_cache()'>清除缓存</a></div> <?php } ?> <div class="link fright" ><a target="_blank"  href="<?php echo 'http://'.MAIN_DOMAIN; ?>">查看前台</a></div> </div> </div> <div class="area_middle w100 clearfix" id="area_middle"> <div class="area_left" id="area_left" style="width:0px;" ></div> <div class="area_right w100 clearfix" id="area_right" > <div class="content clearfix"> <div class="w100 clearfix"> <div class="w100 h10"></div> <div class="layout2_area4" > <a href='/?goods=edit'  ><img src="<?php echo __ROOT__; ?>static/icon/tianjia.png" /><span>添加商品</span></a> <a href='/?jinxiaocun=c&type=order_add' ><img src="<?php echo __ROOT__; ?>static/icon/a2.png" /><span>采购入库</span></a> <a href='/?jinxiaocun=k' ><img src="<?php echo __ROOT__; ?>static/icon/kucun.png" /><span>库存查看</span></a> <a href='/?goods=list&numbers=1' ><img src="<?php echo __ROOT__; ?>static/icon/jinggao.png" /><span>库存不足</span></a> <a href='/?goods=imgmanage' ><img src="<?php echo __ROOT__; ?>static/icon/tupianguanli.png" /><span>图片管理</span></a> <a href='/?order=alist' ><img src="<?php echo __ROOT__; ?>static/icon/dingdan.png" /><span>订单(已支付)</span></a> <?php if($usermark=='business'){ ?> <a href='/?c=index&a=business_base_info'  ><img src="<?php echo __ROOT__; ?>static/icon/a7.png" /><span style="color:#71d800;">基本信息</span></a> <a href='/?c=index&a=business_config'  ><img src="<?php echo __ROOT__; ?>static/icon/a7.png" /><span style="color:#f60;">收银台配置</span></a> <?php } ?> </div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="layout2_area3 clearfix" > <div class="layout2_area11" > <table class="content_list"> <tr class="bg3"> <th colspan="2" width="400" style="text-align: left;">登录信息 (<?php if(isset($info['user_role'])):echo $info['user_role']; endif; ?>)</th> </tr> <tr> <td style="text-align: right;"><span class="fb">商户名称：</span></td> <td><span><?php if(isset($info['business_name'])):echo $info['business_name']; endif; ?></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">商户号：</span></td> <td><span><?php if(isset($info['business_no'])):echo $info['business_no']; endif; ?>(<?php if(isset($info['business_no_hex'])):echo $info['business_no_hex']; endif; ?>)</span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">上一次登录：</span></td> <td><span><?php if(isset($info['lastTime'])):echo $info['lastTime']; endif; ?></span></td> </tr> </table> </div> <div class="layout2_area11" > <table class="content_list"> <tr class="bg4"> <th width="480" style="text-align: left;">综合信息</th> </tr> <tr> <td>今日销售额：</td> </tr> <tr> <td>今日订单数：</td> </tr> <tr> <td>&nbsp;&nbsp;</td> </tr> </table> </div> <div class="layout2_area11" style="float: right; margin-right: 0px; width: 31%;" > <table class="content_list"> <tr class="bg2"> <th width="480" style="text-align: left;">系统通知&nbsp;&nbsp;<a href="#" class="c2">[查看更多]</a></th> </tr> <tr> <td>信息</td> </tr> <tr> <td>信息</td> </tr> <tr> <td>信息</td> </tr> </table> </div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="layout2_area1" > <table class="w100 content_list"> <tr> <th width="180" style="text-align: left;">系统信息</th> <th width="300"></th> <th></th> </tr> <tr> <td style="text-align: right;"><span class="fb">服务器操作系统：</span></td> <td><span><?php if(isset($info['os'])):echo $info['os']; endif; ?></span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">运行环境：</span></td> <td><span><?php if(isset($info['web'])):echo $info['web']; endif; ?> , php/<?php if(isset($info['version'])):echo $info['version']; endif; ?></span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">上传限制：</span></td> <td><span><?php if(isset($info['uploadSize'])):echo $info['uploadSize']; endif; ?></span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">数据库版本：</span></td> <td><span>mysql/<?php if(isset($info['dbversion'])):echo $info['dbversion']; endif; ?></span></td> <td>&nbsp;</td> </tr> </table> </div> <div class="layout2_area2" > <table class="w100 content_list"> <tr> <th width="180" style="text-align: left;">产品团队</th> <th width="300"></th> <th></th> </tr> <tr> <td style="text-align: right;"><span class="fb">总策划：</span></td> <td><span>向露 </span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">产品设计及研发：</span></td> <td><span>异新优网络科技</span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">界面用户体验：</span></td> <td><span>异新优网络科技</span></td> <td>&nbsp;</td> </tr> <tr> <td style="text-align: right;"><span class="fb">官方网站：</span></td> <td><span><a href="http://www.yixinu.com">www.yixinu.com</a></span></td> <td>&nbsp;</td> </tr> </table> </div> </div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="w100 h10 fleft" >&nbsp;</div> <div class="w100 h10 fleft" >&nbsp;</div> </div> </div> </div> </div> </body> </html>