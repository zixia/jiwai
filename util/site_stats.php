<?php
/* 
 * site_stats.php
 * ----------------------------------------------------------------------
 * enables cacti to read website statistics
 * 
 * Originally by glinus at gmail dot com - 2007/09/24
 *
*/

require_once(dirname(__FILE__) . '/../jiwai.inc.php');
if (!extension_loaded('mysql')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        dl('php_mysql.dll');
    } else {
        dl('mysql.so');
    }
}

$hostname = '10.1.30.10';
$username = 'root';
$password = '';
$database = 'jiwai';

/* Phase of SQL */
$link   = mysql_connect($hostname, $username, $password) or die(mysql_error());
mysql_select_db($database) or die('Could not select database');

$mixedArray = array();
$mixedArray['users'] = array();
$mixedArray['device'] = array();
$mixedArray['usmff'] = array();
$mixedArray['inim']  = array();
$mixedArray['inuser']= array();
$mixedArray['minim']= array();
$mixedArray['minuser']= array();
$mixedArray['invite'] = array();

$tables = array(
    'User'      => 'id',
    'Status'    => 'idUser',
    'Message'   => 'idUserSender',
    'Friend'    => 'idUser',
    'Follower'  => 'idUser',
);

foreach ($tables as $table=>$k) {
    $query  = <<<__SQL__
SELECT COUNT(DISTINCT $k ) FROM $table
WHERE timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed:  '. mysql_error());
    $mixedArray['users'][$table] = mysql_result($result, 0);
}

$query = <<<__SQL__
SELECT
COUNT(DISTINCT idUser) FROM LogUserAction
WHERE timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$mixedArray['users']['Login'] = mysql_result($result, 0);

$tables = array('User', 'Status', 'Message', 'Friend', 'Follower');

foreach ($tables as $table) {
    $query  = <<<__SQL__
SELECT COUNT(*) FROM $table
WHERE timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['usmff'][$table] = mysql_result($result, 0);
}

$tables = array('gtalk', 'msn', 'qq', 'web', 'sms', 'api', 'wap');

foreach ($tables as $robot) {
    $query = <<<__SQL__
SELECT COUNT(*) FROM Status
WHERE device="$robot"
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['inim'][$robot] = mysql_result($result, 0);
}

foreach ($tables as $robot) {
    $query = <<<__SQL__
SELECT COUNT(DISTINCT idUser) FROM Status WHERE device="$robot"
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['inuser'][$robot] = mysql_result($result, 0);
}

foreach ($tables as $robot) {
    $query = <<<__SQL__
SELECT COUNT(*) FROM Message WHERE device="$robot"
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['minim'][$robot] = mysql_result($result, 0);
}

foreach ($tables as $robot) {
    $query = <<<__SQL__
SELECT COUNT(DISTINCT idUserSender) FROM Message WHERE device="$robot"
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['minuser'][$robot] = mysql_result($result, 0);
}

/* MMS */
$query = <<<__SQL__
SELECT COUNT(1) FROM Status
WHERE (isMms='Y' OR statusType='MMS')
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$mixedArray['inim']['mms'] = mysql_result($result, 0);

$query = <<<__SQL__
SELECT COUNT(DISTINCT(idUser)) FROM Status
WHERE (isMms='Y' OR statusType='MMS')
AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$mixedArray['inuser']['mms'] = mysql_result($result, 0);

/* Invitation */
$devices = array('gtalk', 'msn', 'sms');
foreach ($devices as $device) {
    $query = <<<__SQL__
        SELECT COUNT(1) FROM Invitation
        WHERE type="$device"
        AND timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['inim']['invite'][$device] = mysql_result($result, 0);

    $query = <<<__SQL__
        SELECT COUNT(1) FROM Invitation
        WHERE type="$device"
        AND timeAccept BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
    $result = mysql_query($query) or die('Query failed: ' . mysql_error());
    $mixedArray['inim']['invite_accept'][$device] = mysql_result($result, 0);
}


/* binded Devices and Mobiles */
$query = <<<__SQL__
SELECT
COUNT(*) FROM Device
WHERE timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
__SQL__;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$mixedArray['device']['devices'] = mysql_result($result, 0);

$query = <<<__SQL__
SELECT
COUNT(*) FROM Device
WHERE timeCreate BETWEEN DATE_ADD(CURDATE(), INTERVAL -1 DAY) AND CURDATE()
AND type="sms"
__SQL__;
$result = mysql_query($query) or die('Query failed: ' . mysql_error());
$mixedArray['device']['mobiles'] = mysql_result($result, 0);


/* Phase of Report Generate */
$timenow    = date("r");
$report     = <<<__REPORT__
本站(http://jiwai.de) $timenow

注意：以下数据自2007/10/15日起，各项指标均为前一日的绝对数
注意：以下数据自2007/10/24日起，发送到邮件域jiwai.com

->概要统计

| 注册用户： %registeredUsers%
| 登陆用户： %loginUsers%
| 发送消息： %statusesSent%
| 私密消息： %messagesSent%
| 好友数量： %countOfFriends%
| 粉丝数量： %countOfFollowers%

->设备统计
| 成功绑定设备： %bindedDevices%
| 成功绑定手机： %bindedMobiles%

->用户行为统计

| 指标        活跃用户
| 消息数      %usersSentStatus%
| 悄悄话      %usersSentMessage%
| 好友数      %usersHaveFriend%
| 粉丝数      %usersHaveFollower%

->消息来源统计

| 机器人      消息数      悄悄话
| GTalk   %statusesSentByGtalk%/%usersSentStatusByGtalk%      %messagesSentByGtalk%/%usersSentMessageByGtalk%
| MSN     %statusesSentByMsn%/%usersSentStatusByMsn%      %messagesSentByMsn%/%usersSentMessageByMsn%
| QQ      %statusesSentByQq%/%usersSentStatusByQq%     %messagesSentByQq%/%usersSentMessageByQq%
| WEB     %statusesSentByWeb%/%usersSentStatusByWeb%       %messagesSentByWeb%/%usersSentMessageByWeb%
| SMS     %statusesSentBySms%/%usersSentStatusBySms%       %messagesSentBySms%/%usersSentMessageBySms%
| API     %statusesSentByApi%/%usersSentStatusByApi%       %messagesSentByApi%/%usersSentMessageByApi%
| WAP     %statusesSentByWap%/%usersSentStatusByWap%       %messagesSentByWap%/%usersSentMessageByWap%
| MMS     %statusesSentByMms%/%usersSentStatusByMms%

->用户邀请

| 机器人      发送数      接受数
| GTalk       %invitationsSentByGtalk%      %invitationsAcceptByGtalk%
| MSN         %invitationsSentByMsn%      %invitationsAcceptByMsn%
| SMS         %invitationsSentBySms%       %invitationsAcceptBySms%

__REPORT__;

/**
->摘要信息

| %usersSentStatusPercentage%%的注册用户发送过消息，他们每人平均发送%usersSentStatusAverage%条消息
| %usersSentMessagePercentage%%的注册用户使用过悄悄话功能，他们每人平均发送%usersSentMessageAverage%条悄悄话
| %usersHaveFriendPercentage%%的注册用户添加了好友，他们平均每人拥有%usersHaveFriendAverage%位好友
| %usersHaveFollowerPercentage%%的注册用户拥有粉丝，他们平均每人添加%usersHaveFollowerAverage%位粉丝

__REPORT__;
**/

$templateReplace = array (
    'registeredUsers'   => $mixedArray['users']['User'],
    'loginUsers'        => $mixedArray['users']['Login'],
    'statusesSent'      => $mixedArray['usmff']['Status'],
    'messagesSent'      => $mixedArray['usmff']['Message'],
    'countOfFriends'    => $mixedArray['usmff']['Friend'],
    'countOfFollowers'  => $mixedArray['usmff']['Follower'],

    'bindedDevices'     => $mixedArray['device']['devices'],
    'bindedMobiles'     => $mixedArray['device']['mobiles'],

    'usersSentStatus'   => $mixedArray['users']['Status'],
    'usersSentMessage'  => $mixedArray['users']['Message'],
    'usersHaveFriend'   => $mixedArray['users']['Friend'],
    'usersHaveFollower' => $mixedArray['users']['Follower'],

    'statusesSentByGtalk'       => $mixedArray['inim']['gtalk'],
    'statusesSentByMsn'         => $mixedArray['inim']['msn'],
    'statusesSentByQq'          => $mixedArray['inim']['qq'],
    'statusesSentByWeb'         => $mixedArray['inim']['web'],
    'statusesSentBySms'         => $mixedArray['inim']['sms'],
    'statusesSentByApi'         => $mixedArray['inim']['api'],
    'statusesSentByWap'         => $mixedArray['inim']['wap'],
    'statusesSentByMms'         => $mixedArray['inim']['mms'],

    'invitationsSentByGtalk'    => $mixedArray['inim']['invite']['gtalk'],
    'invitationsSentByMsn'      => $mixedArray['inim']['invite']['msn'],
    'invitationsSentBySms'      => $mixedArray['inim']['invite']['sms'],

    'invitationsAcceptByGtalk'  => $mixedArray['inim']['invite_accept']['gtalk'],
    'invitationsAcceptByMsn'    => $mixedArray['inim']['invite_accept']['msn'],
    'invitationsAcceptBySms'    => $mixedArray['inim']['invite_accept']['sms'],

    'messagesSentByGtalk'       => $mixedArray['minim']['gtalk'],
    'messagesSentByMsn'         => $mixedArray['minim']['msn'],
    'messagesSentByQq'          => $mixedArray['minim']['qq'],
    'messagesSentByWeb'         => $mixedArray['minim']['web'],
    'messagesSentBySms'         => $mixedArray['minim']['sms'],
    'messagesSentByApi'         => $mixedArray['minim']['api'],
    'messagesSentByWap'         => $mixedArray['minim']['wap'],

    'usersSentStatusByGtalk'    => $mixedArray['inuser']['gtalk'],
    'usersSentStatusByMsn'      => $mixedArray['inuser']['msn'],
    'usersSentStatusByQq'       => $mixedArray['inuser']['qq'],
    'usersSentStatusByWeb'      => $mixedArray['inuser']['web'],
    'usersSentStatusBySms'      => $mixedArray['inuser']['sms'],
    'usersSentStatusByApi'      => $mixedArray['inuser']['api'],
    'usersSentStatusByWap'      => $mixedArray['inuser']['wap'],
    'usersSentStatusByMms'      => $mixedArray['inuser']['mms'],

    'usersSentMessageByGtalk'   => $mixedArray['minuser']['gtalk'],
    'usersSentMessageByMsn'     => $mixedArray['minuser']['msn'],
    'usersSentMessageByQq'      => $mixedArray['minuser']['qq'],
    'usersSentMessageByWeb'     => $mixedArray['minuser']['web'],
    'usersSentMessageBySms'     => $mixedArray['minuser']['sms'],
    'usersSentMessageByApi'     => $mixedArray['minuser']['api'],
    'usersSentMessageByWap'     => $mixedArray['minuser']['wap'],

/**
    'usersSentStatusPercentage'     => round((100.0 * $mixedArray['users']['Status']) / $mixedArray['users']['User']),
    'usersSentMessagePercentage'    => round((100.0 * $mixedArray['users']['Message']) / $mixedArray['users']['User']),
    'usersHaveFriendPercentage'     => round((100.0 * $mixedArray['users']['Friend']) / $mixedArray['users']['User']),
    'usersHaveFollowerPercentage'   => round((100.0 * $mixedArray['users']['Follower']) / $mixedArray['users']['User']),

    'usersSentStatusAverage'    => round((1.0 * $mixedArray['usmff']['Status']) / $mixedArray['users']['Status']),
    'usersSentMessageAverage'   => round((1.0 * $mixedArray['usmff']['Message']) / $mixedArray['users']['Message']),
    'usersHaveFriendAverage'    => round((1.0 * $mixedArray['usmff']['Friend']) / $mixedArray['users']['Friend']),
    'usersHaveFollowerAverage'  => round((1.0 * $mixedArray['usmff']['Follower']) / $mixedArray['users']['Follower']),
    **/
);

foreach ($templateReplace as $k=>$v) {
    $report = preg_replace('/%' . $k . '%/', $v, $report);
}


/* Phase of Sendmail */
$contact = array (
    'Wang Hongwei'  => 'glinus@jiwai.com',
    'Jing RuYa'     => 'jingruya@jiwai.com',
    'Geng Li'       => 'gengli@jiwai.com',
    'Lin Qiuyong'   => 'winnie@jiwai.com',
);

foreach ($contact as $name=>$mail) {
    JWMail::SendMail('site-report@jiwai.de', $mail, 'jiwai.de statistics', $report);
}

?>


