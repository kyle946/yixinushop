<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html> <head> <title>YixinuSHOP商城系统后台管理</title> <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <script src="<?php echo __ROOT__; ?>static/jquery-1.8.1.min.js" type="text/javascript"></script> <link href="<?php echo __ROOT__; ?>static/style.css" rel="stylesheet" type="text/css"/> <script src="<?php echo __ROOT__; ?>static/main.js" type="text/javascript"></script> </head> <body> <div class="area_top w100" id="area_top"> <div class="menu"> <div class="logo fleft"><a href="/" ><img src="<?php echo __ROOT__; ?>static/logo_2.png" /></a></div> <?php if(isset($menu) && is_array($menu)):foreach($menu as $key=>$v){ ?>                    <?php if($querystring == $v['url']  or $parentid == $v['id']): ?> <div class='link fleft'><a style="color:#fff; background-color: #00C1DE;" href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a> </div> <?php else: ?> <div class='link fleft'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a></div> <?php endif; ?> <?php } endif; ?> <div class="link fright" ><a href="<?php echo __ROOT__; ?>?act=logout">退出登录(<?php if(isset($userinfo_name)):echo $userinfo_name; endif; ?>)</a></div> <?php if($isfounder){ ?> <div class="link fright" ><a href="javascript:void(0)" onclick='delete_cache()'>清除缓存</a></div> <?php } ?> <div class="link fright" ><a target="_blank"  href="<?php echo 'http://'.MAIN_DOMAIN; ?>">查看前台</a></div> </div> </div> <div class="area_middle w100 clearfix" id="area_middle"> <div class="area_left" id="area_left"> <?php $t = false; ?> <?php if(isset($menu_second) && is_array($menu_second)):foreach($menu_second as $key=>$v){ ?>                    <?php  if($v['g']!=$t['g'] && !empty($t) ):  ?> <div class="hr w100"></div> <?php endif; ?> <?php if($querystring == matchurl($v['url']) ): ?> <div class='menu'><a class='hover' href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php else: ?> <div class='menu'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php endif; ?> <?php $t =$v; ?> <?php } endif; ?> </div> <div class="area_right" id="area_right"> <div class="content"> <div class="content_title w100 clearfix"> <a href="<?php echo __ROOT__; ?>?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>"><img src="<?php echo __ROOT__; ?>static/icon/a1.png" /><font>列表</font></a> <a href="<?php echo __ROOT__; ?>?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=add"><img src="<?php echo __ROOT__; ?>static/icon/a6.png" /><font>添加</font></a> <div class="search" > <span>标题：</span> <input type="text" class="input2" name="title" value="<?php if(isset($_GET['title'])):echo $_GET['title']; endif; ?>" onkeyup="keydown(function(){ privateMethod.search(); })" /> <span>栏目：</span> <select name="cid"> <option value="0" >请选择</option> <?php if(isset($channelList) && is_array($channelList)):foreach($channelList as $key=>$v){ ?>            <option value="<?php if(isset($v['id'])):echo $v['id']; endif; ?>" <?php if(@@$_GET['cid']==$v['id']): ?>selected="selected"<?php endif; ?> ><?php if(isset($v['name'])):echo $v['name']; endif; ?></option> <?php } endif; ?> </select> &nbsp;&nbsp; <input type="button" class="bt1" onclick="privateMethod.search()" value="搜索" /> </div> </div> <div class="w100"> <?php if( $_GET['article']=='channel-renshi' ){ ?> <table class="content_list w100"> <tr> <th>ID</th> <th>标题</th> <th>工作地点</th> <th>薪资</th> <th>学历</th> <th>发布时间</th> <th>权限</th> <th>操作</th> </tr> <?php if(isset($list) && is_array($list)):foreach($list as $key=>$v){ ?>            <tr> <td><?php if(isset($v['id'])):echo $v['id']; endif; ?></td> <td><?php if(isset($v['title'])):echo $v['title']; endif; ?></td> <td><?php if(isset($v['didian'])):echo $v['didian']; endif; ?></td> <td><?php if(isset($v['xinzi'])):echo $v['xinzi']; endif; ?></td> <td><?php if(isset($v['xueli'])):echo $v['xueli']; endif; ?></td> <td><?php if(isset($v['addtime'])):echo $v['addtime']; endif; ?></td> <td> <font style="color:<?php echo printColor(); ?>"> <?php  switch($v['arcrank']): ?><?php case 'o': ?>开放浏览<?php break; ?><?php case 'c': ?>待审核<?php break; ?><?php case 'oc': ?>禁止评论<?php break; ?> <?php endswitch; ?> </font> </td> <td> <a href="<?php echo __ROOT__; ?>?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=jianlitoudi&id=<?php if(isset($v['id'])):echo $v['id']; endif; ?>"> 简历<?php if($v['read']>0){ ?><font style='color:#f00;'>(<?php if(isset($v['read'])):echo $v['read']; endif; ?>)</font><?php } ?> </a>&nbsp;&nbsp; <a href="<?php echo __ROOT__; ?>?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=edit&id=<?php if(isset($v['id'])):echo $v['id']; endif; ?>">修改</a>&nbsp;&nbsp; <a href="javascript:confirm_('你确定要删除这个招聘信息吗？','/?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=delete&id=<?php if(isset($v['id'])):echo $v['id']; endif; ?>')" >删除</a></td> </tr> <?php } endif; ?> </table> <?php }else{ ?> <table class="content_list w100"> <tr> <th>ID</th> <th>标题</th> <th>栏目</th> <th>创建时间</th> <th>权限</th> <th>操作</th> </tr> <?php if(isset($list) && is_array($list)):foreach($list as $key=>$v){ ?>            <tr> <td><?php if(isset($v['id'])):echo $v['id']; endif; ?></td> <td><?php if(isset($v['title'])):echo $v['title']; endif; ?></td> <td> <font style="color:<?php echo printColor(); ?>"><?php if(isset($v['cname'])):echo $v['cname']; endif; ?></font></td> <td><?php if(isset($v['addtime'])):echo $v['addtime']; endif; ?></td> <td> <font style="color:<?php echo printColor(); ?>"> <?php  switch($v['arcrank']): ?><?php case 'o': ?>开放浏览<?php break; ?><?php case 'c': ?>待审核<?php break; ?><?php case 'oc': ?>禁止评论<?php break; ?> <?php endswitch; ?> </font> </td> <td> <a href="<?php echo __ROOT__; ?>?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=edit&id=<?php if(isset($v['id'])):echo $v['id']; endif; ?>">修改</a>&nbsp;&nbsp; <a href="javascript:confirm_('你确定要删除这篇文章吗？','/?article=<?php if(isset($_GET['article'])):echo $_GET['article']; endif; ?>&type=delete&id=<?php if(isset($v['id'])):echo $v['id']; endif; ?>')" >删除</a></td> </tr> <?php } endif; ?> </table> <?php } ?> <div id="page" class="clearfix"><?php if(isset($pageinfo)):echo $pageinfo; endif; ?></div> </div> <script type="text/javascript" > privateMethod = {}; privateMethod.search = function(){ var data = ''; var title = $("input[name='title']").attr('value'); if(title!=''){ data += '&title='+title; } var cid = $("select[name='cid']").attr('value'); if(cid!=0){ data += '&cid='+cid; } var url = '/?<?php echo 'article='.$_GET['article']; ?>' + data; window.location.replace(url); } </script> </div> <div style="position: fixed; right:20px; bottom: 5px;color:#999" ><?php echo CONTROLLER_NAME.'/'.ACTION_NAME; ?></div> </div> </div> </body> </html>