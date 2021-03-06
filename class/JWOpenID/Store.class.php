<?php

/**
 * Import the interface for creating a new store class.
 */
require_once 'Auth/OpenID/Interface.php';

class JWOpenID_Store extends Auth_OpenID_OpenIDStore {

    function __construct()
    {
    }

    function storeAssociation($server_url, $association)
    {
		JWMemcache::Instance('api')->Set(md5('ass_'.$server_url), $association);
    }

    function getAssociation($server_url, $handle = null)
    {
        $r = JWMemcache::Instance('api')->Get(md5('ass_'.$server_url));
		return $r ? $r : null;
    }

    function removeAssociation($server_url, $handle)
    {
        return JWMemcache::Instance('api')->Del(md5('ass_'.$server_url));
    }

    function useNonce($server_url, $timestamp, $salt)
    {
        return JWMemcache::Instance('api')->Add(md5('on'.$server_url.$timestamp.$salt), '1', 0, 3600);
    }
}

?>
