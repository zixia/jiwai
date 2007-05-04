<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');

JWUser::MustLogined();
$user	= JWUser::GetCurrentUserInfo();
$idUser	= $user['id'];


$div_id = "JiWai_de__gadget_timeline_user_3_iChat_UTF-8_$idUser";
$gadget_script_html = <<<_HTML_
<div id="$div_id"><script type="text/javascript" src="http://api.jiwai.de/gadget/timeline/$idUser.js?selector=user&count=3&theme=iChat&gadget_div=$div_id"></script></div>
_HTML_;

if ( isset($_REQUEST['gadget']) )
{
	//echo "<pre>"; die(var_dump($_REQUEST));
	
	$gadget = $_REQUEST['gadget'];

	$div_id = "JiWai_de__gadget_timeline_$gadget[selector]_$gadget[count]_$gadget[theme]_$gadget[encoding]_$idUser";

	$gadget_script_html = 	 "<div id='$div_id'>"
							."<script type='text/javascript' src='http://api.jiwai.de/gadget/timeline/$idUser.js"
									."?selector=$gadget[selector]"
									."&count=$gadget[count]"
									."&theme=$gadget[theme]"
									."&thumb=$gadget[pictsize]"
									."&encoding=$gadget[encoding]"
									."&hidefollow=$gadget[hidefollow]"
									."&gadget_div=$div_id"
							."'></script></div>";
}

$theme_list	= array ( 	 'DOS_Box'			=> false
						,'iChat'			=> true
						,'Lined-Paper'		=> true
						,'PHP'				=> true
						,'PingPongPicture'	=> true
						,'SerenePicture'	=> false
						,'Swiss'			=> false
						,'Windows9x'		=> false
						,'WindowsXP'		=> false
					);
?>
<html>

<?php JWTemplate::html_head() ?>

<body class="account" id="gadget">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content" style="margin: 1em 1em">
		<div id="wrapper" style="margin: 1em 1em">


			<h2><?php echo $user['nameFull']?>的窗可贴</h2>
			<h3>想在Blog上自动显示你、好友的最新更新？把这段代码插入你的Blog模板吧！</h3>

        	<div class="indent" style="margin:2em 0 1em 0">
				以下为当前效果的嵌入代码：<br />
				<input type="text" value="<?php echo htmlspecialchars($gadget_script_html) ?>" onclick="this.select();" style="width:700px">
				<?php //echo htmlspecialchars($gadget_script_html) ?>
    		</div>
	
			<br/><br/>
	
<table width="95%" valign="top">
	<tr>
		<td valign="top">
			<form method="post">
        
 				<h4>窗可贴代码生成向导：</h4>
        
   				<table align="center">
   					<tr>
						<td align="right" width="80" valign="top">显示:</td>
						<td width="20"/>
						<td>
   							<label><input type="radio" name="gadget[selector]" value="user" 
										<?php 	if ( !isset($gadget['selector']) ) 
													echo 'checked="checked"';
												else if ( 'user'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>我自己的</label>
							<label><input type="radio" name="gadget[selector]" value="friends" 
										<?php 	if ( isset($gadget['selector']) && 'friends'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>我和朋友们的</label>
   							<label><input type="radio" name="gadget[selector]" value="public" 
										<?php if ( isset($gadget['selector']) && 'public'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>所有人的</label>
							<br/><br/>
   						</td>
					</tr>
    				<tr>
						<td align="right" valign="top">选取:</td>
						<td/>
						<td>
    						<label>
								最近	
								<select id="gadget_count" name="gadget[count]">
<?php
for ( $n=1; $n<=20; $n++ )
{
	if ( $n>7 && $n%5 )
		continue;

	$selected = "";

	if ( !isset($gadget['count']) && 3==$n )
		$selected = " 'selected' ";
	else if ( $n==$gadget['count'] )
		$selected = " 'selected' ";

	echo "\t\t\t\t\t<option value='$n' $selected>$n</option>\n";
}
	if ( 40==$gadget['count'] )
		$selected = " 'selected' ";
	else
		$selected = "";

	echo "\t\t\t\t\t\t<option value='40' $selected >40</option>"
?>
								</select>
								条
							</label>
							<br/><br/>

						</td>
					</tr>
   					<tr>
						<td align="right" height="35" valign="top">图片大小:</td>
						<td></td>
						<td>
   							<label><input type="radio" name="gadget[pictsize]" value="24" 
										<?php 	if ( !isset($gadget['pictsize']) )
													echo " checked='checked' ";
												else if ( 24==$gadget['pictsize'] )
													echo " checked='checked' ";
										?>/>小</label>

							<label><input type="radio" name="gadget[pictsize]" value="48" 
										<?php	if ( isset($gadget['pictsize']) && 48==$gadget['pictsize'] )
													echo " checked='checked' ";
										?>/>中</label><br/>
   							<label><input type="checkbox" name="gadget[hidefollow]" value="1";
										<?php	if ( isset($gadget['hidefollow']) ) 
													echo " checked='checked' ";
										?>/>不显示订阅链接</label><br/><br/>
							<br/>
   						</td>
					</tr>
    				<tr>
						<td align="right" valign="top">界面选择:</td>
						<td/>
						<td>
    						<label>
								<select id="gadget_theme" name="gadget[theme]">
<?php 
function cmp($a, $b)
{
	global $theme_list;

	if ( ($theme_list[$a]  && $theme_list[$b]) 
			|| (!$theme_list[$a]  && !$theme_list[$b]) )
		return strcmp($b, $a);
	else if ( $theme_list[$a] )
		return -1;
	else
		return 1;
}

uksort($theme_list, "cmp");

foreach ( $theme_list as $theme => $is_release ) 
{ 
	$release = $is_release ? 'Beta' : 'Alpha';
	$selected = '';

	if ( !isset($gadget['theme']) && 'iChat'==$theme )
		$selected = "selected";
	else if ( $gadget['theme']==$theme )
		$selected = "selected";

	echo <<<_HTML_
									<option value="$theme" "$selected">($release) $theme</option>
_HTML_;
}
?>
								</select>
								<br />
								通过模板效果图选择？<a href="javascript:alert('coming soon!');">点击这里！</a>
							</label>
							<br/><br/>

						</td>
					</tr>
 
					<tr>
						<td align="right" height="35" valign="top">文字编码:</td>
						<td></td>
						<td valign="top">
   							<label><input type="radio" name="gadget[encoding]" value="utf-8" 
										<?php	if ( !isset($gadget['encoding']) )
													echo ' checked="checked" ';
												else if ( 'utf-8'==$gadget['encoding'] )
													echo ' checked="checked" ';
										?>/>UTF-8</label>
   							<label><input type="radio" name="gadget[encoding]" value="gb2312" 
										<?php	if ( isset($gadget['encoding']) && 'gb2312'==$gadget['encoding'] )
													echo ' checked="checked" ';
										?>/>GB2312</label>
							<br/>
						</td>
					</tr>
   					<tr>
						<td/><td/>
						<td>
   							<br/>
							<input type="submit" value="预览一下看看？"/>
						</td>
					</tr>
   				</table>

   				<br/>
			</form>

			<h3>不明白怎么用？看看 <a href="/about?topic=gadget">窗可贴指南</a>。</h3>

		</td>
		<td width="20">
		<td width="30%" valign="top">


    		<h4>窗可贴预览：</h4>

			<?php echo $gadget_script_html ?>

		</td>
	</tr>
</table>

 		</div><!-- wrapper -->
	</div><!-- content -->
	

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>

