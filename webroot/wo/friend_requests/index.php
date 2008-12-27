<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined(false);

$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
$page = ($page < 1 ) ? 1 : $page;

$request_type = 'inrequests';
if( isset( $_REQUEST['out'] ) )
    $request_type = 'outrequests';

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

$request_ids = array();
if( $request_type == 'inrequests' ) 
{
    if ($request_num = JWFollowerRequest::GetInRequestNum($current_user_info['id']))
    {
	    $request_note_rows = JWFollowerRequest::GetInRequestIds($current_user_info['id'], $request_num );
    }
}
else
{
    if ($request_num = JWFollowerRequest::GetOutRequestNum($current_user_info['id']))
    {
	    $request_note_rows = JWFollowerRequest::GetOutRequestIds($current_user_info['id'], $request_num );
    }
}

$request_user_ids = array_keys( $request_note_rows ); 
$request_user_rows = JWDB_Cache_User::GetDbRowsByIds($request_user_ids);
$picture_ids = JWFunction::GetColArrayFromRows($request_user_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="normal">

<?php JWTemplate::header("/wo/account/settings");?>
<?php JWTemplate::ShowActionResultTipsMain();?>

<div id="container">
    <div id="wtFollow"><!-- wtFollow start -->
<?php
if ('inrequests'==$request_type) {
    echo '<p class="title">请求关注你的人</p>';
}
else
{
    echo '<p class="title">你请求关注的人</p>';
}
?>
        <div class="follow">
	<ul class="followlist">
<?php
foreach( $request_user_rows as $list_user_id=>$list_user_row )
{
    $list_user_picture_id = $list_user_row['idPicture'];
    $list_user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
    if ( $list_user_picture_id )
        $list_user_icon_url = $picture_url_row[$list_user_picture_id];

?>
<li><a href="/<?php echo $list_user_row['nameUrl']; ?>/" title="<?php echo $list_user_row['nameScreen']; ?>" rel="contact"><img icon="<?php echo $list_user_row['id'];?>" class="buddy_icon" src="<?php echo $list_user_icon_url; ?>" title="<?php echo $list_user_row['nameFull'];  ?>" border="0" /><?php echo $list_user_row['nameScreen']; ?></a></li>
<?php
}
?>
	</ul>
        </div>
        <div style="clear:both; height:16px;"></div>
    </div><!-- wtFollow end -->
    <div id="wtchannelsidebar">
    <div class="sidediv">
	    <form id="f3" action="<?php echo JW_SRVNAME . '/wo/search/users'; ?>">
		    <P class="title">成员搜索</P>
		    <p><input name="q" type="text" class="inputStyle" /></p>
		    <p class="sidediv3"><input name="Submit" type="submit" class="submitbutton" onClick="$('f3').submit();" value="搜索成员" /></p>
	    </form>

	    <div class="line"></div>
	    <P style="padding:5px 0 10px 10px"><a href="/wo/invitations/invite">&#187;&nbsp;邀请你的朋友加入叽歪</a></P>
<?php
if ('inrequests'==$request_type)
{
	echo "<P style=\"padding:5px 0 10px 10px\"><a href=\"/wo/friend_requests?out\">&#187;&nbsp;你请求关注的人</a></P>";
}
else
{
	echo "<P style=\"padding:5px 0 10px 10px\"><a href=\"/wo/friend_requests?in\">&#187;&nbsp;请求关注你的人</a></P>";
}
?>
	    <div class="line"></div>
	    <div style="clear:both;"></div>
    </div><!-- sidediv -->
    </div><!-- wtsidebar -->
</div>

<?php JWTemplate::container_ending();?>
</div><!-- #container -->
<?php JWTemplate::footer();?>
</body>
</html>
