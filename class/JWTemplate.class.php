<?php
/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	zixia@zixia.net
 * @version		$Id$
 */

/**
 * JiWai.de Template Class
 */
class JWTemplate {
	/**
	 * Instance of this singleton
	 *
	 * @var JWTemplate
	 */
	static private $msInstance;

	/**
	 * Const vars, init when first be used
	 *
	 * @var array
	 */
	static private $msJWConst = null;

	/**
	 * Instance of this singleton class
	 *
	 * @return JWTemplate
	 */
	static public function &Instance()
	{
		if (!isset(self::$msInstance)) {
			$class = __CLASS__;
			self::$msInstance = new $class;
		}
		return self::$msInstance;
	}


	/**
	 * Constructing method, save initial state
	 *
	 */
	function __construct()
	{
		//throw new JWException("JWTemplate no need construct, use static method pls.");
	}

	
	static public function html_doctype( $options=null )
	{
		echo <<<_HTML_
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
_HTML_;
	}

	static public function html_head( $options=null )
	{
		if ( empty($options['version_css_jiwai_screen']) )
			$asset_url_css = self::GetAssetUrl("/css/jiwai-screen.css");
		else
			$asset_url_css = self::GetAssetUrl("/css/$options[version_css_jiwai_screen]-jiwai-screen.css");

		$asset_url_css_box	= self::GetAssetUrl('/lib/smoothbox/smoothbox.css');

		$asset_url_favicon	= self::GetAssetUrl('/img/favicon.ico');
		$asset_url_os_users	= self::GetAssetUrl('/opensearch/users.xml');
		$asset_url_os_statuses	= self::GetAssetUrl('/opensearch/statuses.xml');

		$asset_url_js_jiwai	= self::GetAssetUrl('/js/jiwai.js');
		$asset_url_js_buddy	= self::GetAssetUrl('/js/buddyIcon.js');
		$asset_url_js_moo	= self::GetAssetUrl('/lib/mootools/mootools.v1.11.js' );
		$asset_url_js_location	= self::GetAssetUrl('/js/location.js' );
		$asset_url_js_validator	= self::GetAssetUrl('/js/validator.js' );
		$asset_url_js_action 	= self::GetAssetUrl('/js/action.js' );
		$asset_url_js_box	= self::GetAssetUrl('/lib/smoothbox/smoothbox.js' );
		$asset_url_js_ac_content = self::GetAssetUrl('/js/AC_RunActiveContent.js');

		$title = '叽歪 / ';
		if ( empty($options['title']) )		$title .= '这一刻，你在做什么？';
		else								$title .= $options['title'];

		if ( empty($options['keywords']) )	$keywords = <<<_STR_
叽叽歪歪,唧唧歪歪,叽歪网,歪歪,唧唧,叽叽,唧歪网,矶歪de,唧歪de,唧歪的,微博客,迷你博客,碎碎念,絮絮叨叨,絮叨,jiwai,jiwaide,tiny blog,im nick
_STR_;
		else								$keywords = "叽叽歪歪,唧唧歪歪,歪歪,唧唧,叽叽," . htmlspecialchars($options['keywords']);

		if ( empty($options['description']) )	$description = <<<_STR_
叽歪网 - 通过手机短信、聊天软件（QQ/MSN/GTalk/Skype）和Web，进行组建好友社区并实时与朋友分享的微博客服务。快来加入我们，踏上唧唧歪歪、叽叽歪歪的路途吧！
_STR_;
		else									$description = htmlspecialchars($options['description']) . ",叽叽歪歪,唧唧歪歪,歪歪,唧唧,叽叽" ;

		if ( empty($options['author']) )	$author = htmlspecialchars('叽歪网 <wo@jiwai.de>');
		else								$author = htmlspecialchars($options['author']);


		$rss_html = <<<_HTML_
	<link rel="alternate"  type="application/rss+xml" title="叽歪网 - [RSS]" href="http://feed.blog.jiwai.de" />
_HTML_;

		if ( !empty($options['rss']) )	
		{
			$rss_html = '';
			foreach ( $options['rss'] as $rss_item )
			{
				$rss_html .= <<<_HTML_
	<link rel="alternate" type="application/$rss_item[type]+xml" title="$rss_item[title]" href="$rss_item[url]" />

_HTML_;
			}
		}

		if ( empty($options['refresh_time']) )	$refresh_time 	= null;
		else	$refresh_time 	= $options['refresh_time'];

		if ( empty($options['refresh_url']) )	$refresh_url	= $_SERVER['SCRIPT_URI'];
		else	$refresh_url	= $options['refresh_url'];

		if ( empty($options['refresh_ajax']) )	$refresh_ajax	= false;
		else	$refresh_ajax	= $options['refresh_ajax'];

		if ( null===$refresh_time )
			$refresh_html = '';
		else
			if (!$refresh_ajax) $refresh_html = <<<_HTML_
	<meta http-equiv="refresh" content="$refresh_time;url=$refresh_url" />
_HTML_;
			else $refresh_html = "<script type=\"text/javascript\">RefreshInterval=$refresh_refresh_time;</script>";
	
		if ( !isset($options['ui_user_id']) )
			$options['ui_user_id'] = JWLogin::GetCurrentUserId();

		$current_user_id = JWLogin::GetCurrentUserId();
		$current_anonymous_user = JWUser::IsAnonymous($current_user_id) ? 'true' : 'false';

		/*
		 *	强制不使用自定义界面，设置为 false 即可
		 */
		$ui_css = '';
		if ( false!==$options['ui_user_id'] && !empty($options['ui_user_id']) )
		{
			$ui = new JWDesign($options['ui_user_id']);
			$ui_css = $ui->GetStyleSheet();
		}

		if ( empty($options['openid_server']) )
		{
			$options['openid_server'] = JW_SRVNAME. '/wo/openid/server';
		}

		$openid_html = '';
		if ( !empty($options['openid_delegate']) )
		{
			$openid_html = <<<_HTML_
	<link rel="openid.server" href="$options[openid_server]" />
	<link rel="openid.delegate" href="$options[openid_delegate]" />
_HTML_;
		}
		$time = time();

		/* for description htmlspecialchars */
		$description = htmlspecialchars($description);
		$keywords = htmlspecialchars($keywords);

		echo <<<_HTML_
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>$title</title>
	<meta name="keywords" content="$keywords" />
	<meta name="description" content="$description" />
	<meta name="author" content="$author" />
$rss_html
$refresh_html
$openid_html
	<link rel="shortcut icon" href="$asset_url_favicon" type="image/icon" />
	<script type="text/javascript">window.ServerTime=$time;</script>
	<script>var current_user_id = '$current_user_id';</script>
	<script>var current_anonymous_user = $current_anonymous_user;</script>
_HTML_;
		if(empty($options['is_load_all']))
			echo <<<_HTML_
	<link href="$asset_url_css" media="screen, projection" rel="Stylesheet" type="text/css" />
				<link href="$asset_url_css_box" media="screen, projection" rel="Stylesheet" type="text/css" />
				<script type="text/javascript" src="$asset_url_js_moo"></script>
				<script type="text/javascript" src="$asset_url_js_jiwai"></script>
				<script type="text/javascript" src="$asset_url_js_buddy"></script>
				<script type="text/javascript" src="$asset_url_js_location"></script>
				<script type="text/javascript" src="$asset_url_js_validator"></script>
				<script type="text/javascript" src="$asset_url_js_box"></script>
				<script type="text/javascript" src="$asset_url_js_action"></script>
_HTML_;
		else if('false'==$options['is_load_all'])
		{
			echo <<<_HTML_
	<link href="$asset_url_css" media="screen, projection" rel="Stylesheet" type="text/css" />
	<script language="javascript">AC_FL_RunContent = 0;</script><script type="text/javascript" src="$asset_url_js_ac_content"></script>
_HTML_;
//			echo "<script language=\"javascript\">AC_FL_RunContent = 0;</script><script type=\"text/javascript\" src=\"$asset_url_js_ac_content\"></script>";
		}
		else if('g'==$options['is_load_all'])
		{
			$asset_css_index = JWTemplate::GetAssetUrl('/css/index.css');
			$asset_css_main = JWTemplate::GetAssetUrl('/css/main.css');
			$asset_js_index = JWTemplate::GetAssetUrl('/js/index.js');
			echo <<<_HTML_
	<link href="$asset_css_index" media="screen, projection" rel="Stylesheet" type="text/css" />
	<link href="$asset_css_main" media="screen, projection" rel="Stylesheet" type="text/css" />
	<script type="text/javascript" src="$asset_js_index"></script>
_HTML_;
		}
		?>

	<link rel="start" href="<?php echo JW_SRVNAME;?>" title="叽歪网首页" />
	<link rel="search" href="<?php echo $asset_url_os_statuses; ?>" title="叽歪搜索" type="application/opensearchdescription+xml" />
	<link rel="search" href="<?php echo $asset_url_os_users; ?>" title="叽歪成员搜索" type="application/opensearchdescription+xml" />
	<meta name="ICBM" content="40.4000, 116.3000" />
	<meta name="DC.title" content="叽歪网" />
	<meta name="copyright" content="copyright 2007-<? echo date("Y");?> 叽歪网 <?php echo JW_SRVNAME;?>" />
	<meta name="robots" content="all" />
<?php
	echo $ui_css;
	}


	static public function accessibility()
	{
?>


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


<?php
	}


	static public function header($highlight=null)
	{
		$userInfo = JWUser::GetCurrentUserInfo();
		$is_anonymous = false==empty($userInfo) && JWUser::IsAnonymous($userInfo['id']);
		$nameScreen = @$userInfo['nameScreen'];
		$nameUrl = @$userInfo['nameUrl'];
		if ( empty($nameScreen) ) {
			$nav = array(
				'/' => '首页',
				'/public_timeline/' => '逛逛',
				'/wo/account/create' => '注册',
				'/wo/login' => '登录',
				'http://help.jiwai.de/' => '帮助'
			);
			$nameScreen = '游客';
			$nameUrl = 'public_timeline';
		} else {
			$nav = array(
				'/wo/' => '首页',
				'/public_timeline/' => '逛逛',
				'/wo/gadget/' => '窗可贴',
				'/t/帮助留言板/' => '留言板',
			);
		}
		if ( $is_anonymous )
		{
			$nav = array(
				'/wo/' => '首页',
				'/public_timeline/' => '逛逛',
				'/wo/account/create' => '注册',
				'http://help.jiwai.de/' => '帮助',
			);
		}

		$highlightAlias  = array(
			'/wo/account/notification' => '/wo/account/settings',
			'/wo/account/profile' => '/wo/account/settings',
			'/wo/devices/' => '/wo/account/settings',
			'/wo/account/profile_settings' => '/wo/account/settings',
			'/wo/account/metting' => '/wo/account/settings',
			'/wo/openid/' => '/wo/account/settings',
		);

		if (null==$highlight) 
		{
			$a = array_reverse($nav);
			$urlNow = $_SERVER['REQUEST_URI'];
			$urlNow = ( $pos = strpos($urlNow, '?') ) ? substr($urlNow, 0, $pos) : $urlNow;
			foreach ($highlightAlias as $u=>$aurl) 
			{
				if ( 0===strncasecmp($u,$urlNow,strlen($u)))
				{
					$urlNow=$aurl;
					break;
				}
			}
			foreach ($a as $url => $txt)
			{
				if (substr($urlNow, 0, strlen($url))==$url) 
				{ 
					$highlight = $url;
					break;
				}
			}
			if ( null==$highlight && empty($nameScreen) )
			{
				$highlight = '/public_timeline/'; //$url;
			}
		}

		if( empty( $userInfo ) ) 
		{
			$msgString = '';
		}
		else
		{
			$msgCount = JWMessage::GetMessageStatusNum($userInfo['id'], JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD) ;
			$msgString = '<a style="padding-left:0px;" href="/wo/direct_messages/">';
			$msgString .= (0==$msgCount) ? '<img style="margin-bottom:-4px;" src="'.self::GetAssetUrl('/images/icon_unread_bw.gif').'">' : '<img style="margin-bottom:-4px;" src="'.self::GetAssetUrl('/images/icon_unread.gif').'">('.$msgCount.')';
			$msgString .= '</a>';
		}

?>
<div id="header">
	<div id="navigation">
		<h2><a class="header" href="<?php echo JW_SRVNAME;?>">叽歪网</a></h2>
		<div id="navtip" class="navtip">
		<form id='f3' action="<?php echo JW_SRVNAME . '/wo/search/users'; ?>" style="display:inline;">
		<table class="navtip_table"><tr>
			<td valign="middle">你好，<a class="normLink" href="<?php echo JW_SRVNAME . '/'. $nameUrl . '/';?>"><?php echo $nameScreen;?></a>
			<?php 
			if ( $is_anonymous )
			{
				echo '|<a class="normLink" href="'.JW_SRVNAME.'/wo/login">登录</a>|<a class="normLink" href="'.JW_SRVNAME.'/wo/logout">退出</a>';
			}
			else
			{
				echo $msgString;
				if($nameUrl != 'public_timeline') echo '|<a class="normLink" href="'.JW_SRVNAME.'/wo/account/settings/">设置</a>|<a class="normLink" href="'.JW_SRVNAME.'/wo/logout">退出</a>'; 
				}
			?>
			</td>
			<td valign="middle"><input type="text" name="q" value="QQ、Email、姓名" onClick="if(this.value=='QQ、Email、姓名')this.value=''" onBlur="if(this.value=='')this.value='QQ、Email、姓名';" class="input"/></td>
			<td valign="middle"><input type="button" value="找朋友" class="submit" onClick="$('f3').submit();"/></td>
		</tr></table>
		</form>
		</div>
		<div style="clear:both;"></div>
		<ul>
<?php foreach ($nav as $url => $txt) { ?>
		<li>
			<div class="line1"></div>
			<div class="nav"><a href="<?php echo substr($url,0,1)=='/' ? JW_SRVNAME.$url : $url; ?>" <?php echo ($highlight==$url) ? 'class="active"' : ''; ?>><?php echo $txt; ?></a></div>
		</li>
<?php } ?>
		</ul>
	</div>
</div>
<?php
	}

	static public function SettingTab($highlight=null){
			$nav = array(
				'/wo/account/settings' => '帐号&amp;密码',
				'/wo/account/profile' => '个人资料',
				'/wo/devices/sms' => '手机',
				'/wo/devices/im' => '聊天软件',
				'/wo/account/notification' => '系统通知',
				'/wo/account/profile_settings' => '配色方案',
				'/wo/openid/' => 'OpenID',
				'/wo/bindother/' => '绑定Twitter',
				);

		if (!$highlight) {
			$a = array_reverse($nav);
			$urlNow = $_SERVER['REQUEST_URI'];
			foreach ($a as $url => $txt) if (substr($urlNow, 0, strlen($url))==$url) { $highlight = $url; break; }
			if (!$highlight) $highlight = '/wo/account/settings'; //$url;
		}
?>

<div id="settingsNav" class="subtab">
<?php foreach( $nav as $url=>$text ) { ?>
	<a href="<?php echo $url;?>" <?php echo ($highlight==$url) ? 'class="active"' : ''; ?>><?php echo $text;?></a>
<?php } ?>
</div>
<?php
	}

	static public function FriendsTab( $idUser, $highlight=null ){
		$userInfo = JWUser::GetUserInfo( $idUser );
		$wo = preg_match( '/^\/wo\//', $_SERVER['REQUEST_URI'] );

		$followingsNum = JWDB_Cache_Follower::GetFollowingNum( $idUser );
		$followersNum = JWDB_Cache_Follower::GetFollowerNum( $idUser );
		$inRequestsNum = JWFollowerRequest::GetInRequestNum( $idUser );
		$outRequestsNum = JWFollowerRequest::GetOutRequestNum( $idUser );

		if( $highlight == 'search' ) {
			global $q , $searched_num;
			$nav = array(
					'search' => array("javascript:void(0);", "共找到符合\"$q\"的用户", $searched_num),
			);
		}else {
			if( $wo ) {
				$nav = array(
					'friends' => array("/wo/followings/", "你关注的人", $followingsNum),
					'followers' => array("/wo/followers/","关注你的人", $followersNum),
					'inrequests' => array("/wo/friend_requests/","待审核关注",$inRequestsNum),
					'outrequests' => array("/wo/friend_requests/?out","发出的关注请求",$outRequestsNum),
				);
			}else{
				$nav = array(
					'friends' => array("../followings/", "此人关注的人", $followingsNum),
					//'followers' => array("../followers/","关注此人的人", $followersNum),
				);
			}
		}

		if( !$highlight ) 
			$highlight = 'friends';
?> 
		<p class="subtab">
		<?php foreach ($nav as $k=>$v ){ 
			echo "<a href=\"".$v[0]."\" ".($k==$highlight?"class=\"active\"":"").">".$v[1]."(".$v[2].")</a>";
		}?>
		</p>
<?php
	}


	static public function slogon()
	{
?>
<div class="separator">
	<div class="note1">
	<h2>一句话博客</h2>
	<p>用只言片语串成生活轨迹</p>

	</div>
	<div class="note2">
	<h2>分享与沟通</h2>
	<p>关心朋友的一举一动</p>
	</div>
	<div class="note3">
	<h2>贴身叽歪</h2>

	<p>手机/QQ/MSN/GTalk/Skype</p>
	</div>
</div>
<?php
	}

	static public function container_ending()
	{
		echo '<div style="overflow:hidden; clear:both; height:7px; line-height:1px; font-size:1px;"></div>';
	}

	static public function footer()
	{
?>

<div id="footer">
	<h3>Footer</h3>
	<ul>
		<li class="first">&copy; 2007-<? echo date("Y");?> 叽歪网</li>
		<li><a href="<? echo JW_SRVNAME;?>/wo/about/jiwai" target="_blank">关于我们</a></li>
		<li><a href="<? echo JW_SRVNAME;?>/wo/about/joinus" target="_blank">加入我们</a></li>
		<li><a href="http://help.jiwai.de/MediaComments" target="_blank">媒体和掌声</a></li>
		<li><a href="http://blog.jiwai.de/" target="_blank">Blog</a></li>
		<li><a href="http://help.jiwai.de/Api" target="_blank">API</a></li>
		<li><a href="http://help.jiwai.de/" target="_blank">帮助</a></li>
		<li><a href="http://jiwai.de/wo/feedback/" onClick="return JWAction.redirect(this);" target="_blank">反馈</a></li>
		<li><a href="<? echo JW_SRVNAME;?>/wo/about/partner" target="_blank">友情链接</a></li>
	</ul>
	<ul>
		<li><a href="http://www.miibeian.gov.cn" target="_blank">京ICP备07024804号</a></li>
	</ul>
</div>

<?php
		JWTemplate::GoogleAnalytics();

	}

	static public function footer2()
	{
?>
<div id="footer">
	<h3>Footer</h3>
		<span >&copy; 2007-<? echo date("Y");?> 叽歪网&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<a href="<? echo JW_SRVNAME;?>/wo/about/jiwai" target="_blank">关于我们</a>
		<a href="<? echo JW_SRVNAME;?>/wo/about/joinus" target="_blank">加入我们</a>
		<a href="http://blog.jiwai.de/" target="_blank">Blog</a>
		<a href="http://help.jiwai.de/Api" target="_blank">API</a>
		<a href="http://help.jiwai.de/" target="_blank">帮助</a>
		<a href="<? echo JW_SRVNAME;?>/wo/about/partner" target="_blank">友情链接</a>
		<a href="http://www.miibeian.gov.cn" target="_blank">京ICP备07024804号</a>
</div>

<?php
		JWTemplate::GoogleAnalytics();
	}

	static public function footer3()
	{
?>
<div class="footer">
		<span >&copy; 2007-<? echo date("Y");?>&nbsp;叽歪网版权所有</span><a href="http://www.miibeian.gov.cn" target="_blank" title="京ICP备07024804号" class="grey">京ICP备07024804号</a>
</div>

<?php
		JWTemplate::GoogleAnalytics();
	}

	static public function GoogleAnalytics()
	{
		echo <<<_HTML_
<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">

setTimeout("run_google();", 100);

function run_google() 
{
	if (!window.urchinTracker) {
		setTimeout(run_google, 500);
		return;
	}

	_uacct = "UA-2771171-2";
	_uOsr[24]="iask"; _uOkw[24]="k";
	_uOsr[25]="sogou"; _uOkw[25]="query";
	_uOsr[26]="qihoo"; _uOkw[26]="kw";
	_uOsr[27]="daqi"; _uOkw[27]="content";
	_uOsr[28]="soso.com"; _uOkw[28]="w";
	_uOsr[29]="baidu"; _uOkw[29]="wd";
	_uOsr[30]="3721"; _uOkw[30]="name";
	_uOsr[31]="baidu"; _uOkw[31]="word";
	_uOsr[32]="qq.com"; _uOkw[32]="w";
	urchinTracker();
}
</script>
_HTML_;
	}


	static public function updater($options=null)
	{
		if ( empty($options['title']) )
			$title = '这一刻，你在做什么？';
		else
			$title = $options['title'];
		$mode = ( empty($options['mode']) ) ? 0 : $options['mode']; //0:status 1:direct message
		switch($mode)
		{
			case 1:
				$formAction = '/wo/direct_messages/create';
				break;
			case 2:
				$formAction = '';
				break;
			case 0:
			default:
				$formAction = '/wo/status/update';
		}
?>
		<div id="jwupdate">
			<form action="<?php echo $formAction; ?>" id="updaterForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
				<h2>
					<span class="tip">还可输入：<span class="counter" id="status-field-char-counter">140</span> 个字符</span><?php echo $title;?><?php if(isset($options['friends'])) { ?>给：<select name="user[id]" class="jwselect" id="user_id"> <?php
foreach ($options['friends'] as $id => $f) echo <<<_HTML_
<option value="$id">$f[nameScreen]</option>
_HTML_;
?></select>
					<?php } ?>
				</h2>
				<p>
                    <input type="hidden" id="idUserReplyTo" name="idUserReplyTo"/>
                    <input type="hidden" id="idStatusReplyTo" name="idStatusReplyTo"/>
					<textarea name="jw_status" rows="3" id="jw_status" onkeydown="if(this.value.length>0 && ((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83))){JWAction.updateStatus(); return false;}" onkeyup="updateStatusTextCharCounter(this.value)" onblur="updateStatusTextCharCounter(this.value)" value=""></textarea>
				</p>
				<p class="act">
					<span class="ctrlenter">Ctrl+Enter直接叽歪</span>
					<input style="margin-left:115px;" type="button" class="submitbutton" onclick="if($('jw_status').value.length>0){return JWAction.updateStatus();}else{$('jw_status').focus();}" value="叽歪一下" title="叽歪一下"/>
				</p>	
			<?php
				if(false == empty($options['sendtips']))
				{
			$idCurrent = JWLogin::GetCurrentUserId();
			$ValidNums = JWTemplate::GetSmsAndImValidNums($idCurrent);
			$imicoUrlSms = "/wo/devices/sms";
			$imicoUrlIm = "/wo/devices/im";
			echo '<p style="margin-left:-8px;">';
			if(0 >= $ValidNums[0])
			{
				echo '<a class="sendtips" href="'. JW_SRVNAME . $imicoUrlSms . '">用手机SMS来叽歪 ！</a>';
			}
			if(0 >= $ValidNums[1])
			{
				echo '<a class="sendtips" href="'. JW_SRVNAME . $imicoUrlIm . '">用QQ/MSN也能叽歪 ？</a>';
			}
			echo '</p>';
				}
			?>	
			</form>
				 </div>
<script type="text/javascript">
	<?php if( 2 != $mode ) echo "$('jw_status').focus();"?>
	function updateStatusTextCharCounter(value) {
		len_max = 140;
		if (len_max - value.length >= 0) {
			$('status-field-char-counter').innerHTML = len_max - value.length;
		} else {
			$('status-field-char-counter').innerHTML = 0;
			<?php /*
			var ov = $('status').value;
			var nv = ov.substring(0, len_max);
			if( len_max == 70 ) {
				var max_nv = ov.substring(0, ++len_max);
				while( getStatusTextCharLengthMax( max_nv ) == 140 ) {
					nv = max_nv;
					max_nv = ov.substring(0, ++len_max);
				}
			}
			//$('status').value = nv;  //not cut for bug
			*/?>
		}
	};
</script>
<?php
	}


	/*
	 *	显示 tab 方式的列表
	 *
	 *	@param	array	$menuArr	菜单数据，结构如下：
	 *	array ( 'menu1' => array ( 'active'=true, 'url'='/' ), 'menu2'=>array(...), ... );
	 */
	static public function tab_menu( $menuArr, $menuTips=null )
	{
		echo <<<_TAB_
<div id="wtTableMenu">
	<div class="left"></div>
	<div class="middle">
_TAB_;
		foreach ( $menuArr as $menu => $options )
		{
			$name 		= $options['name'];
			$url 		= $options['url'];
			$is_active	= $options['active'];

			/* custom menu */
			if('joke'===$menu) {
				echo "<a href = '$url' style='color:#0000FF;' class='active'>$name</a>";continue;
			}
			if('contribute'===$menu) {
				echo "<a href = '$url' style='color:#FF0000;' class='active'>$name</a>";continue;
			}
			/* custom menu end */

			if ( $is_active )
				echo "<a href = '$url' class = 'active' ";
			else
				echo "<a href = '$url' ";

			echo ">$name</a>\n";
		}

		echo $menuTips==null ? null : "<h2>$menuTips</h2>\n";
		echo <<<_TAB_
	</div>
	<div class="right"></div>
</div>
_TAB_;
	}


	static public function tab_header( $vars=array() )
	{
	}

	static public function StatusHead( $userRow, $statusRow, $options=null )
	{
		$name_screen = $userRow['nameScreen'];
		$name_url = $userRow['nameUrl'];
		$name_full = $userRow['nameFull'];

		$noneStatus = empty( $statusRow );

		if ( $noneStatus || null==$statusRow['idPicture'] || 'Y'==$statusRow['isMms'] )
		{
			$photo_url = JWPicture::GetUserIconUrl($userRow['id'], 'thumb96');
		}
		else
		{
			$photo_url = JWPicture::GetUrlById($statusRow['idPicture'], 'thumb96');
		}
	
		if ( false == isset($options['trash']) )
			$options['trash'] = true;

		if ( false == isset($options['isMyPages']) )
			$options['isMyPages'] = true;

		$current_user_id = JWLogin::GetCurrentUserId();
		$device = 'WEB';

		$followed = JWFollower::IsFollower($userRow['id'], $current_user_id);
		$protected = JWSns::IsProtected( $userRow, $current_user_id ) 
				|| JWSns::IsProtectedStatus( $statusRow, $current_user_id );

		/** initial **/
		$status = null;
		$isMms = false;

		if ( $noneStatus )
			$status = "到目前为止还没有叽歪过！";
		else if ( $protected ) 
			$status = '我只和我关注的人分享我的叽歪。';

		if ( null == $status )
		{
			$status_id = $statusRow['idStatus'];
			$status = $statusRow['status'];
			$timeCreate = $statusRow['timeCreate'];
			$sign = $statusRow['isSignature'] == 'Y' ? '签名' : '';
			$device = $statusRow['device'];
			$device = JWDevice::GetNameFromType($device, @$statusRow['idPartner']);
		
			$duration = JWStatus::GetTimeDesc($timeCreate);

			$status_result = JWStatus::FormatStatus($statusRow);
			$status = $status_result['status'];
			$replyto = $status_result['replyto'];
			$replytoname = $status_result['replytoname'];

			$isMms = ( @$statusRow['isMms'] == 'Y') ;
		}

?>
			<div id="permalink" style="margin-bottom:8px!important;margin-bottom:0px;">
				<div class="odd" style="padding-top:0; padding-left:0; padding-right:0; background: none;">
					<div class="head"><a href="
					<?php echo true == $options['isMyPages'] ? '/wo/account/profile_image/' . $name_screen : '/' . $name_url ?>/"><img title="<?php echo $name_full; ?>" src="<?php echo $photo_url?>" width="96" height="96" style="padding: 1px;" /></a></div>
					<div class="cont">
						<div class="bg"></div>
<?php
	if ( $followed ) 
	{
		$follow_string = "已关注";
	} else 
	{
		$oc = 'onclick="return JWAction.follow('.$userRow['id'].', this);"';

		if( false == JWBlock::IsBlocked( $userRow['id'], $current_user_id ) ) {
			$follow_string = "<a href=\"/wo/followings/follow/$userRow[id]\" $oc>关注此人</a>";
		}
		else
		{
			$follow_string = null;
		}
	}
?>

<span class="floatright" style="font-size:12px;color:#999;"><?php echo $follow_string;?></span>
<h3><?php echo $name_screen;?></h3>
	<p class="t-text"><?php echo $status;?></p>
<?php
if( false == $protected && false == $noneStatus )
{ 
	/* for plugins display */
	$plugin_result = JWPlugins::GetPluginResult( $statusRow );
	switch( $plugin_result['type'] )
	{
		case 'html':
			echo $plugin_result['html'];
			break;
	}
	/* end plugins */

	$reply_user_row = ( $statusRow['idUserReplyTo'] ) ?
		JWUser::GetUserInfo( $statusRow['idUserReplyTo'] ) : null;

	if ($userRow['id'] != $current_user_id)
		$reply_user_nameScreen_txt = '@' .$userRow['nameScreen']. ' ';
	else
		$reply_user_nameScreen_txt = '';

	$replyLinkClick = ( $options['isMyPages'] ? 
		'' : 'javascript:scroll(0, screen.height);$("idUserReplyTo").value=' .$statusRow['idUser']. ';$("idStatusReplyTo").value=' .$statusRow['id']. ';$("jw_status").focus();$("jw_status").value="' .$reply_user_nameScreen_txt. '";return false;' );

	self::ShowStatusMetaInfo($statusRow, array(
		'showPublisher' => false,
		'replyLinkClick' => $replyLinkClick,
	));
}
?>
			</div><!-- cont -->
		   </div><!-- odd -->
		 </div><!-- permalink -->	

<?php
	}

	/*
	 * 	显示删除的图标和操作
 	 *
	 */
	static public function TrashAction($idStatus, $options=array())
	{

		$asset_trash_alt = '删除';
		$asset_trash_alt2 = '';
//		$asset_trash_title = '删除';
		$asset_trash_url		= self::GetAssetUrl("/img/icon_trash.gif");

		$html_str = <<<_HTML_
	<a href="javascript:void(0);" onclick="JiWai.DoTrash($idStatus);" title="$asset_trash_alt"><img border="0" src="$asset_trash_url" />$asset_trash_alt2</a>
_HTML_;
		if( @$options['isMms'] ) {
			$html_str = <<<_HTML_
	<a href="javascript:void(0);" onclick="JiWai.DoTrash($idStatus);" title="$asset_trash_alt" class="del">$asset_trash_alt</a>
_HTML_;
		}

		return $html_str;
	}


	/*
	 *	显示 favourite 的星星，配合 JWFavourite 进行 Ajax 收藏操作。
	 *	@param	switch	当前是否已经被收藏, true为已经收藏，false为没有收藏
	 */
	static public function FavouriteAction($idStatus, $isFav=false)
	{
		$asset_throbber_url		= self::GetAssetUrl("/img/icon_throbber.gif");
		$asset_star_alt2 = '';

		if ( $isFav )
		{
			$asset_star_alt = '不收藏';
			//$asset_star_title = '已收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_full.gif");
			$ajax_url		= "/wo/favourites/leave/$idStatus";
		}
		else
		{
			$asset_star_alt = '收藏它';
			//$asset_star_title = '未收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_empty.gif");
			$ajax_url		= "/wo/favourites/create/$idStatus";
		}
		$html_str = <<<_HTML_
		<a href="javascript:void(0);" onclick="JiWai.ToggleStar($idStatus); return false;" title="$asset_star_alt"><img id="status_star_$idStatus" border="0" src="$asset_star_url" /><span id="status_star_text_$idStatus">$asset_star_alt2</span></a>
_HTML_;

		return $html_str;
	}


	/*
	 * 公共函数，显示 timeline list
	 * @param 	array	$statusIds	array(1,2,3);
	 * @param 	array	$userRows	user[id]=row
	 * @param 	array	$statusRows	status[id]=row
	 * @param	array	$options	array ( 'icon' => true ) 
	 * @return	
	 */
	static public function Timeline($statusIds, $userRows, $statusRows, $options=array() )
	{
		
		if ( empty($statusIds) || empty($userRows) || empty($statusRows) ) ;

		$current_user_id = JWLogin::GetCurrentUserId();
		$is_admin = JWUser::IsAdmin($current_user_id);
		$is_anonymous = JWUser::IsAnonymous($current_user_id);

		if ( !isset($options['pagination']) )
			$options['pagination'] 	= false;
		if ( !isset($options['search']) )
			$options['search'] 	= false;
		if ( !isset($options['icon']) )
			$options['icon'] 	= true;
		if ( !isset($options['trash']) )
			$options['trash'] 	= true;
		if ( !isset($options['uniq']) )
			$options['uniq']	= 0;
		if ( !isset($options['nummax']) )
			$options['nummax']	= 0;
		if ( !isset($options['protected']) )
			$options['protected']	= false;
		if ( !isset($options['strip']) )
			$options['strip']	= false;
		if ( !isset($options['isMms']) )
			$options['isMms']	= false;

		if( $options['protected'] ) return;

		$is_favourited_array = JWFavourite::IsFavourited($current_user_id, $statusIds);

		$current_user_id = JWUser::GetCurrentUserInfo('id');
		if (!$options['strip']) {
?>
<div id="wtTimeline">
<?php
		}
		$n=0;
		$user_showed = array();

		foreach ( $statusIds as $status_id ){
			if( !isset($statusRows[$status_id]) )
				continue;

			$user_id 	= $statusRows[$status_id]['idUser'];
			$conference_id = $statusRows[$status_id]['idConference'];

			$can_delete = ($is_admin || $user_id==$current_user_id) && false==$is_anonymous;
			$is_favourited = $is_favourited_array[$status_id];

			if ( JWSns::IsProtectedStatus( $statusRows[$status_id], $current_user_id ) )
				continue;

			// 最多显示的条数已经达到
			if ( $options['nummax'] && $n >= $options['nummax'] )
				break;
			// 如果设置了一个用户只显示一条，则跳过
			if ( $options['uniq']>0 && @$user_showed[$user_id]>=$options['uniq'] )
				continue;
			else
				@$user_showed[$user_id] += 1;

			if ( false == isset($userRows[$user_id]) )
				$userRows[$user_id] = JWUser::GetUserInfo( $user_id );
				
			$name_screen = $userRows[$user_id]['nameScreen'];
			$name_url = $userRows[$user_id]['nameUrl'];
			$name_full = $userRows[$user_id]['nameFull'];
			$status = $statusRows[$status_id]['status'];
			$timeCreate = $statusRows[$status_id]['timeCreate'];
			$device = $statusRows[$status_id]['device'];
			$idPartner = @$statusRows[$status_id]['idPartner'];
			$reply_id = $statusRows[$status_id]['idStatusReplyTo'];
			$sign = ( $statusRows[$status_id]['isSignature'] == 'Y' ) ?  '签名' : '';
		
			$reply_num = JWDB_Cache_Status::GetCountReply( $status_id );

			$duration = JWStatus::GetTimeDesc($timeCreate);

			if ( $statusRows[$status_id]['isMms'] == 'Y' ) 
			{
				$photo_url = JWPicture::GetUserIconUrl( $statusRows[$status_id]['idUser'], 'thumb48' );
			}
			else if ( !empty($statusRows[$status_id]['idPicture']) ) 
			{
				$photo_url = JWPicture::GetUrlById($statusRows[$status_id]['idPicture']);
			}
			else 
			{
				$photo_url = JWTemplate::GetAssetUrl('/images/org-nobody-48-48.gif');
			}

			$plugin_result = JWPlugins::GetPluginResult( $statusRows[$status_id] );
	
			$deviceName = JWDevice::GetNameFromType($device, @$statusRows[$status_id]['idPartner'] );

			$formated_status = JWStatus::FormatStatus($statusRows[$status_id]);

			$replyto = $formated_status['replyto'];
			$replytoname = $formated_status['replytoname'];
			$status = $formated_status['status'];

			if ($n) { //分割线
?>
<div class="line"></div>
<?php
			}
			$n++;

?>
<div class="odd" id="status_<?php echo $status_id;?>">
	<div class="head"><a href="/<?php echo $name_url;?>/" rel="contact"><img icon="<?php echo $user_id;?>" class="buddy_icon" width="48" height="48" title="<?php echo $name_full; ?>" src="<?php echo $photo_url?>"/></a></div>
	<div class="cont">
		<div class="bg"></div><?php echo $status; ?><br/>
		<?php

			//plugins
			switch( $plugin_result['type'] )
			{
				case 'html':
					echo $plugin_result['html'];
				break;
			}

			$meta_options = $options;

			$meta_options['can_delete'] = $can_delete;
			$meta_options['is_favourited'] = $is_favourited;

			//meta_info
			$reply_user_row = ( $statusRows[$status_id]['idUserReplyTo'] ) ?
				JWUser::GetUserInfo( $statusRows[$status_id]['idUserReplyTo'] ) : null;
			self::ShowStatusMetaInfo( $statusRows[$status_id], $meta_options );
		?>
	</div><!-- cont -->
</div><!-- odd -->
<?php 
		}
		if ($options['search'] || ( $options['pagination'] && ( $options['pagination']->IsShowNewer() || $options['pagination']->IsShowOlder() ) ) ) {
?>
<div class="line"></div>
<div class="add">
<?php
			if ($options['search']) {
				global $q;
?>
<div class="search" style="width:300px;">
	<form action="/wo/search/statuses" method="GET" id="search_status"><input type="text" name="q" value="<?php echo (isset($q)) ? $q : '输入关键词';?>" onclick='this.value=(this.value=="输入关键词")?"":this.value;' style="width:200px;"/><button onClick='$("search_status").submit();'>搜</button></form>
</div>
<?php
			}
			if ($options['pagination']) {
				static $pages = 4;
				$l = $options['pagination']->GetPageNo() - $pages;
				if ($l<1) $l = 1;
				$r = $l + $pages*2;
				if ($r>$options['pagination']->GetOldestPageNo()) $r = $options['pagination']->GetOldestPageNo();
?>
<div class="pages">
<?php
for ($i=$l;$i<$r+1;$i++) {
	$u = $i == $options['pagination']->GetPageNo() ? '' : JWPagination::BuildPageUrl($_SERVER['REQUEST_URI'], $i);
	if ($u) 
		echo <<<__HTML__
<a href="$u" class="normLink">$i</a>
__HTML__;
	else echo <<<__HTML__
<a style="background:#fff; color:#000;">$i</a>
__HTML__;
}
?>
</div>
<?php
			}
?>
<div style="clear:both;"></div>
</div>
<?php
		} 
?>
</div><!-- wtTimeline -->
<?php
	}

	static public function ShowStatusMetaInfo( $status_row, $options=array()) 
	{
		if( empty( $status_row ) )
			return;

		$showPublisher = isset( $options['showPublisher'] ) ? $options['showPublisher'] : true;
		$replyLinkClick = isset( $options['replyLinkClick'] ) ? $options['replyLinkClick'] : null;
		$isInTag = isset( $options['isInTag'] ) ? $options['isInTag'] : false;

		$current_user_id = JWLogin::GetCurrentUserId();

		$owner_user = JWDB_Cache_User::GetDbRowById( $status_row['idUser'] );
		$owner_user_url = UrlEncode($owner_user['nameUrl']);
		$owner_user_screen = $owner_user['nameScreen'];

		$status_id = $status_row['id'];
		$conf_id = $status_row['idConference'];
		$reply_status_id = $status_row['idStatusReplyTo'];
		$pre_reply_status_id = $reply_status_id;
		$reply_user_id = $status_row['idUserReplyTo'];
		$thread_id = $status_row['idThread'];
		$tag_id = $status_row['idTag'];
		$tag_row = empty($tag_id) ? null : JWDB_Cache_Tag::GetDbRowById( $tag_id );
		$tag_name = empty($tag_row) ? null : $tag_row['name'];

		$reply_user = null;
		$thread_user = null;

		$reply_name_url = null;
		$reply_name_screen = null;

		$reply_count = $thread_id ? 0 : JWDB_Cache_Status::GetCountReply( $status_id );

		$conf_link_string = '';
		if( $conf_id )
		{
			$conf_info = JWConference::GetDbRowById( $conf_id );
			$conf_user_info = JWDB_Cache_User::GetDbRowById( $conf_info['idUser'] );
			$conf_link = "/$conf_user_info[nameUrl]/";
			$conf_link_string = "在$conf_user_info[nameScreen]";
		}

		if( $reply_user_id ) 
		{
			if( $reply_user = JWDB_Cache_User::GetDbRowById( $reply_user_id ) )
			{
				$reply_name_url = $reply_user['nameUrl'];
				$reply_name_screen = $reply_user['nameScreen'];
			}
		}

		if( $thread_id ) {
			if( $thread_status = JWDB_Cache_Status::GetDbRowById( $thread_id ) ) 
			{
				if( $thread_user = JWDB_Cache_User::GetDbRowById( $thread_status['idUser'] ) )
				{
					$reply_status_id = $thread_id;
					$reply_name_url = $thread_user['nameUrl'];
					if( null == $reply_name_screen )
					{
						$reply_name_screen = $thread_user['nameScreen'];
					}
					// For Conference | In Conference wont reply to conference User;
					if( $reply_user == null ) {
						$reply_user = $thread_user;
						$pre_reply_status_id = $thread_id;
					}
				}
			}
		}

		$yuString = $showPublisher ? '' : '';
		$mmsString = $status_row['isMms'] == 'Y' ?  '拍摄于' : $yuString;
		
		$action_string = null;
		if ( $current_user_id && $status_id )	
		{
			$is_fav	= isset($options['is_favourited']) ? 
				$options['is_favourited'] : JWFavourite::IsFavourite($current_user_id, $status_id);

			$action_string .= self::FavouriteAction($status_id, $is_fav);

			$can_delete = isset($options['can_delete']) ?
				$options['can_delete'] : JWStatus::IsUserCanDelStatus($current_user_id, $status_id);

			if ( $can_delete )
			{
				$action_string .= self::TrashAction($status_id);
			}
		}

		$timeCreate = $status_row['timeCreate'];
		$timeDesc = JWStatus::GetTimeDesc( $timeCreate );
		$deviceName = JWDevice::GetNameFromType($status_row['device'], @$status_row['idPartner'] );
		$sign = $status_row['isSignature'] == 'Y' ? '签名' : '';

		$preg_reply_link = null;
		if( $reply_name_screen ) 
		{
			if ( $isInTag && null != $tag_id )
				$replyLink = "/t/$tag_name/thread/$reply_status_id/$status_id";
			else
				$replyLink = "/$reply_name_url/thread/$reply_status_id/$status_id";

			$replyLinkString = "给${reply_name_screen}的回复";
			if( $pre_reply_status_id ) 
				$pre_reply_link = "/$reply_user[nameUrl]/statuses/$pre_reply_status_id";
			else
				$pre_reply_link = "/$reply_user[nameUrl]/";

		}else if( null == $thread_id ) 
		{
			$reply_url_pre = $isInTag ? "t/$tag_name" : $owner_user_url;
			if( $reply_count ) 
			{
				$replyLink = "/$reply_url_pre/thread/$status_id/$status_id";
				$replyLinkString = "${reply_count}条回复";
			}else
			{
				$replyLink = "/$reply_url_pre/thread/$status_id/$status_id";
				$replyLinkString = "回复";
			}
		}
		$pre_reply_link_string = $replyLinkString;
		$replyLinkString = '回复';
		if( $reply_count )
			$replyLinkString = $reply_count.'条回复';

		$replyLinkClickString = null;
		if( $replyLinkClick ) {
			$replyLink = "javascript:void(0);";
			$replyLinkClickString = "onClick='$replyLinkClick'";
		}

		$owner_string = null;
		if( $showPublisher ) {
			$owner_string = "<a class=\"normLink\" href=\"/$owner_user_url/\">$owner_user_screen</a>";
		}

?>
			<span class="meta">
				<span class="floatright">
					<span class="reply"><a <?php echo $replyLinkClickString;?> href="<?php echo $replyLink; ?>" title="<?php echo $timeCreate; ?>"><?php echo $replyLinkString;?></a></span>
					<span id="status_actions_<?php echo $status_id?>">
						<?php echo $action_string;?>
					</span>
				</span>
				<?php echo $owner_string;?><a class="darkLink" title="<?php echo $timeCreate;?>" href="/<?php echo $owner_user_url;?>/statuses/<?php echo $status_id;?>"><?php echo $yuString;?><?php echo $timeDesc?></a>&nbsp;通过&nbsp;<?php echo "$deviceName$sign"?>&nbsp;<?php echo $reply_name_screen ? "<a href='$pre_reply_link' class='darkLink'>$pre_reply_link_string</a>" : '';?>&nbsp;<? echo $conf_id ? "<a href='$conf_link' class='darkLink'>$conf_link_string</a>" : '';?>
			</span><!-- meta -->
<?php
	}

	static public function PaginationLimit( $pagination, $page=1, $url=null, $limit = 4 ) {

		$url = ( empty($url) ) ? $_SERVER['REQUEST_URI'] : $url;

		$l = $pagination->GetPageNo() - $limit;
		if ($l<1) $l = 1;
		$r = $l + $limit*2;
		if ($r>$pagination->GetOldestPageNo()) $r = $pagination->GetOldestPageNo();

		if( $pagination->IsShowOlder() || $pagination->IsShowNewer() ) {
?>
<div class="pages">
<?php
for ($i=$l;$i<$r+1;$i++) {
$u = $i == $pagination->GetPageNo() ? '' : JWPagination::BuildPageUrl($url, $i);
if ($u) 
echo <<<__HTML__
<a href="$u">$i</a>
__HTML__;
else echo <<<__HTML__
<a style="background:#fff; color:#000;">$i</a>
__HTML__;
}
?>
</div>
<div style="clear:both;"></div>
<?
		}
	}


	static public function pagination( $pagination, $qarray=array(), $words = array() )
	{

		if ( empty($pagination) )
		{
			JWLog::LogFuncName(LOG_CRIT,'empty pagination, need fix');
			return;
		}

		/*
		 * 	下面的 utf8 字符无法在 securecrt 里面正常显示，但是是对的
		 *	直接拷贝、黏贴即可（或者用 linux xwin 下面的 term）
最新: «
较新: ‹
较早: ›
最早: »
		 */

		$is_show_newest = $pagination->IsShowNewest();
		$newest_page_no = $pagination->GetNewestPageNo();

		$is_show_newer 	= $pagination->IsShowNewer();
		$newer_page_no	= $pagination->GetNewerPageNo();

		$is_show_older 	= $pagination->IsShowOlder();
		$older_page_no	= $pagination->GetOlderPageNo();

		$is_show_oldest = $pagination->IsShowOldest();
		$oldest_page_no	= $pagination->GetOldestPageNo();

		$prequery = http_build_query($qarray);

		echo <<<_HTML_
				<div class="pagination">
<style type="text/css">
.bl {
font-size:10pt;
padding:5px;
}
</style>

<table cellspacing="0" cellpadding="0" border="0">
<tbody>
<tr>
_HTML_;

		if ( $is_show_newest )
			echo "<td class='bl odd'><a href='?".($prequery ? $prequery."&" : null)."page=$newest_page_no'>".( isset($words['first']) ? $words['first'] : '« 最新' )."</a></td>\n";

		if ( $is_show_newer )
			echo "<td class='bl odd'><a href='?".($prequery ? $prequery."&" : null)."page=$newer_page_no'>".( isset($words['first']) ? $words['pre'] : '‹ 较新' )."</a></td>\n";

		echo '<td class="bl"></td>';

		if ( $is_show_older )
			echo "<td class='bl odd'><a href='?".($prequery ? $prequery."&" : null)."page=$older_page_no'>".( isset($words['first']) ? $words['next'] : '较早 ›' )."</a></td>\n";
		
		if ( $is_show_oldest )
			echo "<td class='bl odd'><a href='?".($prequery ? $prequery."&" : null)."page=$oldest_page_no'>".( isset($words['first']) ? $words['last'] : '最早 »' )."</a></td>\n";

?>
</tr></tbody></table>
<?php
		if (!$options['strip']) {
?>
				</div>

<?php
		}
	}

	static function sidebar_listfollowing($nameUrl="wo", $display=true){
	
		if( $display ) {
			echo <<<_HTML_
<a style="margin:0 10px; text-decoration:none;" href="/$nameUrl/followings/">查看全部</a>
<div style="clear:both;"></div>
_HTML_;
		}
	}


	static function sidebar_block($idUser, $idUserPage){
		if( $idUser == $idUserPage )
			return true;

		if( false == JWBlock::IsBlocked( $idUser, $idUserPage ) ) {
			echo <<<_HTML_
<a style="margin:0 10px; color:#AAA; text-decoration:none;" href="/wo/block/b/$idUserPage">阻止此人</a>
_HTML_;
		}else{
			echo <<<_HTML_
<a style="margin:0 10px; color:#AAA; text-decoration:none;" href="/wo/block/u/$idUserPage">解除阻止</a>
_HTML_;
		}
	}

	/*
	 *	@param	options		focus	=> true	: 是否激活输入焦点到email框
	 *
	 */
	static function sidebar_login($options=null)
	{
		if ( false!==@$options['focus'] )
			$options['focus'] = true;
?>
	<div id="login" class="sidediv">
		<h2>会员登录</h2>
		<form action="/wo/login"  method="post" name="f">
		<table width="100%" border="0" cellspacing="10" cellpadding="0">
  			<tr>
					<td width="50" align="center">用户名</td>
				<td><input type="text" name="username_or_email" id="email" class="logininput"/></td>
			</tr>
 			<tr>
					<td align="center">密　码</td>
					<td><input type="password" name="password" class="logininput"/></td>
  			</tr>
  			<tr>
					<td>&nbsp;</td>
				<td><input type="checkbox" name="remember_me" value="checkbox" id="remember_me" /> 记住我</td>
  			</tr>
  			<tr>
				<td>&nbsp;</td>
				<td><input type="submit" class="submitbutton" value="登录" /></td>
			</tr>
		</table>
		</form>
	</div>
<?php 	
		if ( $options['focus'] ) 
			echo <<<_HTML_
	<script type="text/javascript">
//<![CDATA[
document.getElementById('email').focus()
//]]>
	</script>
_HTML_;

	}

	static function sidebar_register($showLogin=false)
	{
?>
		<div class="register">
		<h2>是第一次来吗？</h2>
		<div style="padding:5px 0 5px 50px; height:35px;"><a class="button" href="/wo/account/create"><img src="<?php echo self::GetAssetUrl('/images/org-text-regnow.gif'); ?>" title="马上注册" /></a></div>
		</div>

<?php
	}

	/*
	 *
	 *	@param	array	$options	$options['title'] = title
									$options['user_ids'] = array(1,2,3,4)
	 *
	 */
	static function sidebar_featured($options)
	{
		if ( empty($options['user_ids']) )
			return;

		$user_ids = $options['user_ids'];

		$activeOrder = isset( $options['activeOrder'] ) ? $options['activeOrder'] : true;

		if ( empty($options['view']) ) $options['view'] = 'large';
		
		if ( !is_array($user_ids) )
		{
			JWLog::LogFuncName("user_ids is not array");
			return;
		}

		if ( isset($options['title']) )
			$title = $options['title'];
		else
			$title = '推荐';

		if ( isset($options['id']) )
			$id = 'id="'.$options['id'].'"';
		else
			$id = 'id="friend"';

		switch ($options['view']) {
			case 'list':
				echo <<<_HTML_
		<div class="headtip"><h2 class="forul">$title</h2></div>
		<ul class="featured newuser">

_HTML_;
				break;
			default:
				echo <<<_HTML_
		<div class="headtip" $id>
			<h2 class="forul">$title</h2>			
		</div>
		<div class="featured" $id>
			<table width="100%" border="0" cellspacing="0" cellpadding="0" class="com">

_HTML_;
				break;
		}

		//$user_db_rows 		= JWDB_Cache_User::GetDbRowsByIds($user_ids);
		$user_db_rows = JWUser::GetDbRowsByIdsAndOrderByActivate($user_ids, 60);
		$picture_ids = JWFunction::GetColArrayFromRows($user_db_rows, 'idPicture');

		$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);
		$n = 0;

		$user_ids = $activeOrder == true ? array_keys( $user_db_rows ) : $user_ids ;

		foreach ( $user_ids as $user_id )
		{
			if( false == isset( $user_db_rows[ $user_id ] ) )
				continue;

			$user_db_row 		= $user_db_rows[$user_id];
			$user_picture_id	= @$user_db_row['idPicture'];

			$user_icon_url		= JWTemplate::GetConst('UrlStrangerPicture');

			if ( $user_picture_id )
				$user_icon_url		= $picture_url_row[$user_picture_id];

			switch ($options['view']) {
				case 'list':
					echo <<<_HTML_
				<li><a href="/$user_db_row[nameUrl]/" title="$user_db_row[nameScreen]" rel="contact" onmouseover="JiWai.ShowThumb($(this).getFirst());" onmouseout="JiWai.HideThumb(this.getFirst());">$user_db_row[nameScreen]<img src="$user_icon_url" class="tip" style="display:none;" title="$user_db_row[nameFull]" width="48" height="48"/></a></li>

_HTML_;
					break;
				default:
					if ($n % 4 == 0) echo "			<tr>\n";
					$name = $user_db_row['nameScreen'];
					//if (mb_strwidth($name)>8) $name = mb_strimwidth($name, 6, '...');
					echo <<<_HTML_
				<td><div><a href="/$user_db_row[nameUrl]/" title="$user_db_row[nameScreen]" rel="contact"><img icon="$user_id" class="buddy_icon" src="$user_icon_url" title="$user_db_row[nameFull]" border="0" />$name</a></div></td>

_HTML_;
					if ($n % 4 == 3) echo "			</tr>\n";
					$n++;
			}
			
		}
		switch ($options['view']) {
			case 'list':
				echo <<<_HTML_
			</ul>

_HTML_;
				break;
			default:
				if ($n % 4) {
					while ($n % 4) {
						$n++;
						echo "				<td></td>\n";
					}
					echo "			</tr>\n";
				} 
				echo <<<_HTML_
			</table>
		</div>
_HTML_;
		}
	}



	/*
	 *
	 *	@param	array	$options	$options['title'] = title
									$options['user_name'] = 'blog'
	 *
	 */
	static function sidebar_announce($options)
	{
		if ( empty($options['title']) )
			$title = '公告';
		else
			$title = $options['title'];

		if( $title == '公告' )
		{
?>
<div class="headtip"><h2 class="forul">公告</h2></div>
<ul class="featured">
<?
			$file_name = FRAGMENT_ROOT . 'page/sidebar_announcement.html';
			echo file_get_contents( $file_name );
?>
</ul>
<?
			return true;
		}

		if ( empty($options['user_name']) )
			return;
		else
			$user_name = $options['user_name'];
		
		if ( empty($options['num']) )
			$num = 5;
		else
			$num = $options['num'];

		if ($title =='公告')
			$isAnnounce = true;
		else
			$isAnnounce = false;


		$user_db_row	= JWUser::GetUserInfo($user_name);

		if ( empty($user_db_row) )
			return;

		$user_status_ids = JWDB_Cache_Status::GetStatusIdsFromUser($user_db_row['idUser'], $num );

		if ( empty($user_status_ids['status_ids']) )
			return;

		echo <<<_HTML_

		<div class="headtip"><h2 class="forul">$title</h2></div>
  		<ul class="featured">
_HTML_;

		if($isAnnounce)
		echo <<<_HTML_
			<li class="FeaturedImage">
				<a href="http://e.jiwai.de/cbc2007/" target="_blank"><img title="叽歪de× 中文网志年会"  height="25" src="http://blog.jiwai.de/images/jiwaicbc.gif" /></a>
			</li>
			<li class="FeaturedImage">
				<a href="/wo/bindother/" target="_blank"><img title="绑定 Twitter" height="25" src="http://blog.jiwai.de/images/twitterbind.gif" /></a>
			</li>
			<li class="FeaturedImage">
				<a href="/wo/devices/im/" target="_blank"><img title="绑定 Yahoo" height="25" src="http://blog.jiwai.de/images/yahoobind.gif" /></a>
			</li>
_HTML_;

		$status_db_row = JWDB_Cache_Status::GetDbRowsByIds($user_status_ids['status_ids']);

		foreach ( $user_status_ids['status_ids'] as $status_id )
		{
			$status = $status_db_row[$status_id]['status'];

			$status = preg_replace("/\s+/",'',$status);

			if ( ! preg_match("#^(\S+?)-?(http://\S+)$#", $status, $matches) )
				continue;

			$desc 	= $matches[1];
			$url 	= $matches[2];

			$max_len = 14;

			if ( mb_strlen($desc) > $max_len )
				$desc = mb_substr($desc,0,$max_len-3) . "...";
			$desc   = str_replace(array('NEW', '！'), array('', ''), $desc);
			$desc   = str_replace(array('[', ']'), array('<b>', '</b>'), $desc);

			echo <<<_HTML_
			<li>
				<a href="$url" target="_blank">$desc</a>
			</li>
_HTML_;
		}
		echo <<<_HTML_
		</ul>
_HTML_;
		if ($title=='公告') echo<<<_HTML_

 			<div class="but"><input type="button" style="width:95px;" class="submitbutton" onclick="window.open('http://blog.jiwai.de/');" value="叽歪大事记" /><input type="button" style="margin-left:10px; width:80px;" class="submitbutton" onclick="window.open('http://blog.jiwai.de/');" value="更多公告" ></div>
_HTML_;
	}


	function sidebar_invite() {
?>
		<div class="featured" id="invite" style="line-height:30px;">
			<p><a href="/wo/invitations/invite">邀请好友加入叽歪网</a></p>
		</div>

<?php
	}

	function sidebar_bookmarklet() {
  		//XXX: bookmarklets in IE6 are limited to 508 characters. 
  ?>
  		<div class="featured" id="bookmarklet" style="line-height:30px;">
 			<p><a onClick="if(confirm('将此按钮添加到浏览器的收藏夹或工具栏上，即可方便地收藏网址信息到叽歪。\r\n需要了解详细的使用方法吗？'))location.href='http://help.jiwai.de/BookmarkletUsage';return false;" href="javascript:var d=document,w=window,f='<?php echo JW_SRVNAME;?>/wo/share/',l=d.location,e=encodeURIComponent,p='?u='+e(l.href)+'&amp;t='+e(d.title)+'&amp;d='+e(w.getSelection?w.getSelection().toString():d.getSelection?d.getSelection():d.selection.createRange().text);a=function(){if(!w.open(f+'s'+p,'sharer','toolbar=0,status=0,resizable=0,width=540,height=310'))l.href=f+'w'+p};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else{a()}void(0)" class="sharebtn"><img src="<?php echo self::GetAssetUrl('/images/org-share-collect.gif');?>" title="收藏到叽歪" /></a></p>
  		</div>
  
<?php
	}

	function sidebar_separator() {
?>

		<div class="line"><div></div></div>
<?php
	}

    function sidebar_asus()
	    {    
			        $src = JWTemplate::GetAssetUrl('/images/adonjiwai.gif');
					        echo '&nbsp;<div class="sidebar" style="margin-top:-3px;"><a href="/asus2008/"><img src="'.$src.'" title="[叽歪网]挑战新高度(华硕2008珠峰志愿者行动)" alt="[叽歪网]挑战新高度(华硕2008珠峰志愿者行动)".></img></a></div>';
		}

	/*
	 * Sidebar menu
	 *	@param array	item list	array ( array('user_notice'	, array(param1=>val1,param2=>val2))
											, array('count'		, array(param1=>val1,param2=>val2))
										);
	 *
	 */
	static public function sidebar( $menuList=array(), $idUser=null, $pageName='' )
	{
		if ( empty($idUser) ){
			$idUser = JWLogin::GetCurrentUserId();
		}

		if ( 0<$idUser )
			$aUserInfo = JWUser::GetUserInfo($idUser);
?>

<div id="sidebar" class="static">
<?php
if ($menuList[0][0] == 'login') {
	array_shift($menuList);
	self::sidebar_login();
}
?>
		<div class="sidediv">
<?php
		foreach ( $menuList as $func_menu ){
			list ($menu_name,$menu_param)	= $func_menu;

			$func_name = 'sidebar_' .$menu_name;

			if ( method_exists('JWTemplate',$func_name) ){
				call_user_func_array( array('JWTemplate',$func_name), $menu_param );
			}else{
				throw new JWException("unsupport sidebar function $func_name");
			}
		}

/*
	now supported:
		self::sidebar_login_notice();

		self::sidebar_head();

		self::sidebar_login();
		self::sidebar_register();
		self::sidebar_featured();
		self::sidebar_status()
		self::sidebar_friend_req()
		self::sidebar_count()
		self::sidebar_jwvia()
		self::sidebar_search()
		self::sidebar_friend()
		self::sidebar_active()
		self::sidebar_action()
*/


?>
	</div><!-- sidediv -->
<? if('public_timeline'==$pageName)            self::sidebar_asus();?>	
</div><!-- sidebar -->

<?php
	}


	/*
	 * @param	array	JWSns::GetUserAction return array
	 */
	static function sidebar_action($action, $idUserDst, $sidebar=true)
	{
		if ( empty($action) )
			return;

		$arr_user_info = JWUser::GetUserInfo($idUserDst);
		$current_user_id = JWLogin::GetCurrentUserId();

		$flag = false;
		foreach( $action as $one=>$result ) {
			$flag |= $result;
		}
		if( $flag == false ) return;

		echo <<<_HTML_
		<ul class="actions">
_HTML_;


		if ( true == $action['nudge'] )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/followings/nudge/$arr_user_info[id]" onClick="return JWAction.redirect(this);">挠挠此人</a>
			</li>
_HTML_;
		}

		if ( true == $action['d'] )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/direct_messages/create/$arr_user_info[id]" onClick="return JWAction.redirect(this);">发送悄悄话</a>
			</li>
_HTML_;
		}


		if ( true == $action['on'] )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/followings/on/$arr_user_info[id]">接收更新通知</a>
			</li>
_HTML_;
		}

		if ( true == $action['off'] )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/followings/off/$arr_user_info[id]">取消更新通知</a>
			</li>
_HTML_;
		}

		if ( true == $action['follow'] )
		{
			$oc = ( 
				JWUser::IsProtected($arr_user_info['id'] )
				&& false == JWFollower::IsFollower( $current_user_id, $arr_user_info['id'] )
			) ? 'onclick="return JWAction.follow('.$arr_user_info['id'].', this);"' : 'onClick="return JWAction.follow('.$arr_user_info['id'].');"' ;

			echo <<<_HTML_
			<li><a href="/wo/followings/follow/$arr_user_info[id]" $oc>关注此人</a></li>
_HTML_;
		}

		if ( true == $action['leave'] )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/followings/leave/$arr_user_info[id]" onclick="return confirm('请确认取消对 $arr_user_info[nameScreen] 的关注')">取消关注此人</a>
			</li>
_HTML_;
		}

		echo <<<_HTML_
		</ul>
_HTML_;

	}



	static function sidebar_user_info($aUserInfo)
	{
?>
		<ul class="about vcard">
<?php
			echo '<li>名字: <span class="fn">' . htmlspecialchars($aUserInfo['nameFull']) . "</span></li>\n";
			if ( !empty($aUserInfo['gender']) && 'secret'!=$aUserInfo['gender'] ) {
				echo "<li>性别: ";
				echo 'male'==$aUserInfo['gender']?"男":"女";
				echo "</li>\n";
			}
			if ( !empty($aUserInfo['location']) ) {
				$location = JWLocation::GetLocationName( $aUserInfo['location'] );
				echo "<li>位置: <span class=\"adr\"><span class=\"region\">$location</span></span></li>\n";
			}
			if ( !empty($aUserInfo['url']) )
			{
				$url = $aUserInfo['url'];

				if ( !preg_match('/^\w+:\/\//',$url) )
					$url = 'http://' . $url;

				$max_url_len = 23;
				if ( strlen($url) > $max_url_len )
					$show_url = substr($url, 0, $max_url_len-3 ) . '...';
				else
					$show_url = $url;

				echo '<li>网站:  <a href="'.htmlspecialchars($url).'" rel="me" class="url" target="_blank">'.htmlspecialchars($show_url).'</a></li>';
			}
			if ( JWUser::IsAnonymous($aUserInfo['id']) )
			{
				$aUserInfo['bio'] = '这是一个IP漂流瓶用户。他是由很多匿名用户组成的，因为他们都有着共同的IP段，于是便汇聚在了一起，你看到的是这个瓶子里所有人的叽歪。';
			}
			if ( !empty($aUserInfo['bio']) )
			{
				$bio_plus = JWUser::IsAnonymous($aUserInfo['id']) ?
					'[<a href="http://help.jiwai.de/SeagoingBottles" target="_blank">查看详情</a>]'
					:
					null;
				echo "<li>自述: <span class=\"bio\">" . htmlspecialchars($aUserInfo['bio']) . $bio_plus . "</span></li>\n";
			}
?>
		</ul>
<?php
	}

	/**
 	  * 功能：显示绑定设备信息
	  * 作者：WqSemc
	  * 日期：2007-10-16
	  */
	static function sidebar_device_info($aUserInfo)
	{	   
		echo "<ul class=imico><li>";

		$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($aUserInfo['id']);

		$isUserLogined = JWLogin::IsLogined() ;
		$imicoUrl = "http://blog.jiwai.de/images";
		$imicoUrlSms = "/wo/devices/sms";
		$imicoUrlIm = "/wo/devices/im";
		$imicoUrlHelpSms = "http://help.jiwai.de/VerifyYourPhone";
		$imicoUrlHelpIm = "http://help.jiwai.de/VerifyYourIM";
		$imicoUrlHref = "";

		$isUseNewSmth = false;

		$pArray = array( 
				'sms' => '已绑定 手机', 
				'jabber' => '已绑定 Jabber',
				'gtalk' => '已绑定 GTalk', 
				'msn' => '已绑定 MSN',
				'qq' => '已绑定 QQ',
				'skype' => '已绑定 Skype',
				'aol' => '已绑定 AOL',
				'fetion' => '已绑定 飞信',
				'yahoo' => '已绑定 Yahoo!',
				'newsmth' => '已绑定 水木社区',
				'facebook' => '已绑定 Facebook',
			       );

		foreach( $pArray as $key=>$bindTip ) {
			if ( isset($aDeviceInfo_rows[$key]) && empty($aDeviceInfo_rows[$key]['secret']) )
			{
				$imicoUrlHref = $isUserLogined ?
					( $key!='sms' ? $imicoUrlIm : $imicoUrlSms ) 
					: 
					( $key!='sms' ? $imicoUrlHelpIm : $imicoUrlHelpSms );
				echo <<<_HTML_
					<a href="$imicoUrlHref"><img src=$imicoUrl/jiwai-${key}.gif title="$bindTip" title="$bindTip" /></a>
_HTML_;
			}
		}

		echo "</li></ul><br />";
	}

   /*
   功能：显示帮助信息
   作者：WqSemc
   日期：2007-10-16
   */
	static function sidebar_help_info()
	{	   
		echo <<<_HTML_
			<div class="sidehelpdiv">是否要问以下的问题呢？</div>
			<ul class="helpinfo">
				<li>
					<a href=http://help.jiwai.de/Faq target=_blank>常见问题集合(FAQ)</a><br/>
				</li>
				<li>
					<a href=http://help.jiwai.de/MobileFAQ target=_blank>手机常见问题</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/IMFAQ target=_blank>QQ、MSN、Gtalk常见问题</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/VerifyYourIM target=_blank>如何绑定QQ、MSN、Gtalk？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/VerifyYourPhone target=_blank>如何绑定手机？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/MakeFriend target=_blank>如何添加好友？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/WhatistheRepliestab target=_blank>如何回复别人？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/WhatisaFavorite target=_blank>如何收藏感兴趣的叽歪？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/NotificationsSelection target=_blank>如何用手机和QQ等收到别人的叽歪？</a>
				</li>
				<li>
					<a href=http://help.jiwai.de/HowToAddWidgetIntoYourBlogs target=_blank>如何在博客上显示我的叽歪？</a>
				</li>
			</ul>
			<div class="sidehelpdiv">没有你要的答案？在左边发给我们吧，我们会尽快回复的。</div>
_HTML_;
	}

/*   
	 功能：返回绑定手机数和绑定IM数。
	 作者：WqSemc
	 日期：2007-10-17
 */
static function GetSmsAndImValidNums($idUser)
{			

	$aDeviceInfo_rows = JWDevice::GetDeviceRowByUserId($idUser);

	$SmsValidNums = 0;
	$ImValidNums = 0;
	foreach($aDeviceInfo_rows as $aDeviceInfo_row)
	{	
		if (empty($aDeviceInfo_row['secret']))
		{	
			switch( $aDeviceInfo_row['type'])
			{	
				case 'sms':
				   ++$SmsValidNums;
				break;
				default:
				   ++$ImValidNums;
			}
		}
	}
	
	return array($SmsValidNums, $ImValidNums);
}

/*   
	 功能：返回用户目前是否使用系统默认头像。
	 作者：WqSemc
	 日期：2007-10-17
 */
static function IsUserUploadPic($idUser)
{			

	$userInSession = JWUser::GetUserInfo($idUser);

	return !empty($userInSession['idPicture']);
}

	static function sidebar_user_notice($aUserInfo)
	{
		$photo_url		= JWPicture::GetUserIconUrl($aUserInfo['id'],'thumb48');
?>
		<div class="msg ">
			<img style="display:none;" src="<?php echo $photo_url; ?>" title="<?php echo $aUserInfo['nameFull'];?>" width="48" height="48" /><h2 class="forul"><?php echo $aUserInfo['nameScreen'];?>的资料</h2>
		</div>

<?php
	}


	static function sidebar_head($msg)
	{
?>
		<div class="msg">
			<?php echo $msg?>
		</div>
<?php
	}


	static function sidebar_login_notice()
	{
?>
		<div class="msg">
			<h3>请登录！</h3>
		</div>
<?php
	}

		
	static function sidebar_friend_req( $num )
	{
		if ( empty($num) )
			return;

		echo <<<_HTML_
		<ul class="featured">
			<li>
				<a href="/wo/friend_requests/">${num}个添加关注请求！</a>
			</li>
		</ul>

_HTML_;
	}


	static function sidebar_count( $countInfo=null, $userInfo=null )
	{
		if ( empty($userInfo) ) 
		{
			$user = 'wo';
			$current_user_id = JWLogin::GetCurrentUserId();
			$is_anonymous = $current_user_id && JWUser::IsAnonymous($current_user_id);
		}
		else
		{
			$user = $userInfo['nameUrl'];
			$name_full = $userInfo['nameFull'];
			$name_screen = $userInfo['nameScreen'];
			$is_anonymous = JWUser::IsAnonymous($userInfo['id']);
		}

		$userInSession = JWUser::GetUserInfo(JWLogin::GetCurrentUserId());
		$asset_star_url = self::GetAssetUrl("/img/icon_star_full.gif");
		echo <<<_HTML_
		<ul id="update_count">
_HTML_;

 		if ($user != 'wo') echo <<<_HTML_
 		<div class="msg ">
 			<div class="line"><div></div></div>
 			<h2 class="forul"><?php //echo $name_screen;?>目前</h2>
 		</div>
_HTML_;
 
 		if ( 'wo'==$user || $user === @$userInSession['nameUrl'] ) 
 		{
 		echo <<<_HTML_
 			<li id="friend_count"><a href="/wo/followings/" onClick="return JWAction.redirect(this);">关注&nbsp;$countInfo[following]&nbsp;人</a></li>
_HTML_;
 		}else{
 			echo <<<_HTML_
 			<li id="friend_count"><a href="/$user/followings/" onClick="return JWAction.redirect(this);">关注&nbsp;$countInfo[following]&nbsp;人</a></li>
_HTML_;
 		}

		if ( 'wo'==$user || $user === @$userInSession['nameUrl'] ) 
		{    
			echo <<<_HTML_
				<li id="follower_count"><a href="/wo/followers/" onClick="return JWAction.redirect(this);">被&nbsp;$countInfo[follower]&nbsp;人关注</a></li>
_HTML_;
		}else{
			echo <<<_HTML_
				<li id="follower_count"><a style="text-decoration:none;" href="javascript:void(0);">被&nbsp;$countInfo[follower]&nbsp;人关注</a></li>
_HTML_;
		}   

		if ( ('wo'==$user || $user === @$userInSession['nameUrl']) && false==$is_anonymous )
		{
			$msg_count = JWMessage::GetMessageStatusNum($userInSession['id'], JWMessage::INBOX, JWMessage::MESSAGE_NOTREAD) ;
			$msg_string = ( $msg_count == 0 ) ? '' : "&nbsp;(&nbsp;${msg_count}&nbsp;条新&nbsp;)";
			echo <<<_HTML_
 			<li id="message_count"><a href="/wo/direct_messages/">$countInfo[pm]&nbsp;条悄悄话${msg_string}</a></li>
_HTML_;
		}

		$archive = ( $user == 'wo' ) ? 'archive/' : null;
		echo <<<_HTML_
 			<li id="favourite_count"><a href="/$user/favourites/" onClick="return JWAction.redirect(this);">$countInfo[fav]&nbsp;条收藏</a><img border="0" src="$asset_star_url" /></li>
 			<li id="status_count"><a href="/$user/">$countInfo[status]&nbsp;条叽歪</a></li>
_HTML_;
 
 		if ( @$countInfo['mms'] )
 		{
			$mms_user = $user;
			if ( 'wo' == $user )
			{    
				$current_user_info = JWUser::GetCurrentUserInfo();
				$mms_user = urlEncode( $current_user_info['nameUrl'] );
			} 
 			echo <<<_HTML_
 			<li id="mms_count"><a href="/$mms_user/mms/">$countInfo[mms]&nbsp;条彩信</a></li>
_HTML_;
		}

		echo <<<_HTML_
		</ul>
_HTML_;
	}


	static function sidebar_status( $userInfo )
	{
		$status_data 	= JWDB_Cache_Status::GetStatusIdsFromUser($userInfo['id'],1);

		if ( !empty($status_data['status_ids']) )
		{
			$status_rows	= JWDB_Cache_Status::GetDbRowsByIds($status_data['status_ids']);
			$status_id		= $status_data['status_ids'][0];
			$current_status	= $status_rows[$status_id];
		}
		else
		{
			$current_status	= '还没有叽歪过！';
		}

		$arr_status		= JWStatus::FormatStatus($current_status);
		$photo_url		= JWPicture::GetUserIconUrl($userInfo['id']);
?>
		<div class="msg ">
			<a href="/wo/account/profile"><img src="<?php echo $photo_url; ?>" title="<?php echo $userInfo['nameFull'];?>" width="48" height="48" align="middle" /></a>欢迎你 <?php echo $userInfo['nameFull'];?>
			<br />
			<?php
			if ( JWUser::IsAnonymous($userInfo['id'] ) )
			{
				echo '<p style="margin-top:10px; line-height:140%;">　　你现在是一个IP漂流瓶用户，你看到的是所有和你同一个ip段的朋友的叽歪哦。我们只为漂流瓶用户提供了基本的功能，如果你想使用更强大的其他功能，就请（<a href="/wo/login">登录</a>）或者（<a href="/wo/account/create">注册</a>）吧。[<a href="http://help.jiwai.de/SeagoingBottles" target="_blank">查看详情</a>]<p>';
			}
			?>
			<?php
			
				if( false == JWTemplate::IsUserUploadPic(JWLogin::GetCurrentUserId()))
				{
					echo '<a class="sendtips" style="margin-left:0" href="/wo/account/photos">上传头像 ↑ </a>';
				}
			?>
		</div>

<?php
	}


	/*
	 *	显示通知信息发送到的位置，以及激活界面（如果有未激活的设备的话）
	 *	@param	array	$activeOptions	array('msn'=>, 'sms'=>, ...)
	 *	@param	string	$viaDevice		'sms' or 'im' or 'web'
	 */
	static function sidebar_jwvia($activeOptions, $viaDevice='web', $strip=false)
	{
		$supported_device_types = JWDevice::GetSupportedDeviceTypes();

		$has_active_device = false;
		$otherDev = array();
		$viaDevName = '网页';
		$supported_device_types[] = 'web';
		$activeOptions['web'] = true;
		foreach ( $supported_device_types as $type )
		{
			if ( $activeOptions[$type] )
			{
				$has_active_device = true;
				if ($viaDevice == $type) {
					$viaDevName = JWDevice::GetNameFromType($type);
				} else {
					$otherDev[$type] = JWDevice::GetNameFromType($type);
				}
			}
		}

		
		if ( $activeOptions['sms'] ) { //FIXME && JWUser::IsSubSms($logined_user_id) 
			unset($activeOptions['sms']);
		}

		if (!$strip) echo '		<div id="device_list" class="featured" style="font-size:14px;">';
?>

			接收通知方式：
			<ul class="droplist" onmouseover="this.className='droplistopen'" onmouseout="this.className='droplist'">
				<li class="slect" onmouseover="style.backgroundColor='#F97B00';" onmouseout="style.backgroundColor='#F97B00'"><?php echo $viaDevName; ?></li>
<?php
		foreach ($otherDev as $d => $n) if ($d!='facebook') echo <<<__HTML__
				<li onmouseover="style.backgroundColor='#FF8D1D';" onmouseout="style.backgroundColor='#F97B00'" onclick="JiWai.ChangeDevice('$d');">$n</li>

__HTML__;
?>
			</ul>
<?php
		if (!$strip) echo "		</div>\n";
	}

	static function sidebar_vistors( $idVistors=array() )
	{
		if ( empty($idVistors) )
			return;
		echo '<div class="line"><div></div></div>';	
		#$title = preg_match( '#/wo/#', $_SERVER['REQUEST_URI'] ) ? '有朋自远方来' : '邻踪侠影' ;
		$title = '最近有谁来过<span style="font-weight:normal;display:none;">(共 88888 人)</span>';
		self::sidebar_featured(array(
				'user_ids' => $idVistors,
				'title' => $title,
				'id' => 'friend',
				'activeOrder' => false,
		));
	}

	static function sidebar_friend($friendIds)
	{
		if ( empty($friendIds) )
			return;

		echo '<div class="line"><div></div></div>';	
		self::sidebar_featured(array('user_ids'=>$friendIds, 'title'=>'最近上线的人', 'id'=>'friend'));

		return;
		$friend_rows = JWUser::GetDbRowsByIdsAndOrderByActivate($friendIds);

		$picture_ids = JWFunction::GetColArrayFromRows($friend_rows, 'idPicture');
		$picture_url_rows = JWPicture::GetUrlRowByIds($picture_ids);

		echo <<<_HTML_
  		<div id="friend">

_HTML_;

		foreach ( $friendIds as $friend_id )
		{
			$friend_info 		= $friend_rows[$friend_id];

			$picture_url		= JWTemplate::GetConst('UrlStrangerPicture');

			$friend_picture_id	= @$friend_info['idPicture'];
			if ( $friend_picture_id )
				$picture_url	= $picture_url_rows[$friend_picture_id];

			echo <<<_HTML_
			<a href="/$friend_info[nameScreen]/" rel="contact" title="$friend_info[nameFull]($friend_info[nameScreen])"><img title="$friend_info[nameFull]" height="24" src="$picture_url" width="24" /></a>

_HTML_;
		}
  		echo <<<_HTML_
		</div>

_HTML_;
	}

	static public function sidebar_rss ( $type, $id , $forceName=null)
	{
   		$rss_url = "http://api.".JW_HOSTNAME."/statuses/";

		if( is_numeric($id) || $id == 'help' ) {
			$idNumber = $id;
		}else{
			$idNumber = JWUser::GetUserInfo( $id, 'id' );
		}

		switch ( $type )
		{
			case 'friends':
				$rss_url .= "${type}_timeline/$idNumber.rss";
				break;
			case 'user':
				$rss_url .= "${type}_timeline/$idNumber.rss";
				break;

			case 'public_timeline':
				// fallto default
			default:
				$rss_url .= "public_timeline.rss";
				break;
		}

		$name = ( $forceName == null ) ? $id : $forceName ;

		echo <<<_HTML_
				<a href="$rss_url" class="rsshim">订阅 $name</a>
_HTML_;
	}



	static function sidebar_search($nameUser=null,$q=null)
	{
		$action = "/wo/search/users";
		$action2 = "/wo/search/statuses";

		if($nameUser) 
			$action = "/$nameUser/search";

?>
<script type="text/javascript">
	function on_sidebar_search_click(o){
		if(!$('search_content').value.trim()){
			$('search_content').value = $('search_content').value.trim();
			return false;
		}
		if(o && o=='s'){
			$('sidebar_search_form').action = '/wo/search/statuses';
		}
		return true;
	}
</script>
<?php

		if($nameUser) {
?>
		<div style="padding:10px 0 20px 0;">
			<form action="<?php echo $action?>" method="GET" id="sidebar_search_form">
				<fieldset class="user_search">
				<input id="search_content" name="q" onclick="" type="text" value="<?php echo $q ?>" /><br/>
				<input type="submit" value="搜叽歪" onclick="return on_sidebar_search_click();"/>
				</fieldset>
			</form>
		</div>
<?php
	}else{
?>
		<div style="padding:10px 0 20px 0;">
			<form action="<?php echo $action?>" method="GET" id="sidebar_search_form">
				<fieldset class="user_search">
				<input id="search_content" name="q" onclick="" type="text" value="<?php echo $q ?>" /><br/>
				<input type="submit" value="搜用户" onclick="on_sidebar_search_click();"/>
				<input type="submit" value="搜叽歪" onclick="on_sidebar_search_click('s');"/>
				</fieldset>
			</form>
		</div>

<?php

	}
}


	/*
	 *	显示 rss 的 link
	 *	@param	string	$type	选择为 'user' 'friends' 'public_timeline'
	 *	@param	int		$id		user_id，如果是 public_timeline 则无作用
	 */
	static public function rss ( $type, $id )
	{
   		$rss_url = "http://api.".JW_HOSTNAME."/statuses/";

		switch ( $type )
		{
			case 'friends':
				$rss_url .= "${type}_timeline/$id.rss";
				break;
			case 'user':
				$rss_url .= "${type}_timeline/$id.rss";
				break;

			case 'public_timeline':
				// fallto default
			default:
				$rss_url .= "public_timeline.rss";
				break;
		}

		echo <<<_HTML_
				<div class="statuses_options odd">
   			 		<a class="rss" href="$rss_url">RSS</a>
				</div>
_HTML_;
	}


	static public function GetConst($constName)
	{
		self::Instance();

		if ( empty(self::$msJWConst) )
		{
			self::$msJWConst = array (
				'UrlContactUs' => '/t/帮助留言板/',
				'UrlRegister' => '/wo/account/create',
				'UrlLogin' => '/wo/login',
				'UrlResetPassword' => '/wo/account/confirm_password_reset',
				'UrlPublicTimeline' => '/public_timeline/',
				'UrlTermOfService' => 'http://help.jiwai.de/TOS',
				'UrlFaq' => 'http://help.jiwai.de/FAQs',
				'UrlError404' => '/wo/error/404',
				'UrlError500' => '/wo/error/500',
				'UrlHelp' => 'http://help.jiwai.de/',
				'UrlHelpComments' => '/help/',
				'UrlHelpGadget' => 'http://help.jiwai.de/Gadget',
				'UrlStrangerPicture' => self::GetAssetUrl('/images/org-nobody-96-96.gif'),
			);
		}

		return @self::$msJWConst[$constName];
	}


	static public function UserGadgetNav($activeMenu='index')
	{
		$arr_menu = array (
				'index' => array (
					'/wo/gadget/',
					'窗可贴说明',
				),
				'flash' => array ( 
					'/wo/gadget/flash',
					'Flash',
				),
				'javascript' => array ( 
					'/wo/gadget/javascript',
					'JavaScript',
				),
				'image' => array (
					'/wo/gadget/image',
					'Image',
				),
			);

		echo '	<div id="gadgetNav" class="subtab">';
		foreach ( $arr_menu as $name=>$setting )
		{
			if ( $activeMenu === $name )
				echo " <a href=\"$setting[0]\" class=\"active\"";
			else
				echo " <a href=\"$setting[0]\"";

			echo ">$setting[1]</a>";
		}
		echo "\t</div>\n";
	}


	static public function UserSettingNav($activeMenu='account')
	{
		$arr_menu = array ( 
				'account' => array ( '/wo/account/settings', '帐号' ),
				'password' => array ( '/wo/account/password', '密码', ),
				'device_sms' => array ( '/wo/devices/?sms', '手机短信', ),
				'device_im' => array ( '/wo/devices/?im', '聊天软件', ),
				'notification' => array ( '/wo/account/notification', '通知', ),
				'meeting'=> array ( '/wo/account/meeting', '会议模式', ),
				'picture' => array ( '/wo/account/picture', '头像',),
				'profile' => array ( '/wo/account/profile_settings', '界面'),
				'openid' => array ( '/wo/openid/', 'OpenID', ),
			);
		echo '	<h4 id="settingsNav">';
		$first = true;
		foreach ( $arr_menu as $name=>$setting )
		{
			if ( $first )	$first = false;
			else 			echo ' | ';


			if ( $activeMenu === $name )
				echo " $setting[1] ";
			else
				echo " <a href=\"$setting[0]\">$setting[1]</a> ";
		}
		echo "\t</h4>\n";
	}


	/*
	 * Get asset url with timestamp
	 *	@param	path		the path of asset.jiwai.de/$path
	 *	@return	URL			the url ( domain name & path )
	 */
	static public function GetAssetUrl($absUrlPath, $mtime=true)
	{
		JWTemplate::Instance();

		$asset_num_max = 6;

		if ( empty($absUrlPath) )
			throw new JWException('must have path');

		$asset_path	= JW_ROOT . 'domain/asset';



		$domain = 'jiwai.de';

		if ( empty($_SERVER['HTTP_HOST']) )
		{
			//$ip = JWRequest::GetClientIp();
			//JWLog::Log(LOG_CRIT, "[$ip] GetAssetUrl($absUrlPath) can't find HTTP_HOST");
		}
		else if ( preg_match('#(alpha|beta)\.jiwai\.(\w+)#i',$_SERVER["HTTP_HOST"],$matches) )
		{
			$domain		= "$matches[1].jiwai.$matches[2]";
		}


		// 同一个文件，总会被分配到同一个 n 上。
		$n = sprintf('%u',crc32($absUrlPath));
		$n %= $asset_num_max;

		if('/' != mb_substr($absUrlPath, 0, 1))
			$absUrlPath ="/$absUrlPath";

		if ( !$mtime )
			return "http://asset${n}.$domain$absUrlPath";

		$timestamp 	= filemtime("$asset_path$absUrlPath");

		//we use more then one domain name to down load asset in parallel
		return "http://asset${n}.$domain$absUrlPath?$timestamp";
	}


	static public function	ShowAlphaBetaTips()
	{
		return;
		if ( ! preg_match('#((alpha)|(beta))\.(?P<domain_url>jiwai\.de.*?)$#i',$_SERVER["HTTP_HOST"],$matches) )
			return;
		
		$version 	= strtolower($matches[1]);
		$domain_url	= $matches['domain_url'];

		$msg	= '';

		switch ( $version )
		{
			case 'alpha':
				$msg = <<<_MSG_
<p>
这里是叽歪网(<strong>Alpha</strong>)测试系统。Alpha测试的定义为：在有开发者关注下对系统进行使用测试。如果你想试用最新的，还在开发中的功能，那么可以在这里继续访问。但是需要注意的是，系统也许会经常的工作不正常，甚至出错，所以我们建议你至少使用<a href='http://beta.$domain_url'>Beta系统</a>。
</p>
_MSG_;
				break;

			case 'beta':
				$msg = <<<_MSG_
<p>
这里是叽歪网(<strong>Beta</strong>)测试系统。如果你想试用最新的，正在准备升级的功能时，欢迎在这里继续访问。最新的系统功能可能有不稳定的情况，欢迎向我们<a href='mailto:wo@jiwai.de'>报告BUG</a>。如果你希望使用最为稳定的版本，请来正式运行的网站：<a href='http://$domain_url'>叽歪网</a>。
</p>
_MSG_;
				break;

			default:
				JWLog::LogFuncName(LOG_CRIT, "unknown version: $version");
				break;
		}

		echo <<<_HTML_
<div class="yft" style="margin:1em; padding:1em">
$msg
</div>
<script type="text/javascript">
window.addEvent('domready', function(){
	JiWai.Yft(".yft",1);
});
</script>
_HTML_;

	}


	static public function	ShowActionResultTips($display=true)
	{
		$error_html	= JWSession::GetInfo('error');
		$notice_html	= JWSession::GetInfo('notice');

		$is_exist = false;
		$tips_content = null;

		if ( !empty($error_html) )
		{
			$is_exist = true;
			$tips_content .= <<<_HTML_
			<div class="tipnote-red" onclick="return JiWai.KillNote(this);"> $error_html </div>
_HTML_;
		}


		if ( !empty($notice_html) )
		{
			$is_exist = true;
			$tips_content .= <<<_HTML_
			<div class="tipnote" onclick="return JiWai.KillNote(this);"> $notice_html </div>
_HTML_;
		}

		if ( $is_exist )
		{
			$tips_content .= <<<_HTML_
<script type="text/javascript">JiWai.Yft(".notice");</script>
_HTML_;
		}

		if( $display ) {
			echo $tips_content;
		} else {
			return $tips_content;
		}
	}

	static public function ShowActionResultTipsMain() {
		$tips_content = self::ShowActionResultTips( false );
		if( $tips_content ) {
			echo <<<_HTML_
<div style="width:776px; margin:0 auto -8px auto!important; margin:0 auto -16px auto; padding:0px;">$tips_content</div>
_HTML_;
		}
	}


	/*
	 *	显示用户的列表，并根据 $idUser 与用户的关系，显示相应的 action
	 *	@param	@options	array ( 'element_id' => 'doing' )
	 *
	 */
	static public function ListUser($idUser, $idListUsers, $options=array() )
	{
		$wo = preg_match( '/^\/wo\//', $_SERVER['REQUEST_URI'] );
		if( false == isset( $options['type'] ) ) 
			$options['type'] = 'following';

		switch( $options['type'] ) {
			case 'inrequests':
			case 'outrequests':
				$friendRequestRows = $idListUsers ;
				$idListUsers = array_keys( $idListUsers );
			break;
			default:
				$action_rows = JWSns::GetUserActions($idUser, $idListUsers);
			break;
		}

		$list_user_rows = JWDB_Cache_User::GetDbRowsByIds($idListUsers);
		$picture_ids = JWFunction::GetColArrayFromRows($list_user_rows, 'idPicture');
		$picture_url_row = JWPicture::GetUrlRowByIds($picture_ids);

		$n = 0;
		foreach ( $idListUsers as $list_user_id )
		{
			$list_user_row = $list_user_rows[$list_user_id];

			$list_user_picture_id = @$list_user_row['idPicture'];

			$list_user_icon_url = JWTemplate::GetConst('UrlStrangerPicture');
			if ( $list_user_picture_id )
				$list_user_icon_url = $picture_url_row[$list_user_picture_id];

			$odd_even = ($n++ % 2) ? 'odd' : 'even';
			$statusNum = JWDB_Cache_Status::GetStatusNum( $list_user_id );
			$mmsNum = JWDB_Cache_Status::GetStatusMmsNum( $list_user_id );
		
			$timeUpdate = $list_user_row['timeStamp'];

			$action_row = $action = $liNote = null;
			switch( $options['type'] ) {
				case 'following':
					$action_row = $action_rows[$list_user_id];
					$action = self::FollowingsAction($list_user_id, $action_row , $wo);
				break;
				case 'followers':
					$action_row = $action_rows[$list_user_id];
					$action = self::FollowersAction($list_user_id, $action_row , $wo);
				break;
				case 'inrequests':
					$action = self::InRequestsAction($list_user_id, $action_row , $wo);
					$requestRow = $friendRequestRows[ $list_user_id ];
					$liNote = "<li class=\"note\">".htmlspecialchars($requestRow['note'])."</li>";
				break;
				case 'outrequests':
					$action = self::OutRequestsAction($list_user_id, $action_row , $wo);
					$requestRow = $friendRequestRows[ $list_user_id ];
					$liNote = "<li class=\"note\">".htmlspecialchars($requestRow['note'])."</li>";
				break;
				case 'search':
					$action_row = $action_rows[$list_user_id];
					$action = self::FollowingsAction($list_user_id, $action_row , $wo);
					$action = trim( $action, '|' );
				break;
			}

			echo <<<_HTML_
	<ul class="liketable"><a href="/$list_user_row[nameUrl]/"><img src="$list_user_icon_url" width="48" height="48" class="img" title="$list_user_row[nameScreen]($list_user_row[nameFull])" /></a>
		<li class="name"><a href="/$list_user_row[nameUrl]/" >$list_user_row[nameScreen]</a></li>
		<li class="nob">${statusNum}条</li>
		<li class="nob">${mmsNum}条</li>
		<li class="time">$timeUpdate</li>
		$liNote
		<li class="action">$action</li>
	</ul>
_HTML_;

			?>
			 <div class="clear" style="border-bottom:none;"></div>
			<?php
		}
	}

   	static public function FollowingsAction($idUser, $actionRow , $wo = true, $separator='|') {
		$idUser = JWDB::CheckInt( $idUser );
		$action = null;

		if( true == $actionRow['d'] )
			$action .= "<a href='/wo/direct_messages/create/$idUser'>发送悄悄话</a>$separator";

		if($wo)
		{

			if( true == $actionRow['nudge'] )
				$action .= "<a href='/wo/followings/nudge/$idUser'>挠挠此人</a>$separator";

			if( true == $actionRow['on'] )
				$action .= "<a href='/wo/followings/on/$idUser'>接收更新通知</a>$separator";

			if( true == $actionRow['off'] )
				$action .= "<a href='/wo/followings/off/$idUser'>取消更新通知</a>$separator";

			if( true == $actionRow['leave'] ) 
				$action .= "<a href='/wo/followings/leave/$idUser'>取消关注</a>$separator";

			if( true == $actionRow['follow'] ) 
				$action .= "<a href='/wo/followings/follow/$idUser'>关注此人</a>$separator";
		}

		return trim( $action, $separator );
	}

	static public function InRequestsAction($idUser, $actionRow=null , $wo = true, $separator='|') {
		$idUser = JWDB::CheckInt( $idUser );
		$action = null;

		$action .= "<a href='/wo/friend_requests/accept/$idUser'>接受请求</a>$separator";
		$action .= "<a href='/wo/friend_requests/deny/$idUser'>拒绝请求</a>$separator";

		return trim( $action, $separator );
	}

	static public function OutRequestsAction($idUser, $actionRow=null , $wo = true, $separator='|') {
		$idUser = JWDB::CheckInt( $idUser );
		$action = null;

		$action .= "<a href='/wo/friend_requests/cancel/$idUser'>取消请求</a>$separator";

		return trim( $action, $separator );
	}

	static public function FollowersAction($idUser, $actionRow , $wo = true, $separator='|') {
		$idUser = JWDB::CheckInt( $idUser );
		$action = null;

		if( true == $actionRow['d']  )
			$action .= "<a href='/wo/direct_messages/create/$idUser'>发送悄悄话</a>$separator";

		if($wo)
		{
			if( isset( $actionRow['add'] ) )
				$action .= "<a href='/wo/followings/follow/$idUser'>关注此人</a>$separator";
		}

		return trim( $action, $separator );
	}

	static public function RedirectTo404NotFound()
	{
		$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];

		header("Location: " . JWTemplate::GetConst('UrlError404') );
		exit(0);
	}

	static public function RedirectToUserPage( $nameUrl=null ) 
	{
		if( null == $nameUrl ) 
		{
			header( 'Location: /' );
		}else
		{
			header( 'Location: /' . urlEncode($nameUrl) . '/' );
		}
		exit(0);
	}

	static public function RedirectBackToLastUrl($defaultReturnRul='/')
	{
		$url = $defaultReturnRul;

		 if ( isset($_SERVER['HTTP_REFERER']) )
		{
			$url = $_SERVER['HTTP_REFERER'];
		}
		else if ( isset($_SERVER['REDIRECT_SCRIPT_URI']) )
		{
			$url = $_SERVER['REDIRECT_SCRIPT_URI'];
		}
		else if ( array_key_exists('404URL',$_SESSION) )
		{
			$url = $_SESSION['404URL'];
			unset ($_SESSION['404URL']);
		}

		if ( empty($url) ) {
			header('Location: /');
		} else {
			header("Location: $url");
		}
		exit(0);
	}
	static public function Strip($html) {
		$l = strpos($html, '>')+1;
		$r = strrpos($html, '<')-1;
		return substr($html, $l, $r-$l+1);
	}

	static public function ShowBalloon( $idUser )
	{
		$balloon_ids = JWBalloon::GetBalloonIds($idUser);

		$balloon_rows	= JWBalloon::GetDbRowsByIds($balloon_ids);

		if( false == empty( $balloon_ids ) ) {

			echo "<div>\n";
			foreach ( $balloon_ids as $balloon_id )
			{
				$balloon_row = $balloon_rows[$balloon_id];
				$html = JWBalloonMsg::FormatMsg($balloon_id,$balloon_row['html']);
				echo <<<_HEAD_
<div class="tipnote">
	$html
</div>
_HEAD_;
			}
			echo "</div>\n";
		}
	}

	static public function RedirectToUrl( $url=null )
	{
		if ( empty($url) )
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		Header( 'Location: ' . $url );
		exit(0);
	}
}
?>
