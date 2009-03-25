<div id="set_bor">
	<div>&nbsp;</div>
	<form action="/wo/notification/im" method="post">
	<dl class="w3">
		<dt><input ${$user_setting['auto_nudge_me']=='Y' ? 'checked':''} id="user_auto_nudge_me" name="user[auto_nudge_me]" type="checkbox" value="Y" /></dt>
		<dd>
			<div class="mar_b8"> 如果我在24内没有更新，请叽歪小弟提醒我</div>
		</dd>
		<dt><input ${$user_setting['is_receive_offline']=='Y' ? 'checked':''} id="user_is_receive_offline" name="user[is_receive_offline]" type="checkbox" value="Y" /></dt>
		<dd>
			<div class="mar_b8"> 我的聊天软件处于离线（隐身）状态时也接收通知</div>
		</dd>
		<dt><input ${$user_setting['allowSystemSms']=='Y' ? 'checked':''} id="user_allowSystemSms" name="user[allowSystemSms]" type="checkbox" value="Y" /></dt>
		<dd>
			<div class="mar_b20"> 允许叽歪通过短信联系我</div>
		</dd>
		<dt></dt>
		<dd>
			<div class="mar_b8"> 
				睡觉时间不给我通知 
				<select name="user[notReceiveTime1]" >
					<option value="">----------</option>
				<!--{foreach $time_split AS $stime}-->
				<!--${$s=($time_one==$stime?'selected':'');}-->
					<option value="${$stime>9?$stime:'0'.$stime}" {$s}>${$stime>9?$stime:'0'.$stime}:00</option>
				<!--{/foreach}-->
				</select>
				至
				<select name="user[notReceiveTime2]" >
					<option value="">----------</option>
				<!--{foreach $time_split AS $stime}-->
				<!--${$s=($time_two==$stime?'selected':'');}-->
					<option value="${$stime>9 ? $stime : '0'.$stime}" {$s}>${$stime>9?$stime:'0'.$stime}:00</option>
				<!--{/foreach}-->
				</select>
			</div>
		</dd>
		<dt></dt>
		<dd>
			<div class="mar_b8">我想
				<select name="user[allowReplyType]">
				<!--{foreach $replytype AS $v=>$tip}-->
					<option value="{$v}" ${$user_setting['allowReplyType']==$v ? 'selected':''}>{$tip}</option>
				<!--{/foreach}-->
				</select>
				<span> &nbsp; <a href="http://help.jiwai.de/ReplyReceive" class="f_gra_l">什么意思？</a></span>
			</div>
		</dd>
	</dl>
	<div class="clear"></div>
	<div align="center"><input type="submit" value="保存" /></div>
	</form>
</div>
<div class="clear"></div>
