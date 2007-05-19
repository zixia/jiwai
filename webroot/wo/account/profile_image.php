<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');
JWDebug::init();


$logined_user_info	= JWUser::GetCurrentUserInfo();


$pathParam 	= @$_REQUEST['pathParam'];
if ( preg_match('/^\/(\d+)$/',$pathParam,$matches) )
{
	$page_user_info = JWUser::GetUserInfo($matches[1]);
}
else if ( preg_match('/^\/(\w.+)$/',$pathParam,$matches) )
{
	$page_user_info = JWUser::GetUserInfo($matches[1]);
}
else if ( empty($logined_user_info) )
{
	JWTemplate::RedirectBackToLastUrl( JWTemplate::GetConst('UrlRegister') );
	exit(0);
}
else
{
	$page_user_info = JWUser::GetCurrentUserInfo();
}

if ( empty($page_user_info) )
{
	JWTemplate::RedirectBackToLastUrl( JWTemplate::GetConst('UrlRegister') );
	exit(0);
}

?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="profile_image">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper" class="wrapper">


			<h2><a href="/<?php echo $page_user_info['nameScreen']?>/"><?php echo $page_user_info['nameFull']?></a></h2>

			<p><img alt="<?php echo $page_user_info['nameFull']?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'],'picture')?>" /></p>

<?php 
if ( isset($logined_user_info) 
		&& $logined_user_info['id']===$page_user_info['id'] )
{
	echo <<<_HTML_

			<p><small><a href="/wo/account/picture">更换图片？</a></small></p>

_HTML_;
}
?>

		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

