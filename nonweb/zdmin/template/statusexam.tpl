<!--{include header}-->
<script>
var x = 1;
function cball(){
	x = (x==1) ? 0 : 1;
	var exam = document.getElementById('exam');
	for (var i=0;i<exam.elements.length;i++) { 
		var e = exam.elements[i];
		if( e.type != 'checkbox' )
			continue;
		e.checked = (x==1) ? false:true;
	}
}
function ons(){
	return confirm("确定操作所选的更新吗？");
}
</script>
<h2>审核更新</h2>
<!--{include tips}-->
<!--{if $statusQuarantine}-->
	<h3>未审核更新信息(每页20条 - 审核完一页再进行下一页)</h3>
	<form name="exam" id="exam" action="statusexam.php" method="POST" onSubmit="return ons();">
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
			<th>屏蔽词</th>
			<th width="100">时间</th>
		</tr>
		<!--{foreach $statusQuarantine as $one}-->
		<!--${ $u = JWUser::GetUserInfo( $one['metaInfo'][0] ); }-->
		<tr>
			<td width="20"><input type="checkbox" name="cb[]" value="{$one['id']}"/></td>
			<td width="100" ><A href="http://JiWai.de/{$u['nameUrl']}/">{$u['nameScreen']}</a></td>
			<td width="20">{$one['metaInfo'][2]}</td>
			<td style="text-align:left;padding:10px;">{$one['metaInfo'][1]}</td>
			<td width="100" style="color:red">${implode(',', $dict_filter->GetFilterWords($one['metaInfo'][1]))}</td>
			<td width="100">${date('Y-m-d H:i:s',$one['metaInfo'][3])}</td>
		</tr>
		<!--{/foreach}-->
	</table>
	</form>
	{$page_string}
<!--{else}-->
<h3>没有需要审核的更新</h3>
<!--{/if}-->

<!--{include footer}-->
