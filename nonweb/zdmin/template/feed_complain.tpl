<!--{include header}-->
<form method="post">
<table width="760"  border="0" cellspacing="0" cellpadding="0">
<tr>
<td height="50"><h1>举报用户 反馈列表</h1></td>

<td width="15%" align="right">查询条件：</td>
<td width="160" align="right">
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
<option>已备案</option>

</select></td>
<td width="10%" align="center"><input type="submit" name="Submit" value=" 查 询 "></td>
</tr>
</table>
</form>
<table width="760"  border="0" cellpadding="1" cellspacing="1" bgcolor="#CCCCCC">
<tr align="center" bgcolor="#CCCCCC" class="titie">
<td width="10%">被举报人</td>
<td>原因</td>
<td width="10%">举报用户</td>

<td width="10%">举报时间</td>
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
<td><a href="http://jiwai.de/{$user_info2[$key]['nameUrl']}/">{$user_info2[$key]['nameScreen']}</a></td>
<td>{$one['remark']}</td>
<td>{$user_info[$key]['nameScreen']}</td>

<td>{$times[0]}</td>
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
