#!/usr/bin/php
<?php
/*
 * Auto Nudge Script (under cron)
 * 
 * 如果用户 24 小时没有更新，并且绑定了 sms / im，并且 auto nudge 选项开启，则发送 auto nudge
 *
 *	1、用户绑定了 Device （至少一个，如果多个，1st尝试 IM，不成功再尝试 SMS）
 *	2、用户在 24 小时以前，48小时以内，有过更新
 *	3、如果用户一直不更新，Auto Nudge 需要按照一定的算法定期进行提醒，每次间隔时间延长（考虑 x2）
 *	4、每天最多提醒用户一次
 *
 *	算法：
 *		current_hour = HOUR(NOW())
 *		
 *		// 我们一般应该在恰巧24小时的时候进行 autonudge
 *		// 因为昨天的这个时候用户有更新。 :) 
 *		// if ( current_hour>=20 || current_hour<=9 ) return;
 *
 *		get id_status_last_auto_nudge_{day,week,month} from db
 *
 *		// 选出在最后一次 auto_nudge 之后，24小时之前更新的用户
 *		id_user_nudge_day 	= SELECT distinct idUser FROM Status 
								WHERE timestamp < NOW()-24H AND id>id_status_last_auto_nudge
 *		id_user_nudge_week 	= SELECT distinct idUser FROM Status 
								WHERE timestamp < NOW()-24H AND id>id_status_last_auto_nudge
 *		id_user_nudge_month	= SELECT distinct idUser FROM Status 	
								WHERE timestamp < NOW()-24H AND id>id_status_last_auto_nudge
 *
 */
require_once(dirname(__FILE__) . "/../jiwai.inc.php");


$idStatusLastDay = JWNudge::GetIdStatusLastDayProcessed();

$idStatus_max = JWStatus::GetMaxId();

/*
	选出idUser，条件为：
		1、24小时前更新过，并且更新没有检查过 auto nudge (意味着idStatus>idStatusLastDay)的
		2、24小时内未更新过的
 */
$nudge_user_ids = JWNudge::GetIdUserNudgeDay();

foreach ( $nudge_user_ids as $idUser )
{
	$user_info = JWUser::GetUserInfo($idUser);

	echo "$user_info[nameScreen]\n";
}

?>
