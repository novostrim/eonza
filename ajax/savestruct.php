<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

function column_query( $idfield, &$ifield )
{
    $fname = $ifield['alias'];
    defval( $fname, $idfield );
    $fid = $ifield['idtype'];
    $field = GS::field( $fid );
    $ftype = $field['sql']( $ifield );
    return "`$fname` $ftype";
}

function is_name( $val )
{
    return preg_match("/^[a-zA-Z_0-9]+$/", $val );
}

$pars = post( 'params' );
//print_r( $_POST );
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
$tbl_columns = ENZ_COLUMNS;
if ( $dbname )
{
     if ( !is_name( $dbname ))
        api_error( 'err_alias', $dbname );
    if ( !$idi && in_array( $dbname, $tables ))
        api_error( 'err_dbexist', $dbname );
}

foreach ( $pars['items'] as &$iext )
{
    $extpars = GS::field( $iext['idtype'] );
    if ( $extpars['pars'] )
    {
        $iext['ext'] = pars_list( $extpars['pars'], $iext['extend'] );
        if ( !empty( $iext['ext']['options'] ))
        {
            $io = explode( "\n", $iext['ext']['options'] );
            $json = array();
            foreach ( $io as $ival ) 
            {
                $ival = trim($ival);
                if ( !$ival )
                    continue;
                $lr = explode( '=', $ival, 2 );
                if ( count( $lr ) == 2 )
                    $json[ trim( $lr[0] ) ] = trim( $lr[1] );
            }
            $iext['ext']['options'] = $json;
        }
        $iext['extend'] = json_encode( $iext['ext'] );
    }
    else
    {
        $iext['ext'] = array();
        $iext['extend'] = '{}';
    }
}
$isnew = $idi;
$sys = false;
if ( ANSWER::is_success() && ANSWER::is_access())
{
    ANSWER::result( array());
//    print_r( $tables );
    if ( !$idi )
    {
        ANSWER::success( $db->insert( ENZ_TABLES, pars_list( 'comment,title,alias,idparent,istree', $pars['form'] ), 
                     GS::owner(), true )); 
        if ( ANSWER::is_success())
        {
            $idi = ANSWER::is_success();
//            $xresult['success'] = true;
            if ( !$dbname )
                $dbname = CONF_PREFIX."_$idi";
            $query = "CREATE TABLE IF NOT EXISTS `$dbname` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  `_owner`  smallint(5) unsigned NOT NULL,\r\n";
              $treeindex = '';
              if ( !empty( $pars['form']['istree'] ))
              {
                  $query .= "  `_parent` int(10) unsigned NOT NULL,\r\n";
                  $treeindex = ",\r\n   KEY `_parent` (`_parent`,`_uptime`)";
              }

            foreach ( $pars['items'] as $ifield )
            {
                $idfield = $db->insert( ENZ_COLUMNS, pars_list( 'title,extend,comment,idtype,alias,visible,align', $ifield ), 
                     array( "idtable = $idi", "`sort`=$sort" ), true ); 
                $sort++;
                $gsfield = GS::field( $ifield['idtype'] );
                if ( isset( $gsfield['sql'] ))
                    $query .= column_query( $idfield, $ifield ).", \r\n";
            }
            $query .= "  PRIMARY KEY (`id`),
    KEY `_uptime` (`_uptime`) $treeindex
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            try {
                $db->query( $query );       
                api_log( $idi, 0, 'create' );
            }
            catch ( Exception $e )
            {
                $db->query("delete from ?n where id=?s", ENZ_TABLES, $idi );
                $db->query("delete from ?n where idtable=?s", ENZ_COLUMNS, $idi );
                api_error( "The structure of the table is wrong.\r\n".$e->getMessage());       
            }
        }
    }
    else
    {
        $curtbl = $db->getrow("select id,alias,idparent,istree from ?n where id=?s",
                                   ENZ_TABLES, $idi );
        if ( !$curtbl )
            api_error( 'err_id', "id=$idi" );
        elseif ( defined( 'DEMO' ) && $curtbl['idparent'] == SYS_ID )
            api_error('This feature is disabled in the demo-version.');
        else
        {
            $sys = $curtbl['idparent'] == SYS_ID;
            if ( !$sys )
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
                            $db->update( ENZ_TABLES, array('alias' => $dbname ), '', $idi ); 
                        else
                            api_error( 2, $dbname );
                    }
                }
                else
                    $dbname = alias( $curtbl, CONF_PREFIX.'_' );
                if ( ANSWER::is_success() && (int)$pars['form']['istree'] != (int)$curtbl['istree'] )
                {
                    if ( $curtbl['istree'] )    
                    {
                        $db->query( "alter table ?n drop index ?n", $dbname, '_parent' );
                        $db->query( "alter table ?n drop ?n", $dbname, '_parent' );
                        $db->query( "delete from ?n where idtable=?s && idtype=?s", ENZ_COLUMNS, $idi, FT_PARENT );
                    }
                    else
                    {
                        $db->query( "alter table ?n add ?p", $dbname, "`_parent` int(10) unsigned NOT NULL" );                    
                        $db->query( "alter table ?n add index `_parent` ( `_parent` , `_uptime` ) ", $dbname );
                    }
                }
            }
            if ( ANSWER::is_success())
            {
                if ( $curtbl['idparent'] != SYS_ID )
                    ANSWER::success( $db->update( ENZ_TABLES, 
                        pars_list( 'comment,title,istree', $pars['form'] ), '', $idi )); 
                if ( ANSWER::is_success())
                {
                    api_log( $idi, 0, 'edit' );
                    $db->query( "update ?n set `sort`=30000 where idtable=?s && idtype != ?s", 
                                 ENZ_COLUMNS, $idi, FT_PARENT );
                    foreach ( $pars['items'] as $ipar )
                    {
                        $ipar['sort'] = $sort++;
                        $gsfield = GS::field( $ipar['idtype'] );
                        try {
                            if ( $ipar['id'] )
                            {
                                $db->update( ENZ_COLUMNS, 
                                    pars_list( 'comment,title,sort,visible,align', $ipar ), '', $ipar['id'] ); 
                                $curcol = $db->getrow("select * from ?n where id=?s", 
                                                       ENZ_COLUMNS, $ipar['id'] );
                                if ( $curcol['alias'] != $ipar['alias'] || 
                                     $curcol['idtype'] != $ipar['idtype'] ||
                                     $curcol['extend'] != $ipar['extend'] )
                                {
                                    $colname = alias( $curcol );
                                    if ( !isset( $gsfield['sql'] ) || $db->query( "alter table ?n change ?n ?p", 
                                                  $dbname, $colname, column_query( $ipar['id'], $ipar )))
                                    {
                                        $db->update( ENZ_COLUMNS, 
                                            pars_list( 'alias,idtype,extend', $ipar ), '', $ipar['id'] ); 
                                    }
                                }
                            }
                            else
                            {
                                $idcol = $db->insert( ENZ_COLUMNS, 
                                    pars_list( 'title,comment,idtype,extend,alias,sort,visible,align', $ipar ), 
                                             array( "idtable = $idi" ), true );
                                if ( $idcol && isset( $gsfield['sql'] ))
                                    $db->query( "alter table ?n add ?p", $dbname, column_query( $idcol, $ipar ));
                            }
                        }
                        catch ( Exception $e )
                        {
                            if ( !empty( $idcol ))
                                $db->query("delete from ?n where id=?s", ENZ_COLUMNS, $idcol );
                            api_error( "The structure of the table is wrong.\r\n".$e->getMessage());
                        }
                    }
                    if ( ANSWER::is_success())
                    {
                        $fordel = $db->getall("select * from ?n where idtable=?s && `sort`=30000", 
                                               ENZ_COLUMNS, $idi );
                        foreach ( $fordel as $idel )
                        {
                            $gsfield = GS::field( $idel['idtype'] );
//                            if ( isset( $FXXTYPES[ $idel['idtype']]['sql'] )) 
                            if ( isset( $gsfield['sql'] )) 
                                $db->query( "alter table ?n drop ?n", $dbname, alias( $idel ));
                            elseif ( $idel['idtype'] == FT_FILE || $idel['idtype'] == FT_IMAGE )
                                files_delcolumn( $idel );
                        }
                        $db->query( "delete from ?n where idtable=?s && `sort`=30000", 
                                     ENZ_COLUMNS, $idi );
                    }
                }
            }
        }
    }
    if ( ANSWER::is_success() && !$sys && !empty($pars['form']['istree'] ))
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
if ( ANSWER::is_success() )
    ANSWER::resultset( 'idparent', $isnew ? $curtbl['idparent'] : $pars['form']['idparent'] );
ANSWER::answer();
