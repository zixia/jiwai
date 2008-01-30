<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

$memcache = JWMemcache::Instance();
$mc_key = $_SESSION['Buddy_Import_Key'] ;
$friends_rows_all = $memcache->Get( $mc_key );
$friends_rows_all = JWBuddy_Import::GetFriendsByIdUserAndRows($current_user_id, $friends_rows_all);
$friends_rows = $friends_rows_all[JWBuddy_Import::NOT_FOLLOW];
$friends_rows_count = count($friends_rows);

if (0>=$friends_rows_count)
	JWTemplate::RedirectToUrl(JW_SRVNAME . "/wo/invitations/invite_not_reg" );

?>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));
echo "<script>var count_select_all=$friends_rows_count;</script>";
echo "<script>var count_select_now=$friends_rows_count;</script>";
?>
</head>

<body class="account" id="friends">

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">寻找与邀请好友</p>
<div id="wtMainBlock">

<!-- left start -->
<div class="leftdiv">
<ul class="leftmenu">
	<li><a id="tab_import" href="invite" class="now">寻找好友</a></li>
	<li><a id="tab_email" href="email" class="">Email邀请</a></li>
	<li><a id="tab_sms" href="sms" class="">短信邀请</a></li>
</ul>
</div>
<!-- leftdiv end -->

<!-- rightdiv start -->
<div class="rightdiv">
<div id="invite_import" style="display:block;">
<div class="lookfriend">
<form name="not_follow_form" id="not_follow_form" method="post" action="/wo/invitations/do">
<p class="black15bold">你共有&nbsp;<?php echo $friends_rows_count;?>&nbsp;个联系人在叽歪上，你可以关注他们</p>
   <div class="box2">
	<p><input name="invite_not_follow" type="submit" class="submitbutton" value="关注" />&nbsp;&nbsp;  
	</div>
  <div class="list">
  <div class="pad">
  <input type="checkbox" name="checkbox" id="not_follow_check" value="checkbox" checked="true" onclick="selectAll();"/>
  <span class="pad1">关注选择的好友</span>
  </div><!-- pad -->
<?php
foreach($friends_rows  as $idFriend => $friends_row)
{
	$friends_array = explode(',',  $friends_row, 2);
	$friends_info = JWUSer::GetUserInfo($idFriend);
?>
  
  <div class="entry">
	<div class="floatleft"><input onclick="selectOne(this)" type="checkbox" name="friends_ids[]" checked="true" value="<?php echo $idFriend; ?>" /></div>
     <div class="head"><a href="<?php echo JW_SRVNAME; ?>/<?php echo $friends_info['nameUrl'];?>/"><img width="48" height="48" title="<?php echo $friends_info['nameFull'];?>" src="<?php echo JWPicture::GetUrlById($friends_info['idPicture']); ?>"/></a></div>
	  <div class="content"><span class="black15bold"><?php echo $friends_info['nameScreen']; ?></span>
		<div class="meta">
		<span class="floatright" title="<?php echo $friends_array[1];?>"><?php echo mb_substr($friends_array[1], 0, 20,'UTF-8');?></span>< <?php echo $friends_array[0];?> >
		</div><!-- meta -->
		</div><!-- content -->
	</div><!-- entry -->
 <?php
}
?> 

	<div style="clear: both;"></div>
</div> <!-- list -->	
   <div class="box2">
	<p><input name="invite_not_follow" type="submit" class="submitbutton" value="关注" />&nbsp;&nbsp;  
	</div>
</div><!-- lookfriend -->
</form>
	<div style="clear: both;"></div>
</div>
</div>
<!-- rightdiv end -->

</div><!-- #wtMainBlock -->

<?php JWTemplate::container_ending();?>
</div><!-- #container -->
<script>
function selectAll()
{
	var c = $('not_follow_check');
	var f = $('not_follow_form');
	for(var  i=0; i<f.elements.length; i++)
	{  
		var  e=f.elements[i];  
		if  (e.id  !=  'not_follow_check')  
			e.checked  =  c.checked;  
	}
}

function selectOne(c)
{
	if ( c.checked )
	{
		count_select_now++;
	}
	else
	{
		count_select_now--;
		$('not_follow_check').checked = false;
	}

	if ( count_select_now == count_select_all )
	{
		$('not_follow_check').checked = true;
	}
}
</script>

<?php JWTemplate::footer() ?>
</body>
</html>
