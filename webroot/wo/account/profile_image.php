<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();


$logined_user_info	= JWUser::GetCurrentUserInfo();


$pathParam 	= @$_REQUEST['pathParam'];
if ( preg_match('/^\/(\d+)$/',$pathParam,$matches) )
{
	$page_user_info = JWUser::GetUserInfo($matches[1]);
}
else if ( preg_match('/^\/([^\?\/]+)$/',$pathParam,$matches) )
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

<head>
<?php 
$options = array ( 'ui_user_id'=>$page_user_info['idUser']);
JWTemplate::html_head($options);
?>
</head>

<body class="account" id="create">

<?php JWTemplate::header() ?>

<div id="container" class="subpage">

			<h2><a href="/<?php echo $page_user_info['nameScreen']?>/"><?php echo $page_user_info['nameFull']?></a></h2>

			<p style="margin:10px 20px;"><img style="border: 1px dashed #FF8404;padding:2px;" alt="<?php echo $page_user_info['nameFull']?>" src="<?php echo JWPicture::GetUserIconUrl($page_user_info['id'],'picture')?>" /></p>

<?php 
if ( isset($logined_user_info) 
		&& $logined_user_info['id']===$page_user_info['id'] )
{
	echo <<<_HTML_

			<p style="margin:10px 20px;"><a href="/wo/account/profile">更换图片？</a></p>

_HTML_;
}
?>

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>

