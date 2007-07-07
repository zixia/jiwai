<!--{include header}-->
<h2>注册人数列表</h2>
<a href="?">所有月份</a>
<!--{foreach $mArray as $m}-->
<a href="?m={$m}">{$m}</a>
<!--{/foreach}-->
<table class="result" width="300">
	<tr>
		<th>日期</th>
		<th>注册人数</th>
	</tr>
	<!--{foreach $result as $one}-->
	<tr ${isWeekend($one['day']) ? 'style="background-color:#CD6;"' : ''}>
		<td>{$one['day']}</td>
		<td>{$one['count']}</td>
	</tr>
	<!--{/foreach}-->
</table>

<!--{include footer}-->
