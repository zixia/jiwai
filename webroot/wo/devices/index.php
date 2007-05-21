<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
require_once('../../../jiwai.inc.php');
JWLogin::MustLogined();

JWDebug::init();

$aDeviceInfo = JWDevice::GetDeviceRowByUserId( JWLogin::GetCurrentUserId() );
$name_screen = JWUser::GetUserInfo( JWLogin::GetCurrentUserId(), 'nameScreen' );

$sms_or_im = isset($_REQUEST['im'])?'im':'sms';

?>
<html>

<?php JWTemplate::html_head() ?>

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
JWTemplate::UserSettingNav("device_$sms_or_im"); 
?>

<?php
$error_html		= JWSession::GetInfo('error');
$notice_html	= JWSession::GetInfo('notice');

if ( isset($error_html) )
{
	echo <<<_ERR_
		<p class="notice">$error_html</p>
_ERR_;
}
else if ( isset($notice_html) )
{
	echo <<<_INFO_
		<p class="notice">$notice_html</p>
_INFO_;
}
?>
	
			<p>通过你的手机和即时聊天软件来感受更多叽歪de乐趣！现在就绑定你的手机或QQ、MSN、GTalk吧！</p>

			<table class="device" cellspacing="0">
<?php if ( 'im'!=$sms_or_im ) { // start SMS_setting ?>
				<tr class="<?php if( empty($aDeviceInfo['sms']['verified']) 
										|| !$aDeviceInfo['sms']['verified'] ) echo 'not_verified'?>">
  					<td class="thumb"><img alt="手机短信" src="http://asset.jiwai.de/img/phone.png" /></td>

					<td>
          				<h3>手机号码</h3>

<?php if ( !isset($aDeviceInfo['sms']) || null==$aDeviceInfo['sms']) { // no sms at all 
?>
						<form action="/wo/devices/create" id="create_device" method="post">
  

    						<input id="device_type" name="device[type]" type="hidden" value="sms" />
    						<input id="device_address" name="device[address]" size="30" type="text" value=""/>
    						<input name="commit" type="submit" value="保存" />
							<p><small>例如：13800138000(移动) 或 13300133000(联通)
								<!-- FIXME: 手机设置的帮助URL -->
								[<a href="http://jiwai.de/help/">?</a>]</small>
							</p>
  
						</form>

<?php } else if ( ! $aDeviceInfo['sms']['verified'] ){ // sms not verified ?>
            			<h4>为了验证您的手机号码(<?php echo $aDeviceInfo['sms']['address']?>)，请将如下验证码发送到短信特服号：
      						<strong> <?php echo JWDevice::GetMobileSpNo($aDeviceInfo['sms']['address']) ?> </strong>
							<code><?php echo $aDeviceInfo['sms']['secret']?></code>
						</h4>

						<p>注意：<strong>免费</strong>通过手机短信更新叽歪de内容。发送短信到叽歪de特服号，跟日常短信一样，只需付给你的手机运营商一条普通短信的费用。 </p>
  

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $aDeviceInfo['sms']['idDevice']?>" class="button-to"><div><input name="_method" type="hidden" value="delete" /><input onclick="return confirm('请确认删除手机号码：<?php echo $aDeviceInfo['sms']['address']?>');" type="submit" value="删除，然后重新设置？" /></div></form></small>

    
  </td>


<?php }else{ // already verified ?>


						<h3> <?php echo $aDeviceInfo['sms']['address']?> </h3>

						通知：

						<form action="/wo/devices/enable/<?php echo $aDeviceInfo['sms']['idDevice']?>" class="device_control" id="device_<?php echo $aDeviceInfo['sms']['idDevice']?>_updates_form" method="post">
							<select name="device[enabled_for]">
  								<option <?php if ('everything'==$aDeviceInfo['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="everything">开启</option>
  								<option <?php if ('nothing'==$aDeviceInfo['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="nothing">关闭</option>
  								<option  <?php if ('direct_messages'==$aDeviceInfo['sms']['enabledFor']) 
											echo 'selected="selected" ';
										?> value="direct_messages">站内消息</option>
							</select>
							<input name="commit" type="submit" value="保存" />
						</form>

						<p><small>手机短信发送到: 
      						<strong> <?php echo JWDevice::GetMobileSpNo($aDeviceInfo['sms']['address']) ?> </strong>
						</small></p>

						<p>注意：<strong>免费</strong>通过手机短信更新叽歪de内容。发送短信到叽歪de特服号，跟日常短信一样，只需付给你的手机运营商一条普通短信的费用。 </p>

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $aDeviceInfo['sms']['idDevice']?>" class="button-to">
								<div>
									<input name="_method" type="hidden" value="delete" />
									<input onclick="return confirm('请确认删除手机号码：<?php echo $aDeviceInfo['sms']['address']?>');" type="submit" value="删除?" />	
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
?>

				<tr class="<?php if ( empty($aDeviceInfo['im']['verified'])
										|| !$aDeviceInfo['im']['verified']) echo 'not_verified'?>">
  					<td class="thumb"><img alt="Im" src="http://asset.jiwai.de/img/im.png" /></td>
  					<td>
          				<h3>聊天(IM)帐号</h3>


<?php if ( empty($aDeviceInfo['im']) ) { // no im at all ?>


						<form action="/wo/devices/create" id="create_device" method="post">
  
    						<input id="device_address" name="device[address]" size="30" type="text" />

							<select name="device[type]">
								<option value="gtalk">GTalk</option>
								<option value="msn">MSN（我们很快会支持QQ！）</option>
<!--
								<option value="qq">QQ</option>
								<option value="jabber">Jabber</option>
-->
							</select>
    						<input name="commit" type="submit" value="保存" />
  
						</form>


<?php } else if ( ! $aDeviceInfo['im']['verified'] ){ // not verified ?>


  						<h4><!--请点击-->验证你的聊天(IM)帐号 (<?php echo strtoupper($aDeviceInfo['im']['type']) . ":" . $aDeviceInfo['im']['address']?>): 
							<!--a href="xmpp:wo@jiwai.de?message;body=<?php echo $aDeviceInfo['im']['secret']?>">wo@jiwai.de</a-->
						</h4>

  						<p><!--直接点击无法验证？-->请将
							<!--a href="xmpp:wo@jiwai.de?message;body=<?php echo $aDeviceInfo['im']['secret']?>">wo@jiwai.de</a-->
								<strong>wo@jiwai.de</strong>
							 加为你的好友，然后将如下验证码发送给她即可：
     						<code><?php echo $aDeviceInfo['im']['secret']?></code>
  						</p>

						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $aDeviceInfo['im']['idDevice']?>" class="button-to">
								<div>
									<input name="_method" value="delete" type="hidden">
									<input onclick="return confirm('请你确认删除帐号：<?php echo strtoupper($aDeviceInfo['im']['type']) . ":" . $aDeviceInfo['im']['address']?>')" 
											value="删除，然后重新设置？" type="submit">
								</div>
							</form>
						</small>


<?php }else{ // already verified ?>


						<h3> <?php echo $aDeviceInfo['im']['address'] 
										. "(" . $aDeviceInfo['im']['type'] . ")" ?> </h3>

						通知：
			
						<form action="/wo/devices/enable/<?php echo $aDeviceInfo['im']['idDevice']?>" class="device_control" 
								id="device_<?php echo $aDeviceInfo['im']['idDevice']?>_updates_form" method="post">

							<select name="device[enabled_for]">
  								<option <?php if ('everything'==$aDeviceInfo['im']['enabledFor']) 
											echo 'selected="selected" ';
										?>value="everything">开启</option>
  								<option <?php if ('nothing'==$aDeviceInfo['im']['enabledFor']) 
											echo 'selected="selected" ';
										?>value="nothing">关闭</option>
  								<option <?php if ('direct_messages'==$aDeviceInfo['im']['enabledFor']) 
											echo 'selected="selected" ';
										?>value="direct_messages">站内消息</option>
							</select>

							<input name="commit" value="保存" type="submit">
						</form>

						<p><small>
							发送更新到：<strong>
								<a href="xmpp:wo@jiwai.de">wo@jiwai.de</a></strong>
						</small></p>


						<small>
							<form method="post" action="/wo/devices/destroy/<?php echo $aDeviceInfo['im']['idDevice']?>" class="button-to">
								<div>
									<input name="_method" value="delete" type="hidden">
									<input onclick="return confirm('请确认删除帐号：<?php echo strtoupper($aDeviceInfo['im']['type']) . ":" . $aDeviceInfo['im']['address']?>？');" 
											value="删除？" type="submit">
								</div>
							</form>
						</small>


<?php } // end im active judge ?>

<?php 
} // end IM_setting 
?>

					</td>
				</tr>
			</table>



		</div><!-- wrapper -->
	</div><!-- content -->

</div><!-- #container -->
<hr class="separator" />

<?php JWTemplate::footer() ?>

</body>
</html>
