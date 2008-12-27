<div id="set_bor">
	<div>&nbsp;</div>
	<dl class="w1">
	<form action="/wo/notification/email" method="post">
		<dt><input id="user_send_new_direct_text_email" name="user[send_new_direct_text_email]" type="checkbox" value="Y" ${$user_setting['send_new_direct_text_email']=='Y' ? 'checked':''}/></dt>
		<dd>
			<div class="mar_b8"> 当有新悄悄话时以邮件形式发到邮箱</div>
		</dd>
		<dt><input id="send_new_friend_email" name="user[send_new_friend_email]" type="checkbox" value="Y" ${$user_setting['send_new_friend_email']=='Y' ? 'checked':''}/></dt>
		<dd>
			<div class="mar_b8"> 当我被别人关注时发到邮箱</div>
		</dd>
		<dt><input id="allow_system_mail" name="user[allow_system_mail]" type="checkbox" value="Y" ${$user_setting['allow_system_mail']=='Y' ? 'checked':''}/></dt>
		<dd>
			<div class="mar_b8"> 同意接收叽歪最新动态</div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" value="保存" /></div>
		</dd>
	</form>
	</dl>
	<div class="clear"></div>
</div>
