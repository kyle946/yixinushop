<div class="userindex" >
    <div class="area11 br1" >

        <!--tableswitch  开始--> 
        <div class="tableswitch clearfix" >
            <div class="tableswitch_area dis clearfix"  id="tableswitch_tab1" >
                <if con="$payment['face_pay']!==false" >
                    <div class="a1" onclick="ugoodsfun.modipayClick(this)"  id="{$payment['face_pay']['sn']}" ><img src="<root />static/{$payment['face_pay']['logo']}" /><i></i></div>
                </if>
                <if con="$payment['alipay']!==false" >
                    <div class="a1" onclick="ugoodsfun.modipayClick(this)"  id="{$payment['alipay']['sn']}" ><img src="<root />static/{$payment['alipay']['logo']}" /><i></i></div>
                </if>
                <if con="$payment['weixinPay']!==false" >
                    <div class="a1" onclick="ugoodsfun.modipayClick(this)"  id="{$payment['weixinPay']['sn']}" ><img src="<root />static/{$payment['weixinPay']['logo']}" /><i></i></div>
                </if>
                <if con="$payment['transfer']!==false" >
                    <div class="a1" onclick="ugoodsfun.modipayClick(this)"  id="{$payment['transfer']['sn']}" ><img src="<root />static/{$payment['transfer']['logo']}" /><i></i></div>
                </if>
            </div>
        </div>
        <input name="orderid"  value="<?php echo $_GET['id']; ?>" type="hidden" />
        <input name="paymemid"  value="" type="hidden" />
    </div>
</div>
