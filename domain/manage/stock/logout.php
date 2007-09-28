<?php
require_once( dirname(__FILE__) . '/config.inc.php' );
unset( $_SESSION['stock_user'] );
Header('Location: /');
?>
