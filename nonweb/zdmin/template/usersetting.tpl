<!--{include header}-->
<h2>JiWai用户合并</h2>
<pre style="color:RED;">
注意：
	1、用户名1为能在web上正常登录的用户
	2、用户名2一般为使用IM或手机注册的叽歪用户
	3、设备可选填『msn、gtalk、skype、qq等』
	4、设备地址与设备匹配
</pre>

<form action="usersetting.php" method="POST">
用户名1： <input type="text" name="un1" id="un1" value="{$un1}" >
新密码：<input type="password" id="password" name="password" value="{$password}">
<input type="submit" name="modifypass" id="modifypass" value="修改密码"><br />
用户名2： <input type="text" name="un2" id="un2" value="{$un2}" ><br />
设　　备：<input type="text" name="device" id="device" value="{$device}"><br />
设备地址：<input type="text" name="address" id="address" value="{$address}"><br />
<input type="submit" name="submit" value="合并" onClick="return(un1.value!='' && un2.value!='' && device.value!='' && address.value!='');" /><br />
<br />

</form>
<!--{include footer}-->

