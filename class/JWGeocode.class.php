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
class JWGeocode {

    /**
     * const credit type
     */
    const GEOCODING_GOOG    = 1;   // Google Inc. (Public, NASDAQ:GOOG) 
    const GEOCODING_GOOGAPI = 2;   // Google Inc. (Public, NASDAQ:GOOG) 
    const GEOCODING_YHOOAPI = 3;   // Yahoo! Inc. (Public, NASDAQ:YHOO) 
    const GEOCODING_MSFTAPI = 4;   // Microsoft Inc. (Public, NASDAQ:MSFT) 

    /**
     * Reverse geocoding: Get geocode info from latitude/longitude
     *
     * @param int
     * @param int
     * @return array
     */
    static public function ReverseGeocoding($latitude, $longitude, $coding) {
        $row = array(
                'latitude'  => $latitude,
                'longitude' => $longitude,
                );
        switch ($coding) {
            case self::GEOCODING_YHOOAPI:
                $row = JWGeocode_Yahoo::ReverseGeocoding($latitude, $longitude);
                break;
            default:
                break;
        }
        return $row;
    }

    /**
     * Geocoding: Get latitude/longitude from address info
     *
     * @param int
     * @param array
     * @return array
     */
    static public function Geocoding($coding, $options) {
        if (!is_array($options) || empty($options)) {
            return false;
        }

        $geocode = array();

        switch ($coding) {
            case self::GEOCODING_GOOG:
                $geocode = self::GeocodingByCellcode(
                        @$options['cid'],
                        @$options['lac'],
                        @$options['mnc'],
                        @$options['mcc'] );
                break;
            case self::GEOCODING_YHOOAPI:
                $geocode = JWGeocode_Yahoo::Geocoding($options);
                break;
            case self::GEOCODING_GOOGAPI:
            case self::GEOCODING_MSFTAPI:
            default:
                break;
        }

        return $geocode;
    }

    /**
     * Get latitude/longitude info from ids
     *
     * @param int
     * @param int
     * @param int
     * @param int
     * @return array
     */
    static private function GeocodingByCellcode($cid, $lac, $mnc = 460, $mcc = 0) {
        /**
         * short usage
         *
         * $mnc = 204;
         * $mcc = 815;
         * $cid = 47889;
         * $lac = 4496;
         * extract($_REQUEST, EXTR_IF_EXISTS);
         * $mnc = intval($mnc);
         * $mcc = intval($mcc);
         * $cid = intval($cid);
         * $lac = intval($lac);
         * $geo = GetTudesByCid($mnc, $mcc, $cid, $lac);
         */

        if (null == $cid || null == $lac) return false;

        $pUrl = 'http://www.google.com/glm/mmap';
        $pUri = '/glm/mmap';

        $pContent = pack("H*",
                '0015'.             # Function Code
                '0000000000000000'. # Session ID?
                '00026272'.         # Country Code
                '0012536f6e'.       # User Agent
                '795f457269'.       # User Agent
                '6373736f6e'.       # User Agent
                '2d4b373530'.       # User Agent
                '0005312e332e31'.   # version
                '0003576562'.       # "Web"
                '1b'                # Op Code?
                );
        $pContent .= pack("N", $mnc);
        $pContent .= pack("N", $mcc);
        $pContent .= pack("H*", '000000030000');
        $pContent .= pack("N", $cid);
        $pContent .= pack("N", $lac);
        $pContent .= pack("H*", '00000000000000000000000000000000');

        $pHeaders = array (
                'POST '. $pUri . ' HTTP/1.0',
                'Content-Type: application/binary',
                'Content-Length: ' . strlen($pContent),
                );

        $ch = curl_init($pUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $pHeaders);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pContent); 
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $pData = curl_exec($ch);

        if (curl_errno($ch)) {
            print "Error: " . curl_error($ch);
            return false;
        }

        curl_close($ch);
        $ret = @unpack("ncode/copcode/Nretcode/Nlat/Nlong", $pData);

        if ($ret['code'] === 21 &&
                $ret['opcode'] === 27 &&
                $ret['retcode'] === 0) {
            return array('latitude' => $ret['lat'], 'longitude' => $ret['long']);
        } else {
            return false;
        }
    }

    static public function Create($options = array()) {
        $country = isset( $options['country'] ) ? strtolower($options['country']) : 'china';
        $mcc = isset( $options['mcc'] ) ? intval( $options['mcc'] ) : 460;
        $mnc = isset( $options['mnc'] ) ? intval( $options['mnc'] ) : 0;
        return JWDB::SaveTableRow( 'Geocode', array(
                    'latitude'  => @$options['latitude'],
                    'longitude' => @$options['longitude'],
                    'country'   => $country,
                    'state'     => @$options['state'],
                    'city'      => @$options['city'],
                    'address'   => @$options['address'],
                    'zip'       => @$options['zip'],
                    'mcc'       => $mcc,
                    'mnc'       => $mnc,
                    'cid'       => @$options['cid'],
                    'lac'       => @$options['lac'],
                    ));
    }

    static public function GetDbRowByGeocode($latitude, $longitude) {
        $conditions = array(
                'latitude'  => $latitude,
                'longitude' => $longitude);

        return JWDB::GetTableRow( 'Geocode', $conditions);
    }

    static public function GetDbRowByCondition($options) {
        if (!is_array($options) || empty($options))
            return false;
        return JWDB::GetTableRow( 'Geocode', $options);
    }

    /**
     * Get idGeocode
     *
     * @param int
     * @param int
     * @param int
     * @param int
     * @return int
     */
    static public function GetGeocodeByCellcode($mcc, $mnc, $cid, $lac) {
        $idGeocode = null;
        $row = self::GetDbRowByCondition( array(
                    'cid' => $cid,
                    'lac' => $lac) );
        if (empty( $row )) {
            $idGeocode = self::Create( array(
                        'mcc'   => $mcc,
                        'mnc'   => $mnc,
                        'cid'   => $cid,
                        'lac'   => $lac) );
        } else {
            return $row['id'];
        }
        $geo = self::Geocoding(self::GEOCODING_GOOG , array(
                    'mcc'   => $mcc,
                    'mnc'   => $mnc,
                    'cid'   => $cid,
                    'lac'   => $lac,
                    ));
        if (false == empty($geo)) {
            JWDB::UpdateTableRow( 'Geocode', $idGeocode, array(
                        'latitude'  => $geo['latitude'],
                        'longitude' => $geo['longitude'],
                        ));
        }
        return $idGeocode;
    }

    /**
     * Get idGeocode
     *
     * @param string
     * @return int
     */
    static public function GetGeocodeByAddress($address) {
        $idGeocode = $latitude = $longitude = null;
        $row = self::GetDbRowByCondition( array(
                    'address'   => $address
                    ));
        if (empty( $row )) {
            //TODO Geocoding API
            $geo = self::Geocoding(self::GEOCODING_YHOOAPI, array(
                        'address'   => $address,
                        ));
            $idGeocode = self::Create( $geo );
        } else {
            $idGeocode = $row['id'];
        }

        return $idGeocode;
    }
}
?>
