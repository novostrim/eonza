<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

define( 'LIMIT', 100 );
define( 'BUF_SIZE', 1000000 );
define( 'MODE_SQL', 0 );
define( 'MODE_GZ', 1 );

function getdata( $itbl, $from, &$buf, $cols )
{
	$db = DB::getInstance();
	$ret = $db->getall("select * from ?n order by ?n limit $from,".LIMIT, $itbl, $cols[0]['Field']);
	if ( !$ret  )
		return false;
	$fields = count( $cols );
	foreach ( $ret as $iret )
	{
		$row = array();
		for ( $i = 0; $i < $fields; $i++ )
		{
			if ( !isset( $iret[$cols[$i]['Field']] )) 
				$row[$i] = '\N';
			elseif ( $cols[$i]['isnum'])
				$row[$i] = $iret[$cols[$i]['Field']];
			else
			   	$row[$i] = $db->escape( $iret[$cols[$i]['Field']] );
		}
		$buf .= '(' . implode(',', $row ) . "),\n";
	}
	return true;
}

if ( ANSWER::is_success() && ANSWER::is_access())
{
    require_once 'backup_common.php';

	$par = MODE_GZ;
	$mode = $par == MODE_GZ && function_exists( 'gzopen' ) ? MODE_GZ : MODE_SQL; 

	$backfile = BACKUP.'/'.CONF_DB.strftime("-%Y%m%d-%H%M%S").'.sql';

	$fsql = $mode == MODE_GZ ? gzopen( $backfile.'.gz', 'wb') : fopen( $backfile, 'wb');

	$buf = '
	/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
	/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
	/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
	/*!40101 SET NAMES utf8 */;
	/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
	/*!40103 SET TIME_ZONE=\'+00:00\' */;
	/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
	/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
	/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'NO_AUTO_VALUE_ON_ZERO\' */;
	/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
	';

	$tables = $db->tables();
	$limit = 100;
	foreach ( $tables as $itbl )
	{
		$create = $db->getrow("show create table ?n", $itbl );
		$buf .= "\n\nDROP TABLE IF EXISTS `$itbl`;\n".$create['Create Table'].";\n";
		$cols = $db->getall("SHOW COLUMNS FROM ?n", $itbl );
		foreach ( $cols as &$icol )
			$icol[ 'isnum' ] = preg_match("/^(tinyint|smallint|mediumint|bigint|int|float|double|real|decimal|numeric|year)/", $icol['Type']) ? 1 : 0; 

		if ( $db->getone("select count(?n) from $itbl", $cols[0]['Field'] ))
		{
			$buf .= "/*!40000 ALTER TABLE `$itbl` DISABLE KEYS */;\n\n";
			$buf .= "INSERT INTO `$itbl` VALUES \n";

			$from = 0;
			while ( getdata( $itbl, $from, $buf, $cols ))
			{
				if ( strlen( $buf ) > BUF_SIZE )
				{
					if ( $mode == MODE_GZ )
						gzwrite( $fsql, $buf );
					else
						fwrite( $fsql, $buf );
					$buf = '';
				}
				$from += LIMIT;
			}
			$buf = substr_replace( $buf, ";\n",  -2, 2 );

			$buf .= "\n\n/*!40000 ALTER TABLE `$itbl` ENABLE KEYS */;\n\n";
		}
	}

	$buf .= "\n".'/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

	/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
	/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
	/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
	/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
	/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
	/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
	/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
	';

	if ( $mode == MODE_GZ )
		gzwrite( $fsql, $buf );
	else
		fwrite( $fsql, $buf );

	if ( $mode == MODE_GZ )
		gzclose( $fsql );
	else
		fclose( $fsql );

    backup_list();
}
ANSWER::answer();
