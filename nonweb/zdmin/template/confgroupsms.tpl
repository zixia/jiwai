<!--{include header}-->
<h2>群发会议短信</h2>
<!--{include tips}-->
<form method="POST">
会议特服号子号 ( 如：01、21、27 这样的  ) <br/>
<input type="text" name="number"/><br/><br/>

会议启用时间<br/>
<input type="text" name="date_begin" value="{$date_begin}"/><br/><br/>

会议结束时间<br/>
<input type="text" name="date_end" value="{$date_end}"/><br/><br/>

短信内容<br/>
<textArea name="content" rows="5" cols="60">${htmlSpecialChars($content)}</textArea><br/><br/>

<input type="submit" value="发送"/>

</form>
<!--{include footer}-->
