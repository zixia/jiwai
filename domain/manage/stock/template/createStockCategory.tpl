<!--{include header}-->

<h3>创建股票分类</h3>

<b>分类代码：请给分类加上字母数字编号 3-8 位，如 上证A股 => SZAG </b>

<form action='/createStockCategory.php' id='f' method="POST" onSubmit="return JWValidator.validate('f');">
<table>
<tr>
	<td align="right">分类代码：</td>
	<td>
		<input type="text" name="nameScreen" value="" id="nameScreen" alt="分类代码" check="null" minlength="3" maxLength="8" ajax="stockCategory" /><i></i>
	</td>
</tr>
<tr>
	<td align="right">分类名称：</td>
	<td>
		<input type="text" name="nameFull" value="" id="nameFull" alt="分类名称" check="null" minlength="2" /><i></i>
	</td>
</tr>
<tr>
	<td align="right">上级分类：</td>
	<td>
		<SELECT name="idParent" id="idParent" alt="详细分类" check="null">
			<option value="">--请选择分类--</option>
		<!--{foreach $topCategory as $k=>$v}-->
			<option value="{$k}">{$v['name']}</option>
		<!--{/foreach}-->
		</SELECT><i></i>
	</td>
</tr>
<tr>
	<td></td>
	<td><input type="submit" value="提交"/></td>
</tr>
</table>

</form>

<!--{include footer}-->
