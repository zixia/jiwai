<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);
define('DEFAULT_GADGET_THEME', 'iChat');

JWLogin::MustLogined();
$user	= JWUser::GetCurrentUserInfo();
$idUser	= $user['id'];


$div_id = "JiWai_de__gadget_timeline_user_3_iChat_UTF-8_$idUser";
$gadget_script_html = <<<_HTML_
<div><div id="$div_id"><script type='text/javascript' charset="utf-8" src='http://api.jiwai.de/gadget/timeline/$idUser.js?selector=user&count=3&theme=iChat&thumb=24&gadget_div=$div_id'></script></div><div style='font: 0px/0px sans-serif;clear: both;display: block'> </div><div clear='both' style='text-align:center'><a href='http://JiWai.de/$user[nameScreen]/' target='_blank' style='align:middle'>$user[nameFull]的叽歪档案<img src="http://asset.jiwai.de/img/favicon.gif" style="align:middle; border:0" /></a></div></div>
_HTML_;

if ( isset($_REQUEST['gadget']) )
{
	//echo "<pre>"; die(var_dump($_REQUEST));
	
	$gadget = $_REQUEST['gadget'];

	if ( isset($gadget['hidefollow']) )
		$gadget['hidefollow']=true;
	else
		$gadget['hidefollow']=false;

	$div_id = "JiWai_de__gadget_timeline_$gadget[selector]_$gadget[count]_$gadget[theme]_$gadget[encoding]_$idUser";

	$gadget_script_html = 	"<div>";

	$gadget_script_html	.= 	"<div id='$div_id'>"
							."<script type='text/javascript' charset='utf-8' src='http://api.jiwai.de/gadget/timeline/$idUser.js"
									."?selector=$gadget[selector]"
									."&count=$gadget[count]"
									."&theme=$gadget[theme]"
									."&thumb=$gadget[pictsize]"
									."&gadget_div=$div_id"
							."'></script>"
							."</div>";
						;

	$gadget_script_html .= "<div style='font: 0px/0px sans-serif;clear: both;display: block'> </div>";

	if ( ! $gadget['hidefollow'] )
	{
			$sub_user_str = $user['nameFull'] . '的叽歪档案';

			if ( isset($gadget['encoding']) && 'UTF-8'!=strtoupper($gadget['encoding']) )
				$sub_user_str = mb_convert_encoding($sub_user_str, $gadget['encoding'], "UTF-8");

			$gadget_script_html .= "<div clear='both' style='text-align:center'>"
									."<a href='http://JiWai.de/$user[nameScreen]/' target='_blank' style='align:middle'>"
									."$sub_user_str"
									."<img src='http://asset.jiwai.de/img/favicon.gif' border='0' />"
									."</a>"
									."</div>";
								;
	}

	$gadget_script_html	.= "</div>";

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
<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="account" id="settings">
<?php JWTemplate::accessibility() ?>
<?php JWTemplate::header() ?>

<div id="container">
    <?php JWTemplate::UserGadgetNav('javascript'); ?>
    <div class="tabbody">
		<h2>配置</h2>
        <fieldset>
            <form method="post">
                    <p>显示 
   							<label><input style="display:inline;width:20px;" type="radio" name="gadget[selector]" value="user" 
										<?php 	if ( !isset($gadget['selector']) ) 
													echo 'checked="checked"';
												else if ( 'user'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>我自己的</label>
							<label><input style="display:inline;width:20px;" type="radio" name="gadget[selector]" value="friends" 
										<?php 	if ( isset($gadget['selector']) && 'friends'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>我和朋友们的</label>
   							<label><input style="display:inline;width:20px;" type="radio" name="gadget[selector]" value="public" 
										<?php if ( isset($gadget['selector']) && 'public'===$gadget['selector'] )
													echo 'checked="checked"';
										?>/>所有人的</label>
		 &nbsp;
								最近	
								<select id="gadget_count" name="gadget[count]">
<?php
// default value
if ( !isset($gadget['count']) )
	$gadget['count'] = DEFAULT_GADGET_COUNT;

for ( $n=1; $n<=20; $n++ )
{
	if ( $n>7 && $n%5 )
		continue;

	if ( $n==$gadget['count'] ){
		$selected = " selected='selected' ";
	} else {
		$selected = "";
	}

	echo "\t\t\t\t\t<option value='$n' $selected>$n</option>\n";
}

if (40==$gadget['count'] ) {
	$selected = " selected='selected' ";
} else {
	$selected = "";
}

echo "\t\t\t\t\t\t<option value='40' $selected >40</option>\n"

?>
								</select>
								条
</p>


								<p>
界面选择:
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

if ( !isset($gadget['theme']) )
	$gadget['theme'] = DEFAULT_GADGET_THEME;

foreach ( $theme_list as $theme => $is_release ) 
{ 
	$release = $is_release ? 'Beta' : 'Alpha';
	$selected = '';

	if ( $gadget['theme']==$theme ){
		$selected = " selected='selected' ";
	}

	echo <<<_HTML_
									<option value="$theme" "$selected">($release) $theme</option>
_HTML_;
}
?>
								</select>
								&nbsp;
								图片大小:
   							<label><input style="display:inline;width:20px;"  type="radio" name="gadget[pictsize]" value="24" 
										<?php 	if ( !isset($gadget['pictsize']) )
													echo " checked='checked' ";
												else if ( 24==$gadget['pictsize'] )
													echo " checked='checked' ";
										?>/>小</label>
							<label><input style="display:inline;width:20px;"  type="radio" name="gadget[pictsize]" value="48" 
										<?php	if ( isset($gadget['pictsize']) && 48==$gadget['pictsize'] )
													echo " checked='checked' ";
										?>/>中</label>
						&nbsp;
   							<label><input style="display:inline;width:20px;"  type="checkbox" name="gadget[hidefollow]" value="1";
										<?php	if ( !empty($gadget['hidefollow']) ) 
													echo " checked='checked' ";
										?>/>不显示叽歪de档案链接</label>
										</p>
						<p>
						

							</p>
							<p>
文字编码:
   							<label><input style="display:inline;width:20px;" type="radio" name="gadget[encoding]" value="utf-8" 
										<?php	if ( !isset($gadget['encoding']) )
													echo ' checked="checked" ';
												else if ( 'utf-8'==$gadget['encoding'] )
													echo ' checked="checked" ';
										?>/>UTF-8</label>
   							<label><input style="display:inline;width:20px;" type="radio" name="gadget[encoding]" value="gb2312" 
										<?php	if ( isset($gadget['encoding']) && 'gb2312'==$gadget['encoding'] )
													echo ' checked="checked" ';
										?>/>GB2312</label>

   							</p>
   							<p>
							<input type="submit" value="预览一下看看？"/>
							</p>
   				<br/>
			</form>
        </fieldset>
		<h2>代码</h2>
        <fieldset>
            <p>
            <textarea onclick="this.select();" style="width:640px;height:200px;"><?php echo htmlspecialchars($gadget_script_html) ?></textarea>
            </p>
        </fieldset>
		<h2>预览</h2>
        <fieldset>
			<?php echo $gadget_script_html ?>
        </fieldset>
			<h3>不明白怎么用？看看 <a href="<?php echo JWTemplate::GetConst('UrlHelpGadget')?>">窗可贴指南</a>。</h3>
    </div>
<div style="clear:both; height:7px; overflow:hidden; line-height:1px; font-size:1px;"></div>
</div><!-- #container -->


<?php JWTemplate::footer() ?>

</body>
</html>

