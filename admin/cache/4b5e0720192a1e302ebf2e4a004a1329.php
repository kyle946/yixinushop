<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html> <head> <title>YixinuSHOP商城系统后台管理</title> <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <script src="<?php echo __ROOT__; ?>static/jquery-1.8.1.min.js" type="text/javascript"></script> <link href="<?php echo __ROOT__; ?>static/style.css" rel="stylesheet" type="text/css"/> <script src="<?php echo __ROOT__; ?>static/main.js" type="text/javascript"></script> </head> <body> <div class="area_top w100" id="area_top"> <div class="menu"> <div class="logo fleft"><a href="/" ><img src="<?php echo __ROOT__; ?>static/logo_2.png" /></a></div> <?php if(isset($menu) && is_array($menu)):foreach($menu as $key=>$v){ ?>                    <?php if($querystring == $v['url']  or $parentid == $v['id']): ?> <div class='link fleft'><a style="color:#fff; background-color: #00C1DE;" href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a> </div> <?php else: ?> <div class='link fleft'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a></div> <?php endif; ?> <?php } endif; ?> <div class="link fright" ><a href="<?php echo __ROOT__; ?>?act=logout">退出登录(<?php if(isset($userinfo_name)):echo $userinfo_name; endif; ?>)</a></div> <?php if($isfounder){ ?> <div class="link fright" ><a href="javascript:void(0)" onclick='delete_cache()'>清除缓存</a></div> <?php } ?> <div class="link fright" ><a target="_blank"  href="<?php echo 'http://'.MAIN_DOMAIN; ?>">查看前台</a></div> </div> </div> <div class="area_middle w100 clearfix" id="area_middle"> <div class="area_left" id="area_left"> <?php $t = false; ?> <?php if(isset($menu_second) && is_array($menu_second)):foreach($menu_second as $key=>$v){ ?>                    <?php  if($v['g']!=$t['g'] && !empty($t) ):  ?> <div class="hr w100"></div> <?php endif; ?> <?php if($querystring == matchurl($v['url']) ): ?> <div class='menu'><a class='hover' href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php else: ?> <div class='menu'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php endif; ?> <?php $t =$v; ?> <?php } endif; ?> </div> <div class="area_right" id="area_right"> <div class="content"> <div class="content_title w100 clearfix"> <a href="<?php echo __ROOT__; ?>?business=list"><img src="<?php echo __ROOT__; ?>static/icon/a1.png" /><font>列表</font></a> <a href="<?php echo __ROOT__; ?>?business=edit"><img src="<?php echo __ROOT__; ?>static/icon/a6.png" /><font>添加</font></a> </div> <div class="w100"> <form method="post" action="" name="iform" onsubmit="return icheck();" id='form1' > <table class="w100 content_list"> <tr> <th width="180" style="text-align: left;">编辑 - 商户</th> <th width="300"></th> <th></th> </tr> <tr> <td style="text-align: right;"><span class="fb">商户号：</span></td> <td><input type="text" class="input11 bg2" readonly='readonly' name="business_no"  value="<?php if(isset($info['business_no'])):echo $info['business_no']; endif; ?>" /></td> <td> </td> </tr> <tr> <td style="text-align: right;"><span class="fb">商户号 16位：</span></td> <td><input type="text"  class="input11 bg2" readonly='readonly' name="business_no_hex" value="<?php if(isset($info['business_no_hex'])):echo $info['business_no_hex']; endif; ?>" /></td> <td> </td> </tr> <tr> <td style="text-align: right;"><span class="fb">商户名称：</span></td> <td><input type="text" class="input11" onblur="ch.onecheck(this)" name="business_name" value="<?php if(isset($info['business_name'])):echo $info['business_name']; endif; ?>" /></td> <td><span id="business_nametips" class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">手机号码：</span></td> <td><input type="text" class="input11"  onblur="ch.onecheck(this)" name="business_mobile" value="<?php if(isset($info['business_mobile'])):echo $info['business_mobile']; endif; ?>" /></td> <td><span id="business_mobiletips" class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">真实姓名：</span></td> <td><input type="text" class="input11" onblur="ch.onecheck(this)" name="business_actual_name" value="<?php if(isset($info['business_actual_name'])):echo $info['business_actual_name']; endif; ?>" /></td> <td><span id="business_actual_nametips" class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">身份证号码：</span></td> <td><input type="text" class="input11" onblur="ch.onecheck(this)" name="business_id_number" value="<?php if(isset($info['business_id_number'])):echo $info['business_id_number']; endif; ?>" /></td> <td><span id="business_id_numbertips" class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">证件照片：</span></td> <td><img src="<?php echo 'http://'.MAIN_DOMAIN.'/idnumberimg/'.$info['idnumberimg']; ?>" /></td> <td><span  class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">主体信息：</span></td> <td> <textarea class='textarea3' name='business_subject_information'><?php if(isset($info['business_subject_information'])):echo $info['business_subject_information']; endif; ?></textarea> </td> <td> </td> </tr> <tr> <td style="text-align: right;"><span class="fb">介绍：</span></td> <td> <textarea class='textarea3' name='business_introduction'><?php if(isset($info['business_introduction'])):echo $info['business_introduction']; endif; ?></textarea> </td> <td> </td> </tr> <tr> <td style="text-align: right;"><span class="fb cy">登录密码：</span></td> <td><input type="password" class="input11" name="business_pwd"  onblur="ch.onecheck(this)" value="" /></td> <td><span id="business_pwdtips" class="c1">如果不修改密码请留空</span></td> </tr> <tr> <td style="text-align: right;"><span class="fb cy">确认密码：</span></td> <td><input type="password" class="input11"   onblur="ch.onecheck(this)" name="business_pwd2" value="" /></td> <td><span id="business_pwd2tips" class="c1"></span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">余额：</span></td> <td><input type="text" class="input11 bg2" readonly='readonly'  onblur="ch.onecheck(this)" name="business_balance" value="<?php if(isset($info['business_balance'])):echo $info['business_balance']; endif; ?>" /></td> <td><span id="moneytips" class="c1">商户余额，默认为0，单位为RMB 元</span></td> </tr> <tr id='nohover'> <td style="text-align: right;"><span class="fb">状态：</span></td> <td colspan="2"> <p> <label><input id='business_status_1' type="checkbox" name="business_status[]"  value="1" <?php if(@$info['business_status']&1){ ?>checked='checked'<?php } ?> />正常</label>&nbsp;&nbsp; <label><input id='business_status_2' type="checkbox" name="business_status[]" value="2" <?php if(@$info['business_status']&2){ ?>checked='checked'<?php } ?> />未通过认证</label>&nbsp;&nbsp; <label><input id='business_status_4' type="checkbox" name="business_status[]" value="4" <?php if(@$info['business_status']&4){ ?>checked='checked'<?php } ?> />冻结账号</label>&nbsp;&nbsp; <label><input id='business_status_8' type="checkbox" name="business_status[]" value="8" <?php if(@$info['business_status']&8){ ?>checked='checked'<?php } ?> />正在申请认证…</label> </p> <p>&nbsp; <span class="c1">说明 ：都不选择时，系统默认选择 [未通过认证] 状态，商户账号、收银账号和商户的商品会</span><span class="cr">全部冻结</span> </p> </td> </tr> <tr> <td style="text-align: right;"><span class="fb">账号冻结原因：</span></td> <td> <textarea class='textarea3' name='business_freeze_cause'><?php if(isset($info['business_freeze_cause'])):echo $info['business_freeze_cause']; endif; ?></textarea> </td> <td> </td> </tr> <tr> <td style="text-align: right;"><span class="fb">注册时间：</span></td> <td><input type="text" class="input11 bg2" readonly='readonly' name="createTime"  onblur="ch.onecheck(this)" value="<?php if(isset($info['createTime'])):echo $info['createTime']; endif; ?>" /></td> <td></td> </tr> <tr> <td style="text-align: right;"><span class="fb">最后一次登录时间：</span></td> <td><input type="text" class="input11 bg2" readonly='readonly' name="lastlogintime"  onblur="ch.onecheck(this)" value="<?php if(isset($info['lastlogintime'])):echo $info['lastlogintime']; endif; ?>" /></td> <td></td> </tr> <tr> <td></td> <td> <button class="bt1" onclick='submit2();' >通过审核 并查看下一个</button>&nbsp;&nbsp;&nbsp;&nbsp; <a href='/?business=edit&no=<?php if(isset($next_one)):echo $next_one; endif; ?>' class='bt1'>查看下一个未审核商户</a> </td> <td><span class="c1">审核通过后，会同步账号(手机号码)和密码到管理员账号</span></td> </tr> <tr> <td></td> <td> <button class="bt1" onclick='submit3();' >保存 并查看下一个</button> </td> <td><span class="c1"></span></td> </tr> <tr> <td></td> <td> <input type='hidden' name='next' value='0' /> <input type="submit" class="bt1" name="_submit_" value="保存" /> </td> <td></td> </tr> <tr><td colspan="4" ></td></tr> </table> </form> <script> $(function(){ var option = new Array(); option[0] = {name:'business_name',type:'regexp', msg: '1到22个字符，不能包括： < > \ = / . ? \'  | & \"', rule: /^[^<>\\=/.?+'|&\"]{1,22}$/, required: 1}; option[1] = {name:'business_mobile',type:'mobile',msg:'请输入正确的手机号码！'};  option[2] = {name:'business_actual_name',type:'regexp', msg: '1到22个字符，不能包括： < > \ = / . ? \'  | & \"', rule: /^[^<>\\=/.?+'|&\"]{1,22}$/, required: 1}; option[3] = {name:'business_id_number',type:'regexp',msg:'运营者  身份证号码应该为15 - 18个字符！',rule:/^[0-9|a-z]{15,18}$/,required:2};  option[4] = {name:'business_pwd',type:'password',msg:'输入一个 6 - 16个字符的密码！' ,required:2}; option[5] = {name:'business_pwd2',type:'password2',msg:'两次密码输入不一致！'}; ch = new formCheck(option); }); function submit2(){ $("#business_status_1").attr("checked","checked"); $("#business_status_2").removeAttr("checked"); $("#business_status_4").removeAttr("checked"); $("#business_status_8").removeAttr("checked"); $("input[name=next]").val(1); $("#form1").submit(); } function submit3(){ $("input[name=next]").val(1); $("#form1").submit(); } function icheck(){ var bool_ = false; var res = ch.start(); if(res==1){ bool_ = true; } return bool_; } </script> </div> </div> <div style="position: fixed; right:20px; bottom: 5px;color:#999" ><?php echo CONTROLLER_NAME.'/'.ACTION_NAME; ?></div> </div> </div> </body> </html>