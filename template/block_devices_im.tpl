<div id="set_bor">
	<div class="mar_b40 f_14">通过QQ、MSN、GTalk、飞信等聊天软件就可以接收和更新你的叽歪，现在就来绑定吧。</div>
	<div class="mar_b50">
		<form action="/wo/devices/create" method="post">
			<dl class="w1">
				<dt>
					<select id="device_select" name="device[type]">
						<option value="0">--请选择--</option>
					<!--{foreach $supported_devices AS $type}-->
					<!--{if !$user_devices[$type]}-->
						<option value="{$type}">${JWDevice::GetNameFromType($type)}</option>
					<!--{/if}-->
					<!--{/foreach}-->
					</select>
					&nbsp; &nbsp; 
					帐号
				</dt>
				<dd>
					<div><input type="text" name="device[address]" id="device_input" /> &nbsp; <input type="submit" value="绑定" onclick="return ($('device_select').value!=0 && $('device_input').value!='');" /></div>
					<div id="login_info" class="f_gra"></div>
				</dd>
			</dl>
			<div class="clear"></div>
		</form>

		<!--{foreach $user_devices AS $type=>$row}-->
		<!--{if $row['secret']}-->
		<!--${
			$typename = JWDevice::GetNameFromType($type);
			$address = $row['address'];
			$robot = JWDevice::GetRobotFromType($type,$address);
			unset($user_devices[$type]);
		}-->
		<div class="bg_gra mar_b20 pad">
			<div class="f_14 mar_b20">绑定的{$typename}帐号为 <b>{$row['address']}</b>（<a href="/wo/devices/destroy/{$row['id']}">删除</a>），没错吧？那就请按以下步骤操作：</div>
			<ul class="mar_b20">
				<li>1． 请在{$typename}上添加【{$typename}叽歪小弟：{$robot}】为好友</li>
				<li>2． 请复制以下验证信息发送给{$typename}叽歪小弟，完成绑定</li>
			</ul>
			<div class="mar_b8">验证码：<input type="text" readonly value="{$row['secret']}" class="secret" /></div>
		</div>
		<!--{/if}-->
		<!--{/foreach}-->

		<div class="mar_b20">发送消息给已绑定的叽歪小弟即可更新你的叽歪，发送 help 获得帮助信息。</div>
		
	</div>
	
	<!--{if count($user_devices)}-->
	<div class="hahadevice">
		<div class="mar_b8 f_14">已绑定聊天软件...</div>
		<!--{foreach $user_devices AS $type=>$row}-->
		<div class="gray mar_b20">
			<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
			<div class="t hand">
				<div class="lt pad_t3"><a id="ctr_{$type}" href="javascript:ctrObj('ctr_{$type}','elm_{$type}')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
				<h4 onClick="ctrObj('ctr_{$type}','elm_{$type}')">&nbsp; <span class="ico_{$type}"><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="18" height="14" /></span>{$row['address']}</h4>
			</div>
			<div class="f">
				<div id="elm_{$type}" class="pad" style="display:none">
					<div class="mar_b20">
						<div class="rt"><a href="/wo/devices/destroy/{$row['id']}" onclick="return confirm('确定删除已绑定的设备吗？');">删除并重设</a></div>
					<!--{if in_array($type, $sig_devices)}-->
						<div><input type="checkbox" name="sig_{$type}" value="{$row['isSignatureRecord']}" ${$row['isSignatureRecord']=='Y' ? 'checked':''} onclick="this.value=(this.value=='Y'?'N':'Y');JiWai.EnableDevice({$row['id']}, 'isSignatureRecord='+this.value);" />将我的签名更新发布到叽歪&nbsp;<i id="tips_{$row['id']}"></i></div>
					<!--{/if}-->
					</div>
				</div>
			</div>
			<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
		</div>
		<!--{/foreach}-->
	</div>
	<!--{/if}-->
</div>
<div class="clear"></div>
