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

	static public function html_head()
	{
		$asset_url_css		= self::GetAssetUrl('/css/jiwai-screen.css');
		$asset_url_favicon	= self::GetAssetUrl('/img/favicon.gif'	   );
		$asset_url_js_jiwai	= self::GetAssetUrl('/js/jiwai.js'		   );
		$asset_url_js_moo	= self::GetAssetUrl('/js/mootools.v1.1.js' );

		echo <<<_HTML_
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />


	<title>叽歪de / 这一刻，你在做什么？</title>

	<link rel="start" href="http://JiWai.de/" title="叽歪de我" />

	<link href="$asset_url_css" media="screen, projection" rel="Stylesheet" type="text/css" />

	<meta name="ICBM" content="40.4000, 116.3000" />
	<meta name="DC.title" content="叽歪de" />

	<meta name="keywords" content="叽歪de, 叽歪的, 唧歪de, 唧歪的, 叽叽歪歪, 唧唧歪歪, tiny blog, blog, im nick, nick, log, 记录, 写下" />

	<meta name="description" content="叽歪de - 记录、并与朋友分享你每天的点滴。" />

	<meta name="author" content="JiWai.de, 叽歪de, 叽歪" />

	<meta name="copyright" content="copyright 2007 http://jiwai.de" />

	<meta name="robots" content="all" />

	<link rel="shortcut icon" href="$asset_url_favicon" type="image/gif" />
	
	<link rel="alternate" title="叽歪de - [RSS]" href="http://feeds.feedburner.com/jiwai" />

	<script type="text/javascript" src="$asset_url_js_jiwai"></script>
	<script type="text/javascript" src="$asset_url_js_moo"></script>

</head>

_HTML_;

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
			<li class="first"><a href="/wo/">首页</a></li>
			<li><a href="/<?php echo $nameScreen ?>/">我的档案</a></li>
			<li><a href="<?php echo self::GetConst('UrlPublicTimeline')?>">叽歪广场</a></li>
			<li><a href="/wo/gadget/">窗可贴</a></li>
			<li><a href="/wo/invitations/invite">邀请</a></li>
			<li><a href="/wo/account/settings">设置</a></li>
			<li><a href="http://help.jiwai.de/">帮助</a></li>
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

setTimeout("run_google();", 100);

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
					<div class="bar even">
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


	/*
	 *	显示 tab 方式的列表
	 *
	 *	@param	array	$menuArr	菜单数据，结构如下：
									array ( 'menu1' => array ( 'active'=true, 'url'='/' ), 'menu2'=>array(...), ... );
	 */
	static public function tab_menu( $menuArr )
	{
		if ( empty($menuArr) )
		{
			JWLog::LogFuncName(LOG_CRIT, "need param menuArr") ;
			return;
		}

		echo "<ul class='tabMenu'>";

		foreach ( $menuArr as $menu => $options )
		{
			$url 		= $options['url'];
			$is_active	= $options['active'];

			if ( $is_active )
				echo "<li class='active'><a href='$url'>$menu</a></li>\n";
			else
				echo "<li><a href='$url'>$menu</a></li>\n";
		}

		echo "</ul>\n";
	}


	static public function tab_header( $vars=null )
	{
/*
		title2 长了之后，会莫名其妙的影响 tab 布局
		$vars=array('title'=>'最新动态 - 大家在做什么？' 
						, 'title2'=>'你想叽歪呀，你想叽歪你就说嘛，你不说我怎么知道你想叽歪呢'//？'//：-）'
				);
*/

		if ( !array_key_exists('title',$vars) )	
			$vars['title'] = '你和<a href="/wo/friends/">你的朋友们</a>在做什么?';

		if ( !array_key_exists('title2',$vars) )	
			$vars['title2'] = '每分钟更新一次。';
?>

				<table class="layout_table" cellpadding="0" cellspacing="0" width="100%">
					<tr>
						<td class="bg_tab_top_left">
						</td>
						<td class="bg_tab_top_mid">
							<h2><?php echo $vars['title']; ?></h2>
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


	static public function StatusHead($idUser, $userRow, $statusRow, $options=null)
	{

		$name_screen 	= $userRow['nameScreen'];
		$name_full		= $userRow['nameFull'];
		$photo_url 		= JWPicture::GetUserIconUrl($idUser);

		if ( !isset($options['trash']) )
			$options['trash'] = true;

		if ( ! empty($statusRow) )
		{
			$status_id 	= $statusRow['idStatus'];
			$status		= $statusRow['status'];
			$timeCreate	= $statusRow['timeCreate'];
			$device		= $statusRow['device'];
	
			$duration	= JWStatus::GetTimeDesc($timeCreate);
		}
		else	
		{
			$status		= "迄今为止还没有更新过！";
		}


		$device				= JWDevice::GetNameFromType($device);

		$current_user_id	= JWLogin::GetCurrentUserId();

?>

			<h2 class="thumb">
				<a href="/wo/account/profile_image/<?php echo $name_screen?>"><img alt="<?php echo $name_full?>" border="0" src="<?php echo $photo_url?>" valign="middle" /></a>
		
				<?php echo $name_screen ?>

<?php
if ( isset($current_user_id)
		&& $current_user_id!=$idUser 
		&& JWFollower::IsFollower($idUser, $current_user_id) )
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
if ( isset($statusRow) ) 
{
	echo <<<_HTML_
  					<a href="/$name_screen/statuses/$status_id">$duration</a>
  					来自 $device
					<span id="status_actions_$status_id">
_HTML_;
}

if ( isset($statusRow) && isset($current_user_id) )	
{
	$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);

	echo self::FavouriteAction($status_id,$is_fav);
	if ( $current_user_id==$idUser 
			&& $options['trash'] )
	{
		echo self::TrashAction($status_id);
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
	 *	显示 favourite 的星星，配合 JWFavourite 进行 Ajax 收藏操作。
	 *	@param	switch	当前是否已经被收藏, true为已经收藏，false为没有收藏
	 */
	static public function FavouriteAction($idStatus, $isFav=false)
	{
		$asset_throbber_url		= self::GetAssetUrl("/img/icon_throbber.gif");

		if ( $isFav )
		{
			$asset_star_alt = '已收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_full.gif");
			$ajax_url		= "/wo/favourites/destroy/$idStatus";
		}
		else
		{
			$asset_star_alt = '未收藏';
			$asset_star_url = self::GetAssetUrl("/img/icon_star_empty.gif");
			$ajax_url		= "/wo/favourites/create/$idStatus";
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
	 * @param 	array	$statusIds	array(1,2,3);
	 * @param 	array	$userRows	user[id]=row
	 * @param 	array	$statusRows	status[id]=row
	 * @param	array	showItem	array ( 'icon' => true ) 
	 * @return	
	 */
	static public function Timeline($statusIds, $userRows, $statusRows, $showItem=null )
	{
		if ( empty($statusIds) || empty($userRows) || empty($statusRows) )
			return;

		if ( !isset($showItem['icon']) )
			$showItem['icon'] 		= true;
		if ( !isset($showItem['trash']) )
			$showItem['trash'] 	= true;

		$current_user_id = JWUser::GetCurrentUserInfo('id');
?>

				<table class="doing" id="timeline" cellspacing="0" cellpadding="0">    
<?php
		$n=0;
		foreach ( $statusIds as $status_id ){
//die(var_dump($aStatusList));
			$user_id 	= $statusRows[$status_id]['idUser'];
			$name_screen= $userRows[$user_id]['nameScreen'];
			$name_full	= $userRows[$user_id]['nameFull'];
			$status		= $statusRows[$status_id]['status'];
			$timeCreate	= $statusRows[$status_id]['timeCreate'];
			$device		= $statusRows[$status_id]['device'];
			
			$duration	= JWStatus::GetTimeDesc($timeCreate);
			$photo_url	= JWPicture::GetUserIconUrl($user_id);
	
			$device		= JWDevice::GetNameFromType($device);

			$formated_status 	= JWStatus::FormatStatus($status);

			$replyto			= $formated_status['replyto'];
			$status				= $formated_status['status'];
?>
					<tr class="<?php echo $n++%2?'even':'odd';?>" id="status_<?php echo $status_id;?>">
<?php if ( $showItem['icon'] ){ ?>
						<td class="thumb">
							<a href="/<?php echo $name_screen;?>"><img alt="<?php echo $name_full;?>" 
									src="<?php echo $photo_url?>" width="48" height="48"/></a>
						</td>
<?php } ?>
						<td>	
<?php if ( $showItem['icon'] ){ ?>
							<strong>
								<a href="/<?php echo $name_screen?>" 
										title="<?php echo $name_full?>"><?php echo $name_screen?></a>
							</strong>
<?php } ?>

							<?php echo $status?>
			
							<span class="meta">
								<a href="/<?php echo $name_screen?>/statuses/<?php echo $status_id?>"><?php echo $duration?></a>
								来自于 <?php echo $device?> 
								<?php if (!empty($replyto) ) echo " <a href='/$replyto/'>给 ${replyto} 的回复</a> " ?>

								<span id="status_actions_<?php echo $status_id?>">

<?php	
if ( isset($current_user_id) )	
{
	$is_fav	= JWFavourite::IsFavourite($current_user_id,$status_id);

	echo self::FavouriteAction($status_id,$is_fav);
	if ( $current_user_id!=$user_id ){
		// 不是自己的status可以收藏
		// 现在可以收藏自己的
	}else if ($showItem['trash']){
		//是自己的 status 可以删除
		echo self::TrashAction($status_id);
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

  			<script type="text/javascript">
//<![CDATA[  

/*
(function() { new Ajax('/wo/status/timeline_refresh?last_check=' + $('timeline').getElementsByTagName('tr')[0].id.split("_")[1], 
    {
      	async:true
		,evalScripts:true
		,onRequest: function() { $('timeline_refresh').effect('opacity', {duration:300}).start(0,1); }
		,onSuccess: function() { $('timeline_refresh').setStyle('display','none'); }
    }).request()
}).periodical(2000);
*/

//]]>
			</script>


<?php
	}


	static public function pagination( $pagination )
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
			echo "<td class='bl odd'><a href='?page=$newest_page_no'>« 最新</a></td>\n";

		if ( $is_show_newer )
			echo "<td class='bl odd'><a href='?page=$newer_page_no'>‹ 较新</a></td>\n";

		echo '<td class="bl"></td>';

		if ( $is_show_older )
			echo "<td class='bl odd'><a href='?page=$older_page_no'>较早 ›</a></td>\n";
		
		if ( $is_show_oldest )
			echo "<td class='bl odd'><a href='?page=$oldest_page_no'>最早 »</a></td>\n";

		echo <<<_HTML_
</tr></tbody></table>

				</div>
_HTML_;

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

		<form action="/wo/login" class="signin" method="post" name="f">
			<fieldset>
				<div>
					<label for="username_or_email">帐号 / Email</label>
					<br>
					<input id="email" name="username_or_email" type="text" style="width:158px"/>
    			</div>

    			<div>
    				<label for="password">密码</label>
					<br>
    				<input id="pass" name="password" type="password" style="width:158px"/>
    			</div>

    			<input id="remember_me" name="remember_me" type="checkbox" value="1" checked style="margin-top:4px"/> <label for="remember_me">记住我</label>
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

	static function sidebar_register($showLogin=false)
	{
?>

		<div class="notify">
  			想拥有一个叽歪de帐号？<br />
  			<a href="/wo/account/create" class="join">免费注册！</a><br />
<?php 	if ( !$showLogin ) { ?>
  			10秒搞定！
			<br/>
<?php	} else { ?>
			已经有帐号了？
			<a href="/wo/login">登录！</a>
<?php 	} ?>
  		</div>

<?php
	}

	
	/*
	 *
	 *	@param	array	$options	$options['title'] = title
									$optoins['user_ids'] = array(1,2,3,4)
	 *
	 */
	static function sidebar_featured($options)
	{
		if ( empty($options['user_ids']) )
			return;

		$user_ids = $options['user_ids'];
		
		if ( !is_array($user_ids) )
		{
			JWLog::LogFuncName("user_ids is not array");
			return;
		}

		if ( isset($options['title']) )
			$title = $options['title'];
		else
			$title = '叽歪de推荐';

		echo <<<_HTML_
  		<ul class="featured">
			<li><strong>$title</strong></li>
_HTML_;

		$user_db_rows 		= JWUser::GetUserDbRowsByIds($user_ids);
		$user_icon_url_rows	= JWPicture::GetUserIconUrlRowsByIds($user_ids);

		foreach ( $user_ids as $user_id )
		{
			$user_db_row 		= $user_db_rows			[$user_id];
			$user_icon_url		= $user_icon_url_rows	[$user_id];

			echo <<<_HTML_
			<li>
				<a href="/$user_db_row[nameScreen]/"><img alt="$user_db_row[nameFull]" height="24" src="$user_icon_url" width="24" /></a>
				<a href="/$user_db_row[nameScreen]/">$user_db_row[nameFull]</a>
			</li>
_HTML_;
		}

		echo <<<_HTML_
		</ul>
_HTML_;
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
		if ( $sidebar )
		echo <<<_HTML_
			<li><strong>好友操作</strong></li>
_HTML_;



		if ( isset($action['nudge']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/nudge/$arr_user_info[id]">挠挠</a> $arr_user_info[nameScreen]
			</li>
_HTML_;
		}

		if ( isset($action['d']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/direct_messages/create/$arr_user_info[id]">消息</a> $arr_user_info[nameScreen]
			</li>
_HTML_;
		}


		if ( isset($action['follow']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/follow/$arr_user_info[id]">订阅</a> $arr_user_info[nameScreen]
			</li>
_HTML_;
		}

		if ( isset($action['leave']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/leave/$arr_user_info[id]">离开</a> $arr_user_info[nameScreen]
			</li>
_HTML_;
		}


		if ( isset($action['add']) )
		{
			echo <<<_HTML_
			<li><a href="/wo/friends/create/$arr_user_info[id]">添加</a> $arr_user_info[nameScreen]</li>
_HTML_;
		}

		if ( isset($action['remove']) )
		{
			echo <<<_HTML_
			<li>
				<a href="/wo/friends/destroy/$arr_user_info[id]" 
						onclick="return confirm('请确认删除好友 $arr_user_info[nameScreen] ')">删除</a> $arr_user_info[nameScreen]
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

		
	static function sidebar_count( $countInfo=null, $user='wo' )
	{
		echo <<<_HTML_
		<ul>
_HTML_;

		if ( 'wo'==$user )
		{
			echo <<<_HTML_
			<li id="message_count"><a href="/$user/direct_messages/">消息: $countInfo[pm]</a></li>
_HTML_;
		}

		echo <<<_HTML_
			<li id="favourite_count"><a href="/$user/favourites/">收藏: $countInfo[fav]</a></li>
			<li id="friend_count"><a href="/$user/friends/">好友: $countInfo[friend]</a></li>
_HTML_;
		
		if ( 'wo'==$user ) 
		{
			echo <<<_HTML_
			<li id="follower_count"><a href="/$user/followers/">粉丝: $countInfo[follower]</a></li>
_HTML_;
		} 
		else 
		{
			echo <<<_HTML_
			<li id="follower_count">粉丝: $countInfo[follower]</li>
_HTML_;
		}

		echo <<<_HTML_
			<li id="update_count">一共记录了 $countInfo[status] 条更新</li>
		</ul>
_HTML_;
	}


	static function sidebar_status( $userInfo )
	{
		$status_data 	= JWStatus::GetStatusIdsFromUser($userInfo['id'],1);

		if ( !empty($status_data['status_ids']) )
		{
			$status_rows	= JWStatus::GetStatusDbRowsByIds($status_data['status_ids']);
			$status_id		= $status_data['status_ids'][0];
			$current_status	= $status_rows[$status_id]['status'];
		}
		else
		{
			$current_status	= '还没有更新过！';
		}

		$arr_status		= JWStatus::FormatStatus($current_status);
//XXX
?>

		<div class="msg">
			欢迎回来，
			<strong><a href="/<?php echo $userInfo['nameScreen'];?>/"><?php echo $userInfo['nameFull'];?></a></strong>
		</div>

		<ul>
			<li>当前：<em id="currently"><?php echo $arr_status['status']?></em></li>
		</ul>

<?php
	}


	/*
	 *	显示通知信息发送到的位置，以及激活界面（如果有未激活的设备的话）
	 *	@param	array	$activeOptions	array('im'=>, 'sms'=>)
	 *	@param	string	$viaDevice		'sms' or 'im' or 'web'
	 */
	static function sidebar_jwvia($activeOptions, $viaDevice)
	{
		$smsActived	=	isset($activeOptions['sms']) 	? true : false;
		$imActived	=	isset($activeOptions['im']) 	? true : false;

?>
		<ul>
			<li>
				<form action="/wo/account/update_send_via" id="send_via_form" method="post" onsubmit="$('send_via_form').send(); return false;">
					<fieldset>
<?php 
		if ( $smsActived || $imActived ) 
		{ 
?>
						<h4>发送通知消息到:</h4>
<?php 
		}

		if ( $smsActived )
		{
?>
						<input id="current_user_send_via_sms" name="current_user[send_via]" onclick="$('send_via_form').send()" type="radio" <?php if ('sms'==$viaDevice) echo ' checked="checked" '; ?> value="sms" />
						<label for="current_user_send_via_sms">手机</label>

<?php
		}
						
		if ( $imActived )
		{
?>
						<input id="current_user_send_via_im" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio" <?php if ('im'==$viaDevice) echo ' checked="checked" '; ?> value="im" />
						<label for="current_user_send_via_im">聊天软件</label>

<?php
		}

		if ( $smsActived || $imActived )
		{
?>
						<input id="current_user_send_via_none" name="current_user[send_via]" onclick="$('send_via_form').onsubmit()" type="radio"  <?php if ('none'==$viaDevice) echo ' checked="checked" '; ?> value="none" />
						<label for="current_user_send_via_none">网页</label>
<?php
		}
?>
					</fieldset>
				</form>	
			</li>
		</ul>

		<ul>
<?php
		if ( !$smsActived )
		{
			echo <<<_HTML_
			<li><a href="/wo/devices/?sms">绑定手机！</a></li>
_HTML_;
		}
	
		if ( !$imActived )
		{
			echo <<<_HTML_
			<li><a href="/wo/devices/?im">绑定聊天软件！</a></li>
_HTML_;
		}

	
		echo "</ul>";
	}


	static function sidebar_friend($friendIds)
	{
		if ( empty($friendIds) )
			return;

		$friend_rows			= JWUser::GetUserDbRowsByIds($friendIds);
		$friend_icon_url_rows 	= JWPicture::GetUserIconUrlRowsByIds($friendIds);

		echo <<<_HTML_
  		<div id="friend">

_HTML_;

		foreach ( $friendIds as $friend_id )
		{
			$friend_info 	= $friend_rows[$friend_id];
			$picture_url	= $friend_icon_url_rows[$friend_id];
			
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
		$arr_menu = array ( 'account'		=> array ( '/wo/account/settings'	, '帐号' )
							, 'password'	=> array ( '/wo/account/password'	, '密码')
							, 'device_sms'	=> array ( '/wo/devices/?sms'		, '手机短信')
							, 'device_im'	=> array ( '/wo/devices/?im'			, '聊天软件')
							, 'notification'=> array ( '/wo/account/notification', '通知')
							, 'picture'		=> array ( '/wo/account/picture'	, '头像')
//							, 'uidesign'	=> array ( '/wo/account/uidesign'	, '界面')
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
	static public function GetAssetUrl($absUrlPath)
	{
		JWTemplate::Instance();

		$asset_num_max = 4;

		if ( empty($absUrlPath) )
			throw new JWException('must have path');

		$asset_path	= JW_ROOT . 'domain/asset';
		$timestamp 	= filemtime("$asset_path$absUrlPath");


		if ( preg_match('#((alpha)|(beta))\.jiwai\.de#i',$_SERVER["HTTP_HOST"],$matches) )
		{
			$domain		= "$matches[1].jiwai.de";
		}
		else
		{
			$domain		= 'jiwai.de';
		}

		// 同一个文件，总会被分配到同一个 n 上。
		$n = crc32($absUrlPath) % $asset_num_max;

		//we use more then one domain name to down load asset in parallel
		return "http://asset${n}.$domain$absUrlPath?$timestamp";

	}


	static public function	ShowAlphaBetaTips()
	{
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
这里是叽歪de(<strong>Alpha</strong>)测试系统。Alpha测试的定义为：在有开发者关注下对系统进行使用测试。如果您想试用最新的，还在开发中的功能，那么可以在这里继续访问。但是需要注意的是，系统也许会经常的工作不正常，甚至出错，所以我们建议您至少使用<a href='http://beta.$domain_url'>Beta系统</a>。
</p>
_MSG_;
				break;

			case 'beta':
				$msg = <<<_MSG_
<p>
这里是叽歪de(<strong>Beta</strong>)测试系统。如果您想试用最新的，正在准备升级的功能时，欢迎在这里继续访问。最新的系统功能可能有不稳定的情况，欢迎向我们<a href='mailto:wo@jiwai.de'>报告BUG</a>。如果您希望使用最为稳定的版本，请来正式运行的网站：<a href='http://$domain_url'>叽歪de</a>。
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
			<div class="notice"> $error_html </div>
_HTML_;
		}


		if ( !empty($notice_html) )
		{
			$is_exist = true;
			echo <<<_HTML_
			<div class="notice"> $notice_html </div>
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
	static public function ListUser($idUser, $idListUsers, $options=null)
	{
		echo <<<_HTML_
<style type="text/css">
.friend-actions ul li
{
	display: inline;
}
.subpage #content p {
line-height:1.2;
margin:5px 0pt;
}
.friend-actions {
text-indent:0.6em;
}
</style>
	
<table class="doing" id="$options[element_id]" cellspacing="0">
_HTML_;

		$n = 0;
		$list_user_rows				= JWUser::GetUserDbRowsByIds			($idListUsers);
		$list_user_icon_url_rows	= JWPicture::GetUserIconUrlRowsByIds($idListUsers);

		$action_rows	= JWSns::GetUserActions($idUser, $idListUsers);

//die(var_dump($action_rows));
		foreach ( $idListUsers as $list_user_id )
		{
			$list_user_row			= $list_user_rows			[$list_user_id];
			$list_user_icon_url		= $list_user_icon_url_rows	[$list_user_id];


			$odd_even			= ($n++ % 2) ? 'odd' : 'even';

			echo <<<_HTML_
	<tr class="$odd_even vcard">
		<td class="thumb">
			<a href="/$list_user_row[nameScreen]/"><img alt="$list_user_row[nameFull]" class="photo" src="$list_user_icon_url" /></a>
		</td>
		<td>
			<strong>
		  		<a href="/$list_user_row[nameScreen]/" class="url"><span class="fn">$list_user_row[nameFull]</span> (<span class="uid">$list_user_row[nameScreen]</span>)</a>
			</strong>
			<p class="friend-actions">
		  		<small>
_HTML_;

			$action_row = $action_rows[$list_user_id];

			JWTemplate::sidebar_action($action_row,$list_user_id,false);

			echo <<<_HTML_
  		  		</small>
		</p>

	</td>
</tr>
_HTML_;
		}

		echo "</table>";
	}


	static public function RedirectBackToLastUrl($defaultReturnRul=null)
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
}
?>
