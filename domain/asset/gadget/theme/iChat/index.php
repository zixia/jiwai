<?php
?>
<html>
<head>
	<link href="main.css" media="screen, projection" rel="Stylesheet" type="text/css" />
</head>
<body>


<?php
$system_status 		= file_get_contents('Status.html');
$incoming_status 	= file_get_contents('Incoming/Content.html');
$outgoing_status 	= file_get_contents('Outgoing/Content.html');

$theme_vars			= array(	'/%userIconPath%/i'
								,'/%sender%/i'
								,'/%message%/i'
							);

$thumb = 'thumb48';
$system_status		= preg_replace($theme_vars, array(	'http://beta.jiwai.de/okboy/picture/' . $thumb
														,'JiWai.de'
														,'I\'m System!'
													)
										 	, $system_status
									);

$outgoing_status	= preg_replace($theme_vars, array(	'http://beta.jiwai.de/zixia/picture/' . $thumb
														,'zixia'
														,'Hello, Girl! 你好！'
													)
											, $outgoing_status);

$incoming_status	= preg_replace($theme_vars, array(	'http://beta.jiwai.de/daodao/picture/' . $thumb
														,'daodao'
														,'Hello, Boy! 你好！中华人民共和国万岁万岁万万岁！！！！'
													)
											, $incoming_status);

echo $system_status;
echo $outgoing_status;
echo $incoming_status;
?>


</body>
</html>
