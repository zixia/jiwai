<!--{include header}-->

<h3>待审核的用户上行信息 - (${count($queue)})</h3>
<form action="" method="POST" id="f" onSubmit="return JWValidator.validate('f');">
<table border="1">
<tr>
	<th width="20"><input type="button" value="全选"/></th>
	<th width="140">会议用户</th>
	<th width="140">发送者</th>
	<th width="40">发送设备</th>
	<th width="300">内容</th>
</tr>
<!--{foreach $queue as $r}-->
<tr>
	<td><input type="checkbox" name="ids[]" value="{$r['id']}"/></td>
	<td>${JWNotify::GetPrettySender($users[$r['idUserTo']])}</td>
	<td>${JWNotify::GetPrettySender($users[$r['idUserFrom']])}</td>
	<td>{$r['metaInfo']['device']}</td>
	<td>{$r['metaInfo']['status']}</td>
</tr>
<!--{/foreach}-->
</table>
<p align="center">
	<input type="hidden" name="action" id="action" value="delete">
	<input type="submit" onClick="${'action'}.value='delete';" value="删除"/>
	<input type="submit" onClick="${'action'}.value='web';" value="不通知"/>
	<input type="submit" onClick="${'action'}.value='im';" value="通知IM"/>
	<input type="submit" onClick="${'action'}.value='all';" value="通知所有"/>
</p>
</form>

<!--{include footer}-->
