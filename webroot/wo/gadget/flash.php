<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();
$user_id	= JWLogin::GetCurrentUserId();
$user_info	= JWUser::GetUserInfo($user_id);


?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="gadget">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">
	<div id="content" style="margin: 1em 1em">
		<div id="wrapper" style="margin: 1em 1em">


			<h2><?php echo $user_info['nameScreen']?>的窗可贴</h2>

<?php JWTemplate::UserGadgetNav('flash'); ?>


<br />
			<h3>想在Blog上自动显示你、好友的最新更新？把这段代码插入你的Blog模板吧！</h3>




<h4>叽歪de你和你的朋友们</h4>

<div style="width:530px;text-align:center">
	<embed pluginspage=" http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src=" http://asset.jiwai.de/gadget/flash/friends_gadget_maker.swf"
			type="application/x-shockwave-flash" 
			height="600" width="530"
			name="jiwai_badge"/>
	</embed>
</div>

<h4>叽歪de你自己</h4>

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

<?php JWTemplate::footer() ?>

</body>
</html>

