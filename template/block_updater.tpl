<div class="update">
	<input type="hidden" id="jw_ruid" name="jw_ruid"/>
	<input type="hidden" id="jw_rsid" name="jw_rsid"/>
	<input type="hidden" id="jw_rtid" name="jw_rtid"/>
	<input type="hidden" name="crumb" value="${JWUtility::GenCrumb()}"/>
	<textarea id="jw_status" name="jw_status" mission="JWAction.updateStatus();" onKeyDown="JWAction.onEnterSubmit(event, this, true);" onKeyUp="textCounter(this.form.jw_status,$('count'),70);">{$_GET['status']}</textarea>
	<ul class="f_gra">
		<li class="lt">1条短信还剩&nbsp;<span id="count">70</span>&nbsp;字</li>
		<li class="rt">Ctrl+Enter直接叽歪</li>
		<li class="button">
			<div class="at"></div><div class="bt"></div>
			<div class="tt"><a href="javascript:;" onclick="if($('jw_status').value.length>0){return JWAction.updateStatus();}else{$('jw_status').focus();}return false;" >叽歪一下</a></div>
			<div class="bt"></div><div class="at"></div>
		</li>
	</ul>
</div>
