<!DOCTYPE html> <html> <head> <title><?php if(isset($webtitle_)):echo $webtitle_; endif; ?> <?php if(isset($webconfig['webtitle'])):echo $webconfig['webtitle']; endif; ?></title> <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> <meta id="viewport" name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,initial-scale=1.0,user-scalable=no"/> <meta name="apple-mobile-web-app-capable" content="yes" /> <script src="/static/jquery-1.8.1.min.js" type="text/javascript"></script> <script src="/static/layer.js" type="text/javascript"></script> <script src="/static/main.js" type="text/javascript"></script> <link href="/static/mobile.css?v=<?php echo time(); ?>" rel="stylesheet" type="text/css"/> <link href="/static/need/layer.css" rel="stylesheet" type="text/css"/> <script>imageUrl = '<?php echo IMAGE_URL; ?>'; rootPath = '<?php echo __ROOT__; ?>';</script> </head> <body class="maxwidth"> <div class="w100 clearfix" > <header class="mainMenu3 clearfix theme_background_color theme_border_color_bottom" > <div class="a1" ><i class="iconfont" >&#xe697</i></div> <div class="a2" > <div class="b4" ><?php if(isset($layout_title)):echo $layout_title; endif; ?></div> </div> <div class="a3" > <div><a href='<?php echo createLink("index/index"); ?>' ><span>首页</span></a> </div> </div> </header> <div class="hr" ></div> <div class="w100" > <div class="goodslist clearfix" > <ul> <?php if(isset($goodslist) && is_array($goodslist)):foreach($goodslist as $key=>$v){ ?>            <li> <div class="left" > <?php if( $v['numbers']<KUCUNBUZU ){ ?> <div class="left1" style="margin-left: 2rem; height: 1.2rem;" ></div> <?php }elseif( $v['xiangou']>0 && $v['xiangouuser']>0 && $v['xiangouuser']>=$v['xiangou'] ){ ?> <div class="left1" style="margin-left: 2rem; height: 1.2rem;" ></div> <?php }else{ ?> <div class="left1" ><i init="0" class="iconfont" >&#xe6ad;</i></div> <?php } ?> <a href="javascript:void(0);" ><img src="<?php echo IMAGE_URL; ?><?php if(isset($v['thumb'])):echo $v['thumb']; endif; ?>" /></a> </div> <div class="right" > <a class="a1" href="<?php echo createLink('index/item',array('id'=>$v['goodsid']) ); ?>"><div class="title" ><?php if(isset($v['goodsname'])):echo $v['goodsname']; endif; ?></div></a> <a href="<?php echo createLink('index/item',array('id'=>$v['goodsid']) ); ?>"> <div class="title" ><?php if(isset($v['attributeStr'])):echo $v['attributeStr']; endif; ?></div> </a> <a href="<?php echo createLink('index/item',array('id'=>$v['goodsid']) ); ?>"> <div class="title2" ><?php if(isset($v['business_name'])):echo $v['business_name']; endif; ?></div> </a> <?php if( $v['numbers']<KUCUNBUZU ){ ?> <div class="price" >&yen;<?php if(isset($v['shopPrice'])):echo $v['shopPrice']; endif; ?>&nbsp;&nbsp;&nbsp;&nbsp;库存不足！</div> <?php }elseif( $v['xiangou']>0 && $v['xiangouuser']>0 && $v['xiangouuser']>=$v['xiangou'] ){ ?> <div class="price" >&yen;<?php if(isset($v['shopPrice'])):echo $v['shopPrice']; endif; ?>&nbsp;&nbsp;&nbsp;&nbsp;[限购] 今天已买</div> <?php }else{ ?> <div class="price" >&yen;&nbsp;<?php if(isset($v['shopPrice'])):echo $v['shopPrice']; endif; ?></div> <div class="button1" data-goodsid="<?php if(isset($v['goodsid'])):echo $v['goodsid']; endif; ?>" data-id="<?php if(isset($v['id'])):echo $v['id']; endif; ?>" data-price="<?php if(isset($v['shopPrice'])):echo $v['shopPrice']; endif; ?>" > <i class="i2" >+</i> <input type="text"  name="num_<?php if(isset($v['goodsid'])):echo $v['goodsid']; endif; ?>" readonly="readonly" value="<?php if(isset($v['goodsnum'])):echo $v['goodsnum']; endif; ?>" /> <i class="i1" >-</i> <i class="i3" >删</i> </div> <?php } ?> </div> <div class="right1" ></div> </li> <?php } endif; ?> </ul> </div> </div> <div class="hg10 w100" ></div> <div class="hg10 w100" ></div> <div class="shoppingcart_jiesuan w100 maxwidth" > <div class="left" > <i init="0" class="iconfont" >&#xe6ad;</i> <div class="text" >全选</div> </div> <div class="right2" > <a href="javascript:void(0);" >结算</a> </div> <div class="right1" > <div class="text" >合计</div> <div class="total" >&yen;&nbsp;<font>0.00</font></div> </div> </div> <script> publicMethod = {}; $(function(){ publicMethod.init(); publicMethod.buycartfun(); }); publicMethod.init = function(){ var w1 = parseInt( $(".goodslist").width() ); var w2 = parseInt( $(".goodslist .left").width() ); $(".goodslist .right").css({'width':w1-w2-15}); }; publicMethod.buycartfun = function(){ $(".goodslist li").each(function(i){ var obj = $(this).find(".right .button1"); var this_ = this; $(obj).find("i.i2").on('click', function(){ var val = parseInt($(obj).find("input").val()) ; if(val<999) { $(obj).find("input").val( val+1 );  goodsfun.ccgn($(obj).data('goodsid'),val+1); publicMethod.computertotal(); } }); $(obj).find("i.i1").on('click', function(){ var val = parseInt($(obj).find("input").val()) ; if(val>1) { $(obj).find("input").val( val-1 );  goodsfun.ccgn($(obj).data('goodsid'),val-1); publicMethod.computertotal(); } }); $(obj).find("i.i3").on('click', function(){ goodsfun.ccgn($(obj).data('goodsid'),-1); }); $(this).find(".left").on('click', function(){ var input = $(obj).find('input'); if( !input.attr('init') || input.attr('init')==0  ){ $(this).find(".left1 i").css({'background-color':'#f00','color':'#fff','border':'.1rem solid #fff'}); input.attr('init',1); $(obj).find(".i3").css({"display":"block"}); $(this_).find(".right a.a1").css({"width":"80%"}); publicMethod.computertotal(); }else{ $(this).find(".left1 i").css({'background-color':'#fff','color':'#fff','border':'.1rem solid #dfdfdf'}); input.attr('init',0); $(obj).find(".i3").css({"display":"none"}); $(this_).find(".right a.a1").css({"width":"100%"}); publicMethod.computertotal(); } }); }); $(".shoppingcart_jiesuan .left").on('click',function(){ if( $(this).find("i").attr('init')==0 ){ $(this).find("i").css({'background-color':'#f00' }); $(this).find("i").attr('init',1); $(".goodslist li .right .button1 input").attr('init',1); $(".goodslist .left1 i").css({'background-color':'#f00','color':'#fff','border':'.1rem solid #fff'}); $(".goodslist li .right .button1 .i3").css({"display":"block"}); $(".goodslist li .right a.a1").css({"width":"80%"}); publicMethod.computertotal(); }else{ $(this).find("i").css({'background-color':'#999' }); $(this).find("i").attr('init',0); $(".goodslist li .right .button1 input").attr('init',0); $(".goodslist .left1 i").css({'background-color':'#fff','color':'#fff','border':'.1rem solid #dfdfdf'}); $(".goodslist li .right .button1 .i3").css({"display":"none"}); $(".goodslist li .right a.a1").css({"width":"100%"}); total = 0; $(".shoppingcart_jiesuan .right1 .total font").text( total.toFixed(2) ); } }); $(".shoppingcart_jiesuan .right2 a").on('click',function(){ publicMethod.buynow(); }); }; publicMethod.selectgoods = ''; publicMethod.computertotal = function(){ publicMethod.selectgoods = ''; var button1dom = $(".goodslist li .right .button1").get(); var  total = 0.00; for(x in button1dom){ var obj = button1dom[x]; if( $(obj).find('input').attr('init')==1 ){ var t = parseFloat($(obj).data('price')) * parseFloat($(obj).find('input').val()); total = total + t; publicMethod.selectgoods += $(obj).data("id")+','; } } $(".shoppingcart_jiesuan .right1 .total font").text( total.toFixed(2) ); }; publicMethod.buynow = function(){ $.getJSON(rootPath+'?v=/goods/ConfirmAnOrderChangeGoods',{'id':publicMethod.selectgoods,'type':2},function(d){ console.log(d); if(d.status==1){ window.location.href = '<?php echo createLink("goods/ConfirmAnOrder/",array("clear"=>1)); ?>';  }else if(d.status==2){ window.location.href = '<?php echo createLink("u/login",array("back"=>base64_encode("?v=/goods/shoppingcartList")) ); ?>'; } }); }; </script> <div class="hg40 w100" ></div> </div> </body> </html> 