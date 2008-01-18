<h2>我在做什么?</h2>
<form action="/wo/status/update" method="post">
<p><input type="text" name="status" value="${get_session_var('name_screen_reply_to')}"/></p>
<p><input type="text" name="status_reply" style="display:none;" value="${get_session_var('id_status_reply_to')}"/></p>
<p><input type="submit" value="叽歪一下"/></p>
</form>
