<?php

$data = filter_input ( INPUT_GET , 'data' , FILTER_SANITIZE_STRING );
// For cpu_*
$cores = filter_input ( INPUT_GET , 'cores' , FILTER_SANITIZE_NUMBER_INT );
// For disk_*
$disks = filter_input ( INPUT_GET , 'disks' , FILTER_SANITIZE_STRING );
// For temp_*
$temps = filter_input ( INPUT_GET , 'temps' , FILTER_SANITIZE_STRING );
$temp_unit = filter_input ( INPUT_GET , 'temp_unit' , FILTER_SANITIZE_STRING );
// For graph pretty-fying
$hideXAxis = filter_input ( INPUT_GET , 'hideXAxis' , FILTER_SANITIZE_STRING );

// Because istat_disks.php is a required file, we do some quick checks now so we can 
// bail out early if needs be
if ( file_exists ( 'istat_disks.php' ) ) {
	include_once ( 'istat_disks.php' );
	define ( 'ISTAT_DISKS_FOUND' , true );
}

// Default to 2 cores for proper graph scaling
if ( !isset ( $cores ) ) {
	$cores = 2;
}

// Default to Celsius for temperature measurements.
if ( !isset ( $temp_unit ) ) {
	$temp_unit = 'c';
}

// From: http://stackoverflow.com/a/5501447
function formatSizeUnits ( $bytes , $force = false ) {
	if ( $bytes >= 1099511627776 || $force == 'TB' ) {
		$bytes = number_format ( $bytes / 1099511627776 , 2 );
	} elseif ( $bytes >= 1073741824 || $force == 'GB' ) {
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

	return round ( $bytes , 2 );
}

// Thanks to my buddy Jedda (http://jedda.me) for the list of known SMC temp registers
// https://github.com/jedda/OSX-Monitoring-Tools/blob/master/check_osx_smc/known-registers.md
$tempArray = array (
	'TC0D' => 'CPU A Temp' ,
	'TC0H' => 'CPU A Heatsink' ,
	'TC0P' => 'CPU A Proximity' ,
	'TA0P' => 'Ambient Air 1' ,
	'TA1P' => 'Ambient Air 2' ,
	'TM0S' => 'Memory Slot 1' ,
	'TMBS' => 'Memory Slot 2' ,
	'TM0P' => 'Memory Slots Proximity' ,
	'TH0P' => 'HDD Bay' ,
	'TH1P' => 'HDD Bay 2' ,
	'TH2P' => 'HDD Bay 3', 
	'TH3P' => 'HDD Bay 4' ,
	'TN0D' => 'Northbridge Diode' ,
	'TN0P' => 'Northbridge Proximity' ,
	'TI0P' => 'Thunderbolt Proximity 1' ,
	'TI1P' => 'Thunderbolt Proximity 2' ,
	'F0Ac' => 'Fan Speed' ,
);

$db = new PDO ( 'sqlite:/Library/Application Support/iStat Server/databases/local.db' );

$finalArray = array (
	'graph' => array (
		'title' => '' ,
		'type' => 'line' ,
		'refreshEveryNSeconds' => '120' ,
		'datasequences' => '' ,
		'yAxis' => array () ,
		'xAxis' => array (
			'hide' => false ,
		)
	)
);

if ( isset ( $hideXAxis ) ) {
	$finalArray['graph']['xAxis']['hide'] = true;
}

switch ( $data ) {
	/* !CPU Day */
	case 'cpu_day' :
		
		$finalArray['graph']['title'] = 'CPU History (Last 24 Hours)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => 100 ,
			'units' => array (
				'suffix' => '%' ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$cpu_user[] = array ( 'title' => $time , 'value' => round ( $row['user'] * $cores , 2 ) );
			
			// Added together for a nice stacked graph
			$cpu_system[] = array ( 'title' => $time , 'value' => round ( ( $row['system'] * $cores ) + ( $row['user'] * $cores ) , 2 ) );
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
		
		$finalArray['graph']['title'] = 'CPU History (Last Hour)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => 100 ,
			'units' => array (
				'suffix' => '%' ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$cpu_user[] = array ( 'title' => $time , 'value' => round ( $row['user'] * $cores , 2 ) );
			
			// Added together for a nice stacked graph
			$cpu_system[] = array ( 'title' => $time , 'value' => round ( ( $row['system'] * $cores ) + ( $row['user'] * $cores ) , 2 ) );
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
		
		$finalArray['graph']['title'] = 'RAM History (Last 24 Hours)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => round ( formatSizeUnits( $total_ram * 1024 ) ) ,
			'units' => array (
				'suffix' => ' GB' ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$ram_wired[] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['wired'] * 1024 , 'GB' ) );
			
			$ram_active[] = array ( 'title' => $time , 'value' => formatSizeUnits ( ( $row['active'] * 1024 ) + ( $row['wired'] * 1024 ) , 'GB' ) );
			
			$ram_inactive[] = array ( 'title' => $time , 'value' => formatSizeUnits ( ( $row['inactive'] * 1024 ) + ( $row['active'] * 1024 ) + ( $row['wired'] * 1024 ) , 'GB' ) );
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
		
		$finalArray['graph']['title'] = 'RAM History (Last Hour)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => round ( formatSizeUnits( $total_ram * 1024 ) ) ,
			'units' => array (
				'suffix' => ' GB' ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$ram_wired[] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['wired'] * 1024 , 'GB' ) );
			
			$ram_active[] = array ( 'title' => $time , 'value' => formatSizeUnits ( ( $row['active'] * 1024 ) + ( $row['wired'] * 1024 ) , 'GB' ) );
			
			$ram_inactive[] = array ( 'title' => $time , 'value' => formatSizeUnits ( ( $row['inactive'] * 1024 ) + ( $row['active'] * 1024 ) + ( $row['wired'] * 1024 ) , 'GB' ) );
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
	
	/* !Disk Month */
	case 'disk_month' :
		
		$finalArray['graph']['title'] = 'Disk Usage (Last Month)';
		
		// Whoops, did you forget to set any $disks in the query string?
		if ( !isset ( $disks ) ) {
			$finalArray['graph']['error'] = array (
				'message' => 'You have not provided any disks in the query string' ,
				'detail' => 'Make sure you fill out istat_disks.php then add their numbers to the query string' ,
			);
			
			break;
		}
		
		// The istat_disks.php file does not exist, we need it
		if ( !defined ( 'ISTAT_DISKS_FOUND' ) ) {
			$finalArray['graph']['error'] = array (
				'message' => 'Unable to locate istat_disks.php' ,
				'detail' => 'Please make sure you\' set up the istat_disks.php with your disks and UUIDs' ,
			);
			
			break;
		}
		
		$explodedDisksArray = explode ( ',' , $disks );
		
		// Remove any int not in the $monitoredDisk array from istat_disks.php
		foreach ( $explodedDisksArray as $disk ) {
			if ( array_key_exists ( $disk , $monitoredDisks ) ) {
				$finalExplodedDiskArray[] = $disk;
				
				// Build up a temp UUID array for the SQL query
				$tempUUIDArray[] = $monitoredDisks[$disk]['uuid'];
				$tempDiskArray[$monitoredDisks[$disk]['uuid']] = $monitoredDisks[$disk]['name'];
			}
		}
		
		$sql = 'SELECT
					time ,
					uuid ,
					used ,
					size
				FROM
					month_diskhistory
				WHERE
					uuid IN ( ';
		
		foreach ( $tempUUIDArray as $key => $uuid ) {
			$sql .= ' "' . $uuid . '" ,';
		}
		
		// If there's a stray comma, we shoot to kill
		$num = strlen ( $sql ) - 1;
		
		if ( $sql{$num} == ',' ) {
			$sql = substr ( $sql , 0 , -1 );	
		}
		
		$sql .= ' )
				ORDER BY
					time
				ASC';
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		foreach ( $stmt->fetchAll() as $row ) {
			$time = date ( 'd/m' , $row['time'] );
			
			$maxValueSize[] = formatSizeUnits ( $row['size'] * 1024 * 1024 );
			
			$diskDataSequence[$row['uuid']][] = array ( 'title' => $time , 'value' => formatSizeUnits ( $row['used'] * 1024 * 1024 ) );
		}
		
		$maxValueScale = max ( $maxValueSize );
		
		$finalArray['graph']['yAxis'] = array (
			'maxValue' => $maxValueScale ,
			'units' => array (
				'suffix' => ' GB' ,
			) ,
		);
		
		// I think this is a really gross way of doing it, but it's the only way I can figure 
		// out how to do it right now
		foreach ( $diskDataSequence as $uuid => $unfilteredArray ) {
			for ( $i = 0 ; $i <= count ( $unfilteredArray ) ; $i++ ) {
				// I only want every 30th row to get an even spread over the last hour
				if ( $i % 30 == 0 && $unfilteredArray[$i] != 0 ) {
					$newArray[$uuid][] = $unfilteredArray[$i];
				}
			}
			
			// Construct the final array for each sensor
			$finalDataSequence[] = array (
				'title' => $tempDiskArray[$uuid] ,
				'datapoints' => $newArray[$uuid] ,
			);
		}
		
		$finalArray['graph']['datasequences'] = $finalDataSequence;
		
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
		
		$highest_load = round ( $max + 0.5 , 2 );
		
		$finalArray['graph']['title'] = 'Load Avg (Last 24 Hours)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => $highest_load ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$load_one[] = array ( 'title' => $time , 'value' => round ( $row['one'] , 2 ) );
			
			$load_five[] = array ( 'title' => $time , 'value' => round ( $row['five'] , 2 ) );
			
			$load_fifteen[] = array ( 'title' => $time , 'value' => round ( $row['fifteen'] , 2 ) );
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
		
		$highest_load = round ( $max + 0.5 , 2 );
		
		$finalArray['graph']['title'] = 'Load Avg (Last Hour)';
		$finalArray['graph']['yAxis'] = array (
			'minValue' => 0 ,
			'maxValue' => $highest_load ,
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
			$time = date ( 'H:i' , $row['time'] );
			
			$load_one[] = array ( 'title' => $time , 'value' => round( $row['one'] , 2 ) );
			
			$load_five[] = array ( 'title' => $time , 'value' => round ( $row['five'] , 2 ) );
			
			$load_fifteen[] = array ( 'title' => $time , 'value' => round ( $row['fifteen'] , 2 ) );
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
	
	/* !Temp Day */
	case 'temp_day' :
		
		// Whoops, did you forget to set any $temps in the query string?
		if ( !isset ( $temps ) ) {
			$finalArray['graph']['error'] = array (
				'message' => 'You have not provided any temperature sensors in the query string' ,
				'detail' => 'Make sure you add sensors to the query string' ,
			);
			
			break;
		}
	
		// Get all our temps from the user
		$explodedTempArray = explode ( ',' , $temps );
		
		// Remove any temp sensors that aren't in my list
		foreach ( $explodedTempArray as $temp ) {
			if ( array_key_exists ( $temp , $tempArray ) ) {
				$finalExplodedTempArray[] = $temp;
			}
		}
		
		$finalArray['graph']['title'] = 'Temp Sensors (Last 24 Hours)';
		$finalArray['graph']['yAxis'] = array (
			'units' => array (
				'suffix' => '°'
			)
		);
		
		// Had to remove the modulus equation from this statement due to the uuid IN where clause, it was breaking
		// the SQL query when I had both in :(
		$sql = 'SELECT
					time ,
					uuid ,
					value
				FROM
					day_sensorhistory
				WHERE
					uuid IN (';
		
		foreach ( $finalExplodedTempArray as $key => $temp ) {
			$sql .= ' "' . $temp . '" ,';
		}
		
		// If there's a stray comma, we shoot to kill
		$num = strlen ( $sql ) - 1;
		
		if ( $sql{$num} == ',' ) {
			$sql = substr ( $sql , 0 , -1 );	
		}
		
		// For each temp sensor I want 600 results
		$tempLimitCount = count ( $finalExplodedTempArray ) * 600;
		
		$sql .= ' )
				ORDER BY
					time
				ASC
				LIMIT ' . $tempLimitCount;
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		// Build up our massive resultset from the SQLite DB
		foreach ( $stmt->fetchAll() as $row ) {
			$time = date ( 'H:i' , $row['time'] );
			
			if ( in_array ( $row['uuid'] , $finalExplodedTempArray ) ) {
				// If it's a fan, divide by 100 to scale the graph correctly
				if ( $row['uuid'] == 'F0Ac' ) {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( $row['value'] / 100 , 2 ) );
				}
				// If temp scale is Fahrenheit, convert (C * 1.8 + 32).
				elseif ( $temp_unit == 'f' ) {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( ( $row['value'] * 1.8 + 32 ) , 2 ) );
				} else {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( $row['value'] , 2 ) );
				}
			}
		}
		
		// I think this is a really gross way of doing it, but it's the only way I can figure 
		// out how to do it right now
		foreach ( $finalTemp as $sensor => $unfilteredArray ) {
			for ( $i = 0 ; $i <= count ( $unfilteredArray ) ; $i++ ) {
				// I only want every 30th row to get an even spread over the last hour
				if ( $i % 30 == 0 && $unfilteredArray[$i] != 0 ) {
					$newArray[$sensor][] = $unfilteredArray[$i];
				}
			}
			
			// Construct the final array for each sensor
			$finalDataSequence[] = array (
				'title' => $tempArray[$sensor] ,
				'datapoints' => $newArray[$sensor]
			);
		}
		
		$finalArray['graph']['datasequences'] = $finalDataSequence;
	
	break;
	
	/* !Temp Hour */
	case 'temp_hour' :
		
		// Whoops, did you forget to set any $temps in the query string?
		if ( !isset ( $temps ) ) {
			$finalArray['graph']['error'] = array (
				'message' => 'You have not provided any temperature sensors in the query string' ,
				'detail' => 'Make sure you add sensors to the query string' ,
			);
			
			break;
		}
		
		// Get all our temps from the user
		$explodedTempArray = explode ( ',' , $temps );
		
		// Remove any temp sensors that aren't in my list
		foreach ( $explodedTempArray as $temp ) {
			if ( array_key_exists ( $temp , $tempArray ) ) {
				$finalExplodedTempArray[] = $temp;
			}
		}
		
		$finalArray['graph']['title'] = 'Temp Sensors (Last Hour)';
		$finalArray['graph']['yAxis'] = array (
			'units' => array (
				'suffix' => '°'
			)
		);
		
		// Had to remove the modulus equation from this statement due to the uuid IN where clause, it was breaking
		// the SQL query when I had both in :(
		$sql = 'SELECT
					time ,
					uuid ,
					value
				FROM
					hour_sensorhistory
				WHERE
					uuid IN (';
		
		foreach ( $finalExplodedTempArray as $key => $temp ) {
			$sql .= ' "' . $temp . '" ,';
		}
		
		// If there's a stray comma, we shoot to kill
		$num = strlen ( $sql ) - 1;
		
		if ( $sql{$num} == ',' ) {
			$sql = substr ( $sql , 0 , -1 );	
		}
		
		// For each temp sensor I want 600 results
		$tempLimitCount = count ( $finalExplodedTempArray ) * 600;
		
		$sql .= ' )
				ORDER BY
					time
				ASC
				LIMIT ' . $tempLimitCount;
		
		$stmt = $db->prepare ( $sql );
		
		$stmt->execute();
		
		// Build up our massive resultset from the SQLite DB
		foreach ( $stmt->fetchAll() as $row ) {
			$time = date ( 'H:i' , $row['time'] );
			
			if ( in_array ( $row['uuid'] , $finalExplodedTempArray ) ) {
				// If it's a fan, divide by 100 to scale the graph correctly
				if ( $row['uuid'] == 'F0Ac' ) {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( $row['value'] / 100 , 2 ) );
				} 
				// If temp scale is Fahrenheit, convert (C * 1.8 + 32).
				elseif ( $temp_unit == 'f' ) {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( ( $row['value'] * 1.8 + 32 ) , 2 ) );
				} else {
					$finalTemp[$row['uuid']][] = array ( 'title' => $time , 'value' => round ( $row['value'] , 2 ) );
				}
			}
		}
		
		// I think this is a really gross way of doing it, but it's the only way I can figure 
		// out how to do it right now
		foreach ( $finalTemp as $sensor => $unfilteredArray ) {
			for ( $i = 0 ; $i <= count ( $unfilteredArray ) ; $i++ ) {
				// I only want every 30th row to get an even spread over the last hour
				if ( $i % 30 == 0 && $unfilteredArray[$i] != 0 ) {
					$newArray[$sensor][] = $unfilteredArray[$i];
				}
			}
			
			// Construct the final array for each sensor
			$finalDataSequence[] = array (
				'title' => $tempArray[$sensor] ,
				'datapoints' => $newArray[$sensor]
			);
		}
		
		$finalArray['graph']['datasequences'] = $finalDataSequence;
		
	break;
}

header ( 'content-type: application/json' );

echo json_encode ( $finalArray );