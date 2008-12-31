<div id="set_bor">
	<div class="mar_b50">除了手机短信、聊天软件外，你还可以通过网页叽歪，并可查看你的叽歪碎碎念博客。</div>
	<form action="/wo/account/complete" method="post">
	<dl class="w1">
		<dt>
			<select name="device" onChange="if(this.value!=0)$('login_info').innerHTML = this.value">
				<option value="0">--请选择--</option>
				<option value="请输入你的手机号">手机</option>
				<option value="输入你的QQ号">QQ</option>
				<option value="请输入完整的邮箱地址，如abc@example.com">MSN</option>
				<option value="请输入完整的邮箱地址，如abc@example.com">Gtalk</option>
				<option value="请输入你的飞信号，而不是你的手机号">飞信</option>
				<option value="请输入完整用户名">Skype</option>
				<option value="请输入完整用户名">Yahoo!</option>
				<option value="请输入完整的邮箱地址，如abc@example.com">AOL</option>
				<option value="请输入完整用户名">水木社区</option>
				<option value="请输入完整的邮箱地址，如abc@example.com">Jabber</option>
				<option value="请输入你的校内网数字ID，&lt;a href='http://blog.jiwai.de/index.php/archives/55' class='f_gra_l'&gt;我不知道什么是校内网数字ID&lt;/a&gt;" >校内</option>
			</select>
		</dt>
		<dd>
			<div><input type="text" name="address" /></div>
			<div id="login_info" class="f_gra"> </div>
		</dd>
		<dt>用户名</dt>
		<dd>
			<div><input type="text" name="nameScreen" /></div>
			<div class="f_gra">发送 woshishui 给叽歪小弟查询你的叽歪用户名</div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value=" &nbsp;开始设置&nbsp;" /></div>
		</dd>
	</dl>
	</form>
	<div class="clear"></div>
</div>
<div class="clear"></div>
