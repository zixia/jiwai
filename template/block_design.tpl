<div id="set_bor">
	<form action="/wo/design/" method="POST" enctype="multipart/form-data">
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_1" href="javascript:ctrObj('ctr_1','elm_1')" class="min" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_1','elm_1')">&nbsp;  推荐配色</h4>
		</div>
		<div class="f">
			<input type="hidden" name="ui[profile_design_choice_whole]" value="{$ui[profile_design_choice][0]}" id="ui_profile_design_choice_whole"/>
			<div id="elm_1" class="pad" >
				<div class="mar_b20">
					<table cellpadding="0" cellspacing="8" width="490" height="180" id="set_style_bor">
					<tr>
						<td title="s_dfdfdf" style="background:#dfdfdf">&nbsp;</td>
						<td title="s_f3e8f7" style="background:#f3e8f7">&nbsp;</td>
						<td title="s_fbf7dc" style="background:#fbf7dc">&nbsp;</td>
					</tr>
					<tr>
						<td title="s_f7fee5" style="background:#f7fee5">&nbsp;</td>
						<td title="s_d8f3e4" style="background:#d8f3e4">&nbsp;</td>
						<td title="index" style="background:#fff4ea">&nbsp;</td>
					</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_3" href="javascript:ctrObj('ctr_3','elm_3')" class="min" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_3','elm_3')">&nbsp; 修改颜色</h4>
		</div>
		<div class="f">
			<div id="elm_3" class="pad">
				<div class="mar_b20">
					<div class="lt50">
						<div>背景颜色：<input type="text" id="ui_profile_background_color" size="4"/><input type="hidden" id="ui_profile_background_color_value" name="ui[profile_background_color]" value="#${$ui['profile_background_color'] ? $ui['profile_background_color'] : '000000'}" /> &nbsp;<span id="ui_profile_background_color_variable" class="f_gra">#${$ui['profile_background_color'] ? $ui['profile_background_color'] : '000000'}</span></div>
					</div>
					<!--
					<div class="rt50">
						侧边栏颜色：
						<select name="" onChange="this.className=this.value;$('set_rightcolor').href = JiWai.AssetUrl('/css/'+this.value + '.css'); $('ui_profile_design_choice_side').value=this.value;">
							<option value="0" selected="selected"> -- 请选择 -- </option>
							<option value="c_f00" class="c_f00" >&nbsp;</option>
							<option value="c_0f0" class="c_0f0" >&nbsp;</option>
							<option value="c_00f" class="c_00f" >&nbsp;</option>
						</select><input type="hidden" name="ui[profile_design_choice_side]" id="ui_profile_design_choice_side" value="{$ui[profile_design_choice_side]}" />
					</div>
					-->
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_2" href="javascript:ctrObj('ctr_2','elm_2')" class="min" ><img src="${JWTemplate::GetAssetUrl('/images/img.gif')}" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_2','elm_2')">&nbsp; 上传背景图片</h4>
		</div>
		<div class="f">
			<div id="elm_2" class="pad">
				<div>
					<div class="lt50">
						<div><input type="file" name="profile_background_image" size="22" /> </div>
						<div class="f_gra">持.jpg .gif .png格式,最大可以上传 2M 大小的图片</div>
						<div class="pad_t8">
							<input type="radio" id="left" name="ui[profile_background_tile]" value="left" ${$ui['profile_background_tile']=='left' ? 'checked':''} /> <label for="left" >居左</label> &nbsp; &nbsp;
							<input type="radio" id="center" name="ui[profile_background_tile]" value="center" ${$ui['profile_background_tile']=='center' ? 'checked':''} /> <label for="center" >居中</label> &nbsp; &nbsp;
							<input type="radio" id="repeat" name="ui[profile_background_tile]" value="repeat" ${$ui['profile_background_tile']=='repeat' ? 'checked':''} /> <label for="repeat" >重复</label> &nbsp; &nbsp;
						</div>
					</div>
					<div class="rt50">
						<div class="mar_b8"><b>当前背景图片</b>：{$picture_name}</div>
						<div style="padding-left:90px"><input type="checkbox" name="ui[profile_use_background_image]" value="{$picture_id}" ${$picture_id ? 'checked':''}/> &nbsp; 使用该背景图片</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div>
		<dl class="w1">
			<dt></dt>
			<dd>
				<div><input type="submit" name="" value="&nbsp; 保存修改 &nbsp;" /> &nbsp; <input type="reset" value="取消" /></div>
			</dd>
		</dl>
	</div>
	</form>
</div>
<div class="clear"></div>
<script>
window.jiwai_init_hook_colorselect = function() {
	colorSelect('ui_profile_background_color', '${$ui['profile_background_color'] ? $ui['profile_background_color'] : '000000'}');
}
window.jiwai_init_hook_designchoice = function() {
	var oTd = $("set_style_bor").getElementsByTagName("td");
	for(var i=0; i<oTd.length; i++){
		oTd[i].style.borderWidth="3px";
		oTd[i].style.borderStyle="solid";
		oTd[i].style.borderColor="#fff"
		oTd[i].onmouseover=function(){this.style.borderWidth="3px";this.style.borderStyle="solid";this.style.borderColor="#F87A01"}
		oTd[i].onmouseout=function(){this.style.borderColor="#fff"}
		oTd[i].onclick=function(){
			$('ui_profile_design_choice_whole').value = this.title;
			$('set_style').href = this.title ? JiWai.AssetUrl('/css/' + this.title + '.css') : '#';
		}
	};
}
</script>
