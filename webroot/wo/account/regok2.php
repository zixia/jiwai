<?php
/*
@header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");
*/

require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();
$followings_num = 8;
$friend_ids = JWUser::GetFeaturedUserIds( $followings_num );
$friend_user_rows = JWDB_Cache_User::GetDbRowsByIds($friend_ids);

$picture_ids = JWFunction::GetColArrayFromRows($friend_user_rows, 'idPicture');
$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
?>

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="regok">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain(); ?>

<div id="container">
    <p class="top">恭喜你注册成功</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <span class="bluebold16">你知道吗？绑定手机、MSN、QQ或Gtalk等后，可以方便地修改你的用户名和密码！</span>
            <p>发送<span class="orange12">gm+空格+想要用户名</span>，到相应的短信号码或者机器人上来设置用户名<br />例如：gm 阿朱</p>
            <p>发送<span class="orange12">mima+空格+密码</span>，来设置密码<br />例如：mima abc123 </p>
        </div><!-- leftdiv -->
        <div class="rightdiv">
<div class="lookfriend">
       <div class="binding">
	   <p>一切就绪了，不知道从哪里开始吗？你可以</p>
	   <p><a href="http://beta.jiwai.de/wo/">&gt;&gt; 马上开始叽歪</a></p>
	   <p><a href="http://beta.jiwai.de/g/">&gt;&gt; 随便逛逛</a></p>
	   <p><b>或者，看看他们在叽歪什么</b></p>
	   <div id="wtRegok"><!-- wtFollow start -->
        <div class="follow">
	<ul class="followlist">
<?php
	   /*<p><a href="<?php echo JW_SRVNAME;?>/wo/">&gt;&gt; 马上开始叽歪</a></p>
	   <p><a href="<?php echo JW_SRVNAME;?>/public_timeline/">&gt;&gt; 随便逛逛</a></p>
	   */
foreach( $friend_ids as $list_user_id )
{
    $list_user_row = $friend_user_rows[$list_user_id];
    $list_user_picture_id = @$list_user_row['idPicture'];
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
	<div style="clear: both;"></div>
    </div><!-- wtFollow end -->
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
        </div><!-- rightdiv end -->
    </div><!-- #wtMainBlock end -->
    <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container end -->

<?php JWTemplate::footer() ?>

</body>
</html>
