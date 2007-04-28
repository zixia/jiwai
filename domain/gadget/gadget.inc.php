<?php
function gadget($nameScreen, $statusType, $themeName, $numMax)
{
	$idUser = JWUser::GetUserIdByName($nameScreen);

	echo <<<_JS_
document.write("<h1>Hello, World!</h1>");
document.write("<h1>[$nameScreen] [$statusType] [$themeName] [$numMax]</h1>");

_JS_;
}
?>
