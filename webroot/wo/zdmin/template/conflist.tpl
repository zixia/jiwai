<!--{include header}-->
<h2>会议用户信息</h2>

<!--{if $unResult}-->
	<hr>
	<table class="result" width="740">
		<tr>
			<th width="48"></th>
			<th>会议编号</th>
			<th>用户编号</th>
			<th>会议号码</th>
			<th>允许设备</th>
			<th>允许好友</th>
			<th>显示名称</th>
			<th>全名</th>
			<th>状态</th>
		</tr>
		<!--{foreach $unResult as $one}-->
		<tr>
			<td><a href="http://jiwai.de/{$one['nameUrl']}/" title="{$one['nameFull']}"><img src="${JWPicture::GetUrlById($one['idPicture'])}" border="0"></a></td>
			<td>{$one['id']}</td>
			<td>{$one['idUser']}</td>
			<td>{$one['number']}</td>
			<td>{$one['deviceAllow']}</td>
			<td>{$one['friendOnly']}</td>
			<td><a href="http://jiwai.de/{$one['nameUrl']}/">{$one['nameScreen']}</a></td>
			<td>{$one['nameFull']}</td>
			<td><a href="confsetting?un={$one['nameScreen']}">修改</a></td>
		</tr>
		<!--{/foreach}-->
	</table>

<!--{/if}-->

<!--{include footer}-->
