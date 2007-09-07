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

if ( $_POST && isset($_REQUEST['commit']) )
{
	/*
	 * Update User Databse
	 */

	JWLogin::Logout();

	if ( JWUser::Destroy($user_info['id']) )
		header ( "Location: /public_timeline/" );

	$contact_url = JWTemplate::GetConst('UrlContactUs');

	$error_html = <<<_HTML_
	<li>哎呀！删除用户失败了！请<a href="$contact_url">联系我们</a></li>
_HTML_;

}


?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>

<body class="account" id="create">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">

			<h2> <?php echo htmlSpecialChars($user_info['nameFull']); ?> </h2>

			<h2>再……见……？</h2>

<?php 
if ( isset($error_html) )
{
	echo "<div class='notice'><ul>$error_html</ul></div>\n";
} 
?>

			<p style="margin:20px;"><a href="/">再谨慎考虑一下？</a> <strong>删除帐号后，所有相关信息都会被永久删除，并且无法挽回。</strong>是否有话对我们说？<a href="<?php echo JWTemplate::GetConst('UrlContactUs')?>">请告诉我们</a>。</p>

            <form style="display:none;" action="/wo/account/delete" id="f" method="POST"><input type="hidden" name="commit" value="true"/></form>
            <p style="margin:20px;"><input onClick="if (confirm('自杀后无法挽救，确认要自杀吗？')) { $('f').submit(); }" type="button" value="是，请删除我。"/></p>

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
