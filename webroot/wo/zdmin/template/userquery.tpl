<!--{include header}-->
<h2>JiWai用户综合信息查询</h2>
<form action="userquery" method="GET">
用户: <input type="text" name="un" id="un" value="{$un}"/>
<input type="submit" value="提交查询" onClick="return (un.value!='');"/>
</form>

<!--{if $unResult}-->
	<hr>
	<h3>用户信息</h3>
	<table class="result" width="740">
		<tr>
			<th width="48">头像</th>
			<th width="60">ID编号</th>
			<th>显示名称</th>
			<th>全名</th>
			<th>更新</th>
			<th>好友</th>
			<th>粉丝</th>
			<th>悄悄话</th>
			<th>收藏</th>
			<th>通知设备</th>
			<th>位置</th>
		</tr>
		<!--{foreach $unResult as $one}-->
		<tr>
			<td><a href="http://jiwai.de/{$one['nameScreen']}/"><img src="${JWPicture::GetUrlById($one['idPicture'])}" border="0"></a></td>
			<td>{$one['id']}</td>
			<td><a href="http://jiwai.de/{$one['nameScreen']}/">{$one['nameScreen']}</a></td>
			<td>{$one['nameFull']}</td>
			<td>${number_format($one['numStatus'])}</td>
			<td>${number_format($one['numFriend'])}</td>
			<td>${number_format($one['numFollower'])}</td>
			<td>${number_format($one['numMessage'])}</td>
			<td>${number_format($one['numFavourite'])}</td>
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

<!--{if $stResult}-->
	<h3>更新信息(最近10条)</h3>
	<table class="result" width="740">
		<tr>
			<th width="50">ID编号</th>
			<th width="30">设备</th>
			<th>叽歪</th>
			<th width="100">时间</th>
		</tr>
		<!--{foreach $stResult as $one}-->
		<tr>
			<td>{$one['idStatus']}</td>
			<td>{$one['device']}</td>
			<td style="text-align:left;padding:10px;">{$one['status']}</td>
			<td>${date('Y-m-d H:i:s', $one['timeCreate'])}</td>
		</tr>
		<!--{/foreach}-->
	</table>
<!--{/if}-->


<!--{include footer}-->
