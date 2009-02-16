<script type="text/javascript">
$("o").onload = window.jiwai_init_hook_eheight;
function draw() 
{
	var url = "http://api.jiwai.de" + ($("v2").checked ? "/g/i/" : "/gadget/image/") + "{$g_current_user_id}/c" + $("c").value + "/w"+ $("w").value + "/m" + $("m").value + "/g.png";
	$('pic_url').value=url; 
	$('ubb_url').value="[url=http://jiwai.de/{$g_current_user['nameUrl']}/][img]" + url + " [/img][/url]";
	$('html_url').value='<a href="http://jiwai.de/{$g_current_user['nameUrl']}/" target="_blank" ><img src=' + url + ' title="叽歪" alt="叽歪" /></a>'; 
	$("o").src = url;
	$("o").onload = window.jiwai_init_hook_eheight;
}
</script>
<div id="set_bor">
	<dl class="w1">
	<form method="post" id="f">
		<dt>版本：</dt>
		<dd>
			<input id="v2" type="radio" name="ver" value="2" checked /> <span>第二版</span> &nbsp; &nbsp; 
	<input type="radio" name="ver" value="1" /> <span>第一版</span>
		</dd>
		<dt>样式：</dt>
		<dd>
			<input type="hidden" id="m" value="2" />
	<input type="radio" name="mode" value="2" onclick="$('c').disabled=false; $('m').value=2;" checked /> <span>侧栏型</span> &nbsp; &nbsp; 
	<input type="radio" name="mode" value="1" onclick="$('only').selected=true;$('c').disabled=true; $('m').value=1" /> <span>签名型</span> &nbsp; &nbsp; 
	<input type="radio" name="mode" value="0" onclick="$('c').disabled=false;$('m').value=0;" /> <span>跑马灯</span>
		</dd>
		<dt>宽度：</dt>
		<dd><div><input name="textfield" id="w" type="text" class="inputStyle1" style="width:50px;" value="300" />&nbsp; <span>像素</span></div></dd>
<dt>条数：</dt>
		<dd>
		<div><select id="c" name="count" size="1" style="width:54px;"> <option value="1" id="only">1</option> <option value="2">2</option> <option value="3">3</option> <option value="4">4</option> <option value="5" selected>5</option> <option value="6">6</option> <option value="7">7</option> <option value="8">8</option> <option value="9">9</option> <option value="10">10</option> </select>&nbsp; <span>条新叽歪</span></div>
		</dd>
		<dt></dt>
		<dd></dd>
		<dt></dt>
		<dd>
			<div class="button">
				<div class="at"></div><div class="bt"></div>
				<div class="tt" onclick="draw()">预览并更新</div>
				<div class="bt"></div><div class="at"></div>
			</div>
		</dd>
	</form>
	</dl>
	<div class="clear mar_b40"></div>
	<div class="mar_b8"><b class="f_14">预览：</b></div>
	<div class="mar_b50"><img id="o" title="叽歪" alt="叽歪" src="http://api.jiwai.de/g/i/{$g_current_user_id}/c5/w300/m2/g.png"/>    </div>
	<div class="mar_b8"><b class="f_14">代码：</b></div>
	<div>
		<ul class="mar_b20">
			<li class="f_14">图片网址<span class="bg_yel no" id="pic_url_tip" >&nbsp; 图片网址复制成功&nbsp;</span></li>
			<li><textarea id="pic_url" rows="1" class="textarea" readonly="readonly"  style="width:500px; height:18px; overflow:hidden" onclick="JiWai.copyToClipboard(this);" >http://api.jiwai.de/g/i/{$g_current_user_id}/c5/w300/m2/g.png</textarea></li>
		</ul>
		<ul class="mar_b20">
			<li class="f_14">UBB代码（论坛专用代码）<span class="bg_yel no" id="ubb_url_tip" >UBB代码复制成功</span></li>
			<li><textarea id="ubb_url" rows="3" class="textarea" readonly="readonly"  style="width:500px; height:35px; overflow:hidden" onclick="JiWai.copyToClipboard(this);" >[url=http://jiwai.de/abcdefghijklmnopqrst/][img]http://api.jiwai.de/g/i/{$g_current_user_id}/c5/w300/m2/g.png[/img][/url]</textarea></li>
		</ul>
		<ul>
			<li>XHTML代码<span class="bg_yel no" id="html_url_tip" >&nbsp; XHTML代码复制成功 &nbsp; </span></li>
			<li><textarea id="html_url" rows="3" class="textarea" readonly="readonly"  style="width:500px; height:35px; overflow:hidden" onclick="JiWai.copyToClipboard(this);"><a title="叽歪" alt="叽歪" href="http://jiwai.de/abcdefghijklmnopqrst/" target="_blank"><img src="http://api.jiwai.de/g/i/{$g_current_user_id}/c5/w300/m2/g.png" /></a></textarea></li>
		</ul>
	</div>
</div>
<div class="clear"></div>
