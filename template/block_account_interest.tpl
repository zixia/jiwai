<div id="set_bor">
	<div class="mar_b50">
		<div>填写这些内容，你将有更多机会认识与自己志同道合的朋友。如果填写多个，可以用逗号分开。</div>
		<div class="f_gra">例如“吃饭，睡觉，打豆豆”</div>
	</div>
	<form action="/wo/account/interest" method="post">
	<dl class="w3">
		<dt>兴趣爱好</dt>
		<dd>
			<div><textarea name="user[interest]" rows="3" cols="60">${htmlSpecialChars($g_current_user['interest'])}</textarea></div>
		</dd>
		<dt>喜欢的书和作者</dt>
		<dd>
			<div><textarea name="user[bookWriter]" rows="3" cols="60">${htmlSpecialChars($g_current_user['bookWriter'])}</textarea></div>
		</dd>
		<dt>喜欢电影和演员</dt>
		<dd>
			<div><textarea name="user[player]" rows="3" cols="60">${htmlSpecialChars($g_current_user['player'])}</textarea></div>
		</dd>
		<dt>喜欢音乐和歌手</dt>
		<dd>
			<div><textarea name="user[music]" rows="3" cols="60">${htmlSpecialChars($g_current_user['music'])}</textarea></div>
		</dd>
		<dt>喜欢的地方</dt>
		<dd>
			<div><textarea name="user[place]" rows="3" cols="60">${htmlSpecialChars($g_current_user['place'])}</textarea></div>
		</dd>
		<dt></dt>
		<dd>
			<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" /> &nbsp; <input type="reset" value="取消" /></div>
		</dd>
	</dl>
	<div class="clear"></div>
			
	</form>
</div>
<div class="clear"></div>
