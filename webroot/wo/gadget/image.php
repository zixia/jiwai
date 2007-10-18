<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);

JWLogin::MustLogined();
$user	= JWUser::GetCurrentUserInfo();
$idUser	= $user['id'];
$nameScreen	= $user['nameScreen'];

?>
<html>
<head>
<?php JWTemplate::html_head() ?>
</head>
<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
	<?php JWTemplate::UserGadgetNav('image'); ?>
	<div class="tabbody">
	<h2>配置</h2>
		<fieldset>
			<form method="post" id="f">
                <p>
		   <input type="hidden" id="m" value="2"/>
                    <input style="display:inline;width:20px;" type="radio" name="mode" value="1" onclick="$('only').selected=true;$('c').disabled=true; $('m').value=1" /> 横幅式
                    <input style="display:inline;width:20px;" type="radio" name="mode" value="2" onclick="$('c').disabled=false; $('m').value=2;" checked /> 侧栏式
                    &nbsp;
                    &nbsp;
			宽度 <input size="3" name="width" id="w" value="200" /> 像素
                </p>	
                <p>显示 
                    最近
                    <select id="c" name="count">
                        <option value="1" id="only">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5" selected>5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                    </select>
                    条更新
                </p>
<script type="text/javascript">
function draw() 
{
	//alert( $('pic_url') );
	var url = "http://api.jiwai.de/gadget/image/<?php echo $idUser;?>/c" + $("c").value 
		+ "/w"+ $("w").value 
		+ "/m" + $("m").value + "/gadget.png";
	$('pic_url').value=url; 
	$('ubb_url').value="[url=http://jiwai.de/<?php echo $nameScreen;?>][img]" + url + " [/img][/url]";
	$('html_url').value='<a href="http://jiwai.de/<?php echo $nameScreen;?>" target="_blank" ><img src=' + url + ' title="叽歪" alt="叽歪" /></a>'; 
	$("o").src = url;
}
</script>

			</form>
                <br/>
                <p>
                    <input type="button" class="submitbutton" style="margin-left:0px!important;margin-left:50px;width:120px" onclick="draw();" value="生成代码并预览" />
                </p>
                <br/>
		</fieldset>
		<h2>代码</h2>
		<fieldset>
		<div style="margin-left:20px">
		    图片网址:
			<span class=copytips id=pic_url_tip>
            　　图片网址复制成功。
			</span>			
			<br/>
                <textarea id="pic_url" rows="1" cols="100" class="urltext" readonly="readonly" onclick="copyToClipboard(this);" >http://api.jiwai.de/gadget/image/<?php echo $idUser;?>/c5/w200/m2/gadget.png</textarea>
			<br/><br/>
		    UBB代码:
			<span class=copytips id=ubb_url_tip>
            　　UBB代码复制成功。
			</span>
			<br/>
                <textarea id="ubb_url" rows="2" cols="100" class="urltext" readonly="readonly" onclick="copyToClipboard(this)" >[url=http://jiwai.de/<?php echo $nameScreen;?>][img]http://api.jiwai.de/gadget/image/<?php echo $idUser;?>/c5/w200/m2/gadget.png[/img][/url] </textarea>
			<br/><br/>
		    Html代码:
			<span class=copytips id=html_url_tip>
            　　Html代码复制成功。
			</span>
			<br/>
                <textarea id="html_url" rows="3" cols="100" class="urltext" readonly="readonly" onclick="copyToClipboard(this)" ><a title="叽歪" alt="叽歪" href="http://jiwai.de/<?php echo $nameScreen;?>" target="_blank"><img src="http://api.jiwai.de/gadget/image/<?php echo $idUser;?>/c5/w200/m2/gadget.png" /></a> </textarea>
			<br/>
		</div>	
		</fieldset>
		<h2>预览</h2>
		<fieldset>
                <br/>
            <p><img id="o" title="叽歪" alt="叽歪" src="http://api.jiwai.de/gadget/image/<?php echo $idUser;?>/c5/w200/m2/gadget.png"/></p>
		</fieldset>
		<h3>不明白怎么用？看看 <a href="<?php echo JWTemplate::GetConst('UrlHelpGadget')?>">窗可贴指南</a>。
		采用开源中文字体<a href="http://wenq.org/">文泉驿</a>绘制。</h3>
	</div>
    <div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>

