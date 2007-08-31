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


<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <?php JWTemplate::UserGadgetNav('flash'); ?>
    <div class="tabbody">

<h2>叽歪de你和你的朋友们</h2>
<fieldset>
<div style="width:530px;text-align:center">
	<embed pluginspage=" http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src=" http://asset.jiwai.de/gadget/flash/friends_gadget_maker.swf"
			type="application/x-shockwave-flash" 
			height="600" width="530"
			name="jiwai_badge"/>
	</embed>
</div>
</fieldset>

<h2>叽歪de你自己</h2>
<fieldset>
<div style="width:600px;text-align:center">
	<embed pluginspage="http://www.macromedia.com/go/getflashplayer" 
			quality="high" allowscriptaccess="always" align="middle" flashvars="userid=<?php echo $user_id?>" 
			src="http://asset.jiwai.de/gadget/flash/user_gadget_maker.swf" 
			type="application/x-shockwave-flash" 
			height="250" width="600" wmode="transparent" 
			name="jiwai_badge"/>
	</embed>
</div>
</fieldset>

    </div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->


<?php JWTemplate::footer() ?>

</body>
</html>

