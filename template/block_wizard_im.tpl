<div class="indent">
	聊天软件：
	<select onChange="$('robotvalue').value=this.value;">
		<option value="">--请选择--</option>
		<option value="${JWDevice::GetRobotFromType('msn');}">MSN</option>
		<option value="${JWDevice::GetRobotFromType('qq');}">QQ</option>
		<option value="${JWDevice::GetRobotFromType('gtalk');}">GTalk</option>
		<option value="${JWDevice::GetRobotFromType('skype');}">Skype</option>
		<option value="${JWDevice::GetRobotFromType('fetion');}">Fetion</option>
		<option value="${JWDevice::GetRobotFromType('jabber');}">Jabber</option>
	</select> &nbsp; 
	请添加：<input type="text" id="robotvalue" readonly onFocus="this.select()" />
</div>
