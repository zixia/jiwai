<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];


$debug = JWDebug::instance();
$debug->init();
?>

<html>

<?php JWTemplate::html_head() ?>

<body class="invitations" id="invitations">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


			<h2>Your Previous Invitations</h2>


  			<h3>2 accepted invitations:</h3>

  			<table class="doing" id="accepted" cellspacing="0">
    
  	  			<tr class="odd">
					<td class="thumb">
						<a href="http://jiwai.de/xinyu19"><img alt="_____" src="http://jiwai.de/daodao/picture" /></a>
					</td>
					<td>
						<strong><a href="http://jiwai.de/xinyu19">maxinyu (xinyu19)</a></strong>
						<p class="friend-actions">
							<small>
		  						<a href="/friends/leave/774107" title="Turn OFF notifications for maxinyu">leave xinyu19</a>
				  				<a href="/direct_messages/create/774107" title="Send a direct message to maxinyu">d xinyu19</a> |
  								<a href="/friends/nudge/774107" title="Remind maxinyu to update!">nudge xinyu19</a>
								| <a href="/friendships/destroy/774107" onclick="return confirm('Sure you want to remove maxinyu from your list?')">remove xinyu19</a>
							</small>
						</p>
					</td>
				</tr>

  	
  	  			<tr class="even">
					<td class="thumb">
						<a href="http://jiwai.de/DAODAO19"><img alt="Default_profile_image_normal" src="http://jiwai.de/zixia/picture?1178587735" /></a>
					</td>
					<td>
						<strong><a href="http://jiwai.de/DAODAO19">DAODAO19</a></strong>

						<p class="friend-actions">
							<small>
		  						<a href="/friends/leave/5533532" title="Turn OFF notifications for DAODAO19">leave DAODAO19</a>
				  				<a href="/direct_messages/create/5533532" title="Send a direct message to DAODAO19">d DAODAO19</a> |
  								<a href="/friends/nudge/5533532" title="Remind DAODAO19 to update!">nudge DAODAO19</a>
								| <a href="/friendships/destroy/5533532" onclick="return confirm('Sure you want to remove DAODAO19 from your list?')">remove DAODAO19</a>

							</small>
						</p>
					</td>
				</tr>
  			</table>

	
  			<h3>2 pending invitations:</h3>

  			<table class="doing" id="pending" cellspacing="3">
   	 			<tr>
   	   				<th>Contact</th>
   	   				<th>Message</th>
   	   				<th>Sent</th>
    			</tr>
    
    			<tr class="odd" style="font-size: small;">
	
   		   			<td width="20%"><a href="mailto:halen@zixia.net">halen@zixia.net</a></td>
   		   			<td width="50%">Interesting... :) </td>
   	   				<td width="30%">2 months ago</td>
    			</tr>
    
    			<tr class="even" style="font-size: small;">
      				<td width="20%"><a href="mailto:zixia@aka.cn">zixia@aka.cn</a></td>
      				<td width="50%">ÄãºÃ</td>
					<td width="30%">less than a minute ago</td>
    			</tr>
    
  			</table>


		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

