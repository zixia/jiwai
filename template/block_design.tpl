<div id="set_bor">
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_1" href="javascript:ctrObj('ctr_1','elm_1')" class="min" ><img src="images/img.gif" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_1','elm_1')">&nbsp;  推荐配色</h4>
		</div>
		<div class="f">
			<div id="elm_1" class="pad" >
				<div class="mar_b20">
					<table cellpadding="0" cellspacing="8" width="570" height="180">
						<tr>
							<td style="background:red">&nbsp;</td>
							<td style="background:green">&nbsp;</td>
							<td style="background:yellow">&nbsp;</td>
						</tr>
						<tr>
							<td style="background:black">&nbsp;</td>
							<td style="background:green">&nbsp;</td>
							<td style="background:yellow">&nbsp;</td>
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
			<div class="lt pad_t3"><a id="ctr_2" href="javascript:ctrObj('ctr_2','elm_2')" class="min" ><img src="images/img.gif" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_2','elm_2')">&nbsp; 上传背景图片</h4>
		</div>
		<div class="f">
			<div id="elm_2" class="pad">
				<div>
					<div class="lt50">
						<div><input type="file" name="" size="30" /> </div>
						<div class="f_gra">持.jpg .gif .png格式,最大可以上传 2M 大小的图片</div>
						<div class="pad_t8">
							<input type="radio" id="left" name="img_style" value="left" checked /> <label for="left" >居左</label> &nbsp; &nbsp;
							<input type="radio" id="center" name="img_style" value="center" /> <label for="center" >居中</label> &nbsp; &nbsp;
							<input type="radio" id="repeat" name="img_style" value="repeat" /> <label for="repeat" >重复</label> &nbsp; &nbsp;
						</div>
						<div class="pad_t8"><input type="button" name="" value="预览" /></div>
					</div>
					<div class="rt50">
						<div class="mar_b8"><b>当前背景图片</b>：389478934789347.JPG</div>
						<div style="padding-left:90px"><input type="checkbox" name="" value="" /> &nbsp; 使用该背景图片</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
	</div>
	<div class="gray mar_b20">
		<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
		<div class="t hand">
			<div class="lt pad_t3"><a id="ctr_3" href="javascript:ctrObj('ctr_3','elm_3')" class="min" ><img src="images/img.gif" width="12" height="12" /></a></div>
			<h4 onClick="ctrObj('ctr_3','elm_3')">&nbsp; 修改颜色</h4>
		</div>
		<div class="f">
			<div id="elm_3" class="pad">
				<div class="mar_b20">
					<div class="lt50">
						<div>背景颜色：<input type="text" name="" size="4" /> &nbsp;<span class="f_gra">#987543</span></div>
					</div>
					<div class="rt50">
						侧边栏颜色：
						<select name="" onChange="if(this.value!=0){this.className=this.value;$('set_style').href = 'css/'+this.value + '.css'};" class="c_f00" >
							<option value="0" selected="selected"> -- 请选择 -- </option>
							<option value="c_f00" class="c_f00" >&nbsp;</option>
							<option value="c_0f0" class="c_0f0" >&nbsp;</option>
							<option value="c_00f" class="c_00f" >&nbsp;</option>
						</select>
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
</div>
<div class="clear"></div>
