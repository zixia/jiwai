<div id="set_bor">
	<div class="mar_b20">如果你已经用手机或聊天工具叽歪过，可以<a href="/wo/login">点击这里</a>登陆到叽歪。</div>
	<form id="f1" class="validator" method="post" action="/wo/account/create">
	<dl class="w3">
		<dt>Email</dt>
		<dd>
			<div>
			<input id="user_email" type="text" name="user[email]" ajax="Email" alt="Email" check="null" /><i></i></div>
			<div class="f_gra">用于接收通知和找回密码，我们不会对外公开</div>
		</dd>
		<dt>密　码</dt>
		<dd>
			<div><input id="user_pass" type="password" name="user[pass]" alt="密码" minlength="6" maxlength="20" /></div>
			<div class="f_gra">至少6个字符</div>
		</dd>
		<dt>确认密码</dt>
		<dd>
			<div><input id="user_pass_confirm" type="password" name="user[pass_confirm]" alt="确认密码" compare="user_pass" minlength="6" maxlength="16" onblur="JWValidator.onPassBlur('user_pass_confirm');"/><i></i></div>
		</dd>
		<dt>用户名</dt>
		<dd>
			<div><input id="user_name_screen" style="display:inline;" name="user[name_screen]" size="30" type="text" minlength="2" maxlength="16" value="" alt="用户名" ajax="nameScreen"/><i></i></div>
			<div class="f_gra">中文两字以上，英文或数字四个字符以上</div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="checkbox" name="read_and_accept" value="true" checked />已阅读并接受<a href="http://jiwai.de/Tos">服务条款</a></div>

		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" value="完成注册" /></div>
		</dd>
		<div class="clear"></div>		
	</dl>
	</form>
</div>
<div class="clear"></div>
