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
	 * calc $n of asset${n}.jiwai.de/$path?$timestamp
	 *
	 * @var int
	 */
	static $msAssetCounter = 0;

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

	static public function html_head()
	{
		$asset_url_css		= self::GetAssetUrl('/css/jiwai-screen.css'	,false);
		$asset_url_favicon	= self::GetAssetUrl('/img/favicon.gif'		,false);
		$asset_url_js_jiwai	= self::GetAssetUrl('/js/jiwai.js'			,false);
		$asset_url_js_moo	= self::GetAssetUrl('/js/mootools.v1.1.js'	,false);
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


	<title>叽歪de / 这一刻，你在做什么？</title>

	<link rel="start" href="http://JiWai.de/" title="叽歪de我" />

	<link href="<?php echo $asset_url_css?>" media="screen, projection" rel="Stylesheet" type="text/css" />

	<meta name="ICBM" content="40.4000, 116.3000" />
	<meta name="DC.title" content="叽歪de" />

	<meta name="keywords" content="叽歪de, 叽歪的, 唧歪de, 唧歪的, 叽叽歪歪, 唧唧歪歪, tiny blog, blog, im nick, nick, log, 记录, 写下" />

	<meta name="description" content="叽歪de - 记录、并与朋友分享你每天的点滴。" />

	<meta name="author" content="JiWai.de, 叽歪de, 叽歪" />

	<meta name="copyright" content="copyright 2007 http://jiwai.de" />

	<meta name="robots" content="all" />

	<link rel="shortcut icon" href="<?php echo $asset_url_favicon?>" type="image/gif" />
	
	<link rel="alternate" title="叽歪de - [RSS]" href="http://feeds.feedburner.com/jiwai" />

	<script type="text/javascript" src="<?php echo $asset_url_js_jiwai?>"></script>
	<script type="text/javascript" src="<?php echo $asset_url_js_moo?>"></script>

</head>

<?php

	}


	static public function accessibility()
	{
?>


<ul id="accessibility">
    <li>
      您正在使用手机吗？请来这里：<a href="http://m.JiWai.de/">m.JiWai.de</a>!
    </li>
	<li>
		<a href="#navigation" accesskey="2">跳转到导航目录</a>
	</li>
	<li>
		<a href="#side">跳转到功能目录</a>
	</li>
</ul>


<?php
	}


	static public function header()
	{
		$nameScreen = JWUser::GetCurrentUserInfo('nameScreen');
?>

<div id="header">
	<div id="navigation">
		<h2><a class="header" href="/">叽歪de</a></h2>
<?php if ( strlen($nameScreen) ){ ?>
		<ul>
			<li class="first"><a href="/wo/">记录一下</a></li>
			<li><a href="/<?php echo $nameScreen ?>/">我的记录</a></li>
			<li><a href="<?php echo self::GetConst('UrlPublicTimeline')?>">最新动态</a></li>
			<li><a href="/wo/invitations/invite">邀请</a></li>
			<li><a href="/wo/gadget/">窗可贴</a></li>
			<li><a href="/wo/account/setting">设置</a></li>
			<li><a href="/help/">帮助</a></li>
			<li><a href="/wo/logout">退出</a></li>
		</ul>
<?php } ?>
	</div>
</div>

<?php
	}


	static public function footer()
	{
?>

<div id="footer">
	<h3>Footer</h3>
	<ul>
		<li class="first">&copy; 2007 叽歪de - JiWai.de, all rights reserved</li>

		<li><a href="/help/aboutus">关于我们</a></li>
		<li><a href="/help/contact">联系我们</a></li>
		<li><a href="http://blog.jiwai.de/">Blog</a></li>
		<li><a href="/help/api">API</a></li>
		<li><a href="/help">帮助</a></li>
		<li><a href="http://help.jiwai.de/tos">使用协议</a></li>

	</ul>
</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">

setTimeout("run_google();", 500);

function run_google() 
{
    if (!window.urchinTracker) {
        setTimeout(run_google, 500);
        return;
    }

	_uacct = "UA-287835-11";
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
<?php
	}


	static public function updater()
	{
?>
			<form action="/wo/status/update" id="doingForm" method="post" onsubmit="new Ajax.Request('/status/update', {asynchronous:true, evalScripts:true, onComplete:function(request){$('status').value = ''; updateStatusTextCharCounter($('status').value);$('submit').disabled = false;$('submit_loading').style.display='none';Effect.Appear('chars_left_notice', {duration:0.5});}, onLoading:function(request){$('submit').disabled = true;Effect.Appear('submit_loading', {duration:0.3});$('chars_left_notice').style.display='none';}, parameters:Form.serialize(this)}); return false;">
				<fieldset>
					<div class="bar odd">
						<h3>
							<label for="status">
								这一刻，你在做什么？
							</label>
						</h3>
						<span id="chars_left_notice">
							还可输入: <strong id="status-field-char-counter"></strong>个字。
						</span>
						<span style="display:none" id="submit_loading">
							<script type="text/javascript">
//<![CDATA[
document.write('<img alt="Updating" src="http://asset.jiwai.de/img/updating.gif" title="更新中..." />')
//]]>

							</script>
						</span>
					</div>
					<div class="jiwai_icon_vtab">
						<div>
							
							<textarea  class="jiwai_icon_vtab_inner" id="status" name="status" onkeypress="return (event.which == 8) || (this.value.length &lt; getStatusTextCharLengthMax(this.value));" onkeyup="updateStatusTextCharCounter(this.value)" rows="3" cols="15"></textarea>
						</div>
					</div>
					<div class="submit">
						<input id="submit" name="commit" type="submit" class="buttonSubmit" value="叽歪一下" />
					</div>
				</fieldset>
			</form>

<script type="text/javascript">
$('submit').onmouseover = function(){
	this.className += "Hovered"; 
}

$('submit').onmouseout = function(){
	this.className = this.className.replace(/Hovered/g, "");
}

</script>


					<!--div style="width:100%; border:1px solid #f00; text-align:left">
						<textarea id='test' name='test' rows="3" style="width:100%"></textarea>
					</div-->
			<script type="text/javascript">
//<![CDATA[
$('status').focus()
//]]>
			</script>
			<script type="text/javascript">
//<![CDATA[

	// FIXME: IE???
	function getStatusTextCharLengthMax(value)
	{
		return 140;
	  if (value.match(/[^\u00-\u7F]/))
	  {
		// chinese msg;
		return 70;
	  }
	  // ascii msg
	  return 140;
	}
// onbeforepaste="clipboardData.setData('text',clipboardData.getData('text').replace(/[^\u4E00-\u9FA5]/g,''))">

	function updateStatusTextCharCounter(value) {
	  len_max = getStatusTextCharLengthMax(value);

	  if (len_max - value.length >= 0) {
		  $('status-field-char-counter').innerHTML = len_max - value.length;
		} else {
		  $('status-field-char-counter').innerHTML = 0;
		}
	};

//]]>
			</script>
			<script type="text/javascript">
//<![CDATA[
$('status-field-char-counter').innerHTML = getStatusTextCharLengthMax($('status').value) - $('status').value.length;
//]]>
			</script>

<?php
	}


	static public function tab_menu()
	{
?>

			<ul class="tabMenu">
				<li><a href="/wo/account/archive">历史</a></li>
				<li class="active"><a href="/wo/">最近</a></li>
			</ul>

<?php
	}


	static public function tab_header( $vars=null )
	{
		if ( !array_key_exists('title',$vars) )	
			$vars['title'] = '<a href="/wo">你</a>和<a href="/friends">朋友们</a>都在做什么?';

		if ( !array_key_exists('title2',$vars) )	
			$vars['title2'] = '每分钟更新一次。';
?>

				<table class="layout_table" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="bg_tab_top_left">
						</td>
						<td class="bg_tab_top_mid">
							<h2><?php echo $vars['title']; ?>
</h2>
						</td>
						<td class="bg_tab_top_right">
						</td>
					</tr>
					<tr class="odd">
						<td class="bg_tab_top2_left">
						</td>
						<td colspan="2" class="bg_tab_top2_right">
							<span style="display:none" id="timeline_refresh">

    							<script type="text/javascript">
//<![CDATA[
document.write('<img alt="更新中..." src="http://asset.jiwai.de/img/icon_throbber.gif" title="更新中..." />')
//]]>
								</script>
  							</span>

							<?php echo $vars['title2']; ?>

						</td>
					</tr>

				</table>

<?php
	}


	static public function StatusHead($idUser, $options=null)
	{

		$statuses 	= JWStatus::GetStatusListUser($idUser, 1);
		$aStatus  	= $statuses[0];

		$user_info	= JWUser::GetUserInfoById($idUser);
		$nameScreen = $user_info['nameScreen'];
		$nameFull	= $user_info['nameFull'];
		$photo_url 	= JWPicture::GetUserIconUrl($idUser);

		if ( !isset($options['trash']) )
			$options['trash'] = true;

		if ( ! empty($aStatus) )
		{
			$idStatus 	= $aStatus['idStatus'];
			$status		= $aStatus['status'];
			$timestamp	= $aStatus['timestamp'];
			$device		= $aStatus['device'];
	
			$duration	= JWStatus::get_time_desc($timestamp);
		}
		else	
		{
			$status		= "迄今为止还没有更新过！";
		}
	
	
		$idLoginedUser	= JWUser::GetCurrentUserId();

?>

			<h2 class="thumb">
				<a href="/wo/account/profile_image/<?php echo $nameScreen?>"><img alt="<?php echo $nameFull?>" border="0" src="<?php echo $photo_url?>" valign="middle" /></a>
		
				<?php echo $nameScreen ?>

<?php
if ( JWUser::IsLogined() 
		&& $idLoginedUser!=$idUser 
		&& JWFollower::IsFollower($idUser, $idLoginedUser) )
{
	echo <<<_HTML_
<style type="text/css">
#content h2.thumb small {
font-size:0.4em;
}
</style>

				<small>
					是一个被你订阅了的朋友
				</small>
_HTML_;
}
?>
			</h2>

			<div class="desc">
	  			<p><?php echo htmlspecialchars($status)?></p>
	  			<p class="meta">
<?php 
if ( isset($aStatus) ) 
{
?>
  					<a href="http://jiwai.de/zixia/statuses/<?php echo $idStatus?>"><?php echo $duration?></a>
  					来自 <?php echo $device?>
					<span id="status_actions_<?php echo $idStatus?>">
<?php	
}

if ( isset($aStatus) && JWUser::IsLogined() )	
{
	$idLoginedUser = JWUser::GetCurrentUserId();
	$isFav	= JWFavorite::IsFavorite($idLoginedUser,$idStatus);

	echo self::FavoriteAction($idStatus,$isFav);
	if ( $idLoginedUser==$idUser 
			&& $options['trash'] )
	{
		echo self::TrashAction($idStatus);
	}
}
?>
					</span>
  				</p>
			</div>

<?php
	}


	/*
	 * 	显示删除的图标和操作
 	 *
	 */
	static public function TrashAction($idStatus)
	{

		$asset_trash_url		= self::GetAssetUrl("/img/icon_trash.gif");

		$html_str = <<<_HTML_
	<a href="/wo/status/destroy/$idStatus"
		onclick="
			if (confirm('请确认操作：删除后将永远无法恢复！')) 
			{
				var f = document.createElement('form');
				f.style.display = 'none';
				this.parentNode.appendChild(f);
				f.method = 'POST';
				f.action = this.href;
				var m = document.createElement('input');
				m.setAttribute('type', 'hidden');
				m.setAttribute('name', '_method');
				m.setAttribute('value', 'delete');
				f.appendChild(m);
				f.submit();
			};
			return false;" 
		title="删除这条更新？"><img alt="删除" border="0" src="$asset_trash_url" /></a>
_HTML_;
		
		$html_str	= preg_replace('/[\r\n\s]+/', ' ',$html_str);

		return $html_str;
	}


	/*
	 *	显示 favorite 的星星，配合 JWFavorite 进行 Ajax 收藏操作。
	 *	@param	switch	当前是否已经被收藏, true为已经收藏，false为没有收藏
	 */
	static public function FavoriteAction($idStatus, $isFav=false)
	{
		$asset_throbber_url		= self::GetAssetUrl("/img/icon_throbber.gif");

		if ( $isFav )
		{
			$asset_star_alt = '已收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_full.gif");
			$ajax_url		= "/wo/favourings/destroy/$idStatus";
		}
		else
		{
			$asset_star_alt = '未收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_empty.gif");
			$ajax_url		= "/wo/favourings/create/$idStatus";
		}
	//<a href="<?php echo $ajax_url? >" onclick="new Ajax.Request(

		$html_str = <<<_HTML_
	<a href="#" onclick="new Ajax(
					'$ajax_url'
					,{
						 method: 		'get'
						,headers: 		{'AJAX':true}
						,async:			true
						,evalScripts:	true
						,evalResponse:	true
						,onRequest:		function(){
							$('status_star_$idStatus').src='$asset_throbber_url'
						}
					}
				).request(); return false;"><img alt="$asset_star_alt" border="0" id="status_star_$idStatus" 
										src="$asset_star_url" /></a>

_HTML_;

		$html_str		= preg_replace('/[\r\n\s]+/', ' ',$html_str);

//echo "<pre>";die($html_str);
		return $html_str;
	}


	/*
	 * 公共函数，显示 timeline list
	 * @param 	array	status list
	 * @param	array	show_item	array ( 'icon' => true ) 
	 * @return	
	 */
	static public function timeline($aStatusList=null, $show_item=null )
	{
		if ( empty($aStatusList) )
			return;

		if ( !isset($show_item['icon']) )
			$show_item['icon'] 		= true;
		if ( !isset($show_item['trash']) )
			$show_item['trash'] 	= true;

		$idCurrentUser = JWUser::GetCurrentUserInfo('id');
?>

				<table class="doing" id="timeline" cellspacing="0" cellpadding="0">    
<?php
		$n=0;
		foreach ( $aStatusList as $aStatus ){
//die(var_dump($aStatusList));
			$idStatus 	= $aStatus['idStatus'];
			$idUser 	= $aStatus['idUser'];
			$nameScreen = $aStatus['nameScreen'];
			$nameFull	= $aStatus['nameFull'];
			$status		= $aStatus['status'];
			$timestamp	= $aStatus['timestamp'];
			$device		= $aStatus['device'];
			
			$duration	= JWStatus::get_time_desc($timestamp);
			$photo_url	= JWPicture::GetUserIconUrl($idUser);
	

			if ( 'sms'==$device )
				$device='手机';
			else
				$device=strtoupper($device);


			$formated_status 	= JWStatus::FormatStatus($status);

			$replyto			= $formated_status['replyto'];
			$status				= $formated_status['status'];
?>
					<tr class="<?php echo $n++%2?'even':'odd';?>" id="status_<?php echo $idStatus;?>">
<?php if ( $show_item['icon'] ){ ?>
						<td class="thumb">
							<a href="/<?php echo $nameScreen;?>"><img alt="<?php echo $nameFull;?>" 
									src="<?php echo $photo_url?>" /></a>
						</td>
<?php } ?>
						<td>	
<?php if ( $show_item['icon'] ){ ?>
							<strong>
								<a href="/<?php echo $nameScreen?>" 
										title="<?php echo $nameFull?>"><?php echo $nameScreen?></a>
							</strong>
<?php } ?>

							<?php echo $status?>
			
							<span class="meta">
								<a href="/<?php echo $nameScreen?>/statuses/<?php echo $idStatus?>"><?php echo $duration?></a>
								来自于 <?php echo $device?> 
								<?php if (!empty($replyto) ) echo " <a href='/$replyto/'>给 ${replyto} 的回复</a> " ?>

								<span id="status_actions_<?php echo $idStatus?>">

<?php	
if ( JWUser::IsLogined() )	
{
	$idLoginedUser = JWUser::GetCurrentUserId();
	$isFav	= JWFavorite::IsFavorite($idLoginedUser,$idStatus);

		echo self::FavoriteAction($idStatus,$isFav);
	if ( $idLoginedUser!=$idUser ){
		// 不是自己的status可以收藏
		// 现在可以收藏自己的
	}else if ($show_item['trash']){
		//是自己的 status 可以删除
		echo self::TrashAction($idStatus);
	}
}
?>

								</span>

							</span>

						</td>
					</tr>
<?php
				}
?>
				</table>

<?php
	}


	static public function pagination()
	{
?>

				<div class="pagination">
  					<ul>
  						<li class="nextpage">
	   	 					<a href="/home?page=2">前一页 &#187;</a>
  						</li>
  					</ul>
				</div>


<?php
	}

	/*
	 *	@param	options		focus	=> true	: 是否激活输入焦点到email框
	 *
	 */
	static function sidebar_login($options)
	{
		if ( false!==@$options['focus'] )
			$options['focus'] = true;
?>

		<form action="/wo/login" class="signin" method="post" name="f">
			<fieldset>
				<div>
					<label for="username_or_email">帐号 / Email</label>
					<input id="email" name="username_or_email" type="text" />
    			</div>

    			<div>
    				<label for="password">密码</label>
    				<input id="pass" name="password" type="password" />
    			</div>

    			<input id="remember_me" name="remember_me" type="checkbox" value="1" checked/> <label for="remember_me">记住我</label>
    			<small><a href="/account/resend_password">忘记?</a></small>
    			<input id="submit" name="commit" type="submit" value="登录" />
    		</fieldset>
		</form>

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

	static function sidebar_register()
	{
?>

		<div class="notify">
  			想拥有一个叽歪de帐号吗？<br />
  			<a href="/wo/account/create" class="join">免费注册！</a><br />
  			10秒搞定！
  		</div>

<?php
	}

	static function sidebar_featured()
	{
?>

  		<ul class="featured">
			<li><strong>叽歪de推荐</strong></li>

			<li>
				<a href="/dev"><img alt="JiWai.de Developer" height="24" src="/dev/picture" width="24" /></a>
				<a href="/dev">JiWai.de Developer</a>
			</li>

			<li>
				<a href="/jessica"><img alt="jessica" height="24" src="/jessica/picture" width="24" /></a>
				<a href="/jessica">Jessica</a>
			</li>

			<li>
				<a href="/maxinyu"><img alt="Ma Xinyu" height="24" src="/maxinyu/picture" width="24" /></a>
				<a href="/maxinyu">daodao</a>
			</li>

			<li>
				<a href="/zixia"><img alt="Li Zhuohuan" height="24" src="/zixia/picture" width="24" /></a>
				<a href="/zixia">Li Zhuohuan</a>
			</li>

			<li>
				<a href="/nullgate"><img alt="Nullgate" height="24" src="/nullgate/picture" width="24" /></a>
				<a href="/nullgate">Nullgate</a>
			</li>

			<li>
				<a href="/zany"><img alt="zany zeng" height="24" src="/zany/picture" width="24" /></a>
				<a href="/zany">Zany Zeng</a>
			</li>
		</ul>

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
			$idUser = JWUser::GetCurrentUserId();
		}

		if ( 0<$idUser )
			$aUserInfo = JWUser::GetUserInfoById($idUser);
?>

    <div id="sidebar" class="static">
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
		self::sidebar_count()
		self::sidebar_jwvia()
		self::sidebar_search()
		self::sidebar_friend()
		self::sidebar_active()
		self::sidebar_action()
*/


?>
	</div><!-- sidebar -->

<?php
	}


	/*
	 * @param	array	array('create'=>true, 'destroy'=>true, 'follow'=>true,'leave'=>true)
	 */
	static function sidebar_action($action, $idUserDst)
	{
		if ( empty($action) )
			return;

		$arr_user_info = JWUser::GetUserInfoById($idUserDst);

		echo <<<_HTML_
		<ul class="actions">
			<li><strong>操作</strong></li>

_HTML_;

		if ( isset($action['create']) )
			echo <<<_HTML_
			<li><a href="/wo/friend/create/$arr_user_info[id]">结友</a> $arr_user_info[nameScreen]</li>

_HTML_;

		if ( isset($action['destroy']) )
		echo <<<_HTML_
			<li>
				<a href="/wo/friend/destroy/$arr_user_info[id]" 
						onclick="return confirm('请确认删除好友"$arr_user_info[nameFull]"')">绝交</a> $arr_user_info[nameScreen]
			</li>

_HTML_;

	if ( isset($action['follow']) )
		echo <<<_HTML_
			<li>
				<a href="/wo/friend/follow/$arr_user_info[id]">订阅</a> $arr_user_info[nameScreen]
			</li>

_HTML_;

		if ( isset($action['leave']) )
		echo <<<_HTML_
			<li>
				<a href="/wo/friend/leave/$arr_user_info[id]">离开</a> $arr_user_info[nameScreen]
			</li>

_HTML_;

		echo <<<_HTML_
		</ul>

_HTML_;

	}


	static function sidebar_active($imActived, $smsActived)
	{
		if ( !$imActived )
			echo <<<_HTML_
		<a href="/wo/device/">启用聊天软件！</a>
_HTML_;

		if ( !$smsActived )
			echo <<<_HTML_
		<a href="/wo/device/">启用手机短信！</a>
_HTML_;
	}


	static function sidebar_user_info($aUserInfo)
	{
?>
		<ul class="about">
			<li>名字: <?php echo htmlspecialchars($aUserInfo['nameFull'])?></li>
<?php
			if ( !empty($aUserInfo['bio']) )
				echo "<li>简介: " . htmlspecialchars($aUserInfo['bio']) . "</li>\n";
			if ( !empty($aUserInfo['location']) )
				echo "<li>位置: " . htmlspecialchars($aUserInfo['location']) . "</li>\n";
			if ( !empty($aUserInfo['url']) )
			{
				if ( !preg_match('/^\w+:\/\//',$aUserInfo['url']) )
					$aUserInfo['url'] = 'http://' . $aUserInfo['url'];

				echo "<li>网站:  <a href='" . htmlspecialchars($aUserInfo['url']) . "'"
											. " rel='" . htmlspecialchars($aUserInfo['nameFull']) . "'"
											. " target='_blank' "
											. ">" . htmlspecialchars($aUserInfo['url']) . "</a></li>\n";
			}
?>
		</ul>
<?php
	}


	static function sidebar_user_notice($aUserInfo)
	{
		$nameScreen = $aUserInfo['nameScreen'];
?>
		<div class="msg">
			关于 <strong><?php echo $nameScreen?></strong>
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

		
	static function sidebar_count( $countInfo=null )
	{
?>

		<ul>
			<li id="message_count"><a href="/wo/message">站内PM: <?php echo $countInfo['pm']?></a></li>
			<li id="favourite_count"><a href="/wo/favourings">收藏夹: <?php echo $countInfo['fav']?></a></li>
			<li id="friend_count"><a href="/wo/friend">叽歪友: <?php echo $countInfo['friend']?></a></li>
			<li id="follower_count"><a href="/wo/follower">订阅者: <?php echo $countInfo['follower']?></a></li>
			<li id="update_count">总共叽歪了 <?php echo $countInfo['status']?> 次</li>
		</ul>

<?php
	}


	static function sidebar_status( $userInfo )
	{
		$arr_current_status = JWStatus::GetStatusListUser($userInfo['id'],1);
		$current_status		= $arr_current_status[0]['status'];

		$arr_status			= JWStatus::FormatStatus($current_status);
//XXX
?>

		<div class="msg">
			欢迎回来，
			<strong><a href="/<?php echo $userInfo['nameScreen'];?>"><?php echo $userInfo['nameFull'];?></a></strong>
		</div>

		<ul>
			<li>当前：<em id="currently"><?php echo $arr_status['status']?></em></li>
		</ul>

<?php
	}


	static function sidebar_jwvia( $aUserInfo )
	{
?>
		<ul>
			<li>
				<form action="/account/update_send_via" id="send_via_form" method="post" onsubmit="/*new Ajax.Request('/account/update_send_via', {asynchronous:true, evalScripts:true, parameters:Form.serialize(this)});*/ return false;">
					<fieldset>
						<h4>发送通知信息到:</h4>

						<input checked="checked" id="current_user_send_via_im" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="im" />
						<label for="current_user_send_via_im">聊天软件</label>
						
						<input id="current_user_send_via_sms" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="sms" />
						<label for="current_user_send_via_sms">手机</label>

						<input id="current_user_send_via_none" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" value="none" />
						<label for="current_user_send_via_none">网页</label>
					</fieldset>
				</form>	
			</li>
		</ul>
<?php
	}


	static function sidebar_friend($friendIdList)
	{
		if ( empty($friendIdList) )
			return;

		echo <<<_HTML_
  		<div id="friend">

_HTML_;

		foreach ( $friendIdList as $idFriend )
		{
			$friend_info 	= JWUser::GetUserInfoById($idFriend);
			$picture_url	= JWPicture::GetUserIconUrl($idFriend);
			
			echo <<<_HTML_
			<a href="/$friend_info[nameScreen]/" rel="contact" title="$friend_info[nameFull]"><img alt="$friend_info[nameFull]" height="24" src="$picture_url" width="24" /></a>

_HTML_;
		}
  		echo <<<_HTML_
		</div>

_HTML_;
	}

	static function sidebar_search()
	{
?>
		<form action="/users/search" method="post" onsubmit="new Ajax.Updater('friend', '/users/search', {asynchronous:true, evalScripts:true, parameters:'name_number_or_email=' + $('user_search').value}); return false;">
			<fieldset class="user_search">
    			<input id="user_search" name="name_number_or_email" onclick="this.value=&quot;&quot;" type="text" value="输入id，email，手机号进行搜索" />
    			<input src="http://asset.jiwai.de/img/icon_search.gif" type="image" />
			</fieldset>
		</form>

		<script type="text/javascript">
//<![CDATA[
/*new Form.Element.Observer('user_search', 0.5, function(element, value) {new Ajax.Updater('friends', '/users/search', {asynchronous:true, evalScripts:true, parameters:'name_number_or_email=' + value})})*/
//]]>
		</script>

<?php
	}


	static public function rss ()
	{
?>
				<span class="statuses_options">
   			 		<a class="rss" href="/statuses/friends_timeline/762460.rss">RSS</a>
				</span>

<?php
	}


	static public function GetConst($constName)
	{
		self::Instance();

		if ( empty(self::$msJWConst) )
		{
			self::$msJWConst = array (	'UrlContactUs'			=>	'/wo/help/contact_us'
										,'UrlRegister'			=>	'/wo/account/create'
										,'UrlPublicTimeline'	=>	'/public_timeline/'
										,'UrlError404'			=>	'/wo/error/404'
										,'UrlError500'			=>	'/wo/error/500'

										,'UrlStrangerPicture'	=>	'http://asset6.jiwai.de/img/stranger.gif'
								);
		}

		return @self::$msJWConst[$constName];
	}


	static public function UserSettingNav($activeMenu='account')
	{
		$arr_menu = array ( 'account'		=> array ( '/wo/account/setting', '帐号' )
							, 'password'	=> array ( '/wo/account/password', '密码')
							, 'device'		=> array ( '/wo/device/', '聊天软件/手机短信')
							, 'notification'=> array ( '/wo/account/notification', '通知')
							, 'picture'		=> array ( '/wo/account/picture', '头像')
							, 'uidesign'	=> array ( '/wo/account/uidesign', '界面')
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
	 *	@param	domainLoop	use asset1/asset2... etc...
	 *	@return	URL		the url ( domain name & path )
	 */
	static public function GetAssetUrl($absUrlPath, $domainLoop=true)
	{
		JWTemplate::Instance();

		$asset_num_max = 6;

		if ( empty($absUrlPath) )
			throw new JWException('must have path');

		$asset_path	= JW_ROOT . '/domain/asset';
		$timestamp 	= filemtime("$asset_path$absUrlPath");


		if ( preg_match('#(alpha)|(beta)\.jiwai\.de/#i',$_SERVER["HTTP_HOST"],$matches) )
		{
			$domain		= "$matches[1].jiwai.de";
		}
		else
		{
			$domain		= 'jiwai.de';
		}


		if ( $domainLoop )
		{
			self::$msAssetCounter++;
			$n = self::$msAssetCounter % $asset_num_max;
			$n++; // from 1 to $asset_num_max
		} else {
			$n = '';
		}


		//we use more then one domain name to down load asset in parallel
		return "http://asset${n}.$domain$absUrlPath?$timestamp";

	}
}
?>
