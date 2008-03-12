<!--{include header}-->
<form method="post">
<table width="760"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="50"><h1>信息不通 反馈列表</h1></td>

<td width="10%" align="right">查询条件：</td>
<td width="300" align="right">
<select name="select">
	<option selected>不限</option>
	<option value="sms">手机</option>
	<option value="msn">MSN</option>
	<option value="gtalk">GTalk</option>
	<option value="skyp">Skype</option>
	<option value="qq">QQ</option>
	<option value="yahoo">Y!Messenger</option>
	<option value="api">API</option>
</select>

<select name="select2">
	<option selected>不限</option>
	<option>今天</option>
	<option>三天内</option>
	<option>一周内</option>
	<option>一月内</option>
</select>
<select name="select3">
	<option selected>全部</option>
	<option>未处理</option>
	<option>已处理</option>
	<option>不予处理</option>
</select>
</td>
<td width="10%" align="center"><input type="submit" name="Submit" value=" 查 询 "></td>
</tr>
</table>
</form>
<table width="760"  border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
<tr align="center" bgcolor="#CCCCCC" class="titie">
<td width="8%">设备</td>
<td width="15%">号码</td>
<td width="10%">时间</td>

<td width="6%">问题</td>
<td>内容</td>
<td width="10%">用户</td>
<td width="10%">状态</td>
<td width="15%">操作</td>
</tr>
<!--{foreach $one as $key => $one}-->
    <!--${
        $time = $one['timeCreate'];
        $times = explode(" ",$time);
        if( $one['type'] == 'MO' )
            $type = '上行';
        if( $one['type'] == 'MT' )
            $type = '下行';
    }-->
<tr align="center" bgcolor="#FFFFFF">
<td>{$one['device']} </td>
<td>{$one['metaInfo']['number']}</td>
<td>{$times[0]}</td>
<td>{$type}</td>
<td>{$one['remark']}</td>
<td><a href="http://jiwai.de/{$user_info[$key]['nameUrl']}/">{$user_info[$key]['nameScreen']}</a></td>
<td>${$one['dealStatus']=='NONE' ? '未处理' : ($one['dealStatus']=='FIXED' ? '已处理' : '已备案')}</td>
<td>
<!--{if ($one['dealStatus'] == 'NONE')}-->
	<a href="/feed_message.php?deal={$one['id']}">处理</a>&nbsp;<a href="/feed_message.php?id={$one['id']}">备案</a>
<!--{/if}-->&nbsp;<a href="/feed_message.php?delete={$one['id']}">Ｘ</a>
</td>

</tr>
<!--{/foreach}-->
</table>
<!--{include footer}-->
