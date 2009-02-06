<div id="set_bor">
	<div class="mar_20">&nbsp;</div>
	<form id="f1" action="/wo/account/password" class="validator" method="post">
	<dl class="w1">
	<!--{if false==$reset_password}-->
		<dt>当前密码</dt>
		<dd>
			<div><input id="current_password" name="current_password" type="password" minlength="6" maxlength="16" class="inputStyle" alt="当前密码" /><i></i></div>
		</dd>
	<!--{/if}-->
		<dt>新密码</dt>
		<dd>
			<div><input id="password" name="password" type="password" alt="新密码" minlength="6" maxlength="16" /><i></i></div>
			<div class="f_gra">密码至少6个字符。<br />叽歪建议你使用数字、符号、字母组合的复杂密码</div>
		</dd>
		<dt>确认新密码</dt>
		<dd>
			<div><input id="password_confrim" name="password_confrim" type="password" compare="password" alt="确认密码" minlength="6" maxlength="16" /><i></i></div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" value="&nbsp; 保存修改 &nbsp;" />  &nbsp; <input type="reset" value="取消" /></div>
		</dd>
	</dl>
	<div class="clear"></div>
	</form>
</div>
<div class="clear"></div>
