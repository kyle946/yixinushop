<div class="orderinfo_area2" style="width:100%; height: 285px;overflow-y: scroll;" > 
    <table class="table1" border="0" cellspacing="5"> <tbody> 
        <list name="list" item="v" >
        <tr>
            <td class="c1 f12" style="text-align: left;">
                <div class="fl" ><input type="radio" name="modiAddressAddressId" value="{$v.id}" /></div>
                <div class="fl pl10" >
                <span class="fb" >{$v.recipients}&nbsp;&nbsp;{$v.mobile}</span>&nbsp;&nbsp;{$v.zipcode }&nbsp;&nbsp;{$v.phone}<br />
                {$v.provice_name},{$v.city_name},{$v.county_name},{$v.town_name},{$v.street} 
                </div>
            </td>
        </tr> 
        </list>
        </tbody> 
    </table>
</div>