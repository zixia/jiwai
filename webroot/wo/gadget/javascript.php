<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

define('DEFAULT_GADGET_COUNT', 3);
define('DEFAULT_GADGET_THEME', 'iChat');

JWLogin::MustLogined();
$theme_list	= array ( 	 'DOS_Box'			=> false
						,'text'			    => true
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
<?php JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
));?>
</head>

<body class="account" id="create">

<?php JWTemplate::header(); ?>

<div id="container">
    <p class="top">窗可贴</p>
    <div id="wtMainBlock">
        <div class="leftdiv">
            <ul class="leftmenu">
                <li><a href="/wo/gadget/">窗可贴说明</a></li>
                <li><a href="/wo/gadget/image/">图片窗可贴</a></li>
                <li><a href="/wo/gadget/flash/">Flash窗可贴</a></li>
                <li><a href="/wo/gadget/javascript/" class="now">代码窗可贴</a></li>
            </ul>
        </div><!-- leftdiv -->
        <div class="rightdiv">
            <div class="lookfriend">
              <form action="/wo/gadget/preview/" method="post" target="w" onsubmit="window.open('','w','width=800,height=600,scrollbars')">
                <p>
                    <span class="black15bold">显示：</span>
		    	<input type="hidden" name="gadget[pictsize]" value="48"/>
                        <input type="radio" name="gadget[selector]" value="user"  
<?php
if ( !isset($gadget['selector']) ) 
    echo 'checked="checked"';
else if ( 'user'===$gadget['selector'] )
    echo 'checked="checked"';
?>/>
                    <span class="pad3">自己的</span>
                        <input type="radio" name="gadget[selector]" value="friends"  
<?php 	
if ( isset($gadget['selector']) && 'friends'===$gadget['selector'] )
	echo 'checked="checked"';
?>/>
                    <span class="pad3">自己和关注的人</span>
                        <input type="radio" name="gadget[selector]" value="public"  
<?php 
if ( isset($gadget['selector']) && 'public'===$gadget['selector'] )
    echo 'checked="checked"';
?>/>
                    <span class="pad3">大家的</span>
    			</p>

				<p>
        			<span class="black15bold">条数：</span>&nbsp;
            			<select id="gadget_count" name="gadget[count]" size="1" class="select">
<?php
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

if (40==$gadget['count'] )  
{
	$selected = " selected='selected' ";
} 
else 
{
	$selected = "";
}

    echo "\t\t\t\t\t\t<option value='40' $selected >40</option>\n"

?>
			            </select>&nbsp;
            		<span class="pad3">条新叽歪</span>
				</p>
            	<p>
            		<span class="black15bold">界面选择：</span>
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
            </p>
            <p>
                <span class="black15bold">编码：</span><input type="radio" name="gadget[encoding]" value="utf-8"  
<?php	
if ( !isset($gadget['encoding']) )
    echo ' checked="checked" ';
else if ( 'utf-8'==$gadget['encoding'] )
    echo ' checked="checked" ';
?>/>
                <span class="pad3">UTF-8</span>
                    <input type="radio" name="gadget[encoding]" value="gb2312" 
<?php	
if ( isset($gadget['encoding']) && 'gb2312'==$gadget['encoding'] )
    echo ' checked="checked" ';
?>/>
                <span class="pad3">GB2312</span>
            </p>
            <p>
                <input style="display:inline;" type="checkbox" name="gadget[hidefollow]" value="1";
<?php if( !empty( $gadget['hidefollow']))
    echo "checked='checked' ";
?>/>
                <span class="pad3">不显示叽歪de档案链接</span></p>

            <p>
            <input name="Submit" type="submit" class="submitbutton" target="_blank" value="预览一下看看" /></p>
            </form>
            </div><!-- lookfriend -->
            <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
        </div><!-- rightdiv -->
    </div><!-- #wtMainBlock -->
    <div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->
<?php JWTemplate::footer(); ?>
</body>
</html>

