<?php
require_once(dirname(__FILE__) . "/../../../jiwai.inc.php");

$mnc = 204;
$mcc = 815;
$cid = 47889;
$lac = 4496;
$pathParam = null;

extract($_REQUEST, EXTR_IF_EXISTS);
$mnc = intval($mnc);
$mcc = intval($mcc);
$cid = intval($cid);
$lac = intval($lac);

$type = trim(strtolower($pathParam), '.');
if (!in_array($type, array('xml', 'json'))) {
    JWApi::OutHeader(406, true);
}

$options = array (
        'mnc'   => $mnc,
        'mcc'   => $mcc,
        'cid'   => $cid,
        'lac'   => $lac,
        );

switch($type) {
    case 'xml' :
        renderXmlReturn($options);
        break;
    case 'json' :
        renderJsonReturn($options);
        break;
    default :
        JWApi::OutHeader(406, true);
        break;
}

function renderXmlReturn($options = null) {
    $geoCodes = GetGeoCodeFromID($options['mnc'],
            $options['mcc'],
            $options['cid'],
            $options['lac']);
    if (false === $geoCodes) {
        JWApi::OutHeader(404, true);
    }

    $xmlString = null;
	header('Content-Type: application/xml; charset=utf-8');
	$xmlString .= '<?xml version="1.0" encoding="UTF-8"?>';
	$xmlString .= JWApi::ArrayToXml($geoCodes, 0, 'geo');
    echo $xmlString;
}

function renderJsonReturn($options = null) {
    $geoCodes = GetGeoCodeFromID($options['mnc'],
            $options['mcc'],
            $options['cid'],
            $options['lac']);
    if (false === $geoCodes) {
        JWApi::OutHeader(404, true);
    }

    echo json_encode($geoCodes);
}

/**
 *
 * @param mnc
 * @param mcc
 * @param cid
 * @param lac
 * @return (latitude, $longitude)
 * 
 * Get geo info from ids
 */
function GetGeoCodeFromID($mnc, $mcc, $cid, $lac) {
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
        return false;
    }

    curl_close($ch);
    $ret = unpack("ncode/copcode/Nretcode/Nlat/Nlon", $pData);

    if ($ret['code'] === 21 &&
        $ret['opcode'] === 27 &&
        $ret['retcode'] === 0) {
        return array('latitude' => $ret['lat'] / 1000000.0, 'longitude' => $ret['lon'] / 1000000.0);
    } else {
        return false;
    }
}

