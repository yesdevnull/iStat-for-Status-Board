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
	case 'cpu_hour' :

		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

		$sql = 'SELECT user, system, time, nice FROM hour_cpuhistory ORDER BY time DESC LIMIT 20';

		$finalArray = array (
			'graph' => array (
				'title' => 'CPU History - Hourly' ,
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
				'color' => 'green' ,
				'datapoints' => $cpu_system ,
			) ,
			array (
				'title' => 'User' ,
				'color' => 'red' ,
				'datapoints' => $cpu_user ,
			) ,
		);

	break;
	
	case 'ram_hour' :

		$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

		$sql = 'SELECT wired, active, time, inactive, free, total FROM hour_memoryhistory ORDER BY time DESC LIMIT 20';

		$finalArray = array (
			'graph' => array (
				'title' => 'RAM History - Hourly' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '30' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => formatSizeUnits ( '8388608.000000' ) ,
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
				'color' => 'blue' ,
				'datapoints' => $ram_inactive ,
			) ,
			array (
				'title' => 'Active' ,
				'color' => 'green' ,
				'datapoints' => $ram_active ,
			) ,
			array (
				'title' => 'Wired' ,
				'color' => 'red' ,
				'datapoints' => $ram_wired ,
			) ,
		);
	
	break;
}

header ( 'content-type: application/json' );

echo json_encode ( $finalArray );