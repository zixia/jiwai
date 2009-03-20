#!/usr/bin/env php

<?php

require_once(dirname(__FILE__) . "/../jiwai.inc.php");
setlocale(LC_ALL, 'zh_CN.UTF8');

function GetBirthdayUsersForecast($delay = 0) {
    $sql = <<<__SQL__
    SELECT id, nameScreen, nameUrl, birthday
    FROM `User`
    WHERE (
    EXTRACT(MONTH FROM `birthday`) = EXTRACT(MONTH FROM ADDDATE(CURDATE(), INTERVAL $delay DAY))
    AND
    DAYOFMONTH(`birthday`) = DAYOFMONTH(ADDDATE(CURDATE(), INTERVAL $delay DAY))
    )
__SQL__;

    return JWDB::GetQueryResult($sql, true, true);
}

$delay = 3;
$system_sender_id = 59668;
$birthday = strftime('%m月%d日', strtotime("+$delay days"));

/* 3days before, notice his/her friends */
$birthday_users = GetBirthdayUsersForecast(3);
foreach ($birthday_users as $birthday_user) {
    $birthday_user_name = $birthday_user['nameScreen'];
    $birthday_user_url = empty($birthday_user['nameUrl'])
        ? 'http://JiWai.de/' . urlencode($birthday_user_name) . '/'
        : 'http://JiWai.de/' . urlencode($birthday_user['nameUrl']) . '/';
    $follower_user_ids = JWFollower::GetFollowerIds($birthday_user['id']);
    $message = <<<__HTML__
${birthday_user_name}( ${birthday_user_url} )的生日是${birthday}，快给TA准备礼物吧！
__HTML__;
    foreach ($follower_user_ids as $follower_user_id) {
        echo "[BirthdayReminder]FROM $system_sender_id TO $follower_user_id AS $birthday_user_name", "\n";
        JWMessage::Create($system_sender_id,
                $follower_user_id,
                $message,
                'web',
                array('notice' => 'notice'));
    }
}

$birthday_users = GetBirthdayUsersForecast();
foreach ($birthday_users as $birthday_user) {
    $birthday_user_name = $birthday_user['nameScreen'];
    $birthday_user_url = empty($birthday_user['nameUrl'])
        ? 'http://JiWai.de/' . urlencode($birthday_user_name) . '/'
        : 'http://JiWai.de/' . urlencode($birthday_user['nameUrl']) . '/';
    $message = <<<__HTML__
亲爱的${birthday_user_name}( ${birthday_user_url} )，叽歪小弟祝你生日快乐!
__HTML__;

    echo "[HappyBirthday]FROM $system_sender_id TO $birthday_user_name", "\n";
    JWMessage::Create($system_sender_id,
            $birthday_user['id'],
            $message,
            'web',
            array('notice' => 'notice'));
}

