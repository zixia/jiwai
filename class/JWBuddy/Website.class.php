<?php
/**
 * @package  JiWai.de
 * @copyright   AKA Inc.
 * @author   glinus@jiwai.com
 * @version  $Id$
 */

/**
 * JWBuddy_Website
 */

abstract class JWBuddy_Website {

  protected $mContactList;

  protected static $mSupportedSite = array('Douban');

  public function __construct() {
    $this->mContactList = array();
  }

  public static function &GetFactory($site) {
    if (in_array ($site, self::$mSupportedSite)) {
      $class_name = "JWBuddy_Website_" . $site;
      return new $class_name;
    } else {
      return null;
    }
  }

  abstract function GetContactList($user, $pass);

  protected function GetWebPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Jiwai Bot/1.0');

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      //print "Error: " . curl_error($ch);
      return false;
    } else {
      curl_close($ch);
    }

    return $data;
  }

  public function RenderContactList($user, $pass = '', $type = 'json') {
    $this->mContactList = $this->GetContactList($user, $pass);
    $ret = null;

    switch ($type) {
      case "json" :
        $ret = json_encode($this->mContactList);
        break;
      case "array" :
        $ret = serialize($this->mContactList);
        break;
      default :
      break;
    }

    return $ret;
  }
}

/**
 * JWBuddy_Website_Douban
 */

class JWBuddy_Website_Douban extends JWBuddy_Website {
// http://www.douban.com/people/lecause/friend_list
  public function GetContactList($user, $pass) {
    if (empty($user)) return false;
    
    $ret = array();
    $url = 'http://www.douban.com/people/' . urlencode($user) . '/friend_list';
    $data = $this->GetWebPage($url);
    $pattern = '/<dd>.*?\/people\/([^\/]+).*?>([^<]+)<\/a>/i';
    preg_match_all($pattern, $data, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $ret[] = array (
          'nameScreen'  => $match[1],
          'nameFull'  => $match[2],
          'url' => 'http://douban.com/people/' . $match[1],
          );
    }
    
    return $ret;
  }
}

?>

