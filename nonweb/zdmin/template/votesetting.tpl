<!--{include header}-->
<h2>修改投票信息</h2>

<hr/>
<form action="/votesetting.php" method="GET"> 投票编号: <br/>
<input type="text" name="vid" id="vid" value="{$vid}"/><br/>
<input type="submit" value="提交查询" onClick="return (vid.value!='');"/>
</form>

<!--{if ($vote['show'])}-->
<hr/>
<!--{include tips}-->
<form method="post" id="f" action="/votesetting.php?vid={$vid}">
<b>投票设定</b><br/>
投票编号：<input id="vote_number" name="vote[number]" type="text" value="{$vote['number']}" ${$vote['fix']=='number' ? 'readOnly' : ''}/><br/>
叽歪编号：<input id="vote_status" name="vote[status_id]" type="text" value="{$vote['status_id']}" ${$vote['fix']=='status' ? 'readOnly' : ''}/><br/>

<br/>
<pre> 使用方法：
	1、手机发送选项编号到 10669318456{$vote['number']}
	2、聊天工具、网页发 "TP {$vote['number']} 选项编号"
</pre>

<b>高级设置</b><br/>
<input ${$vote['d_sms']==true?'checked':''} id="vote_device_sms" name="vote[deviceAllow][]" type="checkbox" value="sms" style="width:24px; display:inline;" /><label for="vote_device_sms">允许手机短信发送</label><br/>
<input ${$vote['d_im']==true?'checked':''} id="vote_device_im" name="vote[deviceAllow][]" type="checkbox" value="im" style="width:24px; display:inline;" /><label for="vote_device_im">允许聊天软件(IM)发送</label><br/>
<input ${$vote['d_web']==true?'checked':''} id="vote_device_web" name="vote[deviceAllow][]" type="checkbox" value="web" style="width:24px; display:inline;" /><label for="vote_device_web">允许Web发送</label><br/>

<br/><b>投票期限</b><br/>
开始时间：<input id="vote_expire" name="vote[time_create]" type="input" style="width:160px; display:inline;" value="{$vote['time_create']}" /><br/>
截至时间：<input id="vote_expire" name="vote[time_expire]" type="input" style="width:160px; display:inline;" value="{$vote['time_expire']}"/><br/>

<br/><b>重复投票</b><br/>
限制：<select name="vote[limit_p]">
<option value="20" ${$vote['limit_p']==20?'selected':''}>20 分钟</option>
<option value="60" ${$vote['limit_p']==60?'selected':''}>1 小时</option>
<option value="360" ${$vote['limit_p']==360?'selected':''}>6 小时</option>
<option value="720" ${$vote['limit_p']==720?'selected':''}>12 小时</option>
<option value="1440" ${$vote['limit_p']==1440?'selected':''}>1 天</option>
<option value="0" ${$vote['limit_p']==0?'selected':''}>全程</option></select> 内，每用户允许投 <select name="vote[limit_l]">
<option value="1" ${$vote['limit_l']==1?'selected':''}>1</option>
<option value="5" ${$vote['limit_l']==5?'selected':''}>5</option>
<option value="10" ${$vote['limit_l']==10?'selected':''}>10</option>
<option value="50" ${$vote['limit_l']==50?'selected':''}>50</option>
<option value="100" ${$vote['limit_l']==100?'selected':''}>100</option>
</select> 票 <br/>


<br/><input type="submit" value="保存" /> 
</form>

<!--{/if}-->

<!--{include footer}-->
