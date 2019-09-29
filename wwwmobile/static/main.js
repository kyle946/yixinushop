/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(function() {
    setmenu2();
    $(".mainMenu3 .a1 i").on('click', function() {
        history.go(-1);
    });
});

function setmenu(e) {
    var init = $(e).attr('init');
    if (init == 2) {
        $(e).attr('init', 1);
        $(e).css({'opacity': '.7'});
        $(".mainMenu2").css({'height': 0});
    } else {
        $(e).attr('init', 2);
        $(e).css({'opacity': '.2'});
        $(".mainMenu2").css({'height': 'auto'});
    }
}

function setmenu2() {
    var w1 = parseInt($(".mainMenu3 .a3").outerWidth());
    var w2 = parseInt($(".mainMenu3 .a1").outerWidth());
    var w = parseInt($(".mainMenu3").outerWidth());
    $(".mainMenu3 .a2").css({'width': w - w1 - w2 - 6});
}


////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * 地区加载  jquery  插件
 * 
 * $("#address1").setaddr({proviceSn:430,citySn:430100000000});
 * <div id="address1" >
                <form method="post" action="" id="iform2" name="iform2" onsubmit="return icheck();" >
                <p>选择地区:<select id='proviceId' name="provice" ></select> <select id='cityId' name="city" ></select>
                        <select id='countyId' name="county" ></select> <select id='townId' name="town" ></select></p>
                <p>首重 <input type="text" name="firstweight" class="input3" /> 克,运费 <input type="text" name="firstprice" class="input3" /> 元 ,每增加 <input type="text" name="secondweight" class="input3" /> 克,增加运费 <input type="text" name="sendprice" class="input3" /> 元</p>
                <p><input type="button" class="bt1" onclick="addarea2()" value="添加 >>" /></p>
                </form>
        </div>
 * 
 * @param {type} $
 * @returns {undefined}
 */
(function($){
    $.fn.setaddr = function(options) {
        var obj = $(this);
        var settings = $.extend({
            proviceId: 'proviceId', cityId: 'cityId', countyId: 'countyId', townId: 'townId', //这一排是 select 元素 的ID
            proviceSn: 0, citySn: 0, countySn: 0, townSn: 0, //  这一排是 默认选中的  省市区镇  编辑 ，默认为0,不选中任何 区域。
        }, options);
        var el = new setaddr(obj, settings);

        ////绑定事件
        //点击省份时，加载城市列表
        obj.find("#" + settings.proviceId).change(function() {
            el.citylist();
            obj.find("#" + settings.countyId).html("<option value='0'>请选择</option>");
            obj.find("#" + settings.townId).html("<option value='0'>请选择</option>");
        });
        //点击市、县时，加载区、县列表
        obj.find("#" + settings.cityId).change(function() {
            el.countylist();
            obj.find("#" + settings.townId).html("<option value='0'>请选择</option>");
        });
        //点击区、县时，加载街道、乡镇列表
        obj.find("#" + settings.countyId).change(function() {
            el.townlist();
        });

        ////加载省
        el.provicelist(settings.proviceSn);
        obj.find("#" + settings.proviceId).append("<option value='0'>------ 省 ------</option>");
        ////加载市，后面写的是上级行政区域(省份)的编号 ，为了判断加载的是哪省份的城市。
        el.citylist(settings.citySn, settings.proviceSn);
        obj.find("#" + settings.cityId).append("<option value='0'>---- 市 ----</option>");
        ////加载区
        el.countylist(settings.countySn, settings.citySn);
        obj.find("#" + settings.countyId).html("<option value='0'>---- 区/县 ----</option>");
        ////加载镇
        el.townlist(settings.townSn, settings.countySn);
        obj.find("#" + settings.townId).html("<option value='0'>---- 街道/乡镇 ----</option>");

    }

    setaddr = function(obj, settings) {
        return {
            townlist: function() {
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到区、县编号
                var v1 = obj.find("#" + settings.countyId).attr("value");
                if (sn1 == 0) {
                    var v1 = obj.find("#" + settings.countyId).attr("value");
                } else {
                    var v1 = sn1;
                }
                //加载街道、乡镇列表
                $.getJSON(rootPath+"?c=goods&a=getaddress&act=town&id=" + v1, function(data) {
                    obj.find("#" + settings.townId).html("");
                    if (sn == 0) {
                        obj.find("#" + settings.townId).append("<option value='0'>请选择</option>");
                    }
                    $.each(data, function(i, item) {
                        var sel = '';
                        if (sn != 0 && sn == item.town_id) {
                            sel = 'selected=\'selected\'';
                        }
                        obj.find("#" + settings.townId).append("<option " + sel + " value='" + item.town_id + "' >" + item.town_name + "</option>");
                    });
                });
            },
            countylist: function() {
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到城市编号
                if (sn1 == 0) {
                    var v1 = obj.find("#" + settings.cityId).attr("value");
                } else {
                    var v1 = sn1;
                }
                //加载区、县列表
                $.getJSON(rootPath+"?c=goods&a=getaddress&act=county&id=" + v1, function(data) {
                    obj.find("#" + settings.countyId).html("");
                    if (sn == 0) {
                        obj.find("#" + settings.countyId).append("<option value='0'>请选择</option>");
                    }
                    $.each(data, function(i, item) {
                        var sel = '';
                        if (sn != 0 && sn == item.county_id) {
                            sel = 'selected=\'selected\'';
                        }
                        obj.find("#" + settings.countyId).append("<option " + sel + " value='" + item.county_id + "' >" + item.county_name + "</option>");
                    });
                });
            },
            citylist: function() {
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到省份编号
                if (sn1 == 0) {
                    var v1 = obj.find("#" + settings.proviceId).attr("value");
                } else {
                    var v1 = sn1;
                }
                //加载城市列表
                $.getJSON(rootPath+"?c=goods&a=getaddress&act=city&id=" + v1, function(data) {
                    obj.find("#" + settings.cityId).html("");
                    if (sn == 0) {
                        obj.find("#" + settings.cityId).append("<option value='0'>请选择</option>");
                    }
                    $.each(data, function(i, item) {
                        var sel = '';
                        if (sn != 0 && sn == item.city_id) {
                            sel = 'selected=\'selected\'';
                        }
                        obj.find("#" + settings.cityId).append("<option " + sel + " value='" + item.city_id + "' >" + item.city_name + "</option>");
                    });
                });
            },
            provicelist: function() {
                var sn = arguments[0] ? arguments[0] : 0;
                $.getJSON(rootPath+"?c=goods&a=getaddress", function(data) {
                    obj.find("#" + settings.proviceId).html("");
                    if (sn == 0) {
                        obj.find("#" + settings.proviceId).append("<option value='0'>……省……</option>");
                    }
                    $.each(data, function(i, item) {
                        var sel = '';
                        if (sn != 0 && sn == item.provice_id) {
                            sel = 'selected=\'selected\'';
                        }
                        obj.find("#" + settings.proviceId).append("<option " + sel + " value='" + item.provice_id + "' >" + item.provice_name + "</option>");
                    });
                });
            }
        }
    }
})(jQuery);


//////////////////////////////////////////////////////////////////////////////////////////////////////////

//商品订单相关函数 
goodsfun = {};
/**
 * 添加到购物车
 * @param {type} goodsid
 * @returns {undefined}
 */
goodsfun.shoppingCart = function(id, num) {
    var n = $("input[name=num]").val();
    if (n > 0) {
        num = n;
    }
    $.ajax({
        url: rootPath + '?c=goods&a=addcart',
        data: 'goodsid=' + id + '&num=' + num,
        type: 'get',
        cache: false,
        dataType: 'json',
        success: function(d) {
//            console.log(d); 
            if (d.status == 1) {
                var content = '商品添加成功！';
                var n2 = $(".item_buynow .cart div i").text();
                $(".item_buynow .cart div i").text(parseInt(n2) + parseInt(num));
            } else if(d.status==2){
                content = '帐号未登录！';
            } else if(d.status==3){
                content = '添加失败，库存不足！';
            } else if(d.status==4){
                content = '添加失败，已超过限购件数！';
            }

            layer.open({
                content: content,
                style: 'background-color:#f1f1f1; color:#E20000; border:none;',
                time: 5
            });
        }
    });
}

/**
 * 购物车列表修改商品
 * 
 * @param {type} id
 * @param {type} num
 * @returns {undefined}
 */
goodsfun.ccgn = function(id, num) {
    $.getJSON(rootPath + '?c=goods&a=upshoppingcart', {id: id, num: num}, function(d) {
//        var content = '修改成功!';
        if (num <= 0) {
            window.location.reload();
            return 1;
        }
        var content;
        if( d.status==4 ){
            content = '已超过限购件数！';
        }
        if( content ){
            layer.open({
                content: content,
                end:function(){ window.location.reload(); }
            });
        }
        
    });
}


/**
 * 立即购买
 * @param {type} goodsid
 * @returns {undefined}
 */
goodsfun.buy = function(goodsid,back){
    var num = null;
    num = $("input[name='num']").attr('value');
    if(num==null){
        num=1;
    }
    $.ajax({
        url:rootPath+'?c=goods&a=ConfirmAnOrderChangeGoods',
        data:'goodsid='+goodsid+'&num='+num+'&clear=all',
        dataType:'json',
        cache:false,
        type:'get',
        success:function(data){
            if(data.status==1){
                window.location.href = rootPath+'?c=goods&a=ConfirmAnOrder';
            }else if(data.status==2){
                window.location.href = rootPath+'?c=u&a=login&back='+back;
            }
        }
    });
}


goodsfun.jia = function() {
    var v = $("input[name=num]").val();
    $("input[name=num]").val(++v);
}

goodsfun.jian = function() {
    var v = $("input[name=num]").val();
    if (v <= 1)
        return 0;
    $("input[name=num]").val(--v);
}

goodsfun.checknum = function() {
    var v = $("input[name=num]").val();
    var r = new RegExp(/^[0-9]{1,5}$/);
    if (r.test(v) == false) {
        $("input[name=num]").val(1);
    }
}