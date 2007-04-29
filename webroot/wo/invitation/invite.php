<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');

$debug = JWDebug::instance();
$debug->init();
?>

<html>

<?php JWTemplate::html_head() ?>

	  <script type="text/javascript">
  function onLJLoading() {
    new Effect.Highlight('lj_details');
    $('lj_details').innerHTML = '<img alt="Icon_throbber" src="http://assets0.twitter.com/images/icon_throbber.gif?1176324540" /> &nbsp; Working on it!  The more friends you have the longer it takes.';
  }

  function onSMTHFailure() {
    $('smth_details').innerHTML = 'NewSMTH doesn\'t like that username.  Try again?';
    new Effect.Shake('smth_details');
  }

  function onEmailChange() {
    text = $('emails').value;
    pieces = text.split(",");  
    
    if ( (pieces.length > 1) && (pieces[1].strip() != '') && !($('mutualrow').visible())) {
      new Effect.Appear('mutualrow');
    } else if (pieces.length <= 1) {
      $('mutualrow').style.display = "none";
    }
  }
  </script>

<body class="invitations" id="invite">
	

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container">
	<div id="content">
		<div id="wrapper">

  			<h2>邀请您的朋友<a href="/wo/">（跳过这一步？</a>）</h2>

  			<p>
				与朋友一起分享叽歪de乐趣！输入您朋友们的Email地址，我们将向他们发送一份邀请。
				当他们接受邀请后，你们就成为叽歪的好友啦！
  			</p>


			<form action="/wo/invitations/invite" id="f" method="post" name="f">
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
						<tr id="mutualrow" style="display: none;">
		  					<th><label for="mutualcheck">他们互相之间是好友吗？</label></th>
		  					<td>

		    					<input id="reciprocal" name="reciprocal" type="checkbox" value="1" />
		    					<p><small>如果您邀请的朋友之间互相也是好友，那么他们在注册之后，会自动互相加为好友。</small></p>
		  					</td>
						</tr>
						<tr>
		  					<th><label for="newsmth">邀请水木社区的好友？</label></th>
		  					<td>
		    					<input id="newsmthusername" name="newsmthusername" type="text" />

		    					<a href="#" onclick="if ($('newsmthusername').value != '') { new Ajax.Request('/wo/invitation/newsmth', {asynchronous:true, evalScripts:true, method:'post', onFailure:function(request){onSMTHFailure()}, onLoading:function(request){onSMTHLoading()}, parameters:Form.Element.serialize('newsmthusername')}); }; return false;">找出我的好友！</a>
		    					<p><small id="newsmth_details">输入您的水木社区帐号</small></p>
							  <td>
						</tr>
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

									<pre id="invite_preview">你好！

										<span id="invite_message"></span>

您的朋友 zixia2 希望您加入JiWai！

请点击这里接受邀请：

http://JiWai.de/wo/invitation/invitee/EXTRASPECIALCODEGOESHERE


查看zixia2的Jiwai档案:

http://JiWai.de/zixia2/

耶!
									</pre>
								</td>
							</tr>
							<tr>
								<th></th>
								<td><input name="commit" type="submit" value="Invite" /></td>
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

<?php JWTemplate::sidebar() ?>
			
		
</div><!-- #container -->
<hr class="separator" />


<?php JWTemplate::footer() ?>

</body>
</html>		

