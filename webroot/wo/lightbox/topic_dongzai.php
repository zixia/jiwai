<?php
header('Content-Type: text/html;charset=UTF-8');
require_once ('../../../jiwai.inc.php');

$current_user_info = JWUser::GetCurrentUserInfo();
$signature = empty($current_user_info) ? 
	'叽歪网友' : $current_user_info['nameScreen'].'('.$current_user_info['nameFull'].')';

JWTemplate::html_doctype(); 

if ( $_POST )
{
	$number = $_POST['number'];
	$server_address = '106693184001';
	$signature = empty($_POST['signature']) ? $signature : $_POST['signature'];
	$message = "我是${signature},我在叽歪网上发现了关于[冻灾]的一个网友新闻集合，直接回复短信就可以直接播报你的所见所闻了(免费)";
	
	$count = 0;
	foreach ( $number as $one )
	{
		if ( JWDevice::IsValid( $one, 'sms' ) )
		{
			JWRobot::SendMtRawQueue($one, 'sms', $message, $server_address, null);
			$count++;
		}
	}

	if ( $count > 0 ) 
	{
		JWSession::SetInfo('notice', '告诉朋友成功，发送短消息到您的好友成功');
	}
	else
	{
		JWSession::SetInfo('notice', '告诉朋友失败，您没有输入合法的手机号码');
	}
	JWTemplate::RedirectBackToLastUrl( '/t/'.urlEncode('冻灾').'/' );
}
?>

<form id="dongzaiForm" name="dongzaiForm" method="post" action="/wo/lightbox/topic_dongzai">
<div id="wtLightbox">
	<p style="font-weight:bold; font-size:14px;">请输入您知道的能提供最牛消息的朋友的号码：</p>
	<p><input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/>&nbsp;&nbsp;<input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/></p>
	<p><input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/>&nbsp;&nbsp;<input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/></p>
	<p><input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/>&nbsp;&nbsp;<input type="text" name="number[]" style="width:120px; height:18px; border:1px solid #999;"/></p>
	<br>
	<p style="font-weight:bold; font-size:14px;">短信内容：</p>
	<p style="font-size:14px;">我是&nbsp;<input type="text" name="signature" style="width:230px; height:18px; border:1px solid #999;" value="<?php echo $signature; ?>"/>,<br/>
	<p style="font-size:14px;">我在叽歪网上发现了关于[冻灾]的一个网友新闻集合，直接回复短信就可以直接播报你的所见所闻了(免费)
	<p>
	  <input id="jwbutton" name="jwbutton" type="submit" class="submitbutton" value="发送"/>&nbsp;&nbsp;<input type="button" class="closebutton" value="取消" onclick="TB_remove();"/>
	</p>
</div><!-- wtLightbox -->
</form>
</body>
</html>
