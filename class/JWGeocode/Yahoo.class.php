<?php

/**
 * @package		JiWai.de
 * @copyright	AKA Inc.
 * @author	  	glinus@jiwai.com
 * @version		$Id$
 */

/**
 * JiWai.de Geocode Class
 */
class JWGeocode_Yahoo {
    
    /**
     * Geocoding: Get latitude/longitude from address info
     *
     * @param array
     * @return array
     */
    static public function Geocoding($options) {
        $country= @$options['country'];
        $state  = @$options['state'];
        $city   = @$options['city'];
        $address= @$options['address'];

        return array (
                'latitude'  => null,
                'longitude' => null,
                'country'   => null,
                'state'     => null,
                'city'      => null,
                'address'   => $address,
                );
    }

    /**
     * Reverse geocoding: Get geocode info from latitude/longitude
     *
     * @param int
     * @param int
     * @return array
     */
    static public function ReverseGeocoding($latitude, $longitude) {
        return array (
                'latitude'  => $latitude,
                'longitude' => $longitude,
                'country'   => null,
                'state'     => null,
                'city'      => null,
                'address'   => null,
                'zip'       => null,
                );
    }
}

