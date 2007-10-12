<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);

JWLogin::MustLogined();
$user	= JWUser::GetCurrentUserInfo();
$idUser	= $user['id'];

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
		   <input type="hidden" id="m" value="1"/>
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
function draw() {
	var url = "http://api.jiwai.de/gadget/image/count" + $("c").value 
		+ "/width"+ $("w").value 
		+ "/mode" + $("m").value + "/id<?php echo $idUser;?>/gadget.png";
	$('url').value=url; 
	$("o").src = url;
}
</script>

			</form>
                <p>
                    <button onclick="draw();">生成代码并预览</button>
                </p>
                <br/>
		</fieldset>
		<h2>代码</h2>
		<fieldset>
			<p>
                图片地址: <input id="url" size="70"/>
			</p>
		</fieldset>
		<h2>预览</h2>
		<fieldset>
                <br/>
            <p><img id="o" /></p>
		</fieldset>
		<h3>不明白怎么用？看看 <a href="<?php echo JWTemplate::GetConst('UrlHelpGadget')?>">窗可贴指南</a>。
		采用开源中文字体<a href="http://wenq.org/">文泉驿</a>绘制。</h3>
	</div>
    <div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>

