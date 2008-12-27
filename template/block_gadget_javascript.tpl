<script type="text/javascript">
function draw() 
{
	var count = $('gadget_count').value;
	var theme = $('gadget_theme').value;
	var selector = radiovalue('selector');
	var encoding = radiovalue('gadget[encoding]');
	var nojiwailink = $('jiwaidelink').checked;
	var div_id = 'JiWai_de__gadget_timeline_user_{$g_current_user_id}';
	var div_id = 'JiWai_de__gadget_timeline_user_'+count+'_'+theme+'_'+encoding+'_'+{$g_current_user_id};
	var content = '<div><div id="'+div_id+'"><script type="text/javascript" charset="utf-8" src="http://api.jiwai.de/gadget/timeline/{$g_current_user_id}.js?selector='+selector+'&count='+count+'&theme='+theme+'&thumb=24&gadget_div='+div_id+'"><\/script><\/div><div style="font:0px/0px sans-serif;clear:both;display:block;"></div>';
	if (!nojiwailink) content += '<div clear="both" style="text-align:center;"><a title="叽歪" alt="叽歪" href="http://jiwai.de/{$g_current_user['nameUrl']}/" target="_blank" style="align:middle;">{$g_current_user['nameScreen']}的叽歪档案<img src="http://asset.jiwai.de/img/favicon.gif" style="align:middle; border:0" /></a></div>';
	content += '</div>';
	$('javascript_url').value = content;
	url = '/wo/gadget/preview?js=' + encodeURIComponent(content);
	opendialog(url,200,500);
}
</script>
<div id="set_bor">
	<dl class="w1">
	<form method="post" id="f">
	<dt>显示：</dt>
	<dd>
		<div>
		<input type="hidden" name="gadget[pictsize]" value="48"/>
	<input type="radio" name="selector" value="user" checked="checked"/>
	自己的 &nbsp; &nbsp;
	<input type="radio" name="selector" value="friends" />
	自己和关注的人 &nbsp; &nbsp;
	<input type="radio" name="selector" value="public" />
		大家的 &nbsp;
			</div>
		</dd>
		<dt>条数：</dt>
		<dd> 
			<select id="gadget_count" name="gadget[count]" size="1" class="select">
				<option value='1'>1</option>
				<option value='2'>2</option>
				<option value='3' selected>3</option>
				<option value='4'>4</option>
				<option value='5'>5</option>
				<option value='6'>6</option>
				<option value='7'>7</option>
				<option value='10'>10</option>
				<option value='15'>15</option>
				<option value='20'>20</option>
				<option value='40'>40</option>
		</select> &nbsp;
		<span>条新叽歪</span>
		</dd>
		<dt>界面：</dt>
		<dd>	<select id="gadget_theme" name="gadget[theme]"> 
				<option value="text">(Beta) text</option>
				<option value="iChat" selected>(Beta) iChat</option>
				<option value="PingPongPicture">(Beta) PingPongPicture</option>
				<option value="PHP">(Beta) PHP</option>
				<option value="Lined-Paper">(Beta) Lined-Paper</option>
				<option value="WindowsXP">(Alpha) WindowsXP</option>	
				<option value="Windows9x">(Alpha) Windows9x</option>
				<option value="Swiss">(Alpha) Swiss</option>	
				<option value="SerenePicture">(Alpha) SerenePicture</option>
				<option value="DOS_Box">(Alpha) DOS_Box</option>
			</select>
		</dd>
		<dt>编码：</dt>
		<dd>
			<div>
				<input type="radio" name="gadget[encoding]" value="utf-8"  checked="checked" />
				<span>UTF-8</span> &nbsp; &nbsp;
				<input type="radio" name="gadget[encoding]" value="gb2312" />
				<span>GB2312</span>
			</div>
		</dd>
		<dt></dt>
		<dd>
			<div>
				<input type="checkbox" id="jiwaidelink" name="gadget[icon]" value="1" />
				<span>不显示叽歪de档案链接</span> &nbsp; &nbsp;
			</div>
		</dd>
		<dt></dt>
		<dd>
			<div class="button">
				<div class="at"></div><div class="bt"></div>
				<div class="tt" onclick="draw()" >预览并更新</div>
				<div class="bt"></div><div class="at"></div>
			</div>
		</dd>
	</form>
	</dl>
	<div class="clear mar_b40"></div>
	<div>
		<div class="mar_b8"><b class="f_14">代码：</b></div>
		<div>复制以下代码<span class="no bg_yel" id="javascript_url_tip">&nbsp;代码复制成功&nbsp;</span></div>
		<div class="mar_b20">
			<textarea id="javascript_url" rows="7" class="textarea" readonly="readonly"  style="width:600px; height:200px; " onclick="JiWai.copyToClipboard(this);" >&lt;div&gt;&lt;div id='JiWai_de__gadget_timeline_user_3_iChat_utf-8_120399'&gt;&lt;script type='text/javascript' charset='utf-8' src='http://api.jiwai.de/gadget/timeline/120399.js?selector=user&amp;count=3&amp;theme=iChat&amp;thumb=48&amp;gadget_div=JiWai_de__gadget_timeline_user_3_iChat_utf-8_120399'&gt;&lt;/script&gt;&lt;/div&gt;&lt;div style='font: 0px/0px sans-serif;clear: both;display: block;'&gt; &lt;/div&gt;&lt;div clear='both' style='text-align:center'&gt;&lt;a title='叽歪' alt='叽歪' href='http://JiWai.de/abcdefghijklmnopqrst/' target='_blank' style='align:middle'&gt;guewool的叽歪档案&lt;img src='http://asset.jiwai.de/img/favicon.gif' border='0' /&gt;&lt;/a&gt;&lt;/div&gt;&lt;/div&gt;</textarea>
		</div>
	</div>
</div>
<div class="clear"></div>
