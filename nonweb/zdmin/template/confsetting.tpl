<!--{include header}-->
<h2>修改会议用户信息</h2>

<!--{if ($confSetting)}-->

	<hr>
	<form action="/confsetting.php" method="GET">
	用户: <input type="text" name="un" id="un" value="{$un}"/>
	<input type="submit" value="提交查询" onClick="return (un.value!='');"/>
	</form>
	<br />

	<form method="post" id="f" action="/confsetting.php?un={$un}">
		<table width="760" cellspacing="3">
			<tr>
				<th valign="top" width="200">
					<b>会议模式</b>
				</th>
				<td>
					<br/>
					<input {$confSetting[enable_conference]} id="enable_conference" name="enableConference" type="checkbox" value="Y" style="width:24px; display:inline;" /><label for="enable_conference">启动会议模式</label><br/>
					会议号码<input id="conf_number" name="conf[number]" type="text" value="{$confSetting['number']}"/>
					<p>
						使用方法：<br/>
						1、手机发送短信给 106693184<font color="red">${strlen($confSetting[number]) ? '10'.$confSetting['number'] : '11'.$uid}</font><br/>
						2、聊天工具、网页发消息时增加头 "${$un}"
					</p>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>高级设置</b>
				</th>
				<td>
					<br/>
					<input {$confSetting[sms]} id="conf_device_sms" name="conf[deviceAllow][]" type="checkbox" value="sms" style="width:24px; display:inline;" />
					<label for="conf_device_sms">允许手机短信发送</label>
					<br/>

					<input {$confSetting[im]} id="conf_device_im" name="conf[deviceAllow][]" type="checkbox" value="im" style="width:24px; display:inline;" />
					<label for="conf_device_im">允许聊天软件(IM)发送</label>
					<br/>

					<input {$confSetting[web]} id="conf_device_web" name="conf[deviceAllow][]" type="checkbox" value="web" style="width:24px; display:inline;" />
					<label for="conf_device_web">允许Web发送</label>
					<br/>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>过滤设置</b>
				</th>
				<td>
					<br/>
					<input {$confSetting[friendOnly]} id="conf_friend_only" name="conf[friendOnly]" type="checkbox" value="Y" style="width:24px; display:inline;" />
					<label for="conf_friend_only">只允许你关注的人回复给我</label>
					<br/>
				</td>
			</tr>
			<tr>
				<th valign="top" width="200">
					<b>保存设置</b>
				</th>
				<td style="padding-top:20px;">
					<input type="submit" value="保存" /> 
					<br/>
				</td>
			</tr>
		</table>

	</form>

<!--{/if}-->

<!--{include footer}-->
