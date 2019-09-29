function setH(){
    var h_ = $(window).height();
    var h = h_ - 40;
    $("#area_left").css({"height":h});
    $("#area_right").css({"height":h});
//    var w1 = $(".area_middle").width();
//    var w2 = $("#area_left").width();
//    var w = w1 - (w2+30);
//    $("#area_right").css({"width":w});
}

$(function(){
    setH();
    $(window).resize(function(){
        setH();
    });
});

/**
 * 根据className(类名)获取元素(返回的是一个HTMLCollection)
 * @param {String}
 * @param {Element} [root] 可选,从哪个根节点查找
 * @return {[Element]|Null}
 */
function getElemsByClassName(className, root, tagName) {
	root = root || document; //没有传入根节点，则默认为document
	tagName = tagName || '*'; //没有传入标签，则默认获得所有标签
	classElements = [],
	elements = root.getElementsByTagName(tagName),
	elementsLen = elements.length;
	pattern = new RegExp('(^|\\s)' + className + '(\\s|$)');//className为传入的参数
	//遍历所有的元素，如果匹配到传入元素的className，则把对应的元素添加到数组中并返回
	for (var i=0; i < elementsLen; i++) {
		if (pattern.test(elements[i].className)) {
			classElements.push(elements[i]);
		}
	}
	return classElements;
}

/**
 * 兼容所有浏览器的回车事件
 * 使用方法  onkeypress="k().keydown(event,submit1)"  ,submit1为函数名
 * 
 * @param event evt
 * @param 函数 fun
 */
function keydown(fun,evt){
    evt = (evt) ? evt : ((window.event) ? window.event : "") ;
    var keyCode = evt.keyCode ? evt.keyCode : (evt.which ? evt.which : evt.charCode); 
    if (keyCode == 13) { 
        if(typeof fun == 'function' ){ fun(); } 
    }
}

function tabSwitch(id,e) {
//    获取tabswitch 的数量 start
        var arr = new Array(); 
        var l = getElemsByClassName('tabswitch').length;
        var i;
        for(i=1;i<=l; i++){
            arr.push('tab'+i);
        }
//    end
        for(var i=0;i<arr.length;i++){
            document.getElementById(arr[i]).style.height = '0px';
            document.getElementById(arr[i]).style.padding = '0px';
        }
        document.getElementById(id).style.height = 'auto';
        document.getElementById(id).style.padding = '10px 0px';
        var aa = document.getElementById('addcolumn_r1').getElementsByTagName('a');
        for(var i=0;i<arr.length;i++){
            aa[i].className = 'addcolumn_a1';
        }
        e.className = 'addcolumn_a2';
}
       
////////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * 表单验证
 * 参数：
    var option = new Array();
    option[0] = {name:'uname',type:'email',msg:'邮件格式不正确！'};
    option[1] = {name:'psw',type:'password',msg:'密码长度应为6~16个字符！'};
    option[2] = {name:'passwd2',type:'password2',msg:'两次填写的密码不一致！'};
    option[3] = {name:'nickname',type:'username',msg:'昵称应为1~8个字符！'};
    option[4] = {name:'email',type:'email',msg:'邮件格式不正确 ！',required:0};   //required: 1为必填，2可以为空
    option[5] = {name:'sort',type:'regexp',msg:'排序应该在1-999之间！',rule:/^[1-9]\d{0,2}$/};   // rule 为自定义的正则，regexp函数独有
    ch = new formCheck(option);
    var res = ch.start();
    if(res==0){
        return ;
    }

        html 部分示例
        <input type="text" name="uname" id="email" onBlur="ch.onecheck(this,'/reg/checkuser/email/'+this.value,'该邮箱已被注册!');" >
        <label id="unametips" style="color: #F00;"></label>
        <input type="password" style="width:170px;" id="passwd" onblur="ch.onecheck(this);" name="passwd" class="form_text left" />
        <span id="passwdtips" class="left" style="color: #F00;"></span> </div>


  *  ajax判断调用方式
  *  onblur="ch4.onecheck(this,'__APP__/PersonalCenter/checkuser/email/'+this.value,'该地址已被注册!');"
  *  ajax链接的请求必须 返回1或0

 * @author Kyle 青竹丹枫 <kyle@yixinu.com> <316686606@qq.com>
 */
function formCheck(op){
   this.option = op;
   this.values = new Array();
    this.init = function(){
        for(x in this.option){
            this.exec(this.option[x].type,x);
        }
    }
    this.exec = function(n,x){
        var res = null;
        switch(n){
            case 'email':  res = this.ex_email(x);  break;  //电子邮件地址 
            case 'password':  res = this.ex_password(x);  break;  //6-16位密码
            case 'password2':  res = this.ex_password2(x,x-1);  break;  //第二次输入 密码
            case 'username':  res = this.ex_username(x);  break; //1-8个字符的用户名
            case 'identity': res = this.ex_identity(x); break;   //身份证号码
            case 'int11':  res = this.ex_int11(x);  break;  //1-11个整数
            case 'str30':  res = this.ex_str30(x);  break;  //1-30个字符字段
            case 'regexp':  res = this.ex_regexp(x);  break;  //执行一个自定义的正则表达式匹配
            case 'str200':  res = this.ex_str200(x);  break;  //1-200个字符字段
            case 'str0_200':  res = this.ex_str0_200(x);  break;  //0-200个字符字段
            case 'mobile':  res = this.ex_mobile(x);  break;  //手机号码
            case 'check':  res = this.ex_check(x);  break;  //单选项
            case 'isempty':  res = this.ex_isempty(x);  break;  //值为空 或者  值为0
        }
        return res;
    }
    /**
     * 检测 方法 ，有3个参数 
     * e 为表单对象
     * url 为 ajax 的验证链接   ； 根据 这个url参数 判断 是否为ajax检测 
     * ajaxmsg  为ajax验证失败的提示消息 
     */
    this.onecheck = function(e){
        var url = arguments[1] ? arguments[1] : null;
        var ajaxmsg = arguments[2] ? arguments[2] : null;
        var n = e.name;
        var res = 1;
        var y = null;
        for(x in this.option){
            if(n==this.option[x].name){
                res = this.exec(this.option[x].type,x);
                y=x;
            }
        }
        var name = '';
        try{
            name = this.option[y].name+'tips';
        }catch(err){
            name = 'tips';
        }
        if(res==0){
            this.values[y] = 0;
            document.getElementById(name).innerHTML = this.option[y].msg;
            document.getElementById(name).style.color = '#f00';
        }else if(res==1){
            this.values[y] = 1;
            document.getElementById(name).innerHTML = '√';
            document.getElementById(name).style.color = 'green';
        }else if(res==2){
                document.getElementById(name).innerHTML = '';
                document.getElementById(name).style.color = 'green';
        }
        //根据 这个url参数 判断 是否为ajax检测 
        if(url!=null){
            this.ajax(url,name,ajaxmsg,y);
        }
    }
    
    /**
     * 检测失败的消息显示 
     * this.values 有三种消息类型，0 检测失败，1 检测成功，2 允许不检测
     * @returns {undefined}
     */
    this.showmsg = function(){
        for(x in this.values){
            name = this.option[x].name+'tips';
            try{
                
                    if(this.values[x]===0){
                        document.getElementById(name).innerHTML = this.option[x].msg;
                        document.getElementsByName(this.option[x].name).item(0).focus();
                        document.getElementById(name).style.color = '#f00';
                    }else if(this.values[x]===1) {
                        document.getElementById(name).innerHTML = '√';//"<font style='color:green;'>可用</font>"
                        document.getElementById(name).style.color = 'green';
                    }else if(this.values[x]===2) {
                        document.getElementById(name).innerHTML = '';
                        document.getElementById(name).style.color = 'green';
                    }
                    
            }catch(err){
                
            }
        }
    }
    this.start = function(){
        this.init();
        this.showmsg();
        var regformcheckres = 1;
        for(var i=0;i<this.values.length;i++){
            if(!this.values[i]){
                regformcheckres = 0;
            }
        }
        //检测设置的ajax检测项，是否通过 。
        var d = new Date();
        var an = 'ajax' + d.getFullYear() + d.getMonth() + d.getDate() + d.getHours();
        var e1 = document.getElementsByName(an);
        if(e1.length!=0){
            for(var j=0;j<e1.length;j++){
                var e1val = e1[j].value;
                if(e1val!=""){
                    regformcheckres=0;
                    var tipsname = this.option[e1val].name+'tips';
                    var tips = document.getElementById(an+e1val+'tips').value;
                    document.getElementById(tipsname).innerHTML = tips;
                    document.getElementsByName(this.option[e1val].name).item(0).focus();
                }
            }
        }
        
        if(!regformcheckres){
            return 0;
        }else{
            return 1;
        }
    }
    this.ajax = function(url,name,ajaxmsg,y){
        //通过创建唯一的input hidden元素来判断ajax检测 是否通。
        var d = new Date();
        var an = 'ajax' + d.getFullYear() + d.getMonth() + d.getDate() + d.getHours();
        var e2 = document.getElementById(an+y);
        if(!e2){
            var e1 = document.createElement("input");
            e1.type = 'hidden';
            e1.id = an + y;
            e1.name = an;
            e1.value = '';
            document.body.appendChild(e1);
            
            var e2 = document.createElement("input");
            e2.type = 'hidden';
            e2.id = an + y+'tips';
            e2.value = ajaxmsg;
            document.body.appendChild(e2);
        }
        
        var obj = this;
        var xmlhttp;
        if (window.XMLHttpRequest){
            // code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp=new XMLHttpRequest();
          }else{
              // code for IE6, IE5
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
          }
        xmlhttp.onreadystatechange=function()
          {
          if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
                var res = xmlhttp.responseText;
                if(res==0){
                    document.getElementById(an+y).value = y;
                    document.getElementById(name).innerHTML = ajaxmsg;
                    document.getElementById(name).style.color = '#f00';
                    obj.values[y] = 0;
                }else{
                    document.getElementById(an+y).value = '';
                }
            }
          }
        xmlhttp.open("GET",url,true);
        xmlhttp.setRequestHeader("X-Requested-With","XMLHttpRequest");
        xmlhttp.send();
    }
    this.ex_isempty = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        if(!t || t==0){
            this.values[x] = 0;
            return 0;
        }else{
            this.values[x] = 1;
            return 1;
        }
    }
    this.ex_check = function(x){
        this.values[x] = 0;
        var b=document.getElementsByName(this.option[x].name);
         for(var i=0;i<b.length;i++)
         {
          if(b[i].checked==true){
            this.values[x] = 1;
            return 1;
          }
        }
        return this.values[x];
    }
    this.ex_email = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+\.[a-zA-Z0-9]{2,3}$/);
        var required_ = this.option[x].required || 2;
        var testbool = false;
        //根据是否可以允许空值做对应判断
        if(required_== 2){
            if(rule.test(t) || t == ''){
                testbool = true;
            }
        }else{
             if(rule.test(t) ){
                testbool = true;
             }
        }
        
        if(testbool){
            if(t==''){
                this.values[x] = 2;
                return 2;
            }else{
                this.values[x] = 1;
                return 1;
            }
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    this.ex_password = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^.{6,16}$/);
        
        //根据是否可以允许空值做对应判断
        var required_ = this.option[x].required || 1;
        var testbool = false;  
        //如果允许为空
        if(required_== 2){
            if(rule.test(t) || t == ''){
                testbool = true;
            }
        }else{
             if(rule.test(t) ){
                testbool = true;
             }
        }
        
        if(testbool){
            if(t==''){
                this.values[x] = 2;
                return 2;
            }else{
                this.values[x] = 1;
                return 1;
            }
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    this.ex_password2 = function(x,y){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var t2 = document.getElementsByName(this.option[y].name).item(0).value;
        if(t===t2){
            //判断第一步密码检测是否通过
            if( this.ex_password(x)==1 ){
                this.values[x] = 1;
                return 1;
            }else{
                this.values[x] = 2;
                return 2;
            }
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_str30 = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^.{1,30}$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_regexp = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(this.option[x].rule);

        //根据是否可以允许空值做对应判断
        var required_ = this.option[x].required || 2;
        var testbool = false;  
        //如果允许为空
        if(required_== 2){
            if(rule.test(t) || t == ''){
                testbool = true;
            }
        }else{
             if(rule.test(t) ){
                testbool = true;
             }
        }
        
        if(testbool){
            if(t==''){
                this.values[x] = 2;
                return 2;
            }else{
                this.values[x] = 1;
                return 1;
            }
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_int11 = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^[0-9]{1,11}$/);
        
        //根据是否可以允许空值做对应判断
        var required_ = this.option[x].required || 2;
        var testbool = false;  
        //如果允许为空
        if(required_== 2){
            if(rule.test(t) || t == ''){
                testbool = true;
            }
        }else{
             if(rule.test(t) ){
                testbool = true;
             }
        }
        
        if(testbool){
            if(t==''){
                this.values[x] = 2;
                return 2;
            }else{
                this.values[x] = 1;
                return 1;
            }
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_username = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^.{1,8}$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    this.ex_identity = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^[0-9]{15,18}|[Xx]$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    this.ex_mobile = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^0?1[3|4|5|8|7][0-9]\d{8}$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_str200 = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^(.|\r|\n){1,200}$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
    
    this.ex_str0_200 = function(x){
        var t = document.getElementsByName(this.option[x].name).item(0).value;
        var rule = new RegExp(/^(.|\r|\n){0,200}$/);
        if(rule.test(t)){
            this.values[x] = 1;
            return 1;
        }else{
            this.values[x] = 0;
            return 0;
        }
    }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////


/**
 * 确定  取消 的弹窗
 * @param {type} content
 * @param {type} url
 * @returns {undefined}
 */
function confirm_(content,url,w,h){
         var msg2 = new message();
         var width =  w ||  400;
         var height = h || 120;
         msg2.show(content,null,null,width,height,null,1,url);
         return msg2;
}

/**
 * 
 * 调用示例：
        var html = '';
        var msg = kmsg(html,'选择发放方式',1,400,120,null,1,function(){
            
        });
 * 
 * @param {type} content  内容：type为2时，这里写url，否则为html
 * @param {type} title  标题
 * @param {type} type  内容类型：默认为 1 （内容）不使用iframe；2为链接，使用iframe
 * @param {type} width  消息框宽度
 * @param {type} height  消息框高度
 * @param {type} time   多长时间后消失（秒钟）
 * @param {type} yesno_   是否默认添加确定取消按钮  ，默认不添加
 * @param {type} url_  确定按钮上面的url地址 ，默认不添加  ，可以是function 也可以是url
 * @returns {message|kmsg.msg}
 */
function kmsg(content,title,type,width,height,time,yesno,url){
    var msg = new message();
    msg.show(content,title,type,width,height,time,yesno,url);
    return msg;
}


/**
 * 消息盒子，用来在网页的消息弹窗
 * 调用示例：
 * var msg2 = new message();
 * msg2.show('111111111111',null,null,null,null,5);
 * 
 * @author Kyle 青竹丹枫 <316686606@qq.com>
 * @returns {message}
 */
function message(){
    
    this.option = {
        bg:null,
        wd:null,
        cn:null,
        ow:null,
        oh:null,
        o:true,
        time:null,
        cn2:null,
        aa:null,
        linka:null
    }
    
    
    /**
     * 
     * @param {type} content  内容：type为2时，这里写url，否则为html
     * @param {type} title  标题
     * @param {type} type  内容类型：默认为 1 （内容）不使用iframe；2为链接，使用iframe
     * @param {type} width  消息框宽度
     * @param {type} height  消息框高度
     * @param {type} time   多长时间后消失（秒钟）
     * @param {type} yesno_   是否默认添加确定取消按钮  ，默认不添加
     * @param {type} url_  确定按钮上面的url地址 ，默认不添加
     */
    this.show = function(content,title,type,width,height,time,yesno_,url){
            var c = content ||  ' ';     //内容
            var title =  title ||  '消息';
            var i =  type ||  1;
            var w = width ||  430;
            var h =  height ||  220;
            var t =  time ||  null;
            var yesno =  yesno_ ||  2;
           // var t = arguments[5] ? arguments[5] : null;   //时间
            
            if (this.option.o) {
                this.option.bg = document.createElement('div');// 半透明背景
                this.option.wd = document.createElement('div');// 内容盒子
                this.option.cn = document.createElement('div');// 内容
                this.option.aa = document.createElement('div');   //标题区域
                this.option.cn2 = document.createElement('div');
                this.option.linka = document.createElement('a');
                document.body.appendChild(this.option.bg);
                document.body.appendChild(this.option.wd);
                this.option.aa.innerHTML = title;
                this.option.wd.appendChild(this.option.aa);
                this.option.aa.appendChild(this.option.linka);
                var obj = this;
                this.option.linka.onclick = function(){  obj.hide();  }
                this.option.linka.innerHTML = "关闭";
                this.option.wd.appendChild(this.option.cn);
                this.option.cn.appendChild(this.option.cn2);
                
                if(yesno==1){
                        /**
                         * 添加底部按钮  start
                         * @type @exp;document@call;createElement
                         */
                        var footer = document.createElement("div");
                        var footer_a1 = document.createElement("a");
                        var footer_a2 = document.createElement("a");
                        footer_a1.style.display = 'inline-block';
                        footer_a2.style.display = 'inline-block';
                        footer_a1.style.height = '24px';
                        footer_a2.style.height = '24px';
                        footer_a1.style.width = '75px';
                        footer_a2.style.width = '75px';
                        footer_a1.style.background = '#e1e1e1';
                        footer_a2.style.background = '#e1e1e1';
                        footer_a1.style.right = '10px';
                        footer_a2.style.right = '100px';
                        footer.style.height = '32px';
                        footer_a1.style.position = 'absolute';
                        footer_a2.style.position = 'absolute';
                        footer_a1.style.border = '1px solid #CECECE';
                        footer_a2.style.border = '1px solid #CECECE';
                        footer_a1.style.color = '#333';
                        footer_a2.style.color = '#333';
                        footer_a1.style.fontSize = '12px';
                        footer_a2.style.fontSize = '12px';
                        footer_a1.innerHTML = '取消';
                        footer_a2.innerHTML = '确定';
                        footer_a1.style.textAlign = 'center';
                        footer_a2.style.textAlign = 'center';
                        footer_a1.style.lineHeight = '24px';
                        footer_a2.style.lineHeight = '24px';
                        footer_a1.style.cursor = 'pointer';
                        footer_a2.style.cursor = 'pointer';
                        footer.appendChild(footer_a1);
                        footer.appendChild(footer_a2);
                        this.option.wd.appendChild(footer);
                        footer_a1.onclick =   function(){  obj.hide();  }
                        footer_a2.onclick =   function(){
                            if(typeof(url)=='function'){
                                url();
                            }
                            if( typeof(url)=='string' ){
                                window.location.href = url;
                            }
                            if(!url){
                                obj.hide();
                            }
                        }
                        //  添加底部按钮   end
                        
                        
                    this.option.cn2.style.width = '100%';
                    this.option.cn2.style.padding = '0px';
                }else{
                    this.option.cn2.style.width = '100%';
                    this.option.cn2.style.padding = '0px';
                }
                
                this.setcss();
                //this.option.bg.onclick = function(){  obj.hide();  }  //点击背景关闭窗口
                window.onresize = function(){ obj.init(); }
                window.onscroll = function(){ obj.scrolls(); }
                this.option.o = false;
            }
            if (i === 2) {
                var inhtml = '<iframe src="' + c + '" width="' + w + '" height="' + h + '" frameborder="0"></iframe>';
            } else {
                var inhtml = c;
                this.option.wd.style.width = w + 'px';
                this.option.cn.style.width = w + 'px';
                this.option.cn.style.height = 'auto';
//                this.option.cn.style.height = h + 'px';
            }
            this.option.cn2.innerHTML = inhtml;
            this.option.oh = this.getCss(this.option.wd, 'offsetHeight');
            this.option.ow = w;//this.getCss(wd,'offsetWidth'); 
            this.init();
            this.alpha(this.option.bg, 40, 1);
            if (t) {
                this.option.time = setTimeout(function() {
                    obj.hide()
                }, t * 1000);
            }
    }
    
    this.setcss = function(){
        this.option.bg.style.display = 'none';
        this.option.wd.style.display = 'none';
        this.option.bg.style.position = 'absolute';
        this.option.bg.style.background = '#000';
        this.option.bg.style.left = '0px';
        this.option.bg.style.top = '0px';
        this.option.bg.style.zIndex = 1000;
        this.option.wd.style.position = 'absolute';
        this.option.wd.style.background = '#fff';
        this.option.wd.style.zIndex = 1500;
        this.option.wd.style.border = '10px solid #e6e6e6';
        this.option.wd.style.borderTop = '0px';
        this.option.cn.style.background = '#fff';
        this.option.cn.style.display = 'block';
        this.option.cn.style.padding = '0px 0px';
        this.option.cn2.style.margin = '0px auto';
        this.option.cn2.style.fontSize = '14px';
        this.option.aa.style.height = '34px';
        this.option.aa.style.right = '0px';
        this.option.aa.style.width = '100%';
        this.option.aa.style.background = '#e6e6e6';
        this.option.aa.style.padding = '0px';
        this.option.aa.style.zIndex = 99;
        this.option.aa.style.lineHeight = '34px';
        this.option.aa.style.textAlign = 'center';
        this.option.aa.style.fontSize = '14px';
        this.option.aa.style.color = '#666';
        this.option.aa.style.position = 'relative';
        this.option.linka.style.fontSize = '12px';
        this.option.linka.style.textDecoration = 'none';
        this.option.linka.style.color = '#979797';
        this.option.linka.style.display = 'block';
        this.option.linka.style.position = 'absolute';
        this.option.linka.style.right = '7px';
        this.option.linka.style.top = '0px';
        this.option.linka.style.cursor = 'pointer';
    }
    
    this.hide = function(){
        this.alpha(this.option.wd, 0, -1);
        clearTimeout(this.option.time);
        
        this.option.wd.parentNode.removeChild(this.option.wd);
        this.option.bg.parentNode.removeChild(this.option.bg);
    }
    
    this.init = function() {
        this.option.bg.style.height = this.pageTotal(1) + 'px';
        this.option.bg.style.width = '';
        this.option.bg.style.width = this.pageTotal(0) + 'px';
        var h = (this.pageHeight() -  this.option.oh) / 2;
        this.option.wd.style.top = (h + this.pageTop()) + 'px';
        this.option.wd.style.left = (this.pageWidth() -  this.option.ow) / 2 + 'px';
    }
    
    this.scrolls = function() {
        var h = (this.pageHeight() - this.option.oh) / 2;
        this.option.wd.style.top = (h+this.pageTop()) + 'px';
    }
    
    this.alpha = function(e, a, d) {
        var obj = this;
        clearInterval(e.ai);
        if (d == 1) {
            e.style.opacity = 0;
            e.style.filter = 'alpha(opacity=0)';
            e.style.display = 'block';
        }
        e.ai = setInterval(function() {
            obj.ta(e, a, d)
        }, 1);
    }
    
    this.ta = function(e, a, d) {
        var anum = Math.round(e.style.opacity * 100);
        if (anum == a) {
            clearInterval(e.ai);
            if (d == -1) {
                e.style.display = 'none';
                if (e == this.option.wd) {
                    this.alpha(this.option.bg, 0, -1);
                }
            } else {
                if (e == this.option.bg) {
                    this.alpha(this.option.wd, 100, 1);
                }
            }
        } else {
            var n = Math.ceil((anum + ((a - anum) * .5)));
            n = n == 1 ? 0 : n;
            e.style.opacity = n / 100;
            e.style.filter = 'alpha(opacity=' + n + ')';
        }
    }
    
    this.getCss = function(e, n) {
        var e_style = e.currentStyle ? e.currentStyle : window.getComputedStyle(e, null);
        if (e_style.display === 'none') {
            var clonDom = e.cloneNode(true);
            clonDom.style.cssText = 'position:absolute; display:block; top:-3000px;';
            document.body.appendChild(clonDom);
            var wh = clonDom[n];
            clonDom.parentNode.removeChild(clonDom);
            return wh;
        }
        return e[n];
    }
    
    this.pageTop = function(){
        return document.documentElement.scrollTop || document.body.scrollTop
    }
    
    this.pageWidth = function(){
        return self.innerWidth || document.documentElement.clientWidth || document.body.clientWidth
    }
    
    this.pageHeight = function(){
        return self.innerHeight || document.documentElement.clientHeight || document.body.clientHeight
    }
    
    this.pageTotal = function(d){
        var b = document.body, e = document.documentElement;
        return d ? Math.max(Math.max(b.scrollHeight, e.scrollHeight), Math.max(b.clientHeight, e.clientHeight)) :
                Math.max(Math.max(b.scrollWidth, e.scrollWidth), Math.max(b.clientWidth, e.clientWidth))
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * 添加图片
 * onclick="insertImage('multiple','imglist','imgfile[]')"
 * onclick="insertImage('null','imglist','imgfile[]')"
 * onclick="insertImage('single','logoFile')"
 * imageBox  为全局变量 ，为了在选择图片的窗口中能方便的调用到隐藏方法。
 * 
 * @param {type} mode   选择图片的模式， 有多选、单选、多选源图、单选源图 |  single、multiple、singleSrc、multipleSrc
 * @param {type} divid  父窗口放图片的div盒子 id
 * @param {type}  inputname   父窗口放图片文件PATH 的input 元素名
 * @param {type}  level   选择的图片大小级别 
 * @returns null
 */
function insertImage(options){
    var settings = $.extend({
            mode:'multiple',
            divid:'imglist',
            inputname:'imgfile',
            level:'1,2,3,4'
        },options);
    var title = '多选模式';
    if(settings.mode=='single' || settings.mode=='singleSrc'){
        title = '单选模式';
    }
    imageBox = new message();
    imageBox.show("?c=image&a=show&type=paste&mode="+settings.mode+"&id="+settings.divid+'&inputName='+settings.inputname+'&level='+settings.level,'添加图片 - '+title,2,630,360);
}

/*
 * 多选 移除图片
 */
function deleteImg(e){
    var i1=e.parentNode.parentNode.parentNode.getElementsByTagName('li').length;
    if(i1<=1){
        var i2 = e.parentNode.parentNode.parentNode.getElementsByTagName('input').item(0);
        i2.setAttribute('value','');
        e.parentNode.parentNode.parentNode.appendChild(i2);
        e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
    }else{
        e.parentNode.parentNode.parentNode.removeChild(e.parentNode.parentNode);
    }
}

/*
 * 单选 移除图片
 */
function deleteImgSingle(e){
    var i1 = e.parentNode.getElementsByTagName('input').item(0);
    if(i1.type=='hidden'){
        i1.setAttribute('value','');
    }
    e.parentNode.parentNode.appendChild(i1);
    e.parentNode.parentNode.removeChild(e.parentNode);
}

/**
 * 清除缓存
 * @returns {undefined}
 */
function delete_cache(){
    var delete_cache_msg = kmsg("/?c=index&a=deleteCache",null,2,630,360,null,1,function(){ delete_cache_msg.hide(); });
}


////////////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * 地区加载  jquery  插件
 * 
 *  * $("#address1").setaddr({proviceSn:430,citySn:430100000000});
 * <div id="address1" >
                <form method="post" action="" id="iform2" name="iform2" onsubmit="return icheck();" >
                <p>选择地区:<select id='proviceId' name="provice" ></select> <select id='cityId' name="city" ></select>
                        <select id='countyId' name="county" ></select> <select id='townId' name="town" ></select></p>
                <p>首重 <input type="text" name="firstweight" class="input3" /> 克,运费 <input type="text" name="firstprice" class="input3" /> 元 ,每增加 <input type="text" name="secondweight" class="input3" /> 克,增加运费 <input type="text" name="sendprice" class="input3" /> 元</p>
                <p><input type="button" class="bt1" onclick="addarea2()" value="添加 >>" /></p>
                </form>
        </div>
    
    
 * @param {type} $
 * @returns {undefined}
 */
(function($){
    $.fn.setaddr = function(options){
        var obj = $(this);
        var settings = $.extend({
            proviceId:'proviceId',cityId:'cityId',countyId:'countyId',townId:'townId',  //这一排是 select 元素 的ID
            proviceSn:0,citySn:0,countySn:0,townSn:0,   //  这一排是 默认选中的  省市区镇  编辑 ，默认为0,不选中任何 区域。
        },options);
        var el = new setaddr(obj,settings);
        
        ////绑定事件
        //点击省份时，加载城市列表
        obj.find("#"+settings.proviceId).change(function(){
            el.citylist();
            obj.find("#"+settings.countyId).html("<option value='0'>请选择</option>");
            obj.find("#"+settings.townId).html("<option value='0'>请选择</option>");
        });
        //点击市、县时，加载区、县列表
        obj.find("#"+settings.cityId).change(function(){
            el.countylist();
            obj.find("#"+settings.townId).html("<option value='0'>请选择</option>");
        });
        //点击区、县时，加载街道、乡镇列表
        obj.find("#"+settings.countyId).change(function(){
            el.townlist();
        });
        
        ////加载省
        el.provicelist(settings.proviceSn);
        obj.find("#"+settings.proviceId).append("<option value='0'>------------ 省 ------------</option>");
        ////加载市，后面写的是上级行政区域(省份)的编号 ，为了判断加载的是哪省份的城市。
        el.citylist(settings.citySn,settings.proviceSn);
        obj.find("#"+settings.cityId).append("<option value='0'>------ 市 ------</option>");
        ////加载区
        el.countylist(settings.countySn,settings.citySn);
        obj.find("#"+settings.countyId).html("<option value='0'>------ 区/县 ------</option>");
        ////加载镇
        el.townlist(settings.townSn,settings.countySn);
        obj.find("#"+settings.townId).html("<option value='0'>------ 街道/乡镇 ------</option>");
        
    }
    
    setaddr = function(obj,settings){
        return {
            townlist:function(){
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到区、县编号
                var v1 = obj.find("#"+settings.countyId).attr("value");
                if(sn1==0){
                    var v1 = obj.find("#"+settings.countyId).attr("value");
                }else{
                    var v1 = sn1;
                }
                //加载街道、乡镇列表
                $.getJSON("?c=mall&a=exemption&type=address&act=town&id="+v1, function(data){
                        obj.find("#"+settings.townId).html("");
                        if(sn==0){
                            obj.find("#"+settings.townId).append("<option value='0'>请选择</option>");
                        }
                        $.each(data, function(i,item){
                                var sel = '';
                                if(sn!=0 && sn==item.town_id){
                                    sel = 'selected=\'selected\'';
                                }
                                obj.find("#"+settings.townId).append("<option "+sel+" value='"+item.town_id  +"' >"+item.town_name+"</option>");
                        });
                });
            },
            countylist:function(){
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到城市编号
                if(sn1==0){
                    var v1 = obj.find("#"+settings.cityId).attr("value");
                }else{
                    var v1 = sn1;
                }
                //加载区、县列表
                $.getJSON("?c=mall&a=exemption&type=address&act=county&id="+v1, function(data){
                        obj.find("#"+settings.countyId).html("");
                        if(sn==0){
                            obj.find("#"+settings.countyId).append("<option value='0'>请选择</option>");
                        }
                        $.each(data, function(i,item){
                                var sel = '';
                                if(sn!=0 && sn==item.county_id){
                                    sel = 'selected=\'selected\'';
                                }
                                obj.find("#"+settings.countyId).append("<option "+sel+" value='"+item.county_id +"' >"+item.county_name+"</option>");
                        });
                });
            },
            citylist:function(){
                var sn = arguments[0] ? arguments[0] : 0;
                var sn1 = arguments[1] ? arguments[1] : 0;
                //找到省份编号
                if(sn1==0){
                    var v1 = obj.find("#"+settings.proviceId).attr("value");
                }else{
                    var v1 = sn1;
                }
                //加载城市列表
                $.getJSON("?c=mall&a=exemption&type=address&act=city&id="+v1, function(data){
                        obj.find("#"+settings.cityId).html("");
                        if(sn==0){
                            obj.find("#"+settings.cityId).append("<option value='0'>请选择</option>");
                        }
                        $.each(data, function(i,item){
                                var sel = '';
                                if(sn!=0 && sn==item.city_id){
                                    sel = 'selected=\'selected\'';
                                }
                               obj.find("#"+settings.cityId).append("<option "+sel+" value='"+item.city_id+"' >"+item.city_name+"</option>");
                        });
                });
            },
            provicelist:function(){
                var sn = arguments[0] ? arguments[0] : 0;
                $.getJSON("?c=mall&a=exemption&type=address", function(data){
                    obj.find("#"+settings.proviceId).html("");
                    if(sn==0){
                        obj.find("#"+settings.proviceId).append("<option value='0'>------------ 省 ------------</option>");
                    }
                    $.each(data, function(i,item){
                        var sel = '';
                        if(sn!=0 && sn==item.provice_id){
                            sel = 'selected=\'selected\'';
                        }
                       obj.find("#"+settings.proviceId).append("<option "+sel+" value='"+item.provice_id+"' >"+item.provice_name+"</option>");
                    });
                });
            }
        }
    }
})(jQuery);