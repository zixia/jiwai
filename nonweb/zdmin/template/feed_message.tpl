<!--{include header}-->
<form method="post">
<table width="760"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="50"><h1>信息不通 反馈列表</h1></td>

<td width="10%" align="right">查询条件：</td>
<td width="300" align="right">
<select name="select">
<option selected>不限</option>
<option value="qq">QQ</option>
<option value="msn">MSN</option>
<option value="gtalk">GTalk</option>
<option value="skyp">Skype</option>
<option value="yahoo">Y!Messenger</option>
<option value="sms">手机</option
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

</select></td>
<td width="10%" align="center"><input type="submit" name="Submit" value=" 查 询 "></td>
</tr>
</table>
</form>
<table width="760"  border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
<tr align="center" bgcolor="#CCCCCC" class="titie">
<td width="12%">设备</td>
<td width="15%">号码</td>
<td width="10%">时间</td>

<td width="6%">问题</td>
<td>内容</td>
<td width="10%">用户</td>
<td width="15%">处理结果</td>
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
<td>{$user_info[$key]['nameScreen']}</td>

<!--{if ($one['dealStatus'] == 'NONE')}-->

    <td><a href="/wo/zdmin/message/?deal={$one['id']}">未处理</a> <a href="/wo/zdmin/message/?id={$one['id']}">不予处理</a></td>
<!--{else if ($one['dealStatus'] == 'FIXED')}-->

    <td>已处理 <a href="/wo/zdmin/message/?delete={$one['id']}">删除</a></td>
<!--{else if ($one['dealStatus'] == 'WONTFIX')}-->
    <td>不予处理 <a href="/wo/zdmin/message/?delete={$one['id']}">删除</a></td>
<!--{/if}-->

</tr>
<!--{/foreach}-->
</table>
<!--{include footer}-->
