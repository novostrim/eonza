<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

define( 'BUF_SIZE', 512*1024 ); // 512
define( 'QUERY_SIZE', 1024*1024 ); //10 * 1024 *
define( 'LINSERT', 'INSERT');
//$temp = '';
GS::set( 'prev', '' );
GS::set( 'insert', '' );

function queries( $input, &$query )
{
	$prev = GS::get( 'prev' );
	$insert = GS::get( 'insert' );
	$start = 0;
	$db = DB::getInstance();
	while ( ( $off = strpos( $input, "\n", $start )) !== false )
	{
		if ( !$start && $prev )
		{
			$line = trim( $prev.substr( $input, 0, $off ));
			$prev = '';
		}
		else
			$line = trim( substr( $input, $start, $off - $start ));
		if ( $line && $line[0] != '#' && ( $line[0] != '-' || $line[1] != '-' ))
		{
			if ( strtoupper( substr( $line, 0, strlen( LINSERT ))) == LINSERT )
				$insert = $line;

			$query .= "\n".$line;
			if ( $insert && $line[ strlen( $line ) - 1 ] == ',' && strlen( $query ) > QUERY_SIZE )
			{
				$query[ strlen( $query ) - 1 ] = ';';
				if ( !$db->rawQuery( $query ))
					return false;
//				$temp .= $query."\r\n\r\n===========================\r\n\r\n";
				$query = $insert;
			}		
			elseif ( $line[ strlen( $line ) - 1 ] == ';' )
			{
//				$temp .= $query."\r\n\r\n===========================\r\n\r\n";
				if ( !$db->rawQuery( $query ))
					return false;
				$query = '';
				$insert = '';
			}
		}
		$start = $off + 1;
	}
	GS::set( 'prev', $prev.substr( $input, $start ));
	GS::set( 'insert', $insert );
	return true;
}

if ( ANSWER::is_success( true ) && ANSWER::is_access())
{
    require_once 'backup_common.php';

    $pars = post( 'params' );
    $filename = BACKUP."/$pars[filename]";

	$mode = substr( $filename, -2 ) == 'gz' ? MODE_GZ : MODE_SQL;

	if ( $mode == MODE_GZ && !function_exists( 'gzopen' ))
		api_error( 'err_system', 100 );
	else
	{
		$fsql = $mode == MODE_GZ ? gzopen( $filename, 'rb') : fopen( $filename, 'rb');
		if ( !$fsql )
			api_error( 'err_system', 101 );	
		else 
		{
			$query = '';
			$buf = '';
			
			if ( $mode == MODE_GZ )
			{
				while( !gzeof( $fsql )) 
					if ( !queries( gzread( $fsql, BUF_SIZE ), $query ))
					{
						api_error( 'err_system', 102 );
						break;
					}
				gzclose( $fsql );
			}
			else
			{
				while ( !feof( $fsql )) 
	          	    if ( !queries( fread( $fsql, BUF_SIZE ), $query ))
					{
						api_error( 'err_system', 102 );
						break;
					}
				fclose( $fsql );
        	}
		}
	}
//	file_put_contents( "query.sql", $temp );
}
ANSWER::answer();
