<!--{include header}-->
<h2>JiWai用户合并</h2>
<form action="usermerge" method="POST">
用户名1： <input type="text" name="un1" id="un1" value="{$un1}" ><br />
用户名2： <input type="text" name="un2" id="un2" value="{$un2}" ><br />
设&nbsp&nbsp&nbsp&nbsp&nbsp备： <input type="text" name="device" id="device" value="{$device}"><br />
设备地址：<input type="text" name="address" id="address" value="{$address}"><br />
<input type="submit" name="submit" value="合并" onClick="return(un1.value!='' && un2.value!='' && device.value!='' && address.value!='');" />
</form>
<!--{include footer}-->

