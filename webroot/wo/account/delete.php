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

if ( isset($_REQUEST['commit']) )
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

<body class="account" id="delete">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2> <?php echo $user_info['nameScreen']?> </h2>



			<h2>再……见……？</h2>

<?php 
if ( isset($error_html) )
{
	echo "<div class='notice'><ul>$error_html</ul></div>\n";
} 
?>

			<p><a href="/">再谨慎考虑一下？</a> <strong>删除帐号后，所有相关信息都会被永久删除，并且无法挽回。</strong>是否有话对我们说？<a href="<?php echo JWTemplate::GetConst('UrlContactUs')?>">请告诉我们</a>。</p>

			<form action="/wo/account/delete" method="post" name="f">
				<fieldset>
					<table>
						<tr><th></th><td><input name="commit" type="submit" value="是，请删除我。" /></td></tr>
					</table>
				</fieldset>
			</form>

		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
