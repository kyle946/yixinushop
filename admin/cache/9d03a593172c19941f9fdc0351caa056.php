<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> <html> <head> <title>YixinuSHOP商城系统后台管理</title> <meta http-equiv="X-UA-Compatible" content="IE=edge" /> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <script src="<?php echo __ROOT__; ?>static/jquery-1.8.1.min.js" type="text/javascript"></script> <link href="<?php echo __ROOT__; ?>static/style.css" rel="stylesheet" type="text/css"/> <script src="<?php echo __ROOT__; ?>static/main.js" type="text/javascript"></script> </head> <body> <div class="area_top w100" id="area_top"> <div class="menu"> <div class="logo fleft"><a href="/" ><img src="<?php echo __ROOT__; ?>static/logo_2.png" /></a></div> <?php if(isset($menu) && is_array($menu)):foreach($menu as $key=>$v){ ?>                    <?php if($querystring == $v['url']  or $parentid == $v['id']): ?> <div class='link fleft'><a style="color:#fff; background-color: #00C1DE;" href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a> </div> <?php else: ?> <div class='link fleft'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'><?php echo $v['name']; ?></a></div> <?php endif; ?> <?php } endif; ?> <div class="link fright" ><a href="<?php echo __ROOT__; ?>?act=logout">退出登录(<?php if(isset($userinfo_name)):echo $userinfo_name; endif; ?>)</a></div> <?php if($isfounder){ ?> <div class="link fright" ><a href="javascript:void(0)" onclick='delete_cache()'>清除缓存</a></div> <?php } ?> <div class="link fright" ><a target="_blank"  href="<?php echo 'http://'.MAIN_DOMAIN; ?>">查看前台</a></div> </div> </div> <div class="area_middle w100 clearfix" id="area_middle"> <div class="area_left" id="area_left"> <?php $t = false; ?> <?php if(isset($menu_second) && is_array($menu_second)):foreach($menu_second as $key=>$v){ ?>                    <?php  if($v['g']!=$t['g'] && !empty($t) ):  ?> <div class="hr w100"></div> <?php endif; ?> <?php if($querystring == matchurl($v['url']) ): ?> <div class='menu'><a class='hover' href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php else: ?> <div class='menu'><a href='<?php echo __ROOT__.'?'.$v['url']; ?>'> <font><?php if(isset($v['name'])):echo $v['name']; endif; ?></font></a></div> <?php endif; ?> <?php $t =$v; ?> <?php } endif; ?> </div> <div class="area_right" id="area_right"> <div class="content"> <style type="text/css"> ul.area1,div.area4 ul{ padding: 0px;} ul.area1 li{ list-style-type: none; width: 180px; float: left; padding: 6px 0px;} div.area4 {height: auto; padding: 10px;} div.area4 ul#imglist li{float: left;margin: 2px;border: 1px solid #d1d1d1;list-style-type: none;height: 90px;width:100px;overflow: hidden; position: relative;} div.area4 ul#imglist li img{width:100px; height: 90px; margin: 0px; padding: 0px; display: block;} div.area4 ul#imglist li .hoverArea{height: 180px;display: block; position: relative;} div.area4 ul#imglist li .hoverArea:hover{top: -20px;} div.area4 ul#imglist li .hoverArea a{ display: block; cursor: pointer; margin: 0px auto; padding: 0px; text-align: center;} #table2 tr th,#table2 tr td{text-align: center;} #goodsdesc{ width: 450px; height: 280px; } div.thumb2{text-indent: 0px; float: left;margin-top: -4px;} div.thumb2 div,div.thumb2 img{ width: 50px; height: 35px;margin:0px;} div.thumb2 div{overflow: hidden;} div.thumb2 div:hover{height: auto; overflow-y: auto;} div.thumb2 a{display: block; width:80%; margin: 0px auto; text-align: center; text-decoration: underline; cursor: pointer;color:#008DFC;} #addcolumn_r1 td{background-color: #fff; border-bottom: 1px solid #777; padding: 10px 0px;} #addcolumn_r1 td a.addcolumn_a1{display: inline-block;width: auto;padding: 6px;background-color: #e1e1e1;color: #555;margin-left: 4px;text-indent: 0px; text-decoration: none;} #addcolumn_r1 td a.addcolumn_a1:hover,#addcolumn_r1 td a.addcolumn_a2{display: inline-block;width: auto;padding: 6px;background-color: #0066AC;color: #fff;margin-left: 4px;text-indent: 0px; text-decoration: none;} #tab2,#tab3{height: 0px;} #tab1,#tab2,#tab3{ overflow: hidden;} #table2 tr td.noborder{ border:0px; text-align: left; } #table2 tr td.left{ text-align: left; } </style> <form method="post" action="" name="iform" onsubmit="editor.sync(); return icheck()" > <table class="w100 content_list"> <tr> <th width="180" style="text-align: left;">编辑 商品 </th> <th width="300"></th> <th></th> </tr> <tr id="addcolumn_r1" > <td colspan="3"> <a class="addcolumn_a2" onclick="tabSwitch_('tab1',this)" href="javascript:void(0);">基本信息</a> <a class="addcolumn_a1" onclick="tabSwitch_('tab2',this)" href="javascript:void(0);">商品属性</a> <a class="addcolumn_a1" onclick="tabSwitch_('tab3',this)" href="javascript:void(0);">描述信息</a> </td> </tr> </table> <div class="w100" id="tab1" > <table class="w100 content_list"> <tr> <th width="180" style="text-align: left;">基本信息</th> <th width="300"></th> <th></th> </tr> <tr> <td style="text-align: right;"><span class="cr">*</span><span class="fb">名称：</span></td> <td><textarea name="name" style="height:40px;" class="textarea3" ><?php if(isset($info['name'])):echo $info['name']; endif; ?></textarea></td> <td><span id="nametips" class="c1">商品的名称</span></td> </tr> <tr> <td style="text-align: right;"><span class="cr">*</span><span class="fb">附标题：</span></td> <td><textarea name="name2" style="height:40px;" class="textarea3" ><?php if(isset($info['name2'])):echo $info['name2']; endif; ?></textarea></td> <td><span id="name2tips" class="c1">一般显示活动、商品特性之类的信息</span></td> </tr> <tr> <td style="text-align: right;"><span class="cr">*</span><span class="fb">商品分类 [商品类型]：</span></td> <td> <select  name="catId" onchange="goodstypeselect()" > <option value="0">请选择</option> <?php if(isset($categroy_list) && is_array($categroy_list)):foreach($categroy_list as $key=>$v){ ?>                    <option <?php if(isset($info['catId']) && $info['catId']==$v['id']){ echo "selected='selected'"; }elseif( !isset($info['catId']) && $v['sysdefault']==1){ echo "selected='selected'";  } ?> value="<?php if(isset($v['id'])):echo $v['id']; endif; ?>"><?php if(isset($v['name'])):echo $v['name']; endif; ?>&nbsp;&nbsp;[<?php if(isset($v['typeName'])):echo $v['typeName']; endif; ?>]</option> <?php } endif; ?> </select> </td> <td><span id="catIdtips" class="c1">选择一个商品分类</span></td> </tr> <tr> <td style="text-align: right; vertical-align: top;"><span class="fb">图片：</span></td> <td colspan="2"> <div class="area3"><input onclick="insertImage({'divid':'imglist','inputname':'imgfiles[]','level':'5'})" type="button" class="bt1" value="添加图片" /></div> <div class="area4 clearfix"> <ul class="clearfix" id='imglist'> <?php  if( isset($info['imgs']) && is_array(unserialize($info['imgs'])) ): foreach(unserialize($info['imgs']) as $value): ?> <li> <div class="hoverArea"> <img border='0' src="<?php echo IMAGE_URL.$value; ?>"> <a onclick="deleteImg(this)" style="color: red;">删除图片</a> <input type="hidden" name="imgfiles[]" value="<?php if(isset($value)):echo $value; endif; ?>"> </div> </li> <?php  endforeach; endif; ?> </ul> </div> </td> </tr> <tr> <td style="text-align: right;"><span class="cr">*</span><span class="fb">排序：</span></td> <td><input type="text" class="input2" name="sort" value="<?php if(!isset($info['sort'])): echo 999; else: echo $info['sort'] ; endif; ?>" /></td> <td><span id="sorttips" class="c1">商品在前台排序位置</span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">是否称重计价商品：</span></td> <td> <label><input type="radio" name="chengzhong" value="1" <?php if(isset($info['status']) && $info['chengzhong']==1){ echo 'checked'; } ?> />按称重计价</label>&nbsp;&nbsp; <label><input type="radio" name="chengzhong" value="0" <?php if( !isset($info['status']) || !$info['chengzhong']){ echo 'checked'; } ?> />不按称重计价</label> </td> <td><span class="c1">重量必须设置为 1 kg，数量×重量×售价=商品购买价。</span></td> </tr> <tr> <td style="text-align: right;"><span class="fb">称重商品编码：</span></td> <td> <input type="number" name="weighing_code" class="input2" value="<?php if(isset($info['weighing_code'])):echo $info['weighing_code']; endif; ?>" onblur="ch.onecheck(this,'?c=goods&a=check_weighing_code&id=<?php if(isset($info['id'])):echo $info['id']; endif; ?>&val='+this.value,'该编码已被使用！')" maxlength="7"  /> </td> <td><span id="weighing_codetips" class="c1">该条码在使用电子称时做识别用，与商品编码无关</span></td> </tr> </table> <table class="w100 content_list"> <tr> <th width="120" style="text-align: left;">商品规格</th> <th width="300"><input type="button" value="选择规格" onclick="open3()" class="bt1" /></th> <th></th> </tr> <tr id='nohover' > <td style="border-bottom: 0px;" colspan="3"> <table id="table2" class="w100 content_list" border="0"> <thead> <tr> <th width='20%' ></th> <th width='20%' ></th> <th width='20%' ></th> <th width='20%' ></th> <th width='20%' ></th> </tr> </thead> <tbody> <?php if( isset($spec2) && !empty($spec2) ): ?> <?php if(isset($spec2) && is_array($spec2)):foreach($spec2 as $key=>$v){ ?>                    <tr> <td class='noborder' >编号：<?php if(isset($v['sn'])):echo $v['sn']; endif; ?><input type="hidden" name="id[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['id'])):echo $v['id']; endif; ?>" /><input type="hidden" name="sn[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>" /><input type="hidden" name="attribute[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['attribute'])):echo $v['attribute']; endif; ?>"> <input type="hidden" name="attributeStr[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['attributeStr'])):echo $v['attributeStr']; endif; ?>"></td> <td class='noborder' >规格：<?php if( empty($v['attributeStr']) ): echo '无规格'; else: echo $v['attributeStr']; endif; ?></td> <td class='noborder' >售价：<input type="text" placeholder="售价" name="shopPrice[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['shopPrice'])):echo $v['shopPrice']; endif; ?>" class="input21" /></td> <td class='noborder' >市场价：<input type="text" placeholder="市场价" name="marketPrice[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['marketPrice'])):echo $v['marketPrice']; endif; ?>" class="input21" /></td> <td class='noborder' >库存：<input type="text" placeholder="库存" name="numbers[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['numbers'])):echo $v['numbers']; endif; ?>" readonly="readonly" class="input21 bg2" /></td> </tr><tr> <td class='left' >条码：<input type="text" placeholder="条码" name="tiaoma[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['tiaoma'])):echo $v['tiaoma']; endif; ?>" class="input21" /></td> <td class='left' >重量：<input type="text" placeholder="重量" name="weight[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['weight'])):echo $v['weight']; endif; ?>" class="input21" /> kg</td> <td class='left' > <div class="clearfix"> <div class="thumb2" id="img<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>"> <?php if( !empty($v['thumb']) ): ?> <div><img src="<?php echo IMAGE_URL.$v['thumb']; ?>"><input type="hidden" name="imgfile[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['thumb'])):echo $v['thumb']; endif; ?>"><a onclick="deleteImgSingle(this)">删除图片</a></div> <?php endif; ?> </div> <input type="button" onclick="insertImage({'mode':'single','divid':'img<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>','inputname':'imgfile[<?php if(isset($key)):echo $key; endif; ?>]','level':'5'})" value="缩略图" class="bt1 fright" /> </div> </td> <td class='left' ></td> <td class='left' ></td> </tr> <?php } endif; ?> <?php else: ?> <tr> <td class='noborder' >编号：<?php if(isset($sn)):echo $sn; endif; ?><input type="hidden" name="sn[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($sn)):echo $sn; endif; ?>" /><input type="hidden" name="attribute[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['attribute'])):echo $v['attribute']; endif; ?>"> <input type="hidden" name="attributeStr[<?php if(isset($key)):echo $key; endif; ?>]" value="<?php if(isset($v['attributeStr'])):echo $v['attributeStr']; endif; ?>"></td> <td class='noborder' >规格：无规格</td> <td class='noborder' >售价：<input type="text" name="shopPrice[<?php if(isset($key)):echo $key; endif; ?>]" value="" class="input21" /></td> <td class='noborder' >市场价：<input type="text" name="marketPrice[<?php if(isset($key)):echo $key; endif; ?>]" class="input21" /></td> <td class='noborder' >库存：<input type="text" name="numbers[<?php if(isset($key)):echo $key; endif; ?>]" value="0" readonly="readonly" class="input21 bg2" /></td> </tr><tr> <td class='left' >条码：<input type="text" name="tiaoma[<?php if(isset($key)):echo $key; endif; ?>]" value="" class="input21" /></td> <td class='left' >重量：<input type="text" name="weight[<?php if(isset($key)):echo $key; endif; ?>]" value="1" class="input21" /> kg</td> <td class='left' > <div class="clearfix"> <div class="thumb2" id="img<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>"></div> <input type="button" onclick="insertImage({'mode':'single','divid':'img<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>','inputname':'imgfile[<?php if(isset($key)):echo $key; endif; ?>]','level':'5'})" value="缩略图" class="bt1 fright" /> </div> </td> <td></td> <td></td> </tr> <?php endif; ?> </tbody> </table> </td> </tr> <tr> <td></td> <td> <input type="submit" class="bt1" name="_submit_" value="保存" /> </td> <td><span class="c1">保存当前信息。</span></td> </tr> </table> </div> <div class="w100" id="tab2" <?php if(@!empty($_GET['id'])): ?>read="<?php if(isset($info['catId'])):echo $info['catId']; endif; ?>"<?php else: ?>read="0"<?php endif; ?> > <table class="w100 content_list"> <thead> <tr> <th width="150" style="text-align: left;">主体参数</th> <th></th> <th></th> </tr> </thead> <tbody> <?php if(@!isset($attrlist) or empty($attrlist)): ?><tr id="nohover" ><td style="height:100px; line-height: 100px;font-size:14px; color:#555;text-align: center;" colspan="3" >未选择 [商品类型] 或该类型没有手工录入的属性！</td></tr><?php endif; ?> <?php if(isset($attrlist) && is_array($attrlist)):foreach($attrlist as $key=>$v){ ?>        <tr id="nohover" > <td style="text-align: right;"><?php if(isset($v['sn'])):echo $v['sn']; endif; ?></td> <td colspan="3" style="text-indent:0px;" > <input type="hidden" name="attr[<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>][sn]" value="<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>" /> <?php foreach($v['val'] as $k_ => $v_): ?> <?php if($v_['mothod']==2): ?> <dl class="dlarea1" > <dt><?php if(isset($v_['name'])):echo $v_['name']; endif; ?>：</dt> <dd> <?php $tmp1=str_replace(array("\r\n", "\n", "\r"),",",trim($v_['attrValue']) ); $tmp2 = explode(",", $tmp1);?> <select name='attr[<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>][<?php if(isset($v_['mark'])):echo $v_['mark']; endif; ?>]' ><?php foreach($tmp2 as $v2): ?><option <?php if($v_['val']==$v2): ?>selected='selected'<?php endif; ?> value='<?php if(isset($v2)):echo $v2; endif; ?>' ><?php if(isset($v2)):echo $v2; endif; ?></option><?php endforeach; ?></select> </dd> </dl> <?php elseif($v_['mothod']==1): ?> <dl class="dlarea1" > <dt><?php if(isset($v_['name'])):echo $v_['name']; endif; ?>：</dt> <dd><input type="text" class="input2" name="attr[<?php if(isset($v['sn'])):echo $v['sn']; endif; ?>][<?php if(isset($v_['mark'])):echo $v_['mark']; endif; ?>]" value="<?php if(isset($v_['val'])):echo $v_['val']; endif; ?>"></dd> </dl> <?php endif; ?> <?php endforeach; ?> </td> </tr> <?php } endif; ?> </tbody> </table> <table class="w100 content_list"> <tr> <td></td> <td> <input type="submit" class="bt1" name="_submit_" value="保存" /> </td> <td><span class="c1"></span></td> </tr> </table>    </div> <div class="w100" id="tab3" > <table class="w100 content_list"> <tr> <th width="15%" style="text-align: left;">商品描述</th> <th></th> <th></th> </tr> <tr id='nohover' > <td colspan="4" > <textarea id="goodsdesc"  style="width:100%;height:600px;" name="goodsDesc" ><?php if(isset($info['goodsDesc'])):echo $info['goodsDesc']; endif; ?></textarea> </td> </tr> <tr> <td></td> <td> <input type="submit" class="bt1" name="_submit_" value="保存" /> </td> <td></td> </tr> </table> </div> </form> <script> $(function(){ var option = new Array(); option[0] = {name:'name',type:'regexp',msg:'名称应该为1-50个字符！',rule:/^.{1,50}$/,required:1}; option[1] = {name:'name2',type:'regexp',msg:'副标题应该为1-70个字符！',rule:/^.{1,70}$/,required:0}; option[2] = {name:'sort',type:'regexp',msg:'排序应该在1-999之间！',rule:/^[1-9]\d{0,2}$/};  option[3] = {name:'catId',type:'isempty',msg:'请选择一个类型！'};   option[4] = {name:'weighing_code',type:'regexp',msg:'2至7位数字！',rule:/^[1-9]{2,7}$/};  ch = new formCheck(option); }); function check_step1(){ var bool_ = false; var res = ch.start(); if(res==1){ bool_ = true; } return bool_; } function check_step3(){ var bool_ = true; $("#table2 input[type='text']").each(function(){ var text = $(this).attr("name"); var val = $(this).attr("value"); var rule1 = new RegExp(/^shopPrice.*$/); if(rule1.test(text)){ var rule2 = new RegExp(/^[0-9]{1,11}\.?\d{0,2}$/); if(!rule2.test(val)){ confirm_("售价数值不正确！请填一个有效的价格。"); return bool_ = false; } } var rule3 = new RegExp(/^numbers.*$/); if(rule3.test(text)){ var rule4 = new RegExp(/^[0-9]{1,11}$/); if(!rule4.test(val)){ confirm_("库存数值不正确！ 请填一个有效库存。"); return bool_ = false; } } }); return bool_; } function check_step2(){ var bool_ = true; /*var catId = $("select[name='catId']").attr('value'); if( parseInt(catId) != parseInt(cat_id) ){ confirm_("<font class='cr fb'>警告：</font>修改 [商品类型] 后，必须重新选择规格属性！"); bool_ = false; }*/ return bool_; } function icheck(){ var bool_ = false; if(check_step1()==true){ if(check_step2()==true){ if(check_step3()==true){ bool_ = true; } } } return bool_; }  function open3(){ deletemsg = kmsg("?c=goods&a=selectspecattr","点击生成规格",2,650,300); } </script> <script type="text/javascript" src="/static/k/kindeditor-min.js" ></script> <script> function tabSwitch_(id,e) { var arr = new Array(); arr[0] = 'tab1'; arr[1] = 'tab2'; arr[2] = 'tab3'; for(var i=0;i<arr.length;i++){ document.getElementById(arr[i]).style.height = '0px'; document.getElementById(arr[i]).style.padding = '0px'; } document.getElementById(id).style.height = 'auto'; document.getElementById(id).style.padding = '0px'; var aa = document.getElementById('addcolumn_r1').getElementsByTagName('a'); for(var i=0;i<arr.length;i++){ aa[i].className = 'addcolumn_a1'; } e.className = 'addcolumn_a2'; } function goodstypeselect(){ var catId = 0; catId = $("select[name='catId']").attr('value'); if(catId==0){ confirm_("（商品属性）必须根据 （商品类型）读取，请选择 （商品分类[商品类型]）！"); return false; }else{ var read=document.getElementById('tab2').getAttribute("read"); if(read==0 || read!=catId){ $.ajax({ url:"/?c=goods&a=getattrJson", data:'catid='+catId+'&goodsid=<?php if(isset($info['id'])):echo $info['id']; endif; ?>', cache:false, dataType:'json', success:function(d){ document.getElementById('tab2').setAttribute('read',catId); var list = document.getElementById('tab2').getElementsByTagName('tbody').item(0); $(list).html(""); $.each(d,function(i,item){ console.log(item); var html = ''; for( x in item.list ){ if(parseInt(item.list[x].mothod)==2){ var str = item.list[x].attrValue; var option_ = str.split(','); var option = '';  for( xx in option_ ){ option+='<option value="'+option_[xx]+'">'+option_[xx]+'</option>'; } html+='<dl class="dlarea1" > <dt>'+item.list[x].name+'：</dt> <dd><select name="attr['+item.sn+']['+item.list[x].mark+']">'+option+'</select></dd> </dl>'; } else{ html+='<dl class="dlarea1" > <dt>'+item.list[x].name+'：</dt> <dd><input type="text" class="input2" name="attr['+item.sn+']['+item.list[x].mark+']" value=""></dd> </dl>'; } } $(list).prepend('<tr id="nohover" > <td style="text-align: right;">'+item.sn+'</td> <td colspan="3" style="text-indent:0px;" > '+html+' </td> </tr>'); }); } }); } } } KindEditor.ready(function(K) { editor = K.create('textarea[name="goodsDesc"]'); }); $(function(){ goodstypeselect(); }); </script> </div> <div style="position: fixed; right:20px; bottom: 5px;color:#999" ><?php echo CONTROLLER_NAME.'/'.ACTION_NAME; ?></div> </div> </div> </body> </html>