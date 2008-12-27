<?php
require_once('../../jiwai.inc.php');

$weathers = array(
		50101 => '北京天气',
		50268 => '上海天气',
		50270 => '广州天气',
		50274 => '天津天气',
		50275 => '深圳天气',
		50276 => '杭州天气',
		50277 => '武汉天气',
		51836 => '哈尔滨天气',
		51837 => '南京天气',
		51838 => '南宁天气',
		51839 => '呼和浩特天气',
		51840 => '济南天气',
		51841 => '昆明天气',
		51842 => '拉萨天气',
		51843 => '香港天气',
		51844 => '银川天气',
		51845 => '石家庄天气',
		51846 => '桂林天气',
		51847 => '兰州天气',
		51848 => '贵阳天气',
		51849 => '南昌天气',
		51850 => '福州天气',
		51851 => '长春天气',
		51852 => '郑州天气',
		51853 => '重庆天气',
		51854 => '成都天气',
		51855 => '澳门天气',
		51856 => '乌鲁木齐天气',
		51857 => '海口天气',
		51858 => '西安天气',
		51859 => '厦门天气',
		51860 => '台北天气',
		51861 => '长沙天气',
		51862 => '合肥天气',
		51863 => '太原天气',
		51864 => '沈阳天气',
		51865 => '西宁天气',
		);

$weather_ids = array_keys($weathers);
$weather_users = JWDB_Cache_User::GetDbRowsByIds($weather_ids);
$picture_ids = JWUtility::GetColumn($weather_users, 'idPicture');
$picture_urls = JWPicture::GetUrlRowByIds($picture_ids);

$element = JWElement::Instance();
$param_main = array(
		'weather_users' => $weather_users,
		'picture_urls' => $picture_urls,
		);
?>

<?php $element->html_header();?>
<?php $element->common_header();?>

<div id="container">
<?php $element->wide_notice();?>
<div id="lefter_g">
	<div class="s"><div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div></div>
	<div id="leftBar" >
		<div class="f">
		<?php $element->block_g_weather($param_main);?>
		</div>
	</div>
	<div class="s"><div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div></div>
</div><!-- end lefter -->

<div id="righter_g">
	<div class="a"></div><div class="b"></div><div class="c"></div><div class="d"></div>
	<div id="rightBar" class="f" >
		<?php $element->side_g_weather();?>
	</div>
	<div class="d"></div><div class="c"></div><div class="b"></div><div class="a"></div>
</div><!-- righter -->

<div class="clear"></div>
</div><!-- container -->

<?php $element->common_footer();?>
<?php $element->html_footer();?>
