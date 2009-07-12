<!--{include header}-->
<h2>上报问题用户</h2>
<!--{include tips}-->
<pre style="color:RED;">
1、在合适的位置填上用户名即可。
2、超级管理员处理过后，由超管删除处理过的记录。
</pre>
<form method="POST">
	<textarea name="w" rows="17" cols="40" >${htmlspecialchars($fresult)}</textarea>&nbsp;<textarea rows="17" name="r" cols="40" ${isAdmin('admin')?'':'disabled'}>${htmlspecialchars($rresult)}</textarea><br/>
	<input type="submit" value="确定"/>
</form>
<!--{include footer}-->
