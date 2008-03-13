<!--{include header}-->
<h2>群发会议短信</h2>
<!--{include tips}-->
<form method="POST">
会议用户名<br/>
<input type="text" name="name_screen"/><br/><br/>

会议启用时间<br/>
<input type="text" name="date_begin" value="{$date_begin}"/><br/><br/>

会议结束时间<br/>
<input type="text" name="date_end" value="{$date_end}"/><br/><br/>

是否会议特服号发送 (午夜心语等使用独立特服号选否吧)<br/>
<input type="radio" name="use_number" value="1" checked> 是
<input type="radio" name="use_number" value="0"> 否 <br/><br/>

短信内容<br/>
<textArea name="content" rows="5" cols="60">${htmlSpecialChars($content)}</textArea><br/><br/>

<input type="submit" value="发送"/>

</form>
<!--{include footer}-->
