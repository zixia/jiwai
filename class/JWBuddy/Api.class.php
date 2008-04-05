<?php
/**
 * @package  JiWai.de
 * @copyright   AKA Inc.
 * @author   glinus@jiwai.com
 * @version  $Id$
 */

/**
 * JWBuddy_Api
 */

abstract class JWBuddy_Api {
  protected $mContactList;

  protected static $mSupportedSite = array(
      'Twitter',
      'Fanfou',
      );

  public function __construct() {
    $this->mContactList = array();
  }

  public static function &GetFactory($site) {
    if (in_array ($site, self::$mSupportedSite)) {
      $class_name = "JWBuddy_Api_" . $site;
      return new $class_name;
    } else {
      return null;
    }
  }

  abstract function GetContactList($user, $pass);

  protected function GetWebPage($url, $user='', $pass='') {
    $credentials = "$user:$pass";
    $headers = array(
        "Authorization: Basic " . base64_encode($credentials)
        );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
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
 * JWBuddy_Api_Twitter
 */

class JWBuddy_Api_Twitter extends JWBuddy_Api {
  private static $apiAddress = 'http://twitter.com/statuses/friends.json';

  public function GetContactList($user, $pass) {
    $rows = json_decode($this->GetWebPage(self::$apiAddress, $user, $pass));
    $ret = array();
    foreach ($rows as $row) {
      $ret[] = array (
          'nameScreen'  => $row->screen_name,
          'nameFull'    => $row->name,
          'url'         => $row->url,
          );
    }
    return $ret;
  }
}

/**
 * JWBuddy_Api_Fanfou
 */

class JWBuddy_Api_Fanfou extends JWBuddy_Api {
  private static $apiAddress = 'http://api.fanfou.com/users/friends.json';

  public function GetContactList($user, $pass) {
    $rows = json_decode($this->GetWebPage(self::$apiAddress, $user, $pass));
    $ret = array();
    foreach ($rows as $row) {
      $ret[] = array (
          'nameScreen'  => $row->screen_name,
          'nameFull'    => $row->name,
          'url'         => $row->url,
          );
    }
    return $ret;
  }
}

?>

