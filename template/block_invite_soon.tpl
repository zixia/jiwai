<div class="block">
	<div class="mar_b40">你的朋友接受邀请注册后，将会自动的开始关注你。</div>
	<div class="gray">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_1" name="ctr_1" href="javascript:ctrObj('ctr_1','elm_1')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
			<div class="rt"><span class="ico_mail"><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="20" height="14" /></span></div>
			<h4 onClick="ctrObj('ctr_1','elm_1')">&nbsp; 通过Email邀请朋友...</h4>
		</div>
		<div class="f">
			<form action="/wo/invite/email" method="POST">
			<div id="elm_1" class="pad" style="display:none">
				<div class="mar_b8">输入朋友的Email地址：[多个收件人用回车或者,分隔]</div>
				<div class="mar_b8">
					<textarea name="emails" style="width:500px;"></textarea>
				</div>
				<div class="mar_b8"><input type="submit" value=" &nbsp;发送邀请" />	</div>
			</div>
			</form>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<div class="block">
	<div class="gray">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_2" name="ctr_2" href="javascript:ctrObj('ctr_2','elm_2')" class="max"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
			<div class="rt"><span class="ico_call"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="20" height="14" /></span></div>
			<h4 onClick="ctrObj('ctr_2','elm_2')">&nbsp; 通过短信邀请朋友...</h4>
		</div>
		<div class="f">
			<form action="/wo/invite/sms" method="POST">
			<div id="elm_2" class="pad" style="display:none">
				<div class="mar_b8">输入朋友的手机号：[多个收件人用回车或者,分隔]</div>
				<div class="mar_b8">
					<textarea name="smses" style="width:500px;"></textarea>
				</div>
				<div>内容：</div>
				<div class="mar_b8">我是<input type="text" name="nickname" value="{$g_current_user['nameScreen']}" size="10" />，我在叽歪网建立了我的碎碎念平台，你可以回复任何想说的话，开始你的碎碎念，回复 F 关注我（可以随时停止关注）</div>
				<div class="mar_b8"><input type="submit" value=" &nbsp;发送邀请" />	</div>
			</div>
			</form>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<div class="block">
	<div class="gray">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_3" name="ctr_3" href="javascript:ctrObj('ctr_2','elm_2')" class="max"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
			<div class="rt"><span class="ico_link"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="20" height="14" /></span></div>
			<h4 onClick="ctrObj('ctr_3','elm_3')">&nbsp; 通过链接邀请朋友...</h4>
		</div>
		<div class="f">
			<div id="elm_3" class="pad" style="display:none">
				<div class="mar_b8">复制下面的网址发送给朋友，当你的朋友接受邀请并注册后，你们将自动的相互关注。</div>
				<div class="mar_b8">
					<input value="http://JiWai.de/wo/invitations/i/${JWUser::GetIdEncodedFromIdUser($g_current_user_id)}" onclick="this.select();" size="60"/>
				</div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<div class="clear"></div>
