<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../jiwai.inc.php');

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

  function onLJFailure() {
    $('lj_details').innerHTML = 'LiveJournal doesn\'t like that username.  Try again?';
    new Effect.Shake('lj_details');
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

  			<h2>Invite Your Friends (<a href="/wo/">Skip this Step?</a>)</h2>

  			<p>
  				Twitter is more fun with friends! Enter the email addresses of some of your friends and
  				we&rsquo;ll send them an invitation.  If they accept you&rsquo;ll become Twitter friends.
  			</p>


			<form action="/wo/invitations/invite" id="f" method="post" name="f">
				<fieldset>
					<table>
						<tr>
							<th>
								<label for="email_addresses">Email Addresses</label><br />
							</th>			
							<td>

								<textarea cols="40" id="emails" name="email_addresses" onchange="onEmailChange();" rows="3"></textarea>

								<p><small>Separate addresses by commas.</small></p>
							</td>
						</tr>
						<tr id="mutualrow" style="display: none;">
		  					<th><label for="mutualcheck">Are these mutual friends?</label></th>
		  					<td>

		    					<input id="reciprocal" name="reciprocal" type="checkbox" value="1" />
		    					<p><small>If so, we'll connect these friends to each other as they sign up.</small></p>
		  					</td>
						</tr>
						<tr>
		  					<th><label for="livejournal">Invite LiveJournal friends?</label></th>
		  					<td>
		    					<input id="ljusername" name="ljusername" type="text" />

		    					<a href="#" onclick="if ($('ljusername').value != '') { new Ajax.Request('/invitations/livejournal', {asynchronous:true, evalScripts:true, method:'post', onFailure:function(request){onLJFailure()}, onLoading:function(request){onLJLoading()}, parameters:Form.Element.serialize('ljusername')}); }; return false;">Find my friends!</a>
		    					<p><small id="lj_details">Enter your LiveJournal user name.</small></p>
							  <td>
						</tr>
						<tr>
							<th><label for="message">An extra note?</label></th>
							<td>

								<textarea cols="40" id="message" name="message" onkeyup="updatePreview(this.value)" rows="3"></textarea>
								<p><small>If you want, we&rsquo;ll send your friend an extra personal note along with the
									invite. Type it in, and we&rsquo;ll take care of the rest.</small></p></td>
							</tr>
							<tr>
								<th>Preview</th>
								<td>

									<pre id="invite_preview">Hi!

										<span id="invite_message"></span>

zixia2 wants you to join Twitter!

Click here to get started:
http://twitter.com/i/EXTRASPECIALCODEGOESHERE

Or, check out zixia2's Twitter profile:
http://twitter.com/zixia2

Ciao!
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

