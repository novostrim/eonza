<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

/*function columns_list( $tablename )
{
	global $db;

	$ret = array();
	$list = $db->getall("show columns from ?n", $tablename );
	foreach ( $list as $ilist )
		$ret[] = $ilist['field'];
	return $ret;
}*/

function column_query( $idfield, $ifield )
{
	global $FIELDS;

	$fname = defval( $ifield['alias'], $idfield );
	$fid = $ifield['idtype'];
//	print_r( $ifield );
	$ftype = $FIELDS[$fid]['sql']( $ifield );
	return "`$fname` $ftype";
}

function is_name( $val )
{
	return preg_match("/^[a-zA-Z_0-9]+$/", $val );
}

$pars = post( 'params' );

$idi = $pars['id'];
$aliases = array();
$sort = 10;
foreach ( $pars['items'] as $ialias )
{
	if ( empty( $ialias['alias']))
		continue;
	if ( !is_name( $ialias['alias'] ) || in_array( $ialias['alias'], $aliases ))
	{
		api_error( 'err_alias', $ialias['alias'] );
		break;
	}
	$aliases[] = $ialias['alias'];
}
$tables = $db->tables();
$dbname = empty( $pars['form']['alias'] ) ? '' : $pars['form']['alias'];
$tbl_columns = CONF_PREFIX.'_columns';
if ( $dbname )
{
 	if ( !is_name( $dbname ))
		api_error( 'err_alias', $dbname );
	if ( !$idi && in_array( $dbname, $tables ))
		api_error( 'err_dbexist', $dbname );
}

foreach ( $pars['items'] as &$iext )
{
	if ( $FIELDS[ $iext['idtype']]['pars'] )
	{
		$iext['ext'] = pars_list( $FIELDS[ $iext['idtype']]['pars'], $iext['extend'] );
		$iext['extend'] = json_encode( $iext['ext'] );
	}
	else
	{
		$iext['ext'] = array();
		$iext['extend'] = '{}';
	}
}

if ( $result['success'] )
{
	$result['result'] = array();
//	print_r( $tables );
	if ( !$idi )
	{
		$result['success'] = $db->insert( CONF_PREFIX.'_tables', pars_list( 'comment,title,alias,idparent,istree', $pars['form'] ), 
			         array( "_owner=$USER[id]"), true ); 
		if ( $result['success'] )
		{
			$idtable = $result['success'];
//			$result['success'] = true;
			if ( !$dbname )
				$dbname = CONF_PREFIX."_$idtable";
			$query = "CREATE TABLE IF NOT EXISTS `$dbname` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  `_owner`  smallint(5) unsigned NOT NULL,\r\n";
  			$treeindex = '';
  			if ( !empty( $pars['form']['istree'] ))
  			{
  				$query .= "  `_parent` int(10) unsigned NOT NULL,\r\n";
  				$treeindex = "\r\n   KEY `_parent` (`_parent`,`_uptime`)";
  			}
			foreach ( $pars['items'] as $ifield )
			{
				$idfield = $db->insert( CONF_PREFIX.'_columns', pars_list( 'title,extend,comment,idtype,alias,visible,align', $ifield ), 
			         array( "idtable = $idtable", "`sort`=$sort" ), true ); 
				$sort++;
				if ( isset( $FIELDS[ $ifield['idtype']]['sql'] ))
					$query .= column_query( $idfield, $ifield ).", \r\n";
			}
			$query .= "  PRIMARY KEY (`id`),
	KEY `_uptime` (`_uptime`) $treeindex
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
			if ( $db->query( $query ))
				api_log( $idtable, 0, 'create' );
		}
	}
	else
	{
		$curtbl = $db->getrow("select id,alias,idparent,istree from ?n where id=?s",
		                           CONF_PREFIX.'_tables', $idi );
		if ( !$curtbl )
			api_error( 'err_id', "id=$idi" );
		else
		{
			if ( $dbname != $curtbl['alias'] ) 
			{
				if ( !$dbname )
					$dbname = CONF_PREFIX."_$idi";
				if ( in_array( $dbname, $tables ))
					api_error( 'err_dbexist', $dbname );
				else
				{
					if ( $db->query( "alter table ?n rename to ?n", alias( $curtbl, CONF_PREFIX.'_' ), $dbname ))
						$db->update( CONF_PREFIX.'_tables', array('alias' => $dbname ), '', $idi ); 
					else
						api_error( 2, $dbname );
				}
			}
			else
				$dbname = alias( $curtbl, CONF_PREFIX.'_' );
			if ( $result['success'] && (int)$pars['form']['istree'] != (int)$curtbl['istree'] )
			{
				if ( $curtbl['istree'] )	
				{
					$db->query( "alter table ?n drop index ?n", $dbname, '_parent' );
					$db->query( "alter table ?n drop ?n", $dbname, '_parent' );
					$db->query( "delete from ?n where idtable=?s && idtype=?s", CONF_PREFIX.'_columns', $idi, FT_PARENT );
				}
				else
				{
					$db->query( "alter table ?n add ?p", $dbname, "`_parent` int(10) unsigned NOT NULL" );					
					$db->query( "alter table ?n add index `_parent` ( `_parent` , `_uptime` ) ", $dbname );
				}
			}
			if ( $result['success'] )
			{
				$result['success'] = $db->update( CONF_PREFIX.'_tables', 
					    pars_list( 'comment,title,istree', $pars['form'] ), '', $idi ); 
				if ( $result['success'] )
				{
					api_log( $idi, 0, 'edit' );
					$coldb = CONF_PREFIX.'_columns';
					$db->query( "update $coldb set `sort`=30000 where idtable=?s && idtype != ?s", $idi, FT_PARENT );
					//$allcol = $db->getall("select * from $coldb where idtable=?s", $idi );
	//				$columns = columns_list( $dbname );
					foreach ( $pars['items'] as $ipar )
					{
						$ipar['sort'] = $sort++;
						if ( $ipar['id'] )
						{
							$db->update( $coldb, 
						    	pars_list( 'comment,title,sort,visible,align', $ipar ), '', $ipar['id'] ); 
							$curcol = $db->getrow("select * from ?n where id=?s", $coldb, $ipar['id'] );
							if ( $curcol['alias'] != $ipar['alias'] || 
								 $curcol['idtype'] != $ipar['idtype'] ||
								 $curcol['extend'] != $ipar['extend'] )
							{
								$colname = alias( $curcol );
								if ( !isset( $FIELDS[ $ipar['idtype']]['sql'] ) || $db->query( "alter table ?n change ?n ?p", 
									          $dbname, $colname, column_query( $ipar['id'], $ipar )))
									$db->update( $coldb, 
								    	pars_list( 'alias,idtype,extend', $ipar ), '', $ipar['id'] ); 
							}
						}
						else
						{
							$idcol = $db->insert( CONF_PREFIX.'_columns', 
								pars_list( 'title,comment,idtype,extend,alias,sort,visible,align', $ipar ), 
				         				array( "idtable = $idi" ), true );
							if ( $idcol && isset( $FIELDS[ $ipar['idtype']]['sql'] ))
								$db->query( "alter table ?n add ?p", $dbname, column_query( $idcol, $ipar ));
						}
	//					print_r( $ipar );
					}
					if ( $result['success'] )
					{
						$fordel = $db->getall("select * from $coldb where idtable=?s && `sort`=30000", 
											$idi );
						foreach ( $fordel as $idel )
						{
							if ( isset( $FTYPES[ $idel['idtype']]['sql'] )) 
								$db->query( "alter table ?n drop ?n", $dbname, alias( $idel ));
							elseif ( $idel['idtype'] == FT_FILE || $idel['idtype'] == FT_IMAGE )
								files_delcolumn( $idel );
						}
						$db->query( "delete from $coldb where idtable=?s && `sort`=30000", $idi );
					}
				}
			}
		}
	}
	if ( $result['success'] && !empty($pars['form']['istree'] ))
	{

		$curparent = $db->getone("select id from ?n where idtable=?s && idtype=?s", $tbl_columns, $idi, FT_PARENT );
		$first =  $db->getone("select id from ?n where idtable=?s && idtype!=?s order by `sort`", $tbl_columns, $idi, FT_PARENT );
		$extend = '{ "table": "'.$idi.'", "column":"'.$first.'","extbyte": "2"}';
		if ( $curparent )
			$db->update( $tbl_columns, array( 'extend' => $extend ), '', $curparent );
		else
			$db->insert( $tbl_columns, array( 'title' => '', 'idtype' => FT_PARENT, 'alias' => '_parent', 
  						'extend'=> $extend, 'align'=>0, 'visible' => '1' ), 
			         	array( "idtable = $idi", "`sort`=0" )); 
	}
}
if ( $result['success'] )
	$result['result']['idparent'] = $idi ? $curtbl['idparent'] : $pars['form']['idparent'];
print json_encode( $result );
?>