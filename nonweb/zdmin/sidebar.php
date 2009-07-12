<?php
require_once( dirname(__FILE__) . '/function.php');
checkAdmin('admin');

$filename =  FRAGMENT_ROOT . "page/sidebar_announcement.html";

extract($_POST, EXTR_IF_EXISTS);
$type = isset($_POST['type']) ? $_POST['type'] : 'reload';

switch($type)
{
    case 'reload':
        $fp   = fopen($filename, 'r');
        $data = '';
        if ($fp)
        {
            $data = fread($fp, filesize($filename));
        }
        fclose($fp);
        break;
    case 'prev':
        $_SESSION['sidebarprev'] = $_POST['content'];
        echo "<script>location.href='sidebarprev.php';</script>";
        break;
    case 'save':
    default:
        if (isset($_POST))
        {
            $fp   = fopen($filename, 'w');
            if( is_writeable($filename) )
            {
                $data = $_POST['content'];
                fwrite($fp, $data);
            }
            fclose($fp);
        }
        $_SESSION['sidebarprev'] = '';
}

$render = new JWHtmlRender();
$render->display("sidebar", array(
        'menu_nav' => 'sidebar',
        'data' => $data,
));
?>
