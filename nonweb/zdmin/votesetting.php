<?php
require_once('./function.php');
$vid = isset($_GET['vid']) ? $_GET['vid'] : null;
$time = time();

$vote = array(
	'status_id' => null,
	'number' => null,
	'limit_p' => 20,
	'limit_l' => 1,
	'd_sms' => true,
	'd_im' => true,
	'd_web' => true,
	'time_create' => DATE('Y-m-d H:i:s', $time),
	'time_expire' => DATE('Y-m-d H:i:s', $time+86400),

	'show' => false,
	'new' => true,
	'fix' => null,
);

if ( $_POST )
{
	doPost();
	Header("Location: /votesetting.php?vid=$vid");
	exit;
}
else
{
	$vid = @$_GET['vid'];

	if ( $vid ) 
	{
		$v_row = JWNanoVote::GetDbRowByNumber( $vid );
		if ( empty($v_row) ) 
		{  
			if ( JWNanoVote::IsVoteNumber($vid) ) 
			{
				newVoteSettingByNumber($vid);
			}
		} 
		else
		{
			VoteSettingByVRow( $v_row );
		}
	}
}

function VoteSettingByVRow( $v_row )
{
	global $vote, $vid;
	$vote['new'] = false;
	$vote['show'] = true;

	list( $vote['limit_p'], $vote['limit_l'] ) = explode('_', $v_row['limit'], 2);
	
	$vote['d_im'] = preg_match( '/im/', $v_row['deviceAllow'] );
	$vote['d_sms'] = preg_match( '/sms/', $v_row['deviceAllow'] );
	$vote['d_web'] = preg_match( '/web/', $v_row['deviceAllow'] );

	$vote['number'] = $v_row['number'];
	$vote['status_id'] = $v_row['idStatus'];

	$vote['time_create'] = $v_row['timeCreate'];
	$vote['time_expire'] = $v_row['timeExpire'];

	$vote['fix'] = (JWNanoVote::IsVoteNumber($vid)) ? 'number' : 'status';
}

function newVoteSettingByIdStatus( $status_id )
{
	global $vote;
	$vote['new'] = true;
	$vote['show'] = true;

	$vote['status_id'] = $status_id;
}

function newVoteSettingByNumber( $vid )
{
	global $vote;
	$vote['new'] = true;
	$vote['show'] = true;

	$vote['fix'] = 'number';
	$vote['number'] = $vid;
}

function doPost()
{
	global $vote;
	$create = true;
	$vote = $_POST['vote'];
	$vote['show'] = true;
	$vote['new'] = false;
	$vote['fix'] = null;

	$deviceAllow = implode(',', $vote['deviceAllow']);
	$vote['d_sms'] = preg_match( '/sms/', $deviceAllow );
	$vote['d_im'] = preg_match( '/im/', $deviceAllow );
	$vote['d_web'] = preg_match( '/web/', $deviceAllow );


	if ( empty($deviceAllow) )
	{
		setTips("必须选择至少1个设备");
		return;
	}
	
	$number = $vote['number'];
	if ( false==JWNanoVote::IsVoteNumber($number) )
	{
		setTips("必须设定合法投票编号");
		return;
	}
	$v_row = JWNanoVote::GetDbRowByNumber($number);
	if ( $v_row )
	{
		$id = $v_row['id'];
		$create = false;	
	}

	$status_id = $vote['status_id'];
	if ( null == $status_id || JWNanoVote::IsVoteNumber($status_id) )
	{
		setTips("必须设定合法投票叽歪编号");
		return;
	}
	$v_row = JWNanoVote::GetDbRowByNumber($status_id);
	if ( empty($v_row) )
	{
		setTips("必须是合法的投票叽歪编号");
		return;
	}else if ( $v_row['id'] )
	{
		$create = false;
		$id = $v_row['id'];
	}

	$limit = $vote['limit_p'] .'_'. $vote['limit_l'];

	if ( $create )
	{
		$options = array(
			'timeCreate' => $vote['time_create'],
			'timeExpire' => $vote['time_expire'],
			'deviceAllow' => $deviceAllow,
			'limit' => $limit,
		);
		JWNanoVote::Create( $status_id, $number, $options );
		setTips( "更新设置成功" );
	}
	else if ( isset($_POST['toZero'] ) )
	{
		$runtime_key = 'JWNANO_VOTE_' . $status_id;	
		JWRuntimeInfo::Set( $runtime_key, array() );
	}
	else
	{
		$options = array(
			'timeCreate' => $vote['time_create'],
			'timeExpire' => $vote['time_expire'],
			'deviceAllow' => $deviceAllow,
			'limit' => $limit,
			'number' => $number,
			'idStatus' => $status_id,
		);
		JWDB::UpdateTableRow('Vote', $id, $options);
		setTips( "更新设置成功" );
	}
}

JWRender::Display("votesetting", array(
	'menu_nav' => 'votesetting',
	'vote' => $vote,
	'vid' => $vid,
));
?>
