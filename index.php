<?php

$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

$sql = 'SELECT user, system, time FROM hour_cpuhistory ORDER BY time DESC LIMIT 20';

$finalArray = array (
	'graph' => array (
		'title' => 'CPU History' ,
		'type' => 'bar' ,
		'datasequences' => '' ,
	)
);


foreach ( $db->query ( $sql ) as $row ) {
	//echo 'Time: ' . $row['time'] . ' &mdash; User: ' . $row['user'] . ' CPU: ' . $row['system'] . "\n";
	$cpu_user[] = $row['user'];
	$cpu_system[] = $row['system'];
}

// print_r ( $cpu_user );

// $finalArray['graph']['datasequences'] = 'cock';


$finalArray['graph']['datasequences'] = array (
	array (
		'title' => 'User' ,
		'color' => 'red' ,
		'datapoints' => $cpu_user ,
	) ,
	array (
		'title' => 'System' ,
		'color' => 'green' ,
		'datapoints' => $cpu_system ,
	)
);

header ( 'content-type: application/json' );

//print_r ( $finalArray );

echo json_encode ( $finalArray );


/*

$result = $db->query ( $sql );

$row = $result->fetch();

//print_r ( $row );
echo $row['time'] . "\n\n";

echo date ( 'd-m-Y H:i:s' , $row['time'] ) . "\n\n";

echo time();
*/