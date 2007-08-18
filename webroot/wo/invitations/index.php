<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];

?>

<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="invitations" id="invitations">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>

<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">


<?php
JWTemplate::ShowActionResultTips();


$invitation_ids		= JWInvitation::GetInvitationIdsFromUser($logined_user_info['id'],100);
$invitation_rows	= JWInvitation::GetInvitationDbRowsByIds($invitation_ids);

$not_registered_invitation_ids	= array();
$registered_invitation_ids		= array();
$invitee_user_ids				= array();

foreach ( $invitation_ids as $invitation_id )
{
	$row	= $invitation_rows[$invitation_id];

	if ( isset($row['idInvitee']) )
	{
		// 用户已经应邀注册
		array_push($registered_invitation_ids	,$invitation_id);
		array_push($invitee_user_ids			,$row['idInvitee']);
	}
	else
	{
		// 用户尚未注册
		array_push($not_registered_invitation_ids	,$invitation_id);
	}
}

if ( !empty($invitee_user_ids) )
	$invitee_user_rows	= JWUser::GetUserDbRowsByIds($invitee_user_ids);


?>
			<h2>你以前的邀请记录</h2>


  			<h3>被接受的邀请数： <?php echo count($registered_invitation_ids)?> 个</h3>

<?php JWTemplate::ListUser($logined_user_id, $invitee_user_ids, array('element_id'=>'accepted') ); ?>

	
<?php 
if ( !empty($not_registered_invitation_ids) ) 
{
?>
  			<h3>还在等待回应的邀请数： <?php echo count($not_registered_invitation_ids)?> 个</h3>

  			<table class="doing" id="pending" cellspacing="3">
   	 			<tr>
   	   				<th>联系地址</th>
   	   				<th>招呼</th>
   	   				<th>邀请时间</th>
    			</tr>
    
<?php
	foreach ( $not_registered_invitation_ids as $pending_invitaion_id )
	{
		$pending_invitation_row = $invitation_rows[$pending_invitaion_id];
		echo <<<_HTML_
    			<tr class="odd" style="font-size: small;">
_HTML_;
	
		if ( 'email'==$pending_invitation_row['type'] )
		{
			echo <<<_HTML_
   		   			<td width="20%"><a href="mailto:$pending_invitation_row[address]">$pending_invitation_row[address]</a></td>
_HTML_;
		}
		else
		{
			echo <<<_HTML_
   		   			<td width="20%">$pending_invitation_row[type]://$pending_invitation_row[address]</a></td>
_HTML_;
		}
		echo <<<_HTML_
   		   			<td width="50%">$pending_invitation_row[message]</td>
   	   				<td width="30%">$pending_invitation_row[timeCreate]</td>
    			</tr>
_HTML_;
	}	// end foreach
?>
    			</tr>
    
  			</table>
<?php
}	//end not_registered_invitation_ids
?>


		</div><!-- wrapper -->
	</div><!-- content -->
</div><!-- #container -->

<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

