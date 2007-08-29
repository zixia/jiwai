<?php 
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$requestType = 'inrequests';
if( isset( $_REQUEST['out'] ) )
    $requestType = 'outrequests';

$logined_user_info 	= JWUser::GetCurrentUserInfo();
$logined_user_id 	= $logined_user_info['id'];

if( $requestType == 'inrequests' ) {
    $request_num			= JWFriendRequest::GetUserNum	($logined_user_info['id']);
    $pagination         = new JWPagination($request_num, $page, 15);
    $friend_ids			= JWFriendRequest::GetUserIds($logined_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
}else {
    $request_num			= JWFriendRequest::GetFriendNum	($logined_user_info['id']);
    $pagination         = new JWPagination($request_num, $page, 15);
    $friend_ids			= JWFriendRequest::GetFriendIds($logined_user_info['id'], $pagination->GetNumPerPage(), $pagination->GetStartPos() );
}

?>

<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="friends">
<?php JWTemplate::header("/wo/account/settings") ?>
<?php JWTemplate::ShowActionResultTips(); ?>

<div id="container">
<?php JWTemplate::FriendsTab( $logined_user_info['id'], $requestType ); ?>
<div class="tabbody" id="myfriend">

    <table width="100%" border="0" cellspacing="1" cellpadding="0" class="tablehead">
    <tr>
        <td width="285"><a href="#">用户名</a></td>
        <td width="60"><a href="#">消息数</a></td>
        <!--td width="60"><a href="#">彩信数</a></td-->
        <td><a href="#">最后更新时间</a></td>
    </tr>
    </table>

<?php JWTemplate::ListUser($logined_user_info['id'], $friend_ids, array('type'=>$requestType)); ?>
</div>

<?php JWTemplate::PaginationLimit( $pagination, $page, null, $limit = 4 ) ; ?>

<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>

</body>
</html>
