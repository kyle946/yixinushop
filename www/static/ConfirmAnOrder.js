$(function(){
    
    //收货地址
    $(".ConfirmAnOrder_area1 .area1 .a1").click(function(){
        //保存ID
        goodsOrder.selectAddress( $(this).attr('id') );
        //修改样式
        $('.ConfirmAnOrder_area1 .area1 .a1').css({'border':'2px solid #e1e1e1'});
        $('.ConfirmAnOrder_area1 .area1 .a1 i').html('');
        $(this).css({'border':'2px solid #f00'});
        $(this).find("i").html('已选');
    });
    //支付方式
    $(".ConfirmAnOrder_area1 .area3 .a1").click(function(){
        //保存ID
        goodsOrder.selectPay( $(this).attr('id') );
        //
        $('.ConfirmAnOrder_area1 .area3 .a1').css({'border':'2px solid #e1e1e1'});
        $('.ConfirmAnOrder_area1 .area3 .a1 i').html('');
        $(this).css({'border':'2px solid #f00'});
        $(this).find("i").html('已选');
    });
    //配送方式
    $(".ConfirmAnOrder_area1 .area6 .a1").click(function(){
        //判断有没有选择  收货 地址 
        var address = document.getElementsByName('addressid').item(0).value;
        if(address==null || address==0){
            confirm_('请先选择一个收货地址，再选择配送方式！');
            return ;
        }
        goodsOrder.selectFreight( $(this).attr('id') );
        
        $('.ConfirmAnOrder_area1 .area6 .a1').css({'border':'2px solid #e1e1e1'});
        $('.ConfirmAnOrder_area1 .area6 .a1 i').html('');
        $(this).css({'border':'2px solid #f00'});
        $(this).find("i").html('已选');
    });
    
    goodsOrder = {};
    //修改商品数量
    goodsOrder.changegoodsNum = function(goodsid,num){
            $.ajax({
                url:rootPath+'?c=goods&a=ConfirmAnOrderChangeGoods',
                data:'goodsid='+goodsid+'&num='+num,
                dataType:'json',
                cache:false,
                type:'get',
                success:function(data){
        //            console.log(data);
                        if(data.status==1){
                            window.location.href = rootPath+'?c=goods&a=ConfirmAnOrder';
                        }
                    }
            });
    }
    //添加新地址
    goodsOrder.addNewAddress = function(){
        $.get(rootPath+'?c=goods&a=_common&act=orderAddNewAddress',function(d){
            deletemsg=kmsg(d,'添加新地址',1,700,300);
            $("#address1").setaddr({proviceSn:430});
        });
    }
    //添加新地址  -  提交数据
    goodsOrder.addNewAddressDone = function(){
        if( goodsOrder.addNewAddressCheck()!=false ){
            document.getElementById('addNewAddress2').submit();
        }
    }
    //添加新地址  -  检查数据
    goodsOrder.addNewAddressCheck = function(){
        
        var t = document.getElementsByName('provice').item(0).value;
        if(!t || t==0){
            confirm_('请完善区域信息！',null,300,120);
            return false;
        }
        var t = document.getElementsByName('city').item(0).value;
        if(!t || t==0){
            confirm_('请完善区域信息！',null,300,120);
            return false;
        }
        var t = document.getElementsByName('county').item(0).value;
        if(!t || t==0){
            confirm_('请完善区域信息！',null,300,120);
            return false;
        }
//        var t = document.getElementsByName('town').item(0).value;
//        if(!t || t==0){
//            confirm_('请完善区域信息！',null,300,120);
//            return false;
//        }
        
        var t = document.getElementsByName('street').item(0).value;
        var rule = new RegExp(/^.{1,120}$/);
        if( rule.test(t)==false ){
            confirm_('请填写街道地址，1-120个字符！',null,300,120);
            return false;
        }
        var t = document.getElementsByName('recipients').item(0).value;
        var rule = new RegExp(/^.{1,120}$/);
        if( rule.test(t)==false ){
            confirm_('请填写收货人姓名，1-20个字符！',null,300,120);
            return false;
        }
        var t = document.getElementsByName('mobile').item(0).value;
        var rule = new RegExp(/^[0-9]{11,11}$/);
        if( rule.test(t)==false ){
            confirm_('请填写手机号码！',null,300,120);
            return false;
        }
        
        var t = document.getElementsByName('phone').item(0).value;
        var rule = new RegExp(/^[0-9]{2,4}?\-?[0-9]{5,10}$/);
        if(t!=''){
            if( rule.test(t)==false ){
                confirm_('请填写正确的电话号码！',null,300,120);
                return false;
            }
        }
        
        var t = document.getElementsByName('zipcode').item(0).value;
        var rule = new RegExp(/^[0-9]{2,8}$/);
        if(t!=''){
            if( rule.test(t)==false ){
                confirm_('请填写正确的邮政编码！',null,300,120);
                return false;
            }
        }
        
    }
    goodsOrder.selectAddress = function(addressid){
        document.getElementsByName('addressid').item(0).value = addressid;
        $.getJSON(rootPath+'?c=goods&a=selectAddress',{'id':addressid},function(d){
            window.location.reload();
        });
    }
    goodsOrder.selectPay = function(payid){
        document.getElementsByName('paymentid').item(0).value = payid;
        $.getJSON(rootPath+'?c=goods&a=selectPayment',{'sn':payid},function(d){ });
    }
    goodsOrder.selectFreight = function(freightid){
        document.getElementsByName('freightid').item(0).value = freightid;
        $.getJSON(rootPath+'?c=goods&a=selectFreight',{'id':freightid},function(d){
            window.location.reload();
        });
    }
    goodsOrder.checkform = function(){
        var a=document.getElementsByName('addressid').item(0).value;
        if(a==null || a==0){
            confirm_('请选择收货地址！');
            return false;
        }
        var p=document.getElementsByName('paymentid').item(0).value;
        if(p==null || p==0){
            confirm_('请选择支付方式！');
            return false;
        }
        var f=document.getElementsByName('freightid').item(0).value;
        if(f==null || f==0){
            confirm_('请选择配送方式！');
            return false;
        }
        var t = document.getElementsByName('comment').item(0).value;
        var rule = new RegExp(/^.{0,200}$/);
        if( rule.test(t)==false ){
            confirm_('补充说明应该是1-200个字符！');
            return false;
        }
        return true;
        
    }
    //设置用户选择的项
    goodsOrder.setselect = function(){
        var a=document.getElementsByName('addressid').item(0).value;
        if(a!=0){
            //修改样式
            $('.ConfirmAnOrder_area1 .area1 .a1').css({'border':'2px solid #e1e1e1'});
            $('.ConfirmAnOrder_area1 .area1 .a1 i').html('');
            $('.ConfirmAnOrder_area1 .area1 #'+a).css({'border':'2px solid #f00'});
            $('.ConfirmAnOrder_area1 .area1 #'+a).find("i").html('已选');
        }
        var p=document.getElementsByName('paymentid').item(0).value;
        if(p!=0){
            //
            $('.ConfirmAnOrder_area1 .area3 .a1').css({'border':'2px solid #e1e1e1'});
            $('.ConfirmAnOrder_area1 .area3 .a1 i').html('');
            $('.ConfirmAnOrder_area1 .area3 #'+p).css({'border':'2px solid #f00'});
            $('.ConfirmAnOrder_area1 .area3 #'+p).find("i").html('已选');
        }
        var f=document.getElementsByName('freightid').item(0).value;
        if(f!=0){
            $('.ConfirmAnOrder_area1 .area6 .a1').css({'border':'2px solid #e1e1e1'});
            $('.ConfirmAnOrder_area1 .area6 .a1 i').html('');
            $('.ConfirmAnOrder_area1 .area6 .f'+f).css({'border':'2px solid #f00'});
            $('.ConfirmAnOrder_area1 .area6 .f'+f).find("i").html('已选');
        }
    }
    //使用优惠券
    goodsOrder.useCoupon = function(){
        $.get(rootPath+'goods/useCouponHTML',function(d){
            var msg = kmsg(d,'选择优惠券',1,550,200,null,1,function(){
                if( document.getElementById('useCouponform1') ){
                    var data = $('#useCouponform1').serialize();
                    $.post(rootPath+'goods/useCouponHTML',data,function(){
                        window.location.reload();
                    });
                }
                msg.hide();
            });
        });
    }
    //设置用户选择的项
    try{
    goodsOrder.setselect();
    }catch(err){}
});


