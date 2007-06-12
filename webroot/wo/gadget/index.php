<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/


JWLogin::MustLogined();


$user_info		= JWUser::GetCurrentUserInfo();

//var_dump($user_info);
?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="gadget">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2><?php echo $user_info['nameScreen']?>的窗可贴</h2>

<?php JWTemplate::UserGadgetNav('index'); ?>

<br />
<br />

<h3>我们目前支持 <a href="javascript">Javascript</a> 和 <a href="flash">Flash</a> 的窗可贴，很快会支持 Gif 格式。</h3>
			
		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
