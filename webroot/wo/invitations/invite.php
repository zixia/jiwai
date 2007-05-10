<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();

if ( isset($_REQUEST['commit']) )
{
	$email_addresses 	= $_REQUEST['email_addresses'];
	$reciprocal			= isset($_REQUEST['reciprocal']);
	$message			= @$_REQUEST['message'];
	
	$email_addresses 	= preg_replace('/[,;，；、]+/',',',$email_addresses);
	$emails				= split(',',$email_addresses);

	$registered_ids		= array();
	$invited_ids		= array();

	foreach ( $emails as $email )
	{
		$email = trim($email);

		if ( !strlen($email) )
			continue;

		$registered_id = JWUser::IsExistEmail($email,true);
		
		if ( $registered_id ){
			array_push($registered_ids, $registered_id);
		} else {
			$invite_id = JWSns::Invite($user_info['id'], $email, 'email', $message);
			array_push($invited_ids, $invite_id);
		}
	}

(var_dump($invited_ids));
die(var_dump($registered_ids));
	if ( $reciprocal )
		JWSns::SetReciprocal( array($invited_ids,$user_info['id']) );

	$notice_html = <<<_HTML_
您的邀请已经发送！
_HTML_;
	JWSession::SetInfo('notice',$notice_html);

	header("Location: /wo/");
	exit(0);
}
?>

<html>

<?php JWTemplate::html_head() ?>

	  <script type="text/javascript">
  function onLJLoading() {
    new Effect.Highlight('lj_details');
    $('newsmth_details').innerHTML = '<img alt="Icon_throbber" src="http://asset.jiwai.de/img/icon_throbber.gif?1176324540" /> &nbsp; 找朋友中……朋友越多，需要的时间会越长。';
  }

  function onEmailChange() {
    text = $('emails').value;

	text = text.replace(/[,;，；、]/,',');

    pieces = text.split(',');  
    
	Element.extend({ visible: function() { return this.style.display != 'none'; }});

    if ( (pieces.length > 1) && (pieces[1].trim() != '') && !($('mutualrow').visible())) {
      $('mutualrow').style.display = "";
      JiWai.Yft('mutualrow');
    } else if (pieces.length <= 1) {
      $('mutualrow').style.display = "none";
    }
  }
  </script>

<body class="invitations" id="invite">
	

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>


<style type="text/css">
#invite_message {
white-space:normal;
}
#invite_preview {
background-color:#EEEEFF;
font-size:1.2em;
padding:10px;
}
</style>
	
<div id="container">
	<div id="content">
		<div id="wrapper">

  			<h2>邀请您的朋友
<?php	
if ( preg_match('#/wo/account/create#i',$_SERVER['HTTP_REFERER']) ){
	echo '（<a href="/wo/">跳过这一步？</a>）';
}
?>
			</h2>

  			<p>
				与朋友一起分享叽歪de乐趣！输入您朋友们的Email地址，我们将向他们发送一份邀请。
				当他们接受邀请后，您们就成为叽歪的好友啦！
  			</p>
<?php
if ( !preg_match('#/wo/account/create#i',$_SERVER['HTTP_REFERER']) ){
	echo '<p><a href="/wo/invitations/">查看以前的邀请历史</a></p>';
}
?>

			<form id="f" method="post" name="f">
				<fieldset>
					<table>
						<tr>
							<th>
								<label for="email_addresses">Email 地址</label><br />
							</th>			
							<td>

								<textarea cols="40" id="emails" name="email_addresses" onchange="onEmailChange();" rows="3"></textarea>

								<p><small>多个 Email 地址间使用逗号（,）分隔</small></p>
							</td>
						</tr>
						<tr id="mutualrow" style="display:none">
		  					<th><label for="mutualcheck">大家互为朋友？</label></th>
		  					<td>

		    					<input id="reciprocal" name="reciprocal" type="checkbox" value="1" /><br />
		    					<p><small>如果您邀请的朋友之间互相也是好友，那么他们在注册之后，会自动互相加为好友。</small></p>
		  					</td>
						</tr>
						<!--tr>
		  					<th><label for="newsmth">邀请水木社区的好友？</label></th>
		  					<td>
		    					<input id="newsmthusername" name="newsmthusername" type="text" />

		    					<a href="#" onclick="if ($('newsmthusername').value != '') { new Ajax.Request('/wo/invitation/newsmth', {asynchronous:true, evalScripts:true, method:'post', onFailure:function(request){onSMTHFailure()}, onLoading:function(request){onSMTHLoading()}, parameters:Form.Element.serialize('newsmthusername')}); }; return false;">找出我的好友！</a>
		    					<p><small id="newsmth_details">输入您的水木社区帐号</small></p>
							  <td>
						</tr-->
						<tr>
							<th><label for="message">打个招呼？</label></th>
							<td>

								<textarea cols="40" id="message" name="message" onkeyup="updatePreview(this.value)" rows="3"></textarea>
								<p><small>
									如果您还有别的话要跟朋友说，请在上面的框中输入，我们将在邀请中一并发送给他们.
								</small></p></td>
							</tr>
							<tr>
								<th>预览</th>
								<td>

								<pre id="invite_preview">您好！

<span id="invite_message"></span>


您的朋友 <?php echo "$user_info[nameFull] ($user_info[nameScreen])"?> 希望您加入叽歪de！


请点击这里接受邀请：

  http://JiWai.de/wo/i/YAOQINGDAIMA


或您可以在这里关注 <?php echo "$user_info[nameFull] ($user_info[nameScreen])"?> 的最新动态：
  http://JiWai.de/<?php echo $user_info['nameScreen']?>/


耶!
叽歪de
									</pre>
								</td>
							</tr>
							<tr>
								<th></th>
								<td><input name="commit" type="submit" value="发送邀请" /></td>
							</tr>
						</table>
					</fieldset>
				</form>
				<script type="text/javascript">
//<![CDATA[
function updatePreview(value) {$('invite_message').innerHTML = value;};
//]]>

				</script>


			</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />


<?php JWTemplate::footer() ?>

</body>
</html>		

