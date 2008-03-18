<?php
require_once( dirname(__FILE__) . '/function.php');

/** 
 * settubg 
 */
$location = JWLocation::GetDBRowsByIdParent( 0 );
$item = array(
	'total_set' => 0,
	'total_bind' => 0,
	'total_via' => 0,
	'd-3-set' => 0,
	'd-3-bind' => 0, 
	'd-2-set' => 0, 
	'd-2-bind' => 0, 
	'd-1-set' => 0, 
	'd-1-bind' => 0, 
	'd-0-set' => 0, 
	'd-0-bind' => 0, 
);

$d3 = date('Y-m-d', strtotime('3 days ago'));
$d2 = date('Y-m-d', strtotime('2 days ago'));
$d1 = date('Y-m-d', strtotime('1 days ago'));
$d0 = date('Y-m-d');

$result = array();
$total = array( 'm'=>$item, 'u'=>$item, 't'=>$item );

for( $i=1; $i<=34; $i++)
{
	$result[$i]= array( 'm'=>$item, 'u'=>$item, 't' => $item,);
}

function set_result( $rows, $item_name)
{
	global $result, $total;
	foreach( $rows as $one )
	{
		if ($one['supplier'] =='MOBILE' )
		{
			$result[$one['i']]['m'][$item_name] = $one['c'];
			$total['m'][$item_name] += $one['c'];
		}
		else if ( 'UNICOM' == $one['supplier'] )
		{
			$result[$one['i']]['u'][$item_name] = $one['c'];
			$total['u'][$item_name] += $one['c'];
		}

		$result[$one['i']]['t'][$item_name] += $one['c'];
		$total['t'][$item_name] += $one['c'];
	}
}

//total_set
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'total_set' );

//total_bind;
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and secret='' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'total_bind' );

//total_via;
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d,User u where prenum=left(d.address,7) and d.type='sms' and secret='' and u.deviceSendVia='sms' and u.id=d.idUser group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'total_via' );

//d-3-set
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d3' and d.timeCreate<='$d2' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-3-set' );

//d-3-bind
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d3' and d.timeCreate<='$d2' and d.secret='' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-3-bind' );

//d-2-set
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d2' and d.timeCreate<='$d1' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-2-set' );

//d-2-bind
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d2' and d.timeCreate<='$d1' and d.secret='' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-2-bind' );

//d-1-set
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d1' and d.timeCreate<='$d0' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-1-set' );

//d-1-bind
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d1' and d.timeCreate<='$d0' and d.secret='' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-1-bind' );

//d-0-set
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d0' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-0-set' );

//d-0-bind
$sql = "select supplier,idLocationProvince as i,count(1) AS c from Mobile m,Device d where prenum=left(d.address,7) and d.type='sms' and d.timeCreate>='$d0' and d.secret='' group by idLocationProvince, supplier order by supplier";
$rows = JWDB::GetQueryResult($sql, true);
set_result( $rows, 'd-0-bind' );

JWRender::Display("mobilebind", array(
	'result' => $result,
	'location' => $location,
	'total' =>$total,
	'menu_nav' => 'mobilebind',
));
?>
