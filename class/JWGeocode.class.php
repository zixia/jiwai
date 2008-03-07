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
     * Geocoding via Google Location
     */
    const GEOCODING_GOOGLOC = 1;

    /**
     * Geocoding via Google API
     */
    const GEOCODING_GOOGAPI = 2;

    /**
     * Geocoding via Yahoo! API
     */
    const GEOCODING_YHOOAPI = 3;

    /**
     * Get Geocode By CellId
     */
    const GEOCODING_FUNC_CELL   = 'cell';

    /**
     * Get Geocode By IP
     */
    const GEOCODING_FUNC_IP     = 'ip';

    /**
     * Get Geocode By WapId
     */
    const GEOCODING_FUNC_WAP    = 'wap';

    /**
     * Get Db Row by (latitude, longitude)
     *
     * @param int latitude
     * @param int longitude
     * @return array
     */
    static public function GetDbRowByGeocode($latitude, $longitude) {
        $conditions = array (
            'latitude'  => $latitude,
            'longitude' => $longitude
            );
        return JWDB_Cache::GetTableRow('Geocode', $conditions);
    }

    /**
     * Get Db Row by (codingBy, codingId)
     *
     * @param string codingBy
     * @param string codingId
     * @return array
     */
    static public function GetDbRowByCoding($codingBy, $codingId) {
        $conditions = array (
                'codingBy'  => $codingBy,
                'codingId'  => $codingId,
                );
        return JWDB_Cache::GetTableRow('Geocode', $conditions);
    }

    /**
     * Wrapper, private use
     *
     * @param array
     * @return int idGeocode
     */
    static private function Create($options = array()) {
        return JWDB::SaveTableRow( 'Geocode', array(
                    'latitude'  => @$options['latitude'],
                    'longitude' => @$options['longitude'],
                    'country'   => @$options['country'],
                    'state'     => @$options['state'],
                    'city'      => @$options['city'],
                    'address'   => @$options['address'],
                    'zip'       => @$options['zip'],
                    'codingBy'  => @$options['codingBy'],
                    'codingId'  => @$options['codingId'],
                    ));
    }

    /**
     * Generate codingId field
     *
     * @param string codingBy
     * @param array options
     * @return int
     */
    static private function GetCodingId($codingBy, $options = array()) {
        $codingId = null;

        switch ($codingBy) {
            case self::GEOCODING_FUNC_CELL :
                $codingId = implode(',', array(
                            @$options['cid'],
                            @$options['lac'],
                            @$options['mcc'],
                            @$options['mnc']));
                break;
            case self::GEOCODING_FUNC_IP :
                $codingId = @$options['ip'];
                if (!is_long($codingId))
                    $codingId = ip2long($codingId);
                break;
            case self::GEOCODING_FUNC_WAP :
                $codingId = @$options['wap'];
                if (!is_int($codingId))
                    $codingId = intval($codingId);
                break;
            default :
                break;
        }

        return $codingId;
    }

    /**
     * Get idGeocode by coding, create if non-exist
     *
     * @param string codingBy
     * @param array codingOptions
     * @return int idGeocode
     */
    static public function GetGeocode($codingBy, $options = array()) {
        $idGeocode = false;
        $codingId = self::GetCodingId($codingBy, $options);

        if ($geo = self::GetDbRowByCoding($codingBy, $codingId)) {
            return $geo['id'];
        }

        if (is_array($options)) {
            switch ($codingBy) {
                case self::GEOCODING_FUNC_CELL :
                    $cid = @$options['cid'];
                    $lac = @$options['lac'];
                    $mcc = @$options['mcc'];
                    $mnc = @$options['mnc'];
                    $geo = self::GeocodingByCellcode($cid, $lac, $mcc, $mnc);
                    if (empty($geo)) break;
                    $idGeocode = self::Create( array(
                                'latitude'  => $geo['latitude'],
                                'longitude' => $geo['longitude'],
                                'codingBy'  => $codingBy,
                                'codingId'  => $codingId,
                                ));
                    break;
                case self::GEOCODING_FUNC_IP :
                    $geo = self::GeocodingByIp($codingId);
                    if (empty($geo)) break;
                    $idGeocode = self::Create( array(
                                'latitude'  => $geo['latitude'],
                                'longitude' => $geo['longitude'],
                                'codingBy'  => $codingBy,
                                'codingId'  => $codingId,
                                ));
                    break;
                case self::GEOCODING_FUNC_WAP :
                    // TODO Rolead pushes country/state/city/street
                    $geo = self::GeocodingByWap($codingId);
                    if (empty($geo)) break;
                    $idGeocode = self::Create( array(
                                'latitude'  => $geo['latitude'],
                                'longitude' => $geo['longitude'],
                                'codingBy'  => $codingBy,
                                'codingId'  => $codingId,
                                ));
                default :
                    break;
            }
        }

        return $idGeocode;
    }

    /**
     * Get latitude/longitude info from ip
     *
     * @param string
     * @return array
     */
    static private function GeocodingByIp($ip) {
        // TODO ip2location
        return array(
                'latitude'  => 1234567,
                'longitude' => 7654321,
                );
    }

    /**
     * Get latitude/longitude info from address
     *
     * @param string
     * @return array
     */
    static private function GeocodingByApi($address = array()) {
        // TODO Geocoding by Address
        return array(
                'latitude'  => 1122334455,
                'longitude' => 5544332211,
                );
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
    static private function GeocodingByCellcode($cid, $lac, $mcc = 460, $mnc = 0) {
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

}
?>
