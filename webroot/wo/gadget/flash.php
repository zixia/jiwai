<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();
$user_id	= JWLogin::GetCurrentUserId();
$user_info	= JWUser::GetUserInfo($user_id);


?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="gadget">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content" style="margin: 1em 1em">
		<div id="wrapper" style="margin: 1em 1em">


			<h2><?php echo $user_info['nameFull']?>的窗可贴</h2>
			<h3>想在Blog上自动显示你、好友的最新更新？把这段代码插入你的Blog模板吧！</h3>


<div style="width:600px;text-align:center">
	<embed pluginspage="http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src="http://asset.jiwai.de/gadget/flash/user_gadget_maker.swf" 
			type="application/x-shockwave-flash" 
			height="250" width="600" wmode="transparent" 
			name="jiwai_badge"/>
	</embed>
</div>
	

 		</div><!-- wrapper -->
	</div><!-- content -->
	

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

