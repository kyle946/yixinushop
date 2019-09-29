$(function(){
    $("input[name='goodsselect']").click(function(){
        
        
        var total = 0;
        var num = 0;
        $.each($("input[name='goodsselect']:checked"),function(i,d){
            total+=parseFloat( $('#total_'+d.value).html() );
            num++;
        });
        $('#selgoodsnum').html(num);
        
        //--保留小数点后两位 start
        var total2_ = total.toString();
        var total2 = total2_.replace(/([0-9]+.[0-9]{2})[0-9]*/,"$1");
        //--保留小数点后两位 end
        $('#selgoodstotal').html(total2);
        
        
    });
});


//用户中心相关函数
ugoodsfun = {};

/**
 * 添加到购物车
 * @param {type} goodsid
 * @returns {undefined}
 */
ugoodsfun.ccgn = function(id,num){
    $.ajax({
        url:rootPath+'?c=user&a=changeCartGoodsNum',
        data:'id='+id+'&num='+num,
        type:'get',
        cache:false,
        dataType:'json',
        success:function(d){
//            console.log(d);
            if(d.status == 4){
                confirm_('<font class="cr" >添加失败，已超过限购件数！</font>',"javascript:window.location.reload()",330,80);
            }
        }
    });
}  

//删除购物车的商品
ugoodsfun.deleteCartGoods = function(id){
    confirm_('正在删除…………',null,330,80);
    $.getJSON(rootPath+'?c=goods&a=deleteshoppingcart',{'cartDataId':id},function(d){
        if(d.status==1){
            window.location.reload();
        }
    });
}

//结算
ugoodsfun.buynow = function(){
    var result = $("input[name='goodsselect']").is(':checked');
    if(result == false){
        confirm_("亲，你还没有选择要结算的商品！");
    }else{
        var idstr = '';
        $.each($("input[name='goodsselect']:checked"),function(i,d){
            idstr+=d.value+',';
        });
        console.log(idstr.slice(0,-1));
        $.getJSON(rootPath+'?c=goods&a=ConfirmAnOrderChangeGoods',{'id':idstr,'type':2},function(d){
            console.log(d); 
            if(d.status==1){
                window.location.href = rootPath+'?c=goods&a=ConfirmAnOrder';
            }else if(d.status==2){
                goodsfun.login(1,null);
            }
        });
    }
}
ugoodsfun.buynow2 = function(){
    if( $("input[name='goodsselect']").attr('checked') ){
        $("input[name='goodsselect']").attr('checked',false);
        $('#selgoodstotal').html(0);
    }else{
        $("input[name='goodsselect']").attr('checked',true);
        
        
        var total = 0;
        var num = 0;
        $.each($("input[name='goodsselect']:checked"),function(i,d){
            total+=parseFloat( $('#total_'+d.value).html() );
            num++;
        });
        $('#selgoodsnum').html(num);
        
        //--保留小数点后两位 start
        var total2_ = total.toString();
        var total2 = total2_.replace(/([0-9]+.[0-9]{2})[0-9]*/,"$1");
        //--保留小数点后两位 end
        $('#selgoodstotal').html(total2);
        
    }
}

//删除订单
ugoodsfun.deleteOrder = function(id){
    $.getJSON(rootPath+'?c=user&a=deleteOrder',{orderid:id},function(d){
        if(d.status==1){
            window.location.reload();
        }
    });
}

//查看物流
ugoodsfun.viewPhysical = function(sn,deliveryId){
     var msg = new message();
//    msg.show('正在加载','消息',null,750,350);
    $.get(rootPath+'?c=user&a=viewPhysical',{sn:sn,deliveryId:deliveryId},function(d){
         msg.show(d,'查看物流',null,750,350,null,1,function(){
             msg.hide();
         });
    });
}

//确认收货
ugoodsfun.takeDeliveryOfGoods = function(id){
    $.getJSON(rootPath+"?c=user&a=takeDeliveryOfGoods",{id:id},function(d){
        if(d.status==1){
            confirm_("已经确认收货！");
        }
    });
}

//修改支付方式
ugoodsfun.modipay = function(id){
    $.get(rootPath+'?c=user&a=modipay&id='+id,function(d){
        var msg = kmsg(d,'修改支付方式',1,700,300,null,1,function(){
            var payid = document.getElementsByName('paymemid').item(0).value;
            var orderid = document.getElementsByName('orderid').item(0).value;
            $.getJSON(rootPath+'?c=user&a=modiPaymentDone',{sn:payid,orderid:orderid},function(j){ 
                if(j.status==1){ window.location.reload(); }
            });
        });
    });
}

//支付方式 修改页面 的切换函数
ugoodsfun.tableswitch =  function (e, id, parentClass) {
    $(parentClass + " .tableswitch .menu li a").attr("class", "");
    $(parentClass + " .tableswitch .menu li a#" + e).attr("class", "hover"); //--$("html,body").animate({scrollTop:$(parentClass+" .tableswitch .menu li a#"+e).offset().top},200);
    $(parentClass + " .tableswitch_area").attr('class', 'tableswitch_area hid clearfix');
    $(parentClass + " #" + id).attr('class', 'tableswitch_area dis clearfix');
}

//支付方式
ugoodsfun.modipayClick = function (e){
    var payid =  $(e).attr('id');
    document.getElementsByName('paymemid').item(0).value = payid
    $('.userindex .area11 .a1').css({'border':'2px solid #e1e1e1'});
    $('.userindex .area11 .a1 i').html('');
    $(e).css({'border':'2px solid #f00'});
    $(e).find("i").html('已选');
}

//修改地址
ugoodsfun.modiAddress = function(id){
    $.get(rootPath+'?c=user&a=modiAddress&id='+id,function(d){
        var msg = kmsg(d,'修改收货地址',1,700,300,null,1,function(){
            var addressid = $("input[name='modiAddressAddressId']:checked").attr('value'); //document.getElementsByName('modiAddressAddressId').item(0).value;
            $.getJSON(rootPath+'?c=user&a=modiAddress',{addressid:addressid,orderid:id,done:1},function(j){ 
                if(j.status==1){ window.location.reload(); }
            });
        });
    });
}