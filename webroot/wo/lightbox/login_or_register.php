<div id="wtLightbox" style="width:600px; ">
	<div style="width:298px; float:left;  border-right:1px solid #cccccc; ">
		<h2 class="red" id="loginTips">您还没有登录，请先登录</h2>
		<p class="lightbox_login">用<span class="mar3">户</span><span class="mar3">名</span>：
		  <input id="username_or_email" name="username_or_email" type="text" class="inputStyle2" /></p>
		<p class="lightbox_login">密　　码：
		  <input id="password" name="password" type="password" class="inputStyle2" /></p>

		<ul >
			<li class="box32"><input name="" type="checkbox" checked value=""></li>
			<li class="box42">在这台电脑上记住我</li>
		</ul>
		<p class="butt2">
		  <input type="button" class="submitbutton" value="登 录" onClick="return JWAction.login();" />
		</p>
	</div>

	<div style="width:292px; float:right;">
		<h2 class="red" id="registerTips">或者，现在注册</h2>
		<p class="lightbox_login">用<span class="mar3">户</span><span class="mar3">名</span>：
		  <input id="username" name="username" type="text" class="inputStyle2" /></p>
		<p class="lightbox_login">密　　码：
		  <input id="password_one" name="password_one" type="password" class="inputStyle2" /></p>
		<p class="lightbox_login">确认密码：
		<input id="password_confirm" name="password_confirm" type="password" class="inputStyle2" /></p>

		  <p class="butt2">
		    <input type="button" class="submitbutton" value="注 册" onClick="return JWAction.register();"/>&nbsp;&nbsp;		</p>
	</div>
	<div style="border-top:1px solid #cccccc;  padding-left:20px; padding-top:8px; padding-bottom:10px; clear:both;">
		<input type="button" class="closebutton" value="取消" onclick="TB_remove();"/>
	</div>
</div> 
