<!--${
	$robot = ($sms_device) ? JWDevice::GetRobotFromType('sms', $sms_device['address']) : null;
}-->
<div id="set_bor">
	<!--{if !$sms_device}-->
	<div class="mar_b20">绑定手机号码后你可以通过手机短信发送悄悄话、接收好友叽歪,随时随地记录生活、和好友交流！</div>
	<div class="mar_b40 f_red">发短信或彩信给叽歪，与发短信或彩信给普通手机费用完全一样</div>
	<form action="/wo/devices/create" method="post">
	<dl class="w1">
		<dt>输入手机号码</dt>
		<dd>
			<div><input type="hidden" name="device[type]" value="sms" /><input type="text" name="device[address]" /> &nbsp; <input type="submit" value="绑定" /></div>
		</dd>				
	</dl>
	</form>
	<!--{elseif $sms_device['secret']}-->
	<div class="bg_gra mar_b20 pad">
		<div class="f_14 mar_b20">你绑定的手机号为 <b>{$sms_device['address']}</b>（<a href="/wo/devices/destroy/{$sms_device['id']}">删除</a>），没错吧？那就请按以下步骤操作：</div>
		<ul class="mar_b20">
			<li>1． 请复制以下验证信息发送短信给 <b>{$robot}</b> 完成绑定</li>
		</ul>
		<div class="mar_b8">验证码：<input type="text" readonly value="{$sms_device['secret']}" class="secret" /></div>
	</div>
	<!--{else}-->
	<div class="mar_b20 f_14">你绑定的手机号码为 <b>{$sms_device['address']}</b> （<a href="/wo/devices/destroy/{$sms_device['id']}">删除并重设</a>）</div>
	<div class="mar_b50">
		<ul class="dot_b mar_b20">
			<li>发送短信到 <b>{$robot}</b> 进行更新</li>
			<li>发送彩信到 <b>m@jiwai.com</b> 进行更新</li>
			<li>发送 help 获得帮助信息。</li>
		</ul>
		<div class="f_gra">发短信或彩信给叽歪，与发短信或彩信给普通手机费用完全一样</div>
	</div>
	<dl class="w1">
		<dt><input type="checkbox" id="direct" value="Y" ${$sms_device['enableFor']=='everything' ? 'checked':''} onclick="var p=(this.checked ? 'direct_messages' : 'everything'); JiWai.EnableDevice('{$sms_device['id']}', 'device[enabled_for]='+p);" /></dt>
		<dd>
			<div> 只用手机接受悄悄话<i id="tips_{$sms_device['id']}"></i></div>
			<div class="f_gra">发送短信 ON 或 OFF 到 <b>1066822888</b> 开启或关闭短信接收。</div>
		</dd>
	</dl>
	<div class="clear"></div>
	<!--{/if}-->
	<!--{if infotip}-->{$infotip}<!--{/if}-->
</div>
<div class="clear"></div>
