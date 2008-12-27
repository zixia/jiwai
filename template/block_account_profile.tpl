<script>
window.jiwai_init_hook_location_setting = function()
{
	JWLocation.select('provinceSelect','citySelect',${intval($pid)},${intval($cid)}); 
	JWLocation.select('nativeProvinceSelect','nativeCitySelect',${intval($native_pid)},${intval($native_cid)}); 
}
</script>
<div id="set_bor">
	<form id="f1" method="post" action="/wo/account/profile" class="validator">
	<div class="pagetitle mar_b8">
		<h4><b>基本个人资料...</b></h4><br />
	</div>
	<dl class="w3">
		<dt>姓名</dt>
		<dd>
			<div><input name="user[nameFull]" type="text" id="user_nameFull" value="{$g_current_user['nameFull']}" ajax="nameFull" alt="姓名" /><i></i></div>
			<div class="f_gra">你的真实姓名，可使用中文和空格</div>
		</dd>
		<dt>性别</dt>
		<dd>
			<div>
				<input type="radio" name="user[gender]" id="male" value="male" ${$g_current_user['gender']=='male' ? 'checked':''}/> <label for="male">男</label> &nbsp;
				<input type="radio" name="user[gender]" id="female" value="female" ${$g_current_user['gender']=='female' ? 'checked':''}/> <label for="female">女 </label> &nbsp;
				<input type="radio" name="user[gender]" id="female" value="secret" ${$g_current_user['gender']=='secret' ? 'checked':''}/> <label for="female">保密 </label>
			</div>
		</dd>
		<dt>当前位置</dt>
		<dd>
			<div><input name="user[current]" type="text" id="user_current" value="{$g_current_user['current']}" alt="当前位置"/></div>
		</dd>
		<dt>自我介绍</dt>
		<dd>
			<div><textarea name="user[bio]" id="user_bio" rows="4" maxLength="200" cols="40" alt="自我介绍">{$g_current_user['bio']}</textarea></div>
		</dd>
		<dt>个人网址</dt>
		<dd>
			<div><input name="user[url]" type="text" id="user_url" value="{$g_current_user['url']}" ajax="url" null="true" alt="网址" size="36" /><i></i></div>
			<div class="f_gra">比如：博客地址、相册地址、个人网站</div>
		</dd>

	</dl>
	<div class="clear"></div>
	<div class="pagetitle mar_b8">
		<h4><b>详细个人资料...</b></h4><br />
	</div>
	<dl class="w3">
		<dt>生日</dt>
		<dd>
			<div>
				<select name="birthday[year]"><option value="0000">请选择</option><!--{foreach $ryear AS $oyear}--><option value="{$oyear}" ${$oyear==$birthday_year ? 'selected':''}>{$oyear}</option><!--{/foreach}--></select>
				<select name="birthday[month]"><option value="00">请选择</option><!--{foreach $rmonth AS $omonth}--><option value="{$omonth}" ${$omonth==$birthday_month ? 'selected':''}>{$omonth}</option><!--{/foreach}--></select>
				<select name="birthday[day]"><option value="00">请选择</option><!--{foreach $rday AS $oday}--><option value="{$oday}" ${$oday==$birthday_day ? 'selected':''}>{$oday}</option><!--{/foreach}--></select></div>
		</dd>
		<dt>婚否</dt>
		<dd>
			<div>
				<input type="radio" name="user[marriage]" id="yes" value="single" ${$g_current_user['marriage']=='single' ? 'checked':''}/> <label for="yes">已婚</label> &nbsp; 
				<input type="radio" name="user[marriage]" id="not" value="had" ${$g_current_user['marriage']=='had' ? 'checked':''} /> <label for="not">未婚</label>
			</div>
		</dd>
		<dt>定居地</dt>
		<dd>
			<div><select id='provinceSelect' name="province" style="width:112px;" onChange="JWLocation.select('provinceSelect','citySelect', this.options[this.options.selectedIndex].value, 0);" class="select"></select><select id='citySelect' name="city" style="width:112px;" class="select"></select></div>
		</dd>
		<dt>籍贯</td>
		<dd>
			<div><select id='nativeProvinceSelect' name="native_province" style="width:112px;" onChange="JWLocation.select('nativeProvinceSelect','nativeCitySelect', this.options[this.options.selectedIndex].value, 0);" class="select"></select><select id='nativeCitySelect' name="native_city" style="width:112px;" class="select"></select></div>
		</dd>
		<dt>邮寄地址</dt>
		<dd>
			<div><input name="user[address]" type="text" id="user_address" value="{$g_current_user['address']}" size="36"/></div>
		</dd>
		<dt>邮编</dt>
		<dd>
			<div><input name="user[zipcode]" type="text" id="user_zipcode" value="{$g_current_user['zipcode']}" maxlength="6"/></div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" /> &nbsp; <input type="reset" value="取消"若罔闻/></div>
		</dd>
	</dl>
	<div class="clear"></div>
	</form>
</div>
<div class="clear"></div> 
