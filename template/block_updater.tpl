<div class="update">
	<input type="hidden" id="jw_ruid" name="jw_ruid"/>
	<input type="hidden" id="jw_rsid" name="jw_rsid"/>
	<input type="hidden" id="jw_rtid" name="jw_rtid"/>
	<textarea id="jw_status" name="jw_status" onKeyDown="if(this.value.length>0&&((event.ctrlKey&&event.keyCode==13)||(event.altKey&&event.keyCode==83))){JWAction.updateStatus();return false;}" onKeyUp="textCounter(this.form.jw_status,$('count'),70);"></textarea>
	<ul class="f_gra">
		<li class="lt">1条短信还剩&nbsp;<span id="count">70</span>&nbsp;字</li>
		<li class="rt">Ctrl+Enter直接叽歪</li>
		<li class="button">
			<div class="at"></div><div class="bt"></div>
			<div class="tt" onclick="if($('jw_status').value.length>0){return JWAction.updateStatus();}else{$('jw_status').focus();}" >叽歪一下</div>
			<div class="bt"></div><div class="at"></div>
		</li>
	</ul>
</div>
