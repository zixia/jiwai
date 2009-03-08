<?php

/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @date	  	2009/03/03
 */

/**
 * JiWai.de AND Bomeiti
 */

class JWPartner_Bomeiti {

    static private $msBspId         = 'r566creakqzlfpja';
    static private $msSpotId        = '0001';

    const BOMEITI_CB_ACTIVATE       = 0;
    const BOMEITI_CB_DEACTIVATE     = 1;

    const BOMEITI_AD_ACTIVATE       = 10;
    const BOMEITI_AD_DEACTIVATE     = 11;

    const BOMEITI_CB_AD_ACTIVATE    = 20;
    const BOMEITI_CB_AD_DEACTIVATE  = 21;

    const BOMEITI_AD_IMPRESSION     = 30;
    const BOMEITI_CB_AD_IMPRESSION  = 31;

    const BOMEITI_REGISTER          = 40;

    static private $msUrlTemplate   = array(
        self::BOMEITI_AD_IMPRESSION =>
            'http://camp.bomeiti.com/impJsServlet?bspid=%bspId%&blogurl=%blogUrl%&spotid=%spotId%', 
        self::BOMEITI_CB_ACTIVATE   =>
            'http://www.bomeiti.com/blogger/blogAdded.do?blogUrl=%blogUrl%',
        self::BOMEITI_CB_DEACTIVATE =>
            'http://www.bomeiti.com/blogger/blogDeleted.do?blogUrl=%blogUrl%',
        self::BOMEITI_AD_ACTIVATE   =>
            'http://www.bomeiti.com/portal/blogger/addAdSpot.do?bspId=%bspId%&blogUrl=%blogUrl%&spotId=%spotId%',
        self::BOMEITI_AD_DEACTIVATE =>
            'http://www.bomeiti.com/portal/blogger/deleteAdSpot.do?bspId=%bspId%&blogUrl=%blogUrl%&spotId=%spotId%',
        self::BOMEITI_CB_AD_ACTIVATE    =>
            'http://www.bomeiti.com/blogger/spotAdded.do?blogUrl=%blogUrl%&spotcode=%spotId%',
        self::BOMEITI_CB_AD_DEACTIVATE  =>
            'http://www.bomeiti.com/blogger/spotDeleted.do?blogUrl=%blogUrl%&spotcode=%spotId%',
        self::BOMEITI_CB_AD_IMPRESSION  =>
            'http://camp.bomeiti.com/impJsServlet?bspid=%bspId%&blogurl=%blogUrl%&spotid=%spotId%',
        self::BOMEITI_REGISTER      =>
            'http://www.bomeiti.com/portal/blogger/register/Js.do?params=%bspId%-%blogUrl%------------',
            );

    static public function GetAdUrl($callback, $user, $option = array()) {
        if (! isset(self::$msUrlTemplate[$callback]) ) {
            throw new JWException(__FUNCTION__);
        }

        $blogUrl = 'http://JiWai.de/';
        $blogUrl.= empty($user['nameUrl'])
            ? urlencode($user['nameScreen'])
            : urlencode($user['nameUrl']);
        $blogUrl.= '/';

        $spotId = isset($option['spotId']) ? $option['spotId'] : self::$msSpotId;
        $callbackUrl = str_replace(
                array('%blogUrl%', '%spotId%', '%bspId%'),
                array(urlencode($blogUrl), $spotId, self::$msBspId),
                self::$msUrlTemplate[$callback
                ]);

        return $callbackUrl;
    }
}

?>

