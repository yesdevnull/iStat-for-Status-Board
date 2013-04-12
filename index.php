<?php

$data = $_GET['data'];

switch ( $data ) {
	case 'cpu_hour' :
	
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
			$cpu_user[] = $row['user'];
			$cpu_system[] = $row['system'];
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

echo json_encode ( $finalArray );