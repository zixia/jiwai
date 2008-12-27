<div id="set_bor">
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_1" name="ctr_1" href="javascript:ctrObj('ctr_1','elm_1')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_1','elm_1')">&nbsp;  信息不通...</h4>
		</div>
		<div class="f">
			<div id="elm_1" style="display:none" >
			<form action="/wo/feedback/#info" method="post">
				<div>&nbsp;</div>
				<dl class="w3" >
					<dt>发生了什么：</dt>
					<dd class="f_14">
						<input type="radio" id="xx" name="type" value="MO" /> <label for="xx">发送信息未到叽歪？</label>
						<input type="radio" id="tz" name="type" value="MT" /> <label for="tz">打开了通知未收到消息？</label>
					</dd>
					<dt>在什么上面：</dt>
					<dd><select name="device" style="width:160px"><!--{foreach $device_row AS $type=>$_}--><option value="{$type}">${JWDevice::GetNameFromType($type)}</option><!--{/foreach}--></select></dd>
					<dt>大概时间：</dt>
					<dd><div><input type="text" name="date" value="${date('Y-m-d')}"  style="width:90px" /> <input type="text" name="time" value="${date('H:i')}"  style="width:60px" /></div></dd>
					<dt>大概内容：</dt>
					<dd>
						<div>
							<textarea name="message" cols="50" rows="3" style="width:430px"></textarea>
						</div>
					</dd>
					<dt></dt>
					<dd>
						<div><input type="submit" name="commit_info" value="&nbsp; 告诉叽歪 &nbsp;" /> </div>
					</dd>
				</dl>
				</form>
				<div class="clear"></div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>

	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_2" name="ctr_2" href="javascript:ctrObj('ctr_2','elm_2')" class="max" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif');}" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_2','elm_2')">&nbsp; 举报用户...</h4>
		</div>
		<div class="f">
			<div id="elm_2" style="display:none">
			<form action="/wo/feedback/#com" method="post">
				<div>&nbsp;</div>
				<dl class="w3" >
					<dt>要举报的用户：</dt>
					<dd class="f_14">http://jiwai.de/ <input type="text" name="feed_user" value="" style="width:100px" /></dd>
					<dt>原因：</dt>
					<dd>
						<div>
							<textarea name="message" cols="50" rows="3" style="width:430px"></textarea>
						</div>
					</dd>
					<dt></dt>
					<dd>
						<div><input type="submit" name="commit_com" value="&nbsp; 告诉叽歪 &nbsp;" /> </div>
					</dd>
				</dl>
				</form>
				<div class="clear"></div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
</div>
<div class="clear"></div>
