<?php

$data = $_GET['data'];

// From: http://stackoverflow.com/a/5501447
function formatSizeUnits ( $bytes ) {
	if ( $bytes >= 1073741824 ) {
		$bytes = number_format ( $bytes / 1073741824 , 2 );
	} elseif ( $bytes >= 1048576 ) {
		$bytes = number_format ( $bytes / 1048576 , 2 );
	} elseif ( $bytes >= 1024 ) {
		$bytes = number_format ( $bytes / 1024 , 2 );
	} elseif ( $bytes > 1 ) {
		$bytes = $bytes;
	} elseif ( $bytes == 1 ) {
		$bytes = $bytes;
	} else {
		$bytes = 0;
	}

	return $bytes;
}

$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

switch ( $data ) {
	/* !CPU Day */
	case 'cpu_day' :

		$finalArray = array (
			'graph' => array (
				'title' => 'CPU History (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
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
		
		$sql = 'SELECT
					user ,
					system ,
					time
				FROM
					day_cpuhistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$cpu_user[] = array ( 'title' => $time , 'value' => $row['user'] );
			
			// Added together for a nice stacked graph
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
	
	/* !CPU Hour */
	case 'cpu_hour' :

		$finalArray = array (
			'graph' => array (
				'title' => 'CPU History (Last Hour)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
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
		
		$sql = 'SELECT
					user ,
					system ,
					time
				FROM
					hour_cpuhistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
			$time = date ( 'H:i:s' ,  $row['time'] );
			
			$cpu_user[] = array ( 'title' => $time , 'value' => $row['user'] );
			
			// Added together for a nice stacked graph
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
	
	/* !RAM Day */
	case 'ram_day' :
		
		$stmt = $db->prepare ( 'SELECT
									total
								FROM
									day_memoryhistory' );
		
		$stmt->execute();
		
		$result = $stmt->fetch();
		
		$total_ram = $result['total'];

		$finalArray = array (
			'graph' => array (
				'title' => 'RAM History (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
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
		
		$sql = 'SELECT
					wired ,
					active ,
					inactive ,
					time
				FROM
					day_memoryhistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
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
	
	/* !RAM Hour */
	case 'ram_hour' :
		
		$stmt = $db->prepare ( 'SELECT
									total
								FROM
									hour_memoryhistory' );
		
		$stmt->execute();
		
		$result = $stmt->fetch();
		
		$total_ram = $result['total'];

		$finalArray = array (
			'graph' => array (
				'title' => 'RAM History (Last Hour)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
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
		
		$sql = 'SELECT
					wired ,
					active ,
					inactive ,
					time
				FROM
					hour_memoryhistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
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
	
	/* !Load Day */
	case 'load_day' :
		
		$sql = 'SELECT
					MAX( one ) AS one ,
					MAX( five ) AS five ,
					MAX( fifteen ) AS fifteen
				FROM
					day_loadavghistory';
					
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		$result = $stmt->fetchAll();
		
		$values = array_values ( max ( $result ) );
		
		$max = max ( $values );
		
		$highest_load = $max + 0.5;
		
		$finalArray = array (
			'graph' => array (
				'title' => 'Load Avg (Last 24 Hours)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
				'datasequences' => '' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => $highest_load ,
				) ,
			)
		);
		
		$sql = 'SELECT
					one ,
					five ,
					fifteen ,
					time
				FROM
					day_loadavghistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
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
	
	/* !Load Hour */
	case 'load_hour' :
		
		$sql = 'SELECT
					MAX( one ) AS one ,
					MAX( five ) AS five ,
					MAX( fifteen ) AS fifteen
				FROM
					hour_loadavghistory';
					
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		$result = $stmt->fetchAll();
		
		$values = array_values ( max ( $result ) );
		
		$max = max ( $values );
		
		$highest_load = $max + 0.5;
		
		$finalArray = array (
			'graph' => array (
				'title' => 'Load Avg (Last Hour)' ,
				'type' => 'line' ,
				'refreshEveryNSeconds' => '60' ,
				'datasequences' => '' ,
				'yAxis' => array (
					'minValue' => 0 ,
					'maxValue' => $highest_load ,
				) ,
			)
		);
		
		$sql = 'SELECT
					one ,
					five ,
					fifteen ,
					time
				FROM
					hour_loadavghistory
				WHERE
					rowid % 30 = 0
				ORDER BY
					time
				ASC
				LIMIT
					20';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
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