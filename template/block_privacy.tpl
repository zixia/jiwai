<div id="set_bor">
	<div>&nbsp;</div>
	<dl class="w1">
		<form action="/wo/privacy/" method="post">
		<dt><input type="checkbox" id="messageFriendOnly" name="user[messageFriendOnly]" value="Y" ${$g_current_user['messageFriendOnly']=='Y' ? 'checked':''}/></dt>
		<dd>
			<div class="f_14 mar_b8">只允许我关注的人给我发送悄悄话</div>
			<div class="f_gra">
				其他任何人将无法给你发送悄悄话<br />
				如果你只是不想通过邮件形式接收悄悄话，可以在这里设置
			</div>
		</dd>
		<dt></dt>
		<dd></dd>
		<dt><input type="checkbox" id="protect" name="user[protected]" value="Y" ${$g_current_user['protected']=='Y' ? 'checked':''}/></dt>
		<dd>
			<div class="f_14 mar_b8">只对我关注的人开放我的叽歪</div>
			<div class="f_gra">其他任何人将无法看到你的叽歪，也不会被搜索引擎获取</div>
		</dd>
		<dt></dt>
		<dd><div><input type="submit" value="&nbsp; 保存 &nbsp;" /></div></dd>
		</form>
	</dl>
	<div class="clear"></div>
</div>
