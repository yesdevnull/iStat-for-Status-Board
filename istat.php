<?php

$data = $_GET['data'];

switch ( $data ) {
	case 'cpu_hour' :

		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

		$sql = 'SELECT user, system, time FROM hour_cpuhistory ORDER BY time DESC LIMIT 10';

		$finalArray = array (
			'graph' => array (
				'title' => 'CPU History' ,
				'type' => 'line' ,
				'datasequences' => '' ,
			)
		);


		foreach ( $db->query ( $sql ) as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$cpu_user[] = array ( 'title' => (string) $time , 'value' => $row['user'] );
			
			$cpu_system[] = array ( 'title' => (string) $time , 'value' => $row['system'] );
		}


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

	break;
}

header ( 'content-type: application/json' );
//echo '<pre>';
//print_r ( $finalArray );
echo json_encode ( $finalArray );