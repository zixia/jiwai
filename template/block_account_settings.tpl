<div id="set_bor">
	<div class="mar_20">&nbsp;</div>
	<form action="/wo/account/settings" method="post" class="validator">
	<dl class="w1">
		<dt>用户名</dt>
		<dd>
			<div><input name="user[nameScreen]" type="text" id="user_nameScreen" value="{$g_current_user['nameScreen']}" ajax="nameScreen" alt="用户名" size="30" /><i></i></div>
			<div class="f_gra">用来登陆叽歪网（4个字符以上）</div>
		</dd>
		<dt>邮　箱</dt>
		<dd>
			<div><input id="user_email" name="user[email]" type="text" value="{$g_current_user['email']}" ajax="email" alt="Email" size="30" /><i></i></div>
			<div class="f_gra">用于找回密码和接收通知</div>
		</dd>
		<dt>你的叽歪地址</dt>
		<dd>
			<a href="http://jiwai.de/{$g_current_user['nameUrl']}">http://jiwai.de/{$g_current_user['nameUrl']}</a>
		</dd>
		<!--{if $g_current_user['isUrlFixed']=='N'}-->
		<dt>永久地址</dt>
		<dd>
			<div><a href="http://jiwai.de/">http://jiwai.de/</a> <input type="text" name="user[nameUrl]" value="{$g_current_user['nameUrl']}" size="10" id="user_nameUrl" ajax="nameUrl" alt="永久地址" /><i></i></div>
			<div class="f_red">你可以设置个性URL地址，但是只能修改一次，以后不能修改！如果现在你不确定你想要的名字，可以暂时维持现状，等以后再说。</div>
		</dd>
		<!--{/if}-->
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" />  &nbsp; <input type="reset" value="取消" /></div>
		</dd>
	</dl>
	<div class="clear"></div>
	</form>
</div>
<div class="clear"></div>
