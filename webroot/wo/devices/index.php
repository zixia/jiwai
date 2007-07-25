<?php
require_once('../../../jiwai.inc.php');
JWTemplate::html_doctype();

JWLogin::MustLogined();

JWDebug::init();

$name_screen 	= JWUser::GetUserInfo( JWLogin::GetCurrentUserId(), 'nameScreen' );

$device_row 	= JWDevice::GetDeviceRowByUserId( JWLogin::GetCurrentUserId() );


$sms_or_im = isset($_REQUEST['im'])?'im':'sms';

?>
<html>

<head>
<?php JWTemplate::html_head() ?>
</head>


<body class="device" id="device">

<?php JWTemplate::accessibility() ?>


<?php JWTemplate::header() ?>

	<div class="separator"></div>

<div id="container" class="subpage">
	<div id="content">
		<div id="wrapper">

<style type="text/css">
td {
padding:7px 3px;
vertical-align:top;
}
#create_device input[type="text"], #create_device input[type="submit"], #create_device select {
font-size:1.5em;
padding:4px 2px;
vertical-align:middle;
}
#create_device input[type="text"] {
width:12em;
}
</style>
	
			<h2><?php echo $name_screen ?></h2>


<?php 
JWTemplate::UserSettingNav("device"); 
?>

<?php JWTemplate::ShowActionResultTips() ?>
	
			<p>通过你的手机和即时聊天软件来感受更多叽歪de乐趣！现在就绑定你的手机或QQ、MSN、GTalk吧！</p>

			<table class="device" cellspacing="0">


<?php if ( 'im'!=$sms_or_im ) { // start SMS_setting ?>
				<tr class="<?php if( empty($device_row['sms']) || !$device_row['sms']['verified'] ) echo 'not_verified'?>">
  					<td class="thumb"><img alt="手机短信" src="http://asset.jiwai.de/img/phone.png" /></td>

					<td>
          				<h3>手机号码</h3>

<?php if ( empty($device_row['sms']) ) { // no sms at all ?>
						<form action="/wo/devices/create" id="create_device" method="post">
  

    						<input id="device_type" name="device[type]" type="hidden" value="sms" />
    						<input id="device_address" name="device[address]" size="30" type="text" value=""/>
    						<input name="commit" type="submit" value="保存" />
							<p><small>例如：13800138000(移动) 或 13300133000(联通)
								<!-- FIXME: 手机设置的帮助URL -->
								[<a href="http://help.jiwai.de/NewUserGuide">?</a>]</small>
							</p>
  
						</form>

<?php } else if ( ! $device_row['sms']['verified'] ){ // sms not verified ?>
            			<h4>为了验证您的手机号码(<?php echo $device_row['sms']['address']?>)，请将如下验证码发送到短信特服号：
      						<strong> <?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']) ?> </strong>
							<code><?php echo $device_row['sms']['secret']?></code>
						</h4>

						<p>注意：<strong>免费</strong>通过手机短信更新叽歪de内容。发送短信到叽歪de特服号，跟日常短信一样，只需付给你的手机运营商一条普通短信的费用。 </p>
  

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice']?>" class="button-to"><div><input name="_method" type="hidden" value="delete" /><input onclick="return confirm('请确认删除手机号码：<?php echo $device_row['sms']['address']?>');" type="submit" value="删除，然后重新设置？" /></div></form></small>

    
  </td>


<?php }else{ // already verified ?>


						<h3> <?php echo $device_row['sms']['address']?> </h3>

						通知：

						<form action="/wo/devices/enable/<?php echo $device_row['sms']['idDevice']?>" class="device_control" id="device_<?php echo $device_row['sms']['idDevice']?>_updates_form" method="post">
							<select name="device[enabled_for]">
  								<option <?php if ('everything'==$device_row['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="everything">开启</option>
  								<option <?php if ('nothing'==$device_row['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="nothing">关闭</option>
  								<option  <?php if ('direct_messages'==$device_row['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="direct_messages">只收悄悄话</option>
							</select>
							<input name="commit" type="submit" value="保存" />
						</form>

						<p><small>手机短信发送到: 
      						<strong> <?php echo JWDevice::GetMobileSpNo($device_row['sms']['address']) ?> </strong>
						</small></p>

						<p>注意：<strong>免费</strong>通过手机短信更新叽歪de内容。发送短信到叽歪de特服号，跟日常短信一样，只需付给你的手机运营商一条普通短信的费用。 </p>

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $device_row['sms']['idDevice']?>" class="button-to">
								<div>
									<input name="_method" type="hidden" value="delete" />
									<input onclick="return confirm('请确认删除手机号码：<?php echo $device_row['sms']['address']?>');" type="submit" value="删除?" />	
								</div>
							</form>
						</small>


<?php } // end sms active judge ?>


					</td>
				</tr>
<?php 
} // end SMS_setting 
else
{ // start IM_setting
	$supported_ims = JWDevice::GetSupportedDeviceTypes();

	// 用来记录已经绑定的 im 
	$binded_ims	= array();

	foreach ( $supported_ims as $im ) 
	{
		if ( 'sms'==$im )
			continue;

		if ( isset($device_row[$im]) )	
			array_push($binded_ims, $im);
	}

	$non_binded_ims	= array_diff($supported_ims, $binded_ims);

	foreach ( $binded_ims as $im )
	{

		$im_robot 	= JWDevice::GetRobotFromType($im);
		$im_name 	= JWDevice::GetNameFromType	($im);

		if ( 'gtalk'==$im )
			$im_name = "GTalk(Jabber)";
?>

				<tr class="<?php if ( !$device_row[$im]['verified']) echo 'not_verified'?>">
  					<td class="thumb"><img alt="Im" src="http://asset.jiwai.de/img/im.png" /></td>
  					<td>
          				<h3><?php echo $im_name?>帐号</h3>



<?php 
		if ( ! $device_row[$im]['verified'] )
		{ // not verified 
?>

  						<h4>请验证你的<?php echo $im_name?>帐号：<?php echo $device_row[$im]['address']?></h4>

  						<p>
<?php
		if ( 'qq'==$im )
		{
			echo <<<_HTML_
							点击这里 <a target=blank href=tencent://message/?uin=$im_robot&Site=叽歪一下吧！&Menu=yes><img border="0" SRC=http://wpa.qq.com/pa?p=1:$im_robot:1 alt="点击这里打开QQ" title="点击这里打开QQ"></a>直接打开聊天窗口；<br />
_HTML_;
		}
		if ( 'facebook' == $im) {
?>
							1、访问 <a target="_blank" href="http://apps.facebook.com/jiwaide/?verify">JiWai.de @ Facebook</a> 并安装<br />
							2、输入如下验证码进行验证：<br />
<?php
		} else {
?>
							1、请在<strong><?php echo $im_name?></strong>上，将<strong><?php echo $im_robot?></strong>加为你的<strong><?php echo $im_name?></strong>好友；<br />
							2、将如下验证码通过<strong><?php echo $im_name?></strong>发送<strong>短消息</strong>给她进行验证：<br />
<?php 
		}
?>
     						<code><?php echo $device_row[$im]['secret']?></code>
  						</p>

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $device_row[$im]['idDevice']?>" class="button-to">
								<div>
									<input name="_method" value="delete" type="hidden">
									<input onclick="return confirm('请你确认删除帐号：<?php echo $im_name . ":" . $device_row[$im]['address']?>')" 
											value="删除，然后重新设置？" type="submit">
								</div>
							</form>
						</small>


<?php 	
		}
		else
		{ // already verified 
?>


						<h3> <?php echo ( $im=='facebook' ? JWFacebook::GetName($device_row[$im]['address']) : $device_row[$im]['address'] )
										. "($im_name)" ?> </h3>

						<form action="/wo/devices/enable/<?php echo $device_row[$im]['idDevice']?>" class="device_control" id="device_<?php echo $device_row[$im]['idDevice']?>_updates_form" method="post">
							通知：<?php
							if( in_array($im, array("gtalk","msn","qq"))){
							?>
								<input type="checkbox" name="isSignatureRecord" value="Y" <?php echo ( ($device_row[$im]['isSignatureRecord']=='Y') ? 'checked' : '' ); ?>/>允许记录我的IM签名更新
							<?php
							}
							?><br/>
			
							<select name="device[enabled_for]">
  								<option <?php if ('everything'==$device_row[$im]['enabledFor']) 
											echo 'selected="selected" ';
										?>value="everything">开启</option>
  								<option <?php if ('nothing'==$device_row[$im]['enabledFor']) 
											echo 'selected="selected" ';
										?>value="nothing">关闭</option>
  								<option <?php if ('direct_messages'==$device_row[$im]['enabledFor']) 
											echo 'selected="selected" ';
										?>value="direct_messages">只收悄悄话</option>
							</select>

							<input name="commit" value="保存" type="submit">
						</form>

						<p><small>
							发送更新短消息给：<strong><?php echo $im_name?></strong>上的<strong><?php echo $im_robot?></strong>(不要发邮件)
						</small></p>


						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $device_row[$im]['idDevice']?>" class="button-to">
								<div>
									<input name="_method" value="delete" type="hidden">
									<input onclick="return confirm('请确认删除帐号：<?php echo "$im_name: " . $device_row[$im]['address']?>？');" 
											value="删除？" type="submit">
								</div>
							</form>
						</small>


<?php 	} // end im active judge ?>

					</td>
				</tr>
<?php
	}	// end foreach binded im

	// 对于系统支持，用户还没有绑定的 IM，做出列表
	$non_binded_ims = array_diff($non_binded_ims, array('sms'));

	if ( count($non_binded_ims) )
	{
?>
				<tr class="not_verified">
  					<td class="thumb"><img alt="Im" src="http://asset.jiwai.de/img/im.png" /></td>
  					<td>
          				<h3>聊天(IM)帐号</h3>

						<form action="/wo/devices/create" id="create_device" method="post">
  
    						<input id="device_address" name="device[address]" size="30" type="text" />

							<select name="device[type]">
<?php
		$is_first = true;
		foreach ( $non_binded_ims as $im )
		{
			$im_name = JWDevice::GetNameFromType($im);

			if ( $is_first )
			{
				// 有新 IM 的预告可以放在这里
				$im_sologon = "";
				$is_first = false;
			}
			else
			{
				$im_sologon = "";
			}
			if ($im=='facebook') $im_sologon = ' (无需填写帐号,直接点击保存)';
			echo <<<_HTML_
								<option value="$im">$im_name$im_sologon</option>
_HTML_;
		}
?>
							</select>
    						<input name="commit" type="submit" value="保存" />
  
						</form>


					</td>
				</tr>

<?php
	}
		
} // end IM_setting 
?>


			</table>



		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
