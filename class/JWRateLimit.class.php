<?php

/**
 * @package     JiWai.de
 * @copyright   AKA Inc.
 * @author      glinus@gmail.com 
 *
 */
class JWRateLimit {

    const MAX_CAPACITY = 100;

    /**
     * Get Cache key by facility and credential
     * @param string facility
     * @param string credential
     * @return string mc_key
     */
    static private function getMemcacheKey($facility, $credential) {
        $mc_key = JWDB_Cache::GetCacheKeyByFunction(
                array( 'JWRateLimit', $facility ),
                array( $credential ));
        return $mc_key;
    }

    /**
     * ++self::$mCounterMap[$facility][$credential]
     * @param string facility
     * @param string credential
     * @return array the new timeline
     */
    static private function increment($facility, $credential) {
        $memcache = JWMemcache::Instance();
        $mc_key = self::getMemcacheKey($facility, $credential);

        $v = $memcache->Get( $mc_key );

        if (!$v) $v = array();
        if (count($v) > self::MAX_CAPACITY) array_shift($v);
        $v[] = time();

        $memcache->set( $mc_key, $v );

        return $v;
    }

    /**
     * Reset the counter
     * @param string facility
     * @param string credential
     */
    static public function Cleanup($facility, $credential) {
        $memcache = JWMemcache::Instance();
        $mc_key = self::getMemcacheKey($facility, $credential);
        $memcache->Del( $mc_key );
    }

    /**
     * Get the timers
     * @param string facility
     * @param string credential
     * @return array of time
     */
    static public function GetTimeline($facility, $credential) {
        $memcache = JWMemcache::Instance();
        $mc_key = self::getMemcacheKey($facility, $credential);

        $v = $memcache->Get( $mc_key );

        if (!$v) $v = array();
        return $v;
    }

    /**
     * Protect the access
     * @param string facility
     * @param string credential
     * @param int maximum counter
     * @param int windows size in seconds
     * @return boolean true if exceeded, otherwise false
     */
    static public function Protect($facility, $credential, $threshold, $window) {
        if ($threshold > self::MAX_CAPACITY || $threshold < 0)
            $threshold = self::MAX_CAPACITY;

        $v = self::increment($facility, $credential);
        $net = count($v);
        if ($net <= $threshold) return false;
        else if (time() - $v[$net - $threshold - 1] > $window) return false;
        //TODO Log

        return true;
    }
}

?>

