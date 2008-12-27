<?php
header('Content-Type: text/html;charset=UTF-8');
?>
<div id="login" class="free reg_log_wid" style="width:600px;">
	<div class="lt50">
		<div class="pagetitle">
			<h3 id="loginTips">登陆到叽歪</h3>
		</div>
		<dl>
			<dt>用户名</dt>
			<dd class="inp"><div><input type="text" id="username_or_email" name="username_or_email" mission="JWAction.login();" onKeyDown="JWAction.onEnterSubmit(event,this);"/></div><br /></dd>
			<dt>密　码</dt>
			<dd class="inp"><div><input type="password" id="password" name="password" mission="JWAction.login();" onKeyDown="JWAction.onEnterSubmit(event,this);" /></div></dd>
			<dt></dt>
			<dd><div><input id="jz" type="checkbox" id="remember_me" name="remember_me" value="1" checked /> <label for="jz">在这台电脑上记住我</label></div></dd>
			<dt></dt>
			<dd>
				<a href="/wo/resend_password" class="rt">忘记密码了</a>
				<div class="button">
					<div class="at"></div><div class="bt"></div>
					<div class="tt" onclick="JWAction.login();" >登录</div>
					<div class="bt"></div><div class="at"></div>
				</div>
			</dd>
		</dl>
	</div>
	<div class="yel_line"></div>
	<div class="rt50">
		<div class="pagetitle">
			<div class="rt"><a href="javascript:void();" onclick="return JWSeekbox.remove();" class="close">X</a></div>
			<h3 id="registerTips">快速注册</h3>
		</div>
		<dl>
			<dt>用户名</dt>
			<dd class="inp"><div><input type="text" id="username" name="username_or_email" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div><div class="f_gra">中文两个字以上<br />英文或数字四个字符以上</div></dd>
			<dt>密　码</dt>
			<dd class="inp"><div><input type="password" id="password_one" name="password_one" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div></dd>
			<dt>确认密码</dt>
			<dd class="inp"><div><input type="password" id="password_confirm" name="password_confirm" onKeyDown="JWAction.onEnterSubmit(event,this);" mission="JWAction.register();" /></div></dd>
			<dt></dt>
			<dd>
				<div class="button">
					<div class="at"></div><div class="bt"></div>
					<div class="tt" onclick="JWAction.register();" >注册</div>
					<div class="bt"></div><div class="at"></div>
				</div>
			</dd>
		</dl>
	</div>
	<div style="border-top:1px solid #cccccc;  padding-left:20px; padding-top:8px; padding-bottom:10px; clear:both;">
		<input type="button" class="submitbutton" value="匿名发布" onClick="return JWAction.anonymous();" />&nbsp;&nbsp;<input type="button" class="closebutton" value="取消" onclick="JWSeekbox.remove();"/>
	</div>
	<div class="clear"></div>
</div>
