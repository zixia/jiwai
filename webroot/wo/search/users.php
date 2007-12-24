<?php
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();
$q = null;
extract($_GET, EXTR_IF_EXISTS);

$logined_user_info = JWUser::GetCurrentUserInfo();

$head_options = array();

$page_user_info		= $logined_user_info;

$searched_ids		= JWSearch::GetSearchUserIds($q);
$searched_num		= count( $searched_ids );
$searched_user_rows	= JWUser::GetDbRowsByIds($searched_ids);

$searched_user_rows = JWUser::GetDbRowsByIds($searched_ids);
$picture_ids = JWFunction::GetColArrayFromRows($searched_user_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head($head_options); ?>
</head>


<body class="normal">
 
<?php JWTemplate::header("/wo/account/settings") ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<!-- ul id="accessibility">
<li>
你正在使用手机吗？请来这里：<a href="http://m.JiWai.de/">m.JiWai.de</a>!
</li>
<li>
<a href="#navigation" accesskey="2">跳转到导航目录</a>
</li>
<li>
<a href="#side">跳转到功能目录</a>
</li>
</ul -->


<div id="container">
    <div id="wtFollow"><!-- wtFollow start -->
        <p class="title">叽歪成员搜索</p>

            <div class="follow">
                <ul class="followlist">
<?php

foreach($searched_ids as $list_user_id)
{
    $list_user_row = $searched_user_rows[$list_user_id];

    $list_user_picture_id = @$list_user_row['idPicture'];

    $list_user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
    if ( $list_user_picture_id )
    $list_user_icon_url = $picture_url_row[$list_user_picture_id];

?>
<li><a href="http://jiwai.de/<?php echo $list_user_row['nameUrl']; ?>/" title="<?php echo $list_user_row['nameScreen']; ?>" rel="contact"><img icon="<?php echo $list_user_row['id'];?>" class="buddy_icon" src="<?php echo $list_user_icon_url; ?>" title="<?php echo $list_user_row['nameFull']; ?>" border="0" /><?php echo $list_user_row['nameScreen']; ?></a></li>
<?php
}
?>

</ul></div>
        <div style="overflow: hidden; clear: both; height:16px; line-height: 1px; font-size: 1px;"></div>
    </div><!-- wtFollow end -->
    <div id="wtchannelsidebar">
        <div class="sidediv">
            <form id="f3" action="<?php echo JW_SRVNAME . '/wo/search/users'; ?>">
                <P class="title">成员搜索</P>
                <p><input name="q" type="text" class="inputStyle" /></p>
                <p class="sidediv3"><input name="Submit" type="submit" class="submitbutton" onClick="$('f3').submit();" value="搜索成员" /></p>
                    <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div></form>

                    <div class="line"><div></div></div>
                <P class="sidediv4">你可以使用成员的<span class="gray12">用户名，QQ号，Email，MSN帐号</span>等来进行搜索</P>
                    <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div></form>
        </div><!-- sidediv -->
    </div><!-- wtsidebar -->
        <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

