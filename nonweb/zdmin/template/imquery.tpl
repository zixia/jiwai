<!--{include header}-->
<h2>根据用户id或nameSreen查询其即时聊天设备地址</h2>
<form action="imquery" method="GET">
用户: <input type="text" name="un" id="un" value="{$un}"/>
<input type="submit" value="查询设备" onClick="return (un.value!='');"/>
</form>

<h2>根据用户聊天工具地址查询用户</h2>
<form action="imquery" method="GET">
设备: <input type="text" name="im" id="im" value="{$im}"/>
<input type="submit" value="查询用户" onClick="return (im.value!='');"/>
</form>

<!--{if $unResult}-->
	<hr>
	<h3>用户信息</h3>
	<table class="result" width="740">
		<tr>
			<th width="48">头像</th>
			<th>ID编号</th>
			<th>显示名称</th>
			<th>全名</th>
			<th>通知设备</th>
			<th>位置</th>
		</tr>
		<!--{foreach $unResult as $one}-->
		<tr>
			<td><a href="http://jiwai.de/{$one['nameScreen']}/"><img src="${JWPicture::GetUrlById($one['idPicture'])}" border="0"></a></td>
			<td>{$one['id']}</td>
			<td><a href="http://jiwai.de/{$one['nameScreen']}/">{$one['nameScreen']}</a></td>
			<td>{$one['nameFull']}</td>
			<td>{$one['deviceSendVia']}</td>
			<td>{$one['location']}</td>
		</tr>
		<!--{/foreach}-->
	</table>
	<h3>设备信息</h3>
	<table class="result" width="740">
		<tr>
			<th width="30">类型</th>
			<th width="140">地址</th>
			<th>签名</th>
			<th width="30">记录</th>
			<th width="30">验证</th>
		</tr>
		<!--{foreach $imResult as $one}-->
		<tr>
			<td>{$one['type']}</td>
			<td>{$one['address']}</td>
			<td>{$one['signature']}</td>
			<td>{$one['isSignatureRecord']}</td>
			<td>${$one['secret']?'N':'Y'}</td>
		</tr>
		<!--{/foreach}-->
	</table>

<!--{/if}-->

<!--{include footer}-->
