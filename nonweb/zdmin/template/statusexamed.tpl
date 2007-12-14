<!--{include header}-->
<h2>已审核JiWai更新 [${$dealStatus=='deleted' ? '已被删除' : '通过审核'}]</h2>
<!--{include tips}-->

<h3>
<!--{if strtoupper($dealStatus)=='DELETED'}-->
	<a href="statusexamed?d=allowed">通过的JiWai更新</a>
<!--{else if strtoupper($dealStatus)=='ALLOWED'}-->
	<a href="statusexamed?d=deleted">删除的JiWai更新</a>
<!--{/if}-->
</h3>

<!--{if $statusQuarantine}-->
	<table class="result" width="740">
		<tr>
			<th style="text-align:center;">状态</th>
			<th width="50">idUser</th>
			<th width="30">Device</th>
			<th>叽歪</th>
			<th width="120">禁忌词汇</th>
			<th width="100">时间</th>
		</tr>
		<!--{foreach $statusQuarantine as $one}-->
		<tr>
			<td>${$one['dealStatus']=='DELETED' ? 'D' : 'A'}</td>
			<td>{$one['idUser']}</td>
			<td>{$one['device']}</td>
			<td style="text-align:left;padding:10px;">{$one['status']}</td>
			<td>(<font color="RED">${implode(',',$dictFilter->GetFilterWords($one['status']))}</font>)</td>
			<td>{$one['timeCreate']}</td>
		</tr>
		<!--{/foreach}-->
	</table>
	${JWTemplate::Pagination($pagination, array('d'=>$dealStatus))}
<!--{else}-->
	<h3>没有符合条件的记录</h3>
<!--{/if}-->

<!--{include footer}-->
