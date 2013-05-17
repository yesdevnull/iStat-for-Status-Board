<?php

$data = $_GET['data'];

// From: http://stackoverflow.com/a/5501447
function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2);
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2);
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2);
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
}

switch ( $data ) {
	case 'cpu_day' :

		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

		$sql = 'SELECT user, system, time, nice FROM day_cpuhistory WHERE rowid % 30 = 0 ORDER BY time ASC LIMIT 20';

		$finalArray = array (
			'graph' => array (
				'title' => 'CPU History (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '30' ,
				'datasequences' => '' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => 100 ,
					'units' => array (
						'suffix' => '%' ,
					) ,
				) ,
			)
		);


		foreach ( $db->query ( $sql ) as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$cpu_user[] = array ( 'title' => $time , 'value' => $row['user'] );
			
			$cpu_system[] = array ( 'title' => $time , 'value' => $row['system'] + $row['user'] );
		}


		$finalArray['graph']['datasequences'] = array (
			array (
				'title' => 'System' ,
				'color' => 'red' ,
				'datapoints' => $cpu_system ,
			) ,
			array (
				'title' => 'User' ,
				'color' => 'blue' ,
				'datapoints' => $cpu_user ,
			) ,
		);

	break;
	
	case 'ram_day' :

		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );
		
		$stmt = $db->prepare ( 'SELECT total FROM day_memoryhistory' );
		
		$stmt->execute();
		
		$result = $stmt->fetch();
		
		$total_ram = $result['total'];

		$sql = 'SELECT wired, active, time, inactive, free, total FROM day_memoryhistory WHERE rowid % 30 = 0 ORDER BY time ASC LIMIT 20';

		$finalArray = array (
			'graph' => array (
				'title' => 'RAM History (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '30' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => formatSizeUnits( $total_ram ) ,
					'units' => array (
						'suffix' => ' GB' ,
					)
				) ,
				'datasequences' => '' ,
			)
		);


		foreach ( $db->query ( $sql ) as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$ram_wired[] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['wired'] ) );
			
			$ram_active[] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['active'] + $row['wired'] ) );
			
			$ram_inactive[] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['inactive'] + $row['active'] + $row['wired'] ) );
		}


		$finalArray['graph']['datasequences'] = array (
			array (
				'title' => 'Inactive' ,
				'color' => 'mediumGray' ,
				'datapoints' => $ram_inactive ,
			) ,
			array (
				'title' => 'Active' ,
				'color' => 'red' ,
				'datapoints' => $ram_active ,
			) ,
			array (
				'title' => 'Wired' ,
				'color' => 'blue' ,
				'datapoints' => $ram_wired ,
			) ,
		);
	
	break;
	
	case 'load_day' :
	
		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

		$sql = 'SELECT one, five, fifteen, time FROM day_loadavghistory WHERE rowid % 30 = 0 ORDER BY time ASC LIMIT 20';

		$finalArray = array (
			'graph' => array (
				'title' => 'Load Avg (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '30' ,
				'datasequences' => '' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => 6 ,
				) ,
			)
		);


		foreach ( $db->query ( $sql ) as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$load_one[] = array ( 'title' => $time , 'value' => $row['one'] );
			
			$load_five[] = array ( 'title' => $time , 'value' => $row['five'] );
			
			$load_fifteen[] = array ( 'title' => $time , 'value' => $row['fifteen'] );
		}


		$finalArray['graph']['datasequences'] = array (
			array (
				'title' => 'Fifteen' ,
				'color' => 'mediumGray' ,
				'datapoints' => $load_fifteen ,
			) ,
			array (
				'title' => 'Five' ,
				'color' => 'red' ,
				'datapoints' => $load_five ,
			) ,
			array (
				'title' => 'One' ,
				'color' => 'blue' ,
				'datapoints' => $load_one ,
			) ,
		);
	
	break;
}

header ( 'content-type: application/json' );

echo json_encode ( $finalArray );