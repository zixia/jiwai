<div id="set_bor">
	<div class="mar_b40 f_14">通过Facebook，Twitter等网站接收和更新你的叽歪，现在就来绑定吧。</div>
	<div class="mar_b50">
		<form action="/wo/devices/create">
			<dl class="w1">
				<dt></dt>
				<dd>
					<div>
						<select name="fb" style="width:150px" onChange="showSetBor(this.value); <!--{if !$facebook}-->this.form.submit();<!--{/if}-->">
							<option value="" selected>--请选择--</option>
						<!--{if !$facebook || $facebook['secret']}-->
							<option value="fb_block">Facebook</option>
						<!--{/if}-->
						<!--{if !$bindother['twitter']}-->
							<option value="tw_block">Twitter</option>
						<!--{/if}-->
						<!--{if !$bindother['fanfou']&&false}-->
							<option value="ff_block">Fanfou</option>
						<!--{/if}-->
						</select>
<input type="hidden" name="device[type]" value="facebook" /><input type="hidden" name="device[address]" /><input type="hidden" name="u" value="/wo/bindother/index/fb" />
					</div>
				</dd>
			</dl>
			<div class="clear"></div>
		</form>

		<!--{if $facebook['secret']}-->
		<div id="fb_block" class="bg_gra pad mar_b20" style="display:${$oblockid=='fb' ? 'block' : 'none'};">
			<div class="f_14 mar_b20">
				<div class="rt"><span class="ico_face"><img src="${JWTemplate::GetAssetUrl('/image/img.gif')}" width="80" height="20" /></span></div>
				<div>你想绑定Facebook，没错吧？那就请按以下步骤操作：</div>
			</div>
			<ul class="mar_b20">
				<li class="mar_b8">1. 请访问 叽歪de <a href="http://apps.facebook.com/jiwaide/?verify">Facebook Application</a> 并安装</li>
				<li class="mar_b20">2. 输入你的叽歪网用户名和以下验证码</li>
				<li class="mar_b20">验证码：<input type="text" readonly value="{$facebook['secret']}" class="secret" /></li>
				<li>3. 点击“关联”确定</li>
			</ul>
		</div>
		<!--{/if}-->

		<!--{if !$bindother['twitter']}-->
		<div id="tw_block" class="bg_gra pad mar_b20" style="display:${$oblockid=='tw' ? 'block' : 'none'}">
			<div class="f_14 mar_b20 pad_t8">
				<div class="rt"><span class="ico_twib"><img src="images/img.gif" width="80" height="20" /></span></div>
				<div>绑定后，你不能从Twitter更新你的叽歪，但是叽歪将自动发送你的更新到Twitter。</div>
			</div>
			<form action="/wo/bindother/create" method="post">
			<input type="hidden" name="service" value="twitter"/>
			<div class="mar_b20">
				请输入你的Twitter帐户和密码：
			</div>
			<dl class="w1">
				<dt>帐户</dt>
				<dd>
					<div><input type="text" name="login_name" /></div>
					<div class="f_gra">请输入Twitter帐户名</div>
				</dd>
				<dt>密码</dt>
				<dd>
					<div><input type="password" name="login_pass" /></div>
					<div><input type="checkbox" name="sync_reply" value="Y" /> 发送你回复的叽歪到Twitter</div>
					<div><input type="checkbox" name="sync_conference" value="Y" /> 发送你的会议叽歪到Twitter</div>
				</dd>
				<dt></dt>
				<dd></dd>
				<dt></dt>
				<dd><div><input type="submit" value=" 完成 " /></div></dd>
			</dl>
			</form>
			<div class="clear"></div>
		</div>
		<!--{/if}-->

		<!--{if !$bindother['fanfou']}-->
		<div id="ff_block" class="bg_gra pad mar_b20" style="display:${$oblockid=='ff' ? 'block' : 'none'};">
			<div class="f_14 mar_b20 pad_t8">
				<div class="rt"><span class="ico_twib"><img src="images/img.gif" width="80" height="20" /></span></div>
				<div>绑定后，你不能从Fanfou更新你的叽歪，但是叽歪将自动发送你的更新到Fanfou。</div>
			</div>
			<form action="/wo/bindother/create" method="post">
			<input type="hidden" name="service" value="fanfou" />
			<div class="mar_b20">
				请输入你的Fanfou帐户和密码：
			</div>
			<dl class="w1">
				<dt>帐户</dt>
				<dd>
					<div><input type="text" name="login_name" /></div>
					<div class="f_gra">请输入完整的邮箱地址，如abc@example.com</div>
				</dd>
				<dt>密码</dt>
				<dd>
					<div><input type="password" name="login_pass" /></div>
					<div><input type="checkbox" name="sync_reply" value="Y" /> 发送你回复的叽歪到Fanfou</div>
					<div><input type="checkbox" name="sync_conference" value="Y" /> 发送你的会议叽歪到Fanfou</div>
				</dd>
				<dt></dt>
				<dd></dd>
				<dt></dt>
				<dd><div><input type="submit" value=" 完成 " /></div></dd>
			</dl>
			</form>
			<div class="clear"></div>
		</div>
		<!--{/if}-->
		<div class="mar_b20">发送消息给已绑定的叽歪小弟即可更新你的叽歪，发送 help 获得帮助信息。</div>
	</div>
	
	<div class="binded_block">
		<div class="mar_b8 f_14">已绑定网站...</div>
		<!--{if $facebook && !$facebook['secret']}-->
		<div class="gray mar_b20">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<div class="t hand">
				<div class="lt pad_t3"><a id="ctr_1" href="javascript:ctrObj('ctr_1','elm_1')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
				<div class="rt"><span class="ico_face"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="80" height="20" /></span></div>
				<h4 onClick="ctrObj('ctr_1','elm_1')">&nbsp; Facebook</h4>
			</div>
			<div class="f">
				<div id="elm_1" class="pad" style="display:none">
					<div class="mar_b20">
						<div class="rt"><a href="/wo/devices/destroy/{$facebook['id']}">删除并重设</a></div>
						<div class="mar_b8">在 <a href="http://apps.facebook.com/jiwaide/">叽歪de Facebook Application</a> 上即可更新你的叽歪</div>
					</div>
				</div>
			</div>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
		<!--{/if}-->

		<!--${$ictr=1;}-->
		<!--{foreach $bindother AS $s=>$bind}-->
		<!--${$us = ucwords($s);}-->
		<!--${$ts = $s=='twitter' ? 'twib' : 'fanb';}-->
		<!--${$ictr++;}-->
		<div class="gray mar_b20">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<form action="/wo/bindother/create" method="post">
			<input type="hidden" name="bid" value="{$bind['id']}"/>
			<div class="t hand">
				<div class="lt pad_t3"><a id="ctr_{$ictr}" href="javascript:ctrObj('ctr_{$ictr}','elm_{$ictr}')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
				<div class="rt"><span class="ico_{$ts}"><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="80" height="20" /></span></div>
				<h4 onClick="ctrObj('ctr_{$ictr}','elm_{$ictr}')">&nbsp;{$us}</h4>
			</div>
			<div class="f">
				<div id="elm_{$ictr}" class="pad" style="display:none">
					<div>
						<div class="mar_b20">
							<div class="rt"><a href="/wo/bindother/destroy/{$bind['id']}">删除并重设</a></div>
							<div class="mar_b8">你绑定的 {$us} 帐号为 {$bind['loginName']}</div>
							<div class="f_gra">你不能从{$us}更新你的叽歪，但是叽歪将自动发送你的更新到{$us}。</div>
						</div>
						<div class="indent">
							<div><input type="checkbox" name="sync_reply" value="Y" ${$bind['syncReply']=='Y' ? 'checked':''}/> 发送你回复的叽歪到{$us}</div>
							<div><input type="checkbox" name="sync_conference" value="Y" ${$bind['syncConference']=='Y' ? 'checked':''}/> 发送你的会议叽歪到{$us}</span></div>
						</div>
						<div class="indent mar_b8">
							<input type="submit" value=" 完成 " />
						</div>
					</div>
				</div>
			</div>
			</form>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
		<!--{/foreach}-->
	</div>
	
</div>
<div class="clear"></div>
