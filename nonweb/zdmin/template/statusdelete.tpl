<!--{include header}-->
<h2>根据Status的Id号删除一条更新</h2>
<!--{include tips}-->
<form action="statusdelete.php" method="get">
查看的Id号：<input type="text" name="id" id="idStatus" value="{$id}"/><input type="submit" value="查看" onClick="return ''!=idStatus.value" />
<input type="hidden" name="type" value="view" />
</form>
<form action="statusdelete.php" method="post">
修改的Id号：<input type="text" name="id" id="idStatus" value="{$id}"/><input type="submit" value="修改" onClick="return ''!=idStatus.value" /><br/>
<textarea name="status" rows=15 cols=80>{$status}</textarea>
<input type="hidden" name="type" value="update" />
</form>
<form action="statusdelete.php" method="POST">
删除的Id号：<input type="text" name="id" id="idStatus" value="{$id}"/> <input type="submit" value="删除" onClick="return (idStatus.value) ? confirm('确认删除'+idStatus.value+'号更新?') : false;"/>
<input type="hidden" name="type" value="delete" />
</form>
<form action="statusdelete.php" method="POST">
拟转移Id号：<input type="text" name="id" id="idStatus" value="{$id}"/> 到用户 <input type="text" name="name" value="{$name}" />的名下&nbsp;<input type="submit" value="转移" onClick="return (idStatus.value) ? confirm('确认转移'+idStatus.value+'号更新?') : false;"/>
<input type="hidden" name="type" value="transfer" />
</form>
<!--{include footer}-->
