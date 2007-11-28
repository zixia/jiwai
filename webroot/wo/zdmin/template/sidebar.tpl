<!--{include header}-->
<form action="sidebar" method="POST">
<table >
<tr>
<td>
<textarea id="content" name="content" rows="25" cols="90">${htmlSpecialChars($data)}
</textarea>
<br/><br/>
<input type="hidden" id="type" name="type">
<input type="submit" value="预览" onclick="this.form.target='_blank'; javascript:document.getElementById('type').value='prev';"/>
<input type="submit" value="保存" id="save" onclick="javascript:document.getElementById('type').value='save';return true;"/>　　
<input type="submit" value="重载" id="reload" onclick="javascript:document.getElementById('type').value='reload';return true;"/>　　
</td>
<td valign="top" align="center">
<div id="preview"></div>
</td>
</tr>
</table>
</form>

<!--{include footer}-->
