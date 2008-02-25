<?php 
header('Content-Type: text/html;charset=UTF-8');
require_once(dirname(__FILE__) . '/../../../jiwai.inc.php'); 
?>

<div id="wtLightbox">
<div class="invitationdiv">
<ul>
	<li class="box1"><img src="<?php echo JWTemplate::GetAssetUrl('/images/invitation-waiting.gif');?>" title="我们正在导入你的好友......" style="margin-left:15px;"/></li>
	<li class="box5" id="importTips">我们正在导入你的好友，此过程可能需要几秒钟的时间，请耐心等待......</li>
</ul>
</div>
</div>
<!-- wtLightbox -->
