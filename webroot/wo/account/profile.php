<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

$user_info = JWUser::GetCurrentUserInfo();
$new_user_info = @$_POST['user'];
$birthday_info = @$_POST['birthday'];
$outInfo = $user_info;
$birthday_array = explode("-", $outInfo['birthday'], 3);
$birthday_year = @$birthday_array[0];
$birthday_month = @$birthday_array[1];
$birthday_day = @$birthday_array[2];

if ( $new_user_info )
{
	$notice_html = null;
	$error_html = null;

	$array_changed = array();
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if ($new_user_info['nameFull'] === '')
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		elseif (JWUser::IsValidFullName($new_user_info['nameFull']))
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}
	if( $new_user_info['nameFull'] != $outInfo['nameFull'] ) {
		if( $new_user_info['nameFull'] === '' )
			$array_changed['nameFull'] = $outInfo['nameScreen'];
		else
			$array_changed['nameFull'] = $new_user_info['nameFull'];
	}

	if( $new_user_info['url'] != $outInfo['url'] ) {
		$new_user_info['url'] = ltrim( $new_user_info['url'], '/' );
		if( $new_user_info['url'] && false == preg_match( '/^(http:|https:)/', strtolower($new_user_info['url']) ) ) {
			$new_user_info['url'] = 'http://' . $new_user_info['url'];
		}
		$array_changed['url'] = $new_user_info['url'];
	}

	if( $new_user_info['address'] != @$outInfo['address'] ) {
		$array_changed['address'] = $new_user_info['address'];
	}

	if( $new_user_info['zipcode'] != @$outInfo['zipcode'] ) {
		$array_changed['zipcode'] = $new_user_info['zipcode'];
	}

	if( $new_user_info['current'] != @$outInfo['current'] ) {
		$array_changed['current'] = $new_user_info['current'];
	}

	if( $new_user_info['gender'] != @$outInfo['gender'] ) {
		$array_changed['gender'] = $new_user_info['gender'];
	}

	if( $new_user_info['marriage'] != @$outInfo['marriage'] ) {
		$array_changed['marriage'] = $new_user_info['marriage'];
	}

	$new_user_info['birthday'] = "$birthday_info[year]-$birthday_info[month]-$birthday_info[day]";
	if( $new_user_info['birthday'] != @$outInfo['birthday'] ) {
		$array_changed['birthday'] = $new_user_info['birthday'];
	}

	if( $new_user_info['bio'] != $outInfo['bio'] ) {
		if( $new_user_info['bio'] === '' ) {
			$array_changed['bio'] = $outInfo['nameFull'];
		} else {
			$array_changed['bio'] = $new_user_info['bio'];
		}
	}

	$new_location = intval(@$_POST['province'])."-".intval(@$_POST['city']);
	$new_location = trim($new_location);
	if( $new_location != $outInfo['location'] ) {
		$array_changed['location'] = $new_location;
	}

	$new_native = intval(@$_POST['native_province'])."-".intval(@$_POST['native_city']);
	$new_native = trim($new_native);
	if( $new_native != $outInfo['native'] ) {
		$array_changed['native'] = $new_native;
	}

	if( count( $array_changed ) ) {
		if( count( $array_changed ) ) {
			JWUser::Modify( $user_info['id'], $array_changed );
			JWSession::SetInfo('notice', '修改个人资料成功');
		}
		JWTemplate::RedirectToUrl();
	}
}

/*Procince and city id */
$pid = $cid =0;
@list($pid, $cid) = explode('-', $outInfo['location']);

/*Native Procince and city id */
$native_pid = $native_cid =0;
@list($native_pid, $native_cid) = explode('-', $outInfo['native']);
?>
<html>
<head>
<?php 
JWTemplate::html_head(array(
	'version_css_jiwai_screen' => 'v1',
)); 
?>

<script type="text/javascript">
window.jiwai_init_hook_location_setting = function()
{
	JWLocation.select('provinceSelect','citySelect',<?php echo intval($pid);?>,<?php echo intval($cid);?>); 
	JWLocation.select('native_provinceSelect','native_citySelect',<?php echo intval($native_pid);?>,<?php echo intval($native_cid);?>); 
}
</script>
</head>

<body class="account" id="settings">

<?php JWTemplate::accessibility() ?>

<?php JWTemplate::header() ?>
<?php JWTemplate::ShowActionResultTipsMain() ?>

<div id="container">
<p class="top">设置</p>
<div id="wtMainBlock">
<div class="leftdiv">
<ul class="leftmenu">
<li><a href="/wo/account/settings" class="now">基本资料</a></li>
<li><a href="/wo/privacy/">保护设置</a></li>
<li><a href="/wo/devices/sms">绑定设置</a></li>
<li><a href="/wo/notification/email">系统通知</a></li>
<li><a href="/wo/account/profile_settings">个性化界面</a></li>
<li><a href="/wo/openid/">Open ID</a></li>
</ul>
</div><!-- leftdiv -->
<div class="rightdiv">
<div class="lookfriend">
<form id="f" method="post" name="f" action="" class="validator"> 
<p class="right14"><a href="/wo/account/settings">帐户信息</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/password">修改密码</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/photos">头像</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/profile" class="now">个人资料</a>&nbsp;|&nbsp;&nbsp;<a href="/wo/account/interest">兴趣爱好</a></p>
	<p class="accountLine black15bold">基本个人资料</p>
	<div id="wtRegist" style="margin:25px 0 0 0; width:520px">
        <ul>
	    <li class="box5">姓名</li>
		<li class="box6"><input name="user[nameFull]" type="text" id="user_nameFull" value="<?php echo $outInfo['nameFull']; ?>" ajax="nameFull" alt="姓名" class="inputStyle"/><i></i></li>
		<li class="box7">你的真实姓名，可使用中文和空格</li>
		<li class="box5">性别</li>
		<li class="box6">
		<?php /*	<label for="user_gender1"><input type="radio" id="user_gender1" name="user[gender]" value="male" <?php echo 'male'==$outInfo['gender']?'checked':'';?> style="height:20px;"><span style="margin-bottom:-2px;">男&nbsp;&nbsp;</label></span>
		<input type="radio" id="user_gender0" name="user[gender]" value="female" <?php echo 'female'==$outInfo['gender']?'checked':'';?>><label for="user_gender0">女&nbsp;&nbsp;</label>
		<input type="radio" id="user_gender2" name="user[gender]" value="secret" <?php echo 'secret'==$outInfo['gender']?'checked':'';?>><label for="user_gender2">保密</label>*/ ?>
	<select id="user_gender" name="user[gender]" size="1" class="select seWidth">
		  <option value="" selected>请选择</option>
		  <option value="male" <?php echo 'male'==$outInfo['gender']?'selected':'';?>>男</option>
		  <option value="female" <?php echo ('female'==$outInfo['gender'])?'selected':'';?>>女</option>
		</select>
		</li>
		<li class="box9"></li>
		<li class="box5">当前位置</li>
		<li class="box6"><input name="user[current]" type="text" id="user_current" value="<?php echo !empty($outInfo['current'])?htmlSpecialChars($outInfo['current']):'地球'; ?>" alt="当前位置" class="inputStyle"/><i></i></li>
		<li class="box9"></li>
		<li class="box5">自我介绍</li>
		<li class="box6"><textarea name="user[bio]" id="user_bio" rows="3" class="textarea position" maxLength="200" alt="自我介绍"><?php echo htmlSpecialChars($outInfo['bio']);?></textarea></li>
		<li class="box7">一句话的介绍，不超过200个字</li>
		<li class="box5">个人网址</li>
		<li class="box6"><input name="user[url]" type="text" id="user_url" value="<?php echo $outInfo['url'] ?>" ajax="url" null="true" alt="网址" class="inputStyle"/><i></i></li>
		<li class="box7">比如：博客地址、相册地址、个人网站</li>
		</ul>
       </div><!-- wtRegist -->
       <div style="overflow: hidden; clear: both; height: 10px; line-height: 1px; font-size: 1px;"></div>
	   <p class="accountLine black15bold">详细个人资料</p>
	   <div id="wtRegist" style="margin:25px 0 0 0; width:520px">
        <ul>
	    <li class="box5">生日</li>
		<li class="box6">
		<select id="birthday_year" name="birthday[year]" size="1" class="select Width80">
		  <option value="" selected>请选择</option>
		<?php
		$j = date("Y");
		$k = $j-99;
		//for($i=$k;$i<=$j;$i++)
		for($i=$j;$i>=$k;$i--)
		{
			echo "<option value='$i' ";
			echo $i==$birthday_year?"selected":"";
			echo ">${i}年</option>";
		}
		?>
		</select>
		<select id="birthday_month" name="birthday[month]" size="1" class="select Width56">
		  <option value="" selected>请选择</option>
		<?php
		$j = 12;
		$k = 1;
		for($i=$k;$i<=$j;$i++)
		{
			echo "<option value='$i' ";
			echo $i==$birthday_month?"selected":"";
			echo ">${i}月</option>";
		}
		?>
		</select>
		<select id="birthday_day" name="birthday[day]" size="1" class="select Width56">
		  <option value="" selected>请选择</option>
		<?php
		$j = 31;
		$k = 1;
		for($i=$k;$i<=$j;$i++)
		{
			echo "<option value='$i' ";
			echo $i==$birthday_day?"selected":"";
			echo ">${i}日</option>";
		}
		?>
		</select>
		</li>
		<li class="box9"></li>
		<li class="box5">婚否</li>
		<li class="box6">
		<select id="user_marriage" name="user[marriage]" size="1" class="select seWidth">
		  <option value="" selected>请选择</option>
		  <option value="single" <?php echo 'single'==$outInfo['marriage']?'selected':'';?>>单身</option>
		  <option value="had" <?php echo 'had'==$outInfo['marriage']?'selected':'';?>>有主</option>
		  <option value="finding" <?php echo 'finding'==$outInfo['marriage']?'selected':'';?>>正找呢</option>
		</select></li>
		<li class="box9"></li>
		<li class="box5">定居地</li>
		<li class="box6">&nbsp;&nbsp;<select id='provinceSelect' name="province" style="width:112px;" onChange="JWLocation.select('provinceSelect','citySelect', this.options[this.options.selectedIndex].value, 0);" class="select"></select><select id='citySelect' name="city" style="width:112px;" class="select"></select></li>
		<li class="box9"></li>
		<li class="box5">籍贯</li>
		<li class="box6">&nbsp;&nbsp;<select id='native_provinceSelect' name="native_province" style="width:112px;" onChange="JWLocation.select('native_provinceSelect','native_citySelect', this.options[this.options.selectedIndex].value, 0);" class="select"></select><select id='native_citySelect' name="native_city" style="width:112px;" class="select"></select></li>
		<li class="box9"></li>
		<li class="box5">邮寄地址</li>
		<li class="box6"><input name="user[address]" type="text" id="user_address" value="<?php echo $outInfo['address']; ?>" class="inputStyle"/></li>
		<li class="box7">邮寄地址是为了便于叽歪网给你寄发资料或赠品</li>		
		<li class="box5">邮编</li>
		<li class="box6"><input name="user[zipcode]" type="text" id="user_zipcode" value="<?php echo $outInfo['zipcode']; ?>" class="inputStyle" maxlength="6"/></li>
		<li class="box9"></li>
		<li class="box7"><input type="submit" id="save" name="save" class="submitbutton" value="保存" /></li>
		</ul>
       </div><!-- wtRegist -->
	   <div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
  </form>
</div><!-- lookfriend -->
<div style="overflow: hidden; clear: both; height: 50px; line-height: 1px; font-size: 1px;"></div>
</div>
<!-- rightdiv -->
</div><!-- #wtMainBlock -->
<div style="overflow: hidden; clear: both; height: 7px; line-height: 1px; font-size: 1px;"></div>
</div><!-- #container -->

<?php JWTemplate::footer() ?>
</body>
</html>
