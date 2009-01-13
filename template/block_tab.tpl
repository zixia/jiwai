<!--${
	list($o,$now) = explode('_',$now,2);
	if ( !$tab ) switch($o)
	{
		case 'user':
			$tab = array(
				'my'=>array('自己的','/'.$g_page_user['nameUrl'].'/'),
				'following'=>array('关注的','/'.$g_page_user['nameUrl'].'/with_friends/'),
				'favourite'=>array('收藏的','/'.$g_page_user['nameUrl'].'/favourites/'),
				'mms'=>array('照片','/'.$g_page_user['nameUrl'].'/mms/'),
			);
			break;
		case 'wo':
			$tab = array(
				'following'=>array('关注的','/wo/'),
				'my'=>array('自己的','/wo/archive/'),
				'reply'=>array('回复我','/wo/replies/'),
				'favourite'=>array('收藏的','/wo/favourites/'),
				'public'=>array('大家的','/public_timeline/'),
			);
			break;
		case 'dm':
			$tab = array(
				'inbox'=>array('收件箱','/wo/direct_messages/'),
				'outbox'=>array('发件箱','/wo/direct_messages/sent'),
				'notice'=>array('提醒','/wo/direct_messages/notice'),
			);
		break;
		case 'thread':
			$tab = array(
				'update'=>array('回复','#update'),
			);
		break;
		case 'invite':
			$tab = array(
				'find'=>array('找朋友','/wo/invite/'),
				'invite'=>array('邀请','/wo/invite/invite'),
			//	'history'=>array('邀请历史','/wo/invite/history'),
			);
		break;
		case 'followings':
		case 'followers':
			$wourl = $wo ?  'wo' : $g_page_user['nameUrl'];
			$tab = array(
				'list'=>array('列表','/'.$wourl.'/'.$o.'/'),
				'square'=>array('方格','/'.$wourl.'/'.$o.'/?s=1'),
			);
		break;
		case 'account':
			$tab = array(
				'settings' => array('账户', '/wo/account/settings'),
				'password' => array('密码', '/wo/account/password'),
				'photos' => array('头像', '/wo/account/photos'),
				'profile' => array('资料', '/wo/account/profile'),
				'interest' => array('兴趣', '/wo/account/interest'),
			);
		break;
		case 'notice':
			$tab = array(
				'email' => array( '邮件', '/wo/notification/email'),
				'im' => array( '通讯工具', '/wo/notification/im'),
			);
		break;
		case 'devices':
			$tab = array(
				'im' => array( '聊天工具', '/wo/devices/im'),
				'sms' => array( '手机', '/wo/devices/sms'),
				'other' => array( '其他网站', '/wo/bindother/index'),
			);
		break;
		default:
			$tab = array();
	}
}-->
<div class="tag">
	<ul>	
	<!--{if $tabtitle && !$tab}-->
		<h2><b>{$tabtitle}</b></h2>
	<!--{elseif $tabtitle && $tab}-->
		<li><h2><b>{$tabtitle}</b></h2></li>
	<!--{/if}-->
	<!--{if $tab && false==isset($tab[$now])}-->
		<li class="sel yel_bor"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div><div class="f"><a href="javascript:void(0);">{$now}</a></div></li>
	<!--{/if}-->
	<!--{foreach $tab AS $tabname=>$one}-->
	<!--{if $tabname==$now}-->
		<li class="sel yel_bor"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div><div class="f"><a class="now" href="{$one[1]}">{$one[0]}</a></div></li>
	<!--{else}-->
		<li><a href="{$one[1]}">{$one[0]}</a></li>
	<!--{/if}-->
	<!--{/foreach}-->
	</ul>
</div>
