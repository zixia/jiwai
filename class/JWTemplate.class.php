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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
_HTML_;
		if( substr(@$_SERVER['SERVER_NAME'],0,5) == 'jiwai' && JWRequest::IsWapBrowser() ) {
			JWSession::SetInfo( 'notice', '手机WAP浏览器，请访问：http://m.JiWai.de/' );
		}
	}

	static public function html_head( $options=null )
	{
		$asset_url_css		= self::GetAssetUrl('/css/jiwai-screen.css');
		$asset_url_favicon	= self::GetAssetUrl('/img/favicon.ico'	   );
		$asset_url_js_jiwai	= self::GetAssetUrl('/js/jiwai.js'		   );
		$asset_url_js_moo	= self::GetAssetUrl('/lib/mootools/mootools.v1.11.js' );
		$asset_url_js_location	= self::GetAssetUrl('/js/location.js' );
		$asset_url_js_validator	= self::GetAssetUrl('/js/validator.js' );

		$title = '叽歪de / ';
		if ( empty($options['title']) )		$title .= '这一刻，你在做什么？';
		else								$title .= $options['title'];

		if ( empty($options['keywords']) )	$keywords = <<<_STR_
叽叽歪歪,唧唧歪歪,叽歪网,歪歪,唧唧,叽叽,唧歪网,矶歪de,唧歪de,唧歪的,微博客,迷你博客,碎碎念,絮絮叨叨,絮叨,jiwai,jiwaide,tiny blog,im nick
_STR_;
		else								$keywords = "叽叽歪歪,唧唧歪歪,歪歪,唧唧,叽叽," . $options['keywords'];

		if ( empty($options['description']) )	$description = <<<_STR_
叽歪de - 通过手机短信、聊天软件（QQ/MSN/GTalk/Skype）和Web，进行组建好友社区并实时与朋友分享的微博客服务。快来加入我们，踏上唧唧歪歪、叽叽歪歪的路途吧！
_STR_;
		else									$description = $options['description'] . ",叽叽歪歪,唧唧歪歪,歪歪,唧唧,叽叽" ;

		if ( empty($options['author']) )	$author = htmlspecialchars('叽歪de <wo@jiwai.de>');
		else								$author = $options['author'];


		$rss_html = <<<_HTML_
	<link rel="alternate"  type="application/rss+xml" title="叽歪de - [RSS]" href="http://feed.blog.jiwai.de" />
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
			$options['openid_server'] = 'http://jiwai.de/wo/openid/server';
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

		echo <<<_HTML_
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>$title</title>
	<meta name="keywords" content="$keywords" />
	<meta name="description" content="$description" />
	<meta name="author" content="$author" />
$rss_html
$refresh_html
$openid_html
	<link href="$asset_url_css" media="screen, projection" rel="Stylesheet" type="text/css" />
	<link rel="shortcut icon" href="$asset_url_favicon" type="image/icon" />
	<script type="text/javascript">window.ServerTime=$time;</script>
	<script type="text/javascript" src="$asset_url_js_moo"></script>
	<script type="text/javascript" src="$asset_url_js_jiwai"></script>
	<script type="text/javascript" src="$asset_url_js_location"></script>
	<script type="text/javascript" src="$asset_url_js_validator"></script>

	<link rel="start" href="http://JiWai.de/" title="叽歪de首页" />
	<meta name="ICBM" content="40.4000, 116.3000" />
	<meta name="DC.title" content="叽歪de" />
	<meta name="copyright" content="copyright 2007 http://jiwai.de" />
	<meta name="robots" content="all" />
	$ui_css

_HTML_;

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
		$nameScreen = @$userInfo['nameScreen'];
		$nameUrl = @$userInfo['nameUrl'];
		if ( empty($nameScreen) ) {
			$nav = array(
				'/' => '首页',
				'/wo/account/create' => '注册',
				'/wo/login' => '登录',
				'/help/' => '留言板'
			);
			$nameScreen = '游客';
			$nameUrl = 'public_timeline';
		} else {
			$nav = array(
				'/wo/' => '首页',
				'/public_timeline/' => '逛逛',
				'/wo/gadget/' => '窗可贴',
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

		if (!$highlight) {
			$a = array_reverse($nav);
			$urlNow = $_SERVER['REQUEST_URI'];
			$urlNow = ( $pos = strpos($urlNow, '?') ) ? substr($urlNow, 0, $pos) : $urlNow;
			foreach($highlightAlias as $u=>$aurl) if( 0===strncasecmp($u,$urlNow,strlen($u))){$urlNow=$aurl; break;}
			foreach ($a as $url => $txt) if (substr($urlNow, 0, strlen($url))==$url) { $highlight = $url; break; }
			if (!$highlight && empty($nameScreen) ) $highlight = '/public_timeline/'; //$url;
		}

?>
<div id="header">
	<div id="navigation">
		<h2><a class="header" href="http://jiwai.de/">叽歪de</a></h2>
		<div id="navtip" class="navtip">
		<form id='f3' action="<?php echo JW_SRVNAME . '/wo/search/users'; ?>" style="display:inline;">
		<table class="navtip_table"><tr>
			<td valign="middle">你好，<a href="<?php echo JW_SRVNAME . '/'. $nameUrl . '/';?>"><?php echo $nameScreen;?></a><?php if($nameUrl != 'public_timeline') echo '|<a href="'.JW_SRVNAME.'/wo/account/settings/">设置</a>|<a href="'.JW_SRVNAME.'/wo/logout">退出</a>'; ?>
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

        $friendsNum = JWFriend::GetFriendNum( $idUser );
        $followersNum = JWFollower::GetFollowerNum( $idUser );
        $inRequestsNum = JWFriendRequest::GetUserNum( $idUser );
        $outRequestsNum = JWFriendRequest::GetFriendNum( $idUser );

        if( $highlight == 'search' ) {
            global $q , $searched_num;
            $nav = array(
                    'search' => array("javascript:void(0);", "共找到符合\"$q\"的用户", $searched_num),
            );
        }else {
            if( $wo ) {
                $nav = array(
                    'friends' => array("/wo/friends/", "我的好友", $friendsNum),
                    'followers' => array("/wo/followers/","我的粉丝", $followersNum),
                    'inrequests' => array("/wo/friend_requests/","待审核好友",$inRequestsNum),
                    'outrequests' => array("/wo/friend_requests/?out","发出的好友请求",$outRequestsNum),
                );
            }else{
                $nav = array(
                    'friends' => array("/$userInfo[nameScreen]/friends/", "$userInfo[nameFull]的好友", $friendsNum),
                    //'followers' => array("$prefix/followers/","$userInfo[nameFull]的粉丝", $followersNum),
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
?>
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"/>
<?php
	}

	static public function footer()
	{
?>

<div id="footer">
	<h3>Footer</h3>
	<ul>
		<li class="first">&copy; 2007 叽歪de</li>

		<li><a href="http://help.jiwai.de/AboutUs" target="_blank">关于我们</a></li>
		<li><a href="http://help.jiwai.de/WeAreHiring" target="_blank">加入我们</a></li>
		<li><a href="http://help.jiwai.de/MediaComments" target="_blank">媒体和掌声</a></li>
		<li><a href="http://blog.jiwai.de/" target="_blank">Blog</a></li>
		<li><a href="http://help.jiwai.de/Api" target="_blank">API</a></li>
		<li><a href="/help/" target="_blank">帮助留言板</a></li>
		<!--li><a href="http://help.jiwai.de/TOS" target="_blank">使用协议</a></li-->
	</ul>
    <ul>
		<li><a href="http://www.miibeian.gov.cn" target="_blank">京ICP备07024804号</a></li>
    </ul>
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
?>
			<form action="<?php echo $mode==1 ? '/wo/direct_messages/create' : '/wo/status/update'; ?>" id="doingForm" method="post" onsubmit="$('jw_status').style.backgroundColor='#eee';">
				<fieldset >
					<div class="bar even">
						<h3>
							<label for="status">
								<?php echo $title?>
<?php if($mode==1) { ?>
						给：
							</label>
						</h3>
						<span class="user_select">
							<select style="width: 132px;" name="user[id]" id="user_id">
<?php
foreach ($options['friends'] as $id => $f) echo <<<_HTML_
<option value="$id">$f[nameScreen]</option>
_HTML_;
?>
                        </select>
                    </span>
<?php } else { ?> 
							</label>
<?php } ?>
                    </h3>
                    <span id="chars_left_notice" style="margin-top: 5px;">
                        还可输入: <strong id="status-field-char-counter">140</strong>个字符
                    </span>
                </div>
                <div class="jiwai_icon_vtab">
                    <div>
                        
                        <textarea  class="jiwai_icon_vtab_inner" id="jw_status" name="jw_status" onkeydown="if((event.ctrlKey && event.keyCode == 13) || (event.altKey && event.keyCode == 83)){$('doingForm').submit();return false;}" onkeyup="updateStatusTextCharCounter(this.value)" rows="3" cols="15"></textarea>
                    </div>
                </div>
                <div class="submit" >
                    <a class="button" href="#" style="margin-left:210px!important; margin-left:105px;" onclick="$('doingForm').submit();return false;"><img src="<?php echo self::GetAssetUrl("/images/org-text-jiwai.gif"); ?>" alt="叽歪一下" /></a>
                    <span style="margin-left:73px;color:#A2A2A2;">Ctrl+Enter直接叽歪</span>
                </div>
                <br/><br/>
				<div>
            <?php
                if(false == empty($options['sendtips']))
                {
                    $idCurrent = JWLogin::GetCurrentUserId();
                    $ValidNums = JWTemplate::GetSmsAndImValidNums($idCurrent);
					$imicoUrlSms = "/wo/devices/sms";
					$imicoUrlIm = "/wo/devices/im";
                
                    if(0 >= $ValidNums[0])
                    {
                        echo '<a class="sendtips" href="'. JW_SRVNAME . $imicoUrlSms . '">用手机来叽歪 ！</a><br />';
                    }
                    if(0 >= $ValidNums[1])
                    {
                        echo '<a class="sendtips" href="'. JW_SRVNAME . $imicoUrlIm . '">用QQ/MSN也能叽歪 ？</a><br />';
                    }
                }
            ?>    
				</div>
            </fieldset>
        </form>
	<br/>

<script type="text/javascript">
    $('jw_status').focus();

    function updateStatusTextCharCounter(value) {

        len_max = 140;

        if (len_max - value.length >= 0) {
            $('status-field-char-counter').innerHTML = len_max - value.length;
        } else {
            $('status-field-char-counter').innerHTML = 0;
            /*
            var ov = $('status').value;
            var nv = ov.substring(0, len_max);

            if( len_max == 70 ) {
                var max_nv = ov.substring(0, ++len_max);
                while( getStatusTextCharLengthMax( max_nv ) == 140 ) {
                    nv = max_nv;
                    max_nv = ov.substring(0, ++len_max);
                }
            }
            */
            //$('status').value = nv;  //not cut for bug
        }
    };

//]]>
</script>


</script>
<?php
/*
		<p class="notice">
			叽歪de MSN 机器人美眉目前正在偷懒，我们会很快将她找回来的。<br />
			请你先暂时使用 Web / QQ / GTalk 进行叽歪。
		</p>
*/

	}


	/*
	 *	显示 tab 方式的列表
	 *
	 *	@param	array	$menuArr	菜单数据，结构如下：
	 *	array ( 'menu1' => array ( 'active'=true, 'url'='/' ), 'menu2'=>array(...), ... );
	 */
	static public function tab_menu( $menuArr, $fix_pos=1 )
	{
		if ( empty($menuArr) )
		{
			JWLog::LogFuncName(LOG_CRIT, "need param menuArr") ;
			return;
		}

		$left = 510 - 80 * count($menuArr);
		$fix = $fix_pos ? 'margin-top: 7px;' : '';
		echo "<ul class=\"tabMenu\" style=\"margin-left: ${left}px; $fix\">";

		foreach ( $menuArr as $menu => $options )
		{
			$name 		= $options['name'];
			$url 		= $options['url'];
			$is_active	= $options['active'];

			if ( $is_active )
				echo "<li><a href = '$url' class = 'active' ";
			else
				echo "<li><a href = '$url' ";

			echo ">$name</a></li>\n";
		}

		echo "</ul>\n";
	}


	static public function tab_header( $vars=array() )
	{
/*
		//title2 长了之后，会莫名其妙的影响 tab 布局
		$vars=array('title'=>'最新动态 - 大家在做什么？' 
		//, 'title2'=>'你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢'//？'//：-）'
		, 'title2'	=>	'你想叽歪你就说嘛，'
		);
*/

		if ( !array_key_exists('title',$vars) )	
			$vars['title'] = '你和你的朋友们在做什么?';

		if ( !array_key_exists('title2',$vars) )	
			$vars['title2'] = '每分钟自动刷新一次。';
?>

				<table class="layout_table" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="bg_tab_top_left">
						</td>
						<td class="bg_tab_top_mid">
							<h2><?php echo $vars['title']; ?></h2>
<?php
		if (!empty($vars['find'])) {
            global $q;
?>
<form action="/wo/search/users" method="GET" id="search_user"><input type="text" name="q" value="<?php echo (isset($q)) ? $q : '用户名、Email';?>" onclick='this.value=""' /><button onClick='$("search_user").submit();'>找</button></form>
<?php
		}
?>
						</td>
						<td class="bg_tab_top_right">
						</td>
					</tr>
					<tr class="odd">
						<td class="bg_tab_top2_left">
						</td>
						<td colspan="2" class="bg_tab_top2_right">
							<?php //echo $vars['title2']; ?>
						</td>
					</tr>

				</table>

<?php
	}


	static public function StatusHead($idUser, $userRow, $statusRow, $options=null, $isOpen=true)
	{
		$name_screen = $userRow['nameScreen'];
		$name_url = $userRow['nameUrl'];
		$name_full = $userRow['nameFull'];

		if ( !empty($statusRow['idPicture']) )
			$photo_url	= JWPicture::GetUrlById($statusRow['idPicture'], 'thumb96');
		else
			$photo_url	= JWPicture::GetUserIconUrl($idUser, 'thumb96');
	

		if ( !isset($options['trash']) )
			$options['trash'] = true;


		$device		= 'WEB';
		if ( ! $isOpen )
		{
			$status		= <<<_HTML_
我只和我的好友分享我的叽歪de。<br /><a href="/wo/friendships/create/$idUser" onclick="return JiWai.requestFriend($idUser, this);">加我为好友。</a>
_HTML_;

		}
		else if ( empty($statusRow) )
		{
			$status		= "迄今为止还没有叽歪过！";
		}
		else	
		{
			$status_id 	= $statusRow['idStatus'];
			$status		= $statusRow['status'];
			$timeCreate	= $statusRow['timeCreate'];
			$sign		= $statusRow['isSignature'] == 'Y' ? '签名' : '';
			$device		= $statusRow['device'];
			$device		= JWDevice::GetNameFromType($device, @$statusRow['idPartner']);
			
			$duration	= JWStatus::GetTimeDesc($timeCreate);
			$duration2	= JWStatus::GetTimeDesc($timeCreate, true);


			$status_result 	= JWStatus::FormatStatus($statusRow);
			$status			= $status_result['status'];
			$replyto		= $status_result['replyto'];

			$isMms			= ( @$statusRow['isMms'] == 'Y') ;

		}


		$current_user_id	= JWLogin::GetCurrentUserId();

?>
			<div id="permalink">
				<div class="odd" style="padding-top:0; padding-left:0; padding-right:0; background: none;">
				<?php if( @$isMms ) { ?>
					<div class="head"><a href="/<?php echo $name_url; ?>/mms/<?php echo $statusRow['id']; ?>"><img alt="<?php echo $name_full; ?>" src="<?php echo $photo_url?>" width="96" height="96" style="padding: 1px;" /></a></div>
				<?php } else { ?>
					<div class="head"><a href="/wo/account/profile_image/<?php echo $name_url; ?>"><img alt="<?php echo $name_full; ?>" src="<?php echo $photo_url?>" width="96" height="96" style="padding: 1px;" /></a></div>
				<?php } ?>
					<div class="cont">
						<div class="bg"></div>
<table width="100%" border="0" cellspacing="5" cellpadding="0">
  <tr>
    <td><h3><?php echo $name_screen; ?></h3></td>
    <td align="right">
<?php
if ($current_user_id!=$idUser) {
if ( isset($current_user_id) && JWFollower::IsFollower($idUser, $current_user_id) ) {
?>
      <small> 已订阅 </small>
<?php
} else {
	$oc = ( JWUser::IsProtected($idUser) && !JWFriend::IsFriend($idUser, $current_user_id) ) ? 'onclick="return JiWai.requestFriend('.$idUser.', this);"' : '';
?>
      <a href="/wo/friendships/create/<?php echo $idUser;?>" <?php echo $oc; ?>>成为<?php echo $name_screen; ?>的粉丝吧</a>
<?php
}
} else {
?>
<small>  &nbsp; </small>
<?php
}
?>
    </td>
  </tr>
  <tr>
    <td colspan="2"><?php echo $status?></td>
   </tr>
  <tr>
    <td><span class="meta">
<?php
if ( $isOpen && isset($statusRow) ) 
{
	echo <<<_HTML_
  					<a href="/$name_url/statuses/$status_id" title="$duration2" alt="$duration2" >$duration</a>
  					来自 $device $sign
_HTML_;

	if (!empty($replyto))
	{
		if ( empty($statusRow['idStatusReplyTo']) )
			echo " <a href='/$replyto/'>给 ${replyto} 的回复</a> ";
		else
			echo " <a href='/$replyto/statuses/$statusRow[idStatusReplyTo]'>给 ${replyto} 的回复</a> ";
	}
}
?>
    </span></td>
    <td align="right">
<?php
if ( $isOpen && isset($statusRow) && isset($current_user_id) )	
{
	$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);

	echo self::FavouriteAction($status_id,$is_fav);
	if ( ( $current_user_id==$idUser || JWUser::IsAdmin($current_user_id) )
			&& $options['trash'] )
	{
		echo self::TrashAction($status_id);
	}
}
?>
    </td>
  </tr>
</table>
					</div>
			      </div>
		  </div>	

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
	<a href="#" onclick="JiWai.DoTrash($idStatus);" title="$asset_trash_alt" alt="$asset_trash_alt"><img border="0" src="$asset_trash_url" />$asset_trash_alt2</a>
_HTML_;
		if( @$options['isMms'] ) {
			$html_str = <<<_HTML_
	<a onclick="JiWai.DoTrash($idStatus);" title="$asset_trash_alt" alt="$asset_trash_alt" class="del">$asset_trash_alt</a>
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
			$ajax_url		= "/wo/favourites/destroy/$idStatus";
		}
		else
		{
			$asset_star_alt = '收藏它';
			//$asset_star_title = '未收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_empty.gif");
			$ajax_url		= "/wo/favourites/create/$idStatus";
		}
		$html_str = <<<_HTML_
    	<a href="#" onclick="JiWai.ToggleStar($idStatus);" title="$asset_star_alt" alt="$asset_star_alt"><img id="status_star_$idStatus" border="0" src="$asset_star_url" /><span id="status_star_text_$idStatus">$asset_star_alt2</span></a>
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
		
		$idCurrent = JWLogin::GetCurrentUserId();

		if ( empty($statusIds) || empty($userRows) || empty($statusRows) )
			return;

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

		$current_user_id = JWUser::GetCurrentUserInfo('id');
		if (!$options['strip']) {
?>
<div id="timeline" <?php echo ($options['isMms']) ? 'class="wapnew"':'' ?>>
<?php
		}
		$n=0;
		$user_showed = array();

		foreach ( $statusIds as $status_id ){
			if( !isset($statusRows[$status_id]) )
				continue;

			$user_id 	= $statusRows[$status_id]['idUser'];

			if ( $options['protected'] || 
					( JWUser::IsProtected($user_id) && 
					  	(
							( $idCurrent 
								&& false == JWFriend::IsFriend($user_id, $idCurrent) 
								&& $idCurrent != $user_id 
							)
							|| 
							( !$idCurrent )
					       	)	
				       	)
			   )
			continue;
				
			// 最多显示的条数已经达到
			if ( $options['nummax'] && $n >= $options['nummax'] )
				break;
			// 如果设置了一个用户只显示一条，则跳过
			if ( $options['uniq']>0 && @$user_showed[$user_id]>=$options['uniq'] )
				continue;
			else
				@$user_showed[$user_id] += 1;
				
			$name_screen	= $userRows[$user_id]['nameScreen'];
			$name_url 	= $userRows[$user_id]['nameUrl'];
			$name_full	= $userRows[$user_id]['nameFull'];
			$status		= $statusRows[$status_id]['status'];
			$timeCreate	= $statusRows[$status_id]['timeCreate'];
			$device		= $statusRows[$status_id]['device'];
			$idPartner	= @$statusRows[$status_id]['idPartner'];
			$reply_id	= $statusRows[$status_id]['idStatusReplyTo'];
			$sign		= ( $statusRows[$status_id]['isSignature'] == 'Y' ) ?  '签名' : '';
			
			$duration	= JWStatus::GetTimeDesc($timeCreate);
			$duration2	= JWStatus::GetTimeDesc($timeCreate, true);

			if ( !empty($statusRows[$status_id]['idPicture']) ) {
				if( $options['isMms'] ) {
					$photo_row = JWPicture::GetDbRowById( $statusRows[$status_id]['idPicture'] );
					$photo_subject = $photo_row['fileName'];
					$photo_url = JWPicture::GetUrlById($statusRows[$status_id]['idPicture'], 'picture');
				} else {
					$photo_url = JWPicture::GetUrlById($statusRows[$status_id]['idPicture']);
				}
			} else {
				$photo_url	= JWPicture::GetUserIconUrl($user_id);
			}
	
			$deviceName	= JWDevice::GetNameFromType($device, @$statusRows[$status_id]['idPartner'] );

			$formated_status 	= JWStatus::FormatStatus($statusRows[$status_id]);

			$replyto			= $formated_status['replyto'];
			$status				= $formated_status['status'];
			if ($n) { //分割线
?>
<div class="line"></div>
<?php
			}
			$n++;

			if( $options['isMms'] ) {
?>


<div class="odd">
	<div class="head">
		<a href="/<?php echo $name_url;?>/mms/<?php echo $status_id;?>"><img alt="<?php echo $photo_subject;?>" src="<?php echo $photo_url;?>"/></a>
		<div class="meta"><?php echo substr($statusRows[$status_id]['timeCreate'],0,16);?>来自彩信</div>
	</div>
	<div class="cont">
		<div class="bg"></div>
		<div class="name"><a href="/<?php echo $name_url;?>/" title="<?php echo $name_full;?>"><?php echo $name_full;?></a><span class="meta">拍摄时间：<?php echo substr($statusRows[$status_id]['timeCreate'],0,16);?><span id="status_actions_6361924"></span></span></div>
		<?php echo $status; ?>
		<div class="action">
<?php
	if ( ( $current_user_id==$user_id || JWUser::IsAdmin($current_user_id) ) 
			&& $options['trash'] ) {
		echo self::TrashAction($status_id);
	}
	if( $current_user_id ) {
		$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);
		echo self::FavouriteAction($status_id,$is_fav);
	}
?>
		</div>
	</div>
</div>

<?php
			}else{
?>
<div class="odd" id="status_<?php echo $status_id;?>">
	<div class="head"><a href="/<?php echo $name_url;?>/<?php echo (@$statusRows[$status_id]['isMms']=='Y')? 'mms/'.$status_id : '' ?>"><img width="48" height="48" title="<?php echo $name_screen; ?>" alt="<?php echo $name_full; ?>" src="<?php echo $photo_url?>"/></a></div>
	<div class="cont"><div class="bg"></div><a href="/<?php echo $name_url;?>/" title="<?php echo $name_screen; ?>" class="name"><?php echo $name_screen;?></a><?php echo $status; ?>

		<span class="meta">
<?php if (is_numeric($status_id)) {?>
			<a href="/<?php echo $name_url?>/statuses/<?php echo $status_id?>" title="<?php echo $duration2; ?>" alt="<?php echo $duration2; ?>"><?php echo $duration?></a>
<?php } else {
			echo $duration;	
} ?>
			来自 <?php echo "$deviceName $sign"?> 
<?php if (!empty($replyto) ) { ?>
			<a href="<?php echo empty($reply_id) ? "/$replyto/" : "/$replyto/statuses/$reply_id"; ?>">给 <?php echo $replyto; ?> 的回复</a>
<?php } ?>
			<span id="status_actions_<?php echo $status_id?>">
<?php	
if ( isset($current_user_id) && is_numeric($status_id) )	
{
	$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);
	echo self::FavouriteAction($status_id,$is_fav);
	if ( ( JWUser::IsAdmin($current_user_id) || $current_user_id==$user_id  ) && $options['trash'] )
	{
		//是自己的 status 可以删除
		echo self::TrashAction($status_id);
	}
}
?>
			</span>
		</span><!-- meta -->
	</div><!-- cont -->
</div><!-- odd -->
<?php
			}
?>
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
<div class="search">
    <form action="/wo/search/statuses" method="GET" id="search_status"><input type="text" name="q" value="<?php echo (isset($q)) ? $q : '输入关键词';?>" onclick='this.value=""' /><button onClick='$("search_status").submit();'>搜</button></form>
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
<a href="$u">$i</a>
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
</div><!-- timeline -->
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
				<td><input type="text" name="username_or_email" id="email" /></td>
			</tr>
 			<tr>
    				<td align="center">密　码</td>
    				<td><input type="password" name="password" /></td>
  			</tr>
  			<tr>
    				<td>&nbsp;</td>
				<td><input type="checkbox" name="remember_me" value="checkbox" id="remember_me" /> 记住我</td>
  			</tr>
  			<tr>
				<td>&nbsp;</td>
                <td><input type="submit" class="submitbutton" value="登 录" /></td>
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
		<div style="padding:5px 0 5px 50px; height:35px;"><a class="button" href="/wo/account/create"><img src="<?php echo self::GetAssetUrl('/images/org-text-regnow.gif'); ?>" alt="马上注册" /></a></div>
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

		$activeOrder = isset( $options['activeOrder'] ) ? $activeOrder : true;

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

		//$user_db_rows 		= JWUser::GetUserDbRowsByIds($user_ids);
		$user_db_rows = JWUser::GetUserDbRowsByIds($user_ids, $activeOrder, 80);
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
				<li><a href="/$user_db_row[nameUrl]/" title="$user_db_row[nameScreen]" rel="contact" onmouseover="JiWai.ShowThumb($(this).getFirst());" onmouseout="JiWai.HideThumb(this.getFirst());">$user_db_row[nameScreen]<img src="$user_icon_url" class="tip" style="display:none;" alt="$user_db_row[nameFull]" width="48" height="48"/></a></li>

_HTML_;
					break;
				default:
					if ($n % 4 == 0) echo "			<tr>\n";
					$name = $user_db_row['nameScreen'];
					//if (mb_strwidth($name)>8) $name = mb_strimwidth($name, 6, '...');
					echo <<<_HTML_
				<td><div><a href="/$user_db_row[nameUrl]/" title="$user_db_row[nameScreen]" rel="contact"><img src="$user_icon_url" alt="$user_db_row[nameFull]" border="0" />$name</a></div></td>

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
		if ( empty($options['user_name']) )
			return;
		else
			$user_name = $options['user_name'];
		
		if ( empty($options['num']) )
			$num = 5;
		else
			$num = $options['num'];

		if ( empty($options['title']) )
			$title = '公告';
		else
			$title = $options['title'];

        if ($title =='公告')
            $isAnnounce=true;


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
				<a href="http://e.jiwai.de/cbc2007/" target="_blank"><img title="叽歪de× 中文网志年会" alt="叽歪de× 中文网志年会" height="25" src="http://blog.jiwai.de/images/jiwaicbc.gif" /></a>
			</li>
			<li class="FeaturedImage">
				<a href="/wo/bindother/" target="_blank"><img title="绑定 Twitter" alt="绑定 Twitter" height="25" src="http://blog.jiwai.de/images/twitterbind.gif" /></a>
			</li>
			<li class="FeaturedImage">
				<a href="/wo/devices/im/" target="_blank"><img title="绑定 Yahoo" alt="绑定 Yahoo" height="25" src="http://blog.jiwai.de/images/yahoobind.gif" /></a>
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

			<div class="but"><button onclick="window.open('http://blog.jiwai.de/');">叽歪大记事</button><button onclick="window.open('http://blog.jiwai.de/');">更多公告</button></div>
_HTML_;
	}


	function sidebar_invite() {
?>
		<div class="featured" id="invite" style="line-height:30px;">
			<p><a href="/wo/account/invite">邀请好友加入JiWai</a></p>
		</div>

<?php
	}

	function sidebar_bookmarklet() {
		//XXX: bookmarklets in IE6 are limited to 508 characters. 
?>
		<div class="featured" id="bookmarklet" style="line-height:30px;">
			<p><a onclick="if(confirm('将此按钮拖拽或添加到浏览器的收藏夹或工具栏上，即可方便的分享网址信息到叽歪。\r\n需要了解详细的使用方法吗？'))location.href='http://help.jiwai.de/BookmarkletUsage';return false;" href="javascript:var%20d=document,w=window,l=d.location,e=encodeURIComponent,x=w.getSelection?w.getSelection().toString():d.getSelection?d.getSelection():d.selection.createRange().text;if(!x){var%20m=d.getElementsByTagName('meta');for(var%20i=0;i<m.length;i++)if(/esc/.test(m[i].name))x=m[i].content;}var%20u='http://jiwai.de/wo/share/?u='+e(l.href)+'&t='+e(d.title)+'&d='+e(x);var%20o=function(){if(!w.open(u,'J','toolbar=0'))l.href=u+'&f'};if(/refo/.test(navigator.userAgent))setTimeout(o,0);else{o()};void(0)">分享到叽歪</a></p>
		</div>

<?php
	}

	function sidebar_separator() {
?>

		<div class="line"><div></div></div>
<?php
	}

	/*
	 * Sidebar menu
	 *	@param array	item list	array ( array('user_notice'	, array(param1=>val1,param2=>val2))
											, array('count'		, array(param1=>val1,param2=>val2))
										);
	 *
	 */
	static public function sidebar( $menuList=array(), $idUser=null )
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

		echo <<<_HTML_
		<ul class="actions">
_HTML_;


		if ( isset($action['nudge']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/nudge/$arr_user_info[id]">挠挠 $arr_user_info[nameScreen]</a>
			</li>
_HTML_;
		}

		if ( isset($action['d']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/direct_messages/create/$arr_user_info[id]">向$arr_user_info[nameScreen]发悄悄话</a>
			</li>
_HTML_;
		}


		if ( isset($action['follow']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/follow/$arr_user_info[id]">成为 $arr_user_info[nameScreen] 的粉丝</a>
			</li>
_HTML_;
		}

		if ( isset($action['leave']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/leave/$arr_user_info[id]">退定 $arr_user_info[nameScreen]</a>
			</li>
_HTML_;
		}

		if ( isset($action['cancel']) )
		{
			echo <<<_HTML_
			<li><a href="/wo/friend_requests/cancel/$arr_user_info[id]">取消请求 $arr_user_info[nameScreen]</a></li>
_HTML_;
		}


		if ( isset($action['add']) )
		{
			$oc = (JWUser::IsProtected($arr_user_info['id'])) ? 'onclick="return JiWai.requestFriend('.$arr_user_info['id'].', this);"' : '';
			echo <<<_HTML_
			<li><a href="/wo/friendships/create/$arr_user_info[id]" $oc>加$arr_user_info[nameScreen]为好友</a></li>
_HTML_;
		}

		if ( isset($action['remove']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friendships/destroy/$arr_user_info[id]" 
						onclick="return confirm('请确认删除好友 $arr_user_info[nameScreen] ')">删除好友 $arr_user_info[nameScreen]</a>
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
		<ul class="about">
<?php
	        echo "<li>名字: " . htmlspecialchars($aUserInfo['nameFull']) . "</li>\n";
			if ( !empty($aUserInfo['bio']) )
				echo "<li>自述: " . htmlspecialchars($aUserInfo['bio']) . "</li>\n";
			if ( !empty($aUserInfo['location']) ) {
				$location = JWLocation::GetLocationName( $aUserInfo['location'] );
				echo "<li>位置: $location</li>\n";
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

				echo "<li>网站:  <a href='" . htmlspecialchars($url) . "'"
											. " rel='" . htmlspecialchars($aUserInfo['nameFull']) . "'"
											. " target='_blank' "
											. ">" . htmlspecialchars($show_url) . "</a></li>\n";
			}
?>
		</ul>
<?php
	}

   /*
   功能：显示绑定设备信息
   作者：WqSemc
   日期：2007-10-16
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
              $arrUseDevices =array();
			  foreach($aDeviceInfo_rows as $aDeviceInfo_row)
              { 
                  $deviceType = $aDeviceInfo_row['type'];
                  if (empty($aDeviceInfo_row['secret']))
                  {
                      $arrUseDevices[$aDeviceInfo_row['type']]=true;
                      if ($aDeviceInfo_row['type'] == 'sms') {
                          $imicoUrlHref = $isUserLogined ? $imicoUrlSms : $imicoUrlHelpSms;
                      } else {
                          $imicoUrlHref = $isUserLogined ? $imicoUrlIm : $imicoUrlHelpIm;
                      }
                      echo <<<_HTML_
                          <a href="$imicoUrlHref"><img src=$imicoUrl/jiwai-$deviceType.gif alt="$deviceType" title="$deviceType" /></a>
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
                    <a href=http://help.jiwai.de/IMFAQ target=_blank>QQ、MSN、Gtalk常见问题</a
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
			<img style="display:none;" src="<?php echo $photo_url; ?>" alt="<?php echo $aUserInfo['nameScreen'];?>" width="48" height="48" /><h2 class="forul"><?php echo $aUserInfo['nameScreen'];?>的资料</h2>
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
				<a href="/wo/friend_requests/">${num}个好友添加请求！</a>
			</li>
		</ul>

_HTML_;
	}


	static function sidebar_count( $countInfo=null, $userInfo=null )
	{
		if ( empty($userInfo) ) {
		    $user = 'wo';
		} else {
		    $user = $userInfo['nameUrl'];
		    $name_full = $userInfo['nameFull'];
			$name_screen = $userInfo['nameScreen'];
		}

		$userInSession = JWUser::GetUserInfo(JWLogin::GetCurrentUserId());
		$asset_star_url = self::GetAssetUrl("/img/icon_star_full.gif");
		echo <<<_HTML_
		<ul id="update_count">
_HTML_;

		if ($user != 'wo') echo <<<_HTML_
			<li style="font-size:12px; text-indent:12px;">$name_screen 目前有：</li>
_HTML_;

		echo <<<_HTML_
			<li id="friend_count"><a href="/$user/friends/">$countInfo[friend] 个好友</a></li>
_HTML_;
		
		if ( 'wo'==$user || $user === @$userInSession['nameScreen'] )
		{
			echo <<<_HTML_
				<li id="follower_count"><a href="/wo/followers/">$countInfo[follower] 个粉丝</a></li>
_HTML_;
		}else{
			echo <<<_HTML_
				<li id="follower_count"><a style="text-decoration:none;" href="javascript:void(0);">$countInfo[follower] 个粉丝</a></li>
_HTML_;
		}

		if ( 'wo'==$user || $user === @$userInSession['nameUrl'] )
		{
			echo <<<_HTML_
			<li id="message_count"><a href="/wo/direct_messages/">$countInfo[pm] 条悄悄话</a></li>
_HTML_;
		}


		echo <<<_HTML_
			<li id="favourite_count"><a href="/$user/favourites/">$countInfo[fav] 条收藏</a><img border="0" src="$asset_star_url" /></li>
			<li id="status_count"><a href="/$user/">$countInfo[status] 条叽歪</a></li>
_HTML_;

		if ( 'wo'!=$user && @$countInfo['mms'] )
		{
			echo <<<_HTML_
			<li id="mms_count"><a href="/$user/mms/">$countInfo[mms] 条彩信</a></li>
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
			<a href="/wo/account/profile"><img src="<?php echo $photo_url; ?>" alt="<?php echo $userInfo['nameScreen'];?>" width="48" height="48" align="middle" /></a>欢迎你 <?php echo $userInfo['nameFull'];?>
            <br />
            <?php
            
                if(false == JWTemplate::IsUserUploadPic(JWLogin::GetCurrentUserId()))
                {
                    echo '<a class="sendtips" style="margin-left:0" href="/wo/account/profile">上传头像 ↑ </a>';
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
				<li class="slect" onmouseover="style.backgroundColor='#FF830B';" onmouseout="style.backgroundColor='#F97B00'"><?php echo $viaDevName; ?></li>
<?php
		foreach ($otherDev as $d => $n) if ($d!='facebook') echo <<<__HTML__
				<li onmouseover="style.backgroundColor='#FF830B';" onmouseout="style.backgroundColor='#F97B00'" onclick="JiWai.ChangeDevice('$d');">$n</li>

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
		
		#$title = preg_match( '#/wo/#', $_SERVER['REQUEST_URI'] ) ? '有朋自远方来' : '邻踪侠影' ;
		$title = '最近有谁来过';
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

		self::sidebar_featured(array('user_ids'=>$friendIds, 'title'=>'最近上线好友', 'id'=>'friend'));
		return;
		$friend_rows			= JWUser::GetUserDbRowsByIds($friendIds);

        $picture_ids        	= JWFunction::GetColArrayFromRows($friend_rows, 'idPicture');
        $picture_url_rows   	= JWPicture::GetUrlRowByIds($picture_ids);

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
			<a href="/$friend_info[nameScreen]/" rel="contact" title="$friend_info[nameFull]($friend_info[nameScreen])"><img alt="$friend_info[nameFull]" height="24" src="$picture_url" width="24" /></a>

_HTML_;
		}
  		echo <<<_HTML_
		</div>

_HTML_;
	}

	static public function sidebar_rss ( $type, $id , $forceName=null)
	{
   		$rss_url = "http://api.jiwai.de/statuses/";

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
   		$rss_url = "http://api.jiwai.de/statuses/";

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
				'UrlContactUs' => 'http://help.jiwai.de/ContactUs',
				'UrlRegister' => '/wo/account/create',
				'UrlLogin' => '/wo/login',
				'UrlResetPassword' => 'http://jiwai.de/wo/account/confirm_password_reset',
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
这里是叽歪de(<strong>Alpha</strong>)测试系统。Alpha测试的定义为：在有开发者关注下对系统进行使用测试。如果你想试用最新的，还在开发中的功能，那么可以在这里继续访问。但是需要注意的是，系统也许会经常的工作不正常，甚至出错，所以我们建议你至少使用<a href='http://beta.$domain_url'>Beta系统</a>。
</p>
_MSG_;
				break;

			case 'beta':
				$msg = <<<_MSG_
<p>
这里是叽歪de(<strong>Beta</strong>)测试系统。如果你想试用最新的，正在准备升级的功能时，欢迎在这里继续访问。最新的系统功能可能有不稳定的情况，欢迎向我们<a href='mailto:wo@jiwai.de'>报告BUG</a>。如果你希望使用最为稳定的版本，请来正式运行的网站：<a href='http://$domain_url'>叽歪de</a>。
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


	static public function	ShowActionResultTips()
	{
		$error_html		= JWSession::GetInfo('error');
		$notice_html	= JWSession::GetInfo('notice');

		$is_exist = false;

		if ( !empty($error_html) )
		{
			$is_exist = true;
			echo <<<_HTML_
			<div class="tipnote-red" onclick="return JiWai.KillNote(this);"> $error_html </div>
_HTML_;
		}


		if ( !empty($notice_html) )
		{
			$is_exist = true;
			echo <<<_HTML_
			<div class="tipnote" onclick="return JiWai.KillNote(this);"> $notice_html </div>
_HTML_;
		}

		if ( $is_exist )
		{
			echo <<<_HTML_
<script type="text/javascript">JiWai.Yft(".notice");</script>
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
			$options['type'] = 'friends';

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

		$list_user_rows = JWUser::GetUserDbRowsByIds($idListUsers);

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
			$statusNum = JWStatus::GetStatusNum( $list_user_id );
			$mmsNum = JWPicture::GetMMSNum( $list_user_id );
        
			$timeUpdate = $list_user_row['timeStamp'];

			$action_row = $action = $liNote = null;
			switch( $options['type'] ) {
				case 'friends':
					$action_row = $action_rows[$list_user_id];
					$action = self::FriendsAction($list_user_id, $action_row , $wo);
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
					$action = self::FollowersAction($list_user_id, $action_row , false);
					$action .= '|'. self::FriendsAction($list_user_id, $action_row , false);
					$action = trim( $action, '|' );
				break;
			}

			echo <<<_HTML_
	<ul class="liketable"><img src="$list_user_icon_url" width="48" height="48" class="img" title="$list_user_row[nameFull]($list_user_row[nameScreen])" />
		<li class="name"><a href="/$list_user_row[nameUrl]/" title="$list_user_row[nameScreen]">$list_user_row[nameScreen]</a></li>
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

    static public function FriendsAction($idUser, $actionRow , $wo = true, $separator='|') {
        $idUser = JWDB::CheckInt( $idUser );
        $action = null;
        if( isset( $actionRow['follow'] ) )
            $action .= "<a href='/wo/friends/follow/$idUser'>关注</a>$separator";
        if( isset( $actionRow['leave'] ) )
            $action .= "<a href='/wo/friends/leave/$idUser'>取消关注</a>$separator";

        if( $wo ) $action .= "<a href='/wo/friendships/destroy/$idUser'>删除</a>$separator";

        if( isset( $actionRow['nudge'] ) )
            $action .= "<a href='/wo/direct_messages/create/$idUser'>悄悄话</a>$separator";
        
        return trim( $action, $separator );
    }

    static public function InRequestsAction($idUser, $actionRow=null , $wo = true, $separator='|') {
        $idUser = JWDB::CheckInt( $idUser );
        $action = null;

        $action .= "<a href='/wo/friend_requests/accept/$idUser'>接受</a>$separator";
        $action .= "<a href='/wo/friend_requests/deny/$idUser'>拒绝</a>$separator";

        return trim( $action, $separator );
    }

    static public function OutRequestsAction($idUser, $actionRow=null , $wo = true, $separator='|') {
        $idUser = JWDB::CheckInt( $idUser );
        $action = null;

        $action .= "<a href='/wo/friend_requests/cancel/$idUser'>取消</a>$separator";

        return trim( $action, $separator );
    }

    static public function FollowersAction($idUser, $actionRow , $wo = true, $separator='|') {
        $idUser = JWDB::CheckInt( $idUser );
        $action = null;
        if( isset( $actionRow['add'] ) )
            $action .= "<a href='/wo/friendships/create/$idUser'>加为好友</a>$separator";

        if( isset( $actionRow['nudge'] ) )
            $action .= "<a href='/wo/direct_messages/create/$idUser'>悄悄话</a>$separator";
        
        return trim( $action, $separator );
    }

	static public function RedirectTo404NotFound()
	{
		$_SESSION['404URL'] = $_SERVER['SCRIPT_URI'];

		header("Location: " . JWTemplate::GetConst('UrlError404') );
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
?>
