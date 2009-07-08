<!--{include header}-->
<h2>JiWai用户设置</h2>
<pre style="color:RED;">
注意：
	1、用户名1为能在web上正常登录的用户
	2、用户名2一般为使用IM或手机注册的叽歪用户
	3、设备可选填『msn、gtalk、skype、qq等』
	4、设备地址与设备匹配
</pre>

<!--{include tips}-->

<form action="usersetting.php" method="POST">

<fieldset>
<legend>待设置用户名</legend>
用户名1： <input type="text" name="un1" id="un1" value="{$un1}" >
</fieldset>

<fieldset>
<legend>特殊管理</legend>
<input type="submit" name="removeuser" id="removeuser" value="销毁用户" onclick="return confirm('确定彻底销毁指定用户吗(无法恢复)？');">
<input type="submit" name="isolateuser" id="isolateuser" value="隔离用户">
</fieldset>

<fieldset>
<legend>修改用户密码</legend>
新密码：<input type="password" id="password" name="password" value="{$password}">&nbsp;<input type="submit" name="modifypass" id="modifypass" value="修改密码"><br />
</fieldset>

<fieldset>
<legend>合并用户</legend>
用户名2： <input type="text" name="un2" id="un2" value="{$un2}" >
设备：<input type="text" name="device" id="device" value="{$device}">
设备地址：<input type="text" name="address" id="address" value="{$address}">
<input type="submit" name="submit" value="合并" onClick="return(un1.value!='' && un2.value!='' && device.value!='' && address.value!='');" /><br />
</fieldset>

</form>
<!--{include footer}-->

