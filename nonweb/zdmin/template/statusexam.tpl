<!--{include header}-->
<script>
var x = 1;
function cball(){
	x = (x==1) ? 0 : 1;
	for (var i=0;i<exam.elements.length;i++) { 
		var e = exam.elements[i];
		if( e.type != 'checkbox' )
			continue;
		e.checked = (x==1) ? false:true;
	}
}
function ons(){
	return confrim("确定操作所选的更新吗？");
}
</script>
<h2>审核更新</h2>
<!--{include tips}-->
<!--{if $statusQuarantine}-->
	<h3>未审核更新信息(每页20条 - 审核完一页再进行下一页)</h3>
	<form name="exam" action="statusexam" method="POST" onSubmit="return ons();">
	<input type="submit" name="delete" value="删除">
	<input type="submit" name="allow" value="审核通过">
	<table class="result" width="740">
		<tr>
			<th style="text-align:center;">
				<img src="http://asset1.jiwai.de/img/form/check.gif" onClick="cball();"/>
			</th>
			<th width="50">idUser</th>
			<th width="30">Device</th>
			<th>叽歪</th>
			<th width="120">禁忌词汇</th>
			<th width="100">时间</th>
		</tr>
		<!--{foreach $statusQuarantine as $one}-->
		<tr>
			<td><input type="checkbox" name="cb[]" value="{$one['id']}"/></td>
			<td>{$one['idUser']}</td>
			<td>{$one['device']}</td>
			<td style="text-align:left;padding:10px;">{$one['status']}</td>
			<td>(<font color="RED">${implode(',',$dictFilter->GetFilterWords($one['status']))}</font>)</td>
			<td>{$one['timeCreate']}</td>
		</tr>
		<!--{/foreach}-->
	</table>
	</form>
<!--{else}-->
<h3>没有需要审核的更新</h3>
<!--{/if}-->

<!--{include footer}-->
