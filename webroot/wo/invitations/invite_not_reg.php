<?php
require_once('../../../jiwai.inc.php');

JWLogin::MustLogined();

$current_user_info = JWUser::GetCurrentUserInfo();
$current_user_id = $current_user_info['id'];

$memcache = JWMemcache::Instance();
$mc_key = $_SESSION['Buddy_Import_Key'] ;
$friends_rows_all = $memcache->Get( $mc_key );
$friends_rows_all = JWBuddy_Import::GetFriendsByIdUserAndRows($current_user_id, $friends_rows_all);
$friends_rows = $friends_rows_all[JWBuddy_Import::NOT_REG];
$friends_rows_count = count($friends_rows);

if (0>=$friends_rows_count)
	JWTemplate::RedirectToUrl(JW_SRVNAME . "/wo/invitations/invite_finished" );
?>

<?php JWTemplate::html_doctype(); ?>
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
<div class="leftdiv">
<ul class="leftmenu">
		<li><a id="tab_import" href="invite_not_reg" class="now">寻找好友</a></li>
		<li><a id="tab_email" href="email" class="">Email邀请</a></li>
		<li><a id="tab_sms" href="sms" class="">短信邀请</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div id="invite_import" style="display:block;">
<div class="lookfriend">
<form name="not_reg_form" id="not_reg_form" method="post" action="/wo/invitations/do">
<p class="black15bold">你共有&nbsp;<?php echo $friends_rows_count;?>&nbsp;个联系人不在叽歪上，你可以邀请他们加入叽歪。</p>
   <div class="box2">
	<p><input name="invite_not_reg" type="submit" class="submitbutton" value="完成" />&nbsp;&nbsp;  
	</div>
</div><!-- lookfriend -->
<div class="lookfriend">
  <div class="list">
  <div class="pad">
  <input type="checkbox" name="checkbox" id="not_reg_check" value="checkbox" checked="true" onclick="selectAll();"/>
  <label class="pad1" for="not_reg_check">选择所有好友</label>
  </div><!-- pad -->
<?php
foreach($friends_rows  as $idFriend => $friends_row)
{
	$friends_array = explode(',',  $friends_row, 2);
?>
  
  <div class="entry">
	<div class="floatleft"><input onclick="selectOne(this);" type="checkbox" name="friends_emails[]" checked="true" value="<?php echo $idFriend; ?>" /></div>
	  <div class="content">
		<div class="meta">
		<span class="floatright" title="<?php echo $friends_array[0];?>"><&nbsp;<?php echo mb_substr($friends_array[0], 0, 22,'UTF-8');?>&nbsp;></span><span title="<?php echo $friends_array[1];?>"><?php echo mb_substr($friends_array[1], 0, 18,'UTF-8');?>
		</div><!-- meta -->
		</div><!-- content -->
	</div><!-- entry -->
 <?php
}
?> 
	<div style="clear: both;"></div>
</div> <!-- list -->	
   <div class="box2">
	<p><input name="invite_not_reg" type="submit" class="submitbutton" value="完成" />&nbsp;&nbsp;  
	</div>
</div><!-- lookfriend -->
</form>
	<div style="clear: both;"></div>
</div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->

<?php JWTemplate::container_ending(); ?>
</div><!-- #container -->

<script>
function selectAll()
{
	var c = $('not_reg_check');
	var f = $('not_reg_form');
	for(var  i=0; i<f.elements.length; i++)
	{  
		var  e=f.elements[i];  
		if  (e.id  !=  'not_reg_check')  
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
		$('not_reg_check').checked = false;
	}

	if ( count_select_now == count_select_all )
	{
		$('not_reg_check').checked = true;
	}
}
</script>

<?php JWTemplate::footer() ?>
</body>
</html>
