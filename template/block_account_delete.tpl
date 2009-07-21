<div class="h_2em one_block f_14">
	<ul>
		<li>&nbsp;</li>
		<li><b>{$g_current_user['nameFull']} | {$g_current_user['nameScreen']}</b></li>
		<li>再……见……？</li>
		<li>删除账号后，所有相关信息都会被永久删除，并且无法挽回。</li>
		<li><span class="f_yel"><a href="/wo/">要不要在谨慎考虑一下？</a></span>如果有意见要抒发，<a href="/t/帮助留言板/">请告诉我们</a></li>
		<li>&nbsp;</li>
		<li><form method="POST"><input type="submit" value="真的想好了，请删除我吧！"  onclick="return confirm('账户删除后，你的所有叽歪内容将无法找回，确定删除账户吗？');"/><input type="hidden" name="crumb" value="${JWUtility::GenCrumb()}"/></form></li>
		<li>&nbsp;</li>
	</ul>
</div>
