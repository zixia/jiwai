<?php
require_once('../../jiwai.inc.php');

$weather = JWStatus::GetHeadStatusRow(50101); //北京天气
$video = JWStatus::GetHeadStatusRow(56598); //youkuhot
$photo = JWFarrago::GetGPicture(1); $photo = $photo[0]; //last mms;

//news related
$newsbot = array(
		'信息科技' => array('cnBetaBot', 'engadget', 'solidot'),
		'时事新闻' => array('googlenews', '新华网', 'bbcchinese'),
		'体育竞技' => array('火炬', '中超', '休斯敦火箭'),
		'生活休闲' => array('jiandan', 'qiushi', '55bot'),
		'娱乐八卦' => array('星座', '劲爆娱乐', '豆瓣影评'),
		'其他资讯' => array('历史上的今天', '你知道吗', 'autoblogcn'),
	    );

if (JWLogin::IsLogined()) {
	$current_user = JWUser::GetCurrentUserInfo();
	$astro = JWUtility::GetAstro($current_user['birthday']);
	if ( $astro && $astro_user = JWUser::GetUserInfo("{$astro}运程") ){
		$astro = JWStatus::GetHeadStatusRow($astro_user['id']);
	}
}
if (!$astro_user) {
	$astro_ids = range(162559, 162570);
	$astro_id = rand(162559,162570);
	$astro_user = JWUser::GetUserInfo($astro_id);
	$astro = JWStatus::GetHeadStatusRow($astro_id); 
}

$program = JWStatus::GetHeadStatusRow(51689); //cctv5
//

$darens = array(
		'手机达人' => array(
			'sms', JWFarrago::GetJiWaiDaRenIds('sms',1),
			),
		'飞信达人' => array( 
			'fetion', JWFarrago::GetJiWaiDaRenIds('fetion',1),
			),
		'QQ达人' => array( 
			'qq', 
			JWFarrago::GetJiWaiDaRenIds('qq',1),
			),
		'GTalk达人' => array( 
			'gtalk',
			JWFarrago::GetJiWaiDaRenIds('gtalk',1),
			),
		'MSN达人' => array(
			'msn',
			JWFarrago::GetJiWaiDaRenIds('msn',1),
			),
	       );

//user list
$featureds = JWUser::GetFeaturedUserIds(5);
$hotids = JWUtility::GetColumn(JWVisitUser::Total(5), 'idUser');
$newids = JWUser::GetNewestUserIds(5);

//build parameter
$param_main = array(
		'weather' => $weather,
		'newsbot' => $newsbot,
		'darens' => $darens,

		'featureds' => $featureds,
		'hotids' => $hotids,
		'newids' => $newids,

		'astro' => $astro,
		'astro_user' => $astro_user,
		'program' => $program,
		);

$param_side = array(
		'video' => $video,
		'photo' => $photo,
		);

$element = JWElement::Instance();
?>
<?php $element->html_header();?>
<?php $element->common_header();?>
<div id="container">
<?php $element->wide_notice();?>
<div id="lefter_g">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar_g" class="f">
		<?php $element->block_g_index($param_main);?>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter_g">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar_g" class="f" >
		<?php $element->side_g_index($param_side);?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
