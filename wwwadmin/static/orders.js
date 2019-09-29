goodsorder = {}

//设置位置，以方便用户知道刚刚编辑了什么位置
goodsorder.setpos = function(id,obj){
    $('.orderlist_rightHere').css({'display':'none'});
    var id = 'editOrderId_'+id;
    $('#'+id).css({'display':'block'});
    if(obj!=null){ obj.hide(); }
}

//查看订单信息
goodsorder.info = function(id){
    $.get("?c=order&a=orderinfo&id="+id,function(d){
         var msg2 = new message();
        msg2.show(d,'订单详情',null,750,350,null,1,function(){ goodsorder.setpos(id,msg2);});
    });
}

//调价  页面
goodsorder.priceMod = function(id){
    
    $.get("?c=order&a=priceMod&id="+id,function(d){
         var msg2 = new message();
        msg2.show(d,'订单总金额调整',null,580,220,null,1,function(){ 
            var bool_=goodsorder.priceModPost(id); 
            if(bool_){
                goodsorder.setpos(id,msg2);
            } 
        });
    });
    
}
//调价  提交数据
goodsorder.priceModPost = function(id){
    var pricenum = $("input[name='pricenum']").attr('value');
     var rule = new RegExp(/^\-?[0-9]{1,11}\.?[0-9]{0,2}$/);
     if(rule.test(pricenum) || pricenum == ''){
         $.get("?c=order&a=priceMod&id="+id+"&pricenum="+pricenum,function(d){
//             confirm_('调价设置成功！');
                window.location.reload();
         });
         return true;
     }else{
        confirm_('请填写一个有效的数字！');
        return false;
     }
}

//确认 支付
goodsorder.ConfirmPayment = function(id){
    var msg2=kmsg('<p>你确认用户已经付款了吗?</p>','消息',null,400,120,null,1,function(){
        var bool_=goodsorder.ConfirmPayment2(id);
        if(bool_){
             goodsorder.setpos(id,msg2);
        }
    });
}
//确认 支付
goodsorder.ConfirmPayment2 = function(id){
    $.getJSON("?c=order&a=ConfirmPayment",{'orderid':id},function(d){
        if(d.status==1){
            window.location.reload();
        }
    });
     return true;
}

//发货
goodsorder.sendout = function(id,sn){
    $.get('?c=order&a=sendout',{id:id,sn:sn},function(d){
            var msg2 = kmsg(d,'发货',1,520,120,null,1,function(){ goodsorder.sendoutDone(id); } );
            goodsorder.setpos(id,null);
            setTimeout('document.getElementById("expressNo").focus()',700);
        });
}
goodsorder.sendoutDone = function(id){
        var expressNo = $("input[name='expressNo']").attr('value');
        var rule = new RegExp(/^[0-9-_]{1,30}$/);
        if(!rule.test(expressNo)){
            confirm_('请填写一个有效的订单号！');
            return false;
        }
        $.ajax({
            url:'?c=order&a=sendout&id='+id,
            data:$("#order_sendout_form11").serialize(),
            type:'post',
            cache:false,
            dataType:'json',
            success:function(d){
                if(d.status==1){
                    window.location.reload();
                }
            }
        });
        return false;
}

//查看物流信息
goodsorder.viewPhysical = function(sn,deliveryId){
     var msg = new message();
    $.get('?c=order&a=viewPhysical',{sn:sn,deliveryId:deliveryId},function(d){
         msg.show(d,'查看物流',null,750,350,null,1,function(){
             msg.hide();
         });
    });
}

//删除订单
goodsorder.deleteOrder = function(id){
     var msg2 = kmsg('你确定要删除这个订单吗？','警告',1,420,110,null,1,function(){
         $.getJSON('?c=order&a=deleteOrder',{id:id},function(d){
             if(d.status==1){
                 window.location.reload();
             }
         });
     });
}