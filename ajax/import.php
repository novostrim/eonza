<?php

require_once 'ajax_common.php';

$pars = post( 'params' );

if ( ANSWER::is_success())
{
    ANSWER::success( $db->insert( CONF_PREFIX.'_tables', 
            array( 'title' => $pars['tblname'] ), GS::owner(), true )); 
    $extend = array();
    if ( ANSWER::is_success())
    {
        $table = CONF_PREFIX.'_'.( ANSWER::is_success());
        $ret = $db->query("create table ?n like ?n", $table, $pars['tblname'] );
        if ( $pars['importdata'])
            $ret = $db->query("insert into ?n select * from ?n", $table, $pars['tblname'] );
        if ( !$ret )
        {
            $db->query("delete from ?n where id=?s", CONF_PREFIX.'_tables', ANSWER::is_success() );
            api_error( 101 );
        }
        else {
            api_log( ANSWER::is_success(), 0, 'create' );
            $list = $db->getall("show columns from ?n", $table );
            $isid = 1;
            $istime = 1;
            $isowner = 1;
            $sort = 1;
            foreach ( $list as $ilist )
            {
                foreach ( $ilist as $ikey => $ival )
                    $ilist[ strtolower( $ikey ) ] = $ilist[ $ikey ];
                if ( strtolower( $ilist['extra'] ) == 'auto_increment' )
                {
                    $isid = 0;
                    if ( $ilist['field'] != 'id' )
                        $db->query("alter table ?n change ?n `id` $ilist[type] NOT NULL auto_increment", 
                                 $table, $ilist['field'] );
                }
                elseif ( $ilist['field'] == 'id' )
                {
                    $db->query("alter table ?n change `id` `id` $ilist[type] NOT NULL auto_increment PRIMARY KEY", 
                                 $table );
                    $isid = 0;
                }
                elseif ( $ilist['field'] == '_uptime' )
                    $istime = 0;
                elseif ( $ilist['field'] == '_owner' )
                    $isowner = 0;
                else
                {
                    $idtype = FT_SQL;
                    $xtype = preg_split( '/[\(\)]+/', $ilist['type'], 0, PREG_SPLIT_NO_EMPTY );
                    if ( $xtype[0] == 'datetime' )
                    {
                        $idtype = FT_DATETIME;
                        $extend['date'] = 1;
                    }
                    elseif ( $xtype[0] == 'date' )
                    {
                        $idtype = FT_DATETIME;
                        $extend['date'] = 2;
                    }
                    elseif ( $xtype[0] == 'timestamp' )
                    {
                        $idtype = FT_DATETIME;
                        $extend['date'] = 3;
                    }
                    elseif ( $xtype[0] == 'text' )
                    {
                        $idtype = FT_TEXT;
                        $extend['weditor'] = 1;
                        $extend['bigtext'] = 0;
                    }
                    elseif ( $xtype[0] == 'float' )
                    {
                        $idtype = FT_DECIMAL;
                        $extend['dtype'] = 1;
                    }                    
                    elseif ( $xtype[0] == 'double' )
                    {
                        $idtype = FT_DECIMAL;
                        $extend['dtype'] = 2;
                    }                    
                    elseif ( count($xtype) >= 2 && (int)$xtype[1] )
                    {
                        $shift = count( $xtype ) ==3 && trim( $xtype[2] ) == 'unsigned'    ? 1 : 0;
                        if ( $xtype[0] == 'varchar' )
                        {
                            $idtype = FT_VAR;
                            $extend['length'] = (int)$xtype[1];
                        }
                        elseif ( $xtype[0] == 'int' )
                        {
                            $idtype = FT_NUMBER;
                            $extend['range'] = 7 + $shift;
                        }
                        elseif ( $xtype[0] == 'tinyint' )
                        {
                            $idtype = FT_NUMBER;
                            $extend['range'] = 1 + $shift;
                        }
                        elseif ( $xtype[0] == 'smallint' )
                        {
                            $idtype = FT_NUMBER;
                            $extend['range'] = 3 + $shift;
                        }
                        elseif ( $xtype[0] == 'mediumint' )
                        {
                            $idtype = FT_NUMBER;
                            $extend['range'] = 5 + $shift;
                        }
                        elseif ( $xtype[0] == 'float' || $xtype[0] == 'double' )
                        {
                            $idtype = FT_DECIMAL;
                            $extend['dtype'] = $xtype[0] == 'float' ? 1 : 2;
                            $extend['dlen'] = $xtype[1];
                        }
                    }
                    if ( $idtype == FT_SQL )
                        $extend['sqlcmd'] = $ilist['type'];
                    $fields = array( 'title'=> $ilist['field'], 'alias'=> $ilist['field'],
                            'idtype' => $idtype,
                            'visible' => ( $sort < 7 ? 1 : 0 ), 'align' => 0 );
                    if ( $extend )
                        $fields['extend'] = json_encode( $extend );
                    $db->insert( CONF_PREFIX.'_columns', $fields,
                         array( "idtable = ".ANSWER::is_success(), "`sort`=$sort" ), true ); 
                    $sort++;
                }
            }
            if ( $isid )
                $db->query("alter table ?n add `id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY", $table );
            if ( $istime )
                $db->query( "alter table ?n add `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", $table );
            if ( $isowner )
            {
                if ( $db->query( "alter table ?n add `_owner` smallint(5) unsigned NOT NULL", $table ))
                    $db->query( "update ?n set _owner = ?s", $table, GS::userid());
            }
        }
    }
}

//if ( !isset( $pars['newtblname']) || $pars['newtblname'])

//$tables = $db->tables();

//if ( in_array( $pars['']))
//print_r( $tables );
//print_r( $pars );
/**/
/*
function is_name( $val )
{
    return preg_match("/^[a-zA-Z_0-9]+$/", $val );
}

$pars = post( 'params' );
$idi = $pars['id'];
$aliases = array();
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
if ( $dbname )
{
     if ( !is_name( $dbname ))
        api_error( 'err_alias', $dbname );
    if ( !$idi && in_array( $dbname, $tables ))
        api_error( 'err_dbexist', $dbname );
}

if ( ANSWER::is_success())
{
    $xresult['result'] = array();
//    print_r( $tables );
    if ( !$idi )
    {
        $xresult['success'] = $db->insert( CONF_PREFIX.'_tables', pars_list( 'comment,title,alias,idparent', $pars['form'] ), 
                     GS::owner( '_uptime = NOW()' ), true ); 
        if ( ANSWER::is_success())
        {
            $idtable = $xresult['success'];
//            $xresult['success'] = true;
            if ( !$dbname )
                $dbname = CONF_PREFIX."_$idtable";
            $query = "CREATE TABLE IF NOT EXISTS `$dbname` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` datetime NOT NULL, 
  `_owner`  smallint(5) unsigned NOT NULL,\r\n";
            $sort = 0;
            foreach ( $pars['items'] as $ifield )
            {
                $idfield = $db->insert( CONF_PREFIX.'_columns', pars_list( 'title,extpar,comment,idtype,alias,visible,align', $ifield ), 
                     array( "idtable = $idtable", "`sort`=$sort" ), true ); 
                $sort++;
                $query .= column_query( $idfield, $ifield ).", \r\n";
            }
            $query .= "  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $db->query( $query );
        }
    }
    else
    {
        $curtbl = $db->getrow("select id,alias,idparent from ?n where id=?s",
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
                    if ( !$db->query( "alter table ?n rename to ?n", 
                        $curtbl['alias'] ? $curtbl['alias'] : CONF_PREFIX."_$idi", $dbname ))
                        $db->update( CONF_PREFIX.'_tables', array('alias' => $dbname ), '', $idi ); 
                    else
                        api_error( 2, $dbname );
                }
            }
            else
                $dbname = $curtbl['alias'] ? $curtbl['alias'] : CONF_PREFIX."_$idi";

            $xresult['success'] = $db->update( CONF_PREFIX.'_tables', 
                    pars_list( 'comment,title', $pars['form'] ), '', $idi ); 
            if ( ANSWER::is_success())
            {
                $coldb = CONF_PREFIX.'_columns';
                $db->query( "update $coldb set `sort`=30000 where idtable=?s", $idi );
                //$allcol = $db->getall("select * from $coldb where idtable=?s", $idi );
//                $columns = columns_list( $dbname );
                foreach ( $pars['items'] as $ipar )
                {
                    if ( $ipar['id'] )
                    {
                        $db->update( $coldb, 
                            pars_list( 'comment,title,sort,visible,align', $ipar ), '', $ipar['id'] ); 
                        $curcol = $db->getrow("select * from ?n where id=?s", $coldb, $ipar['id'] );
                        if ( $curcol['alias'] != $ipar['alias'] || 
                             $curcol['idtype'] != $ipar['idtype'] ||
                             $curcol['extpar'] != $ipar['extpar'] )
                        {
                            $colname = empty( $curcol['alias'] ) ? $curcol['id'] : $curcol['alias'];
                            $query = column_query( $ipar['id'], $ipar );
//                            if ( !$idi && in_array( $dbname, $tables ))
//                                api_error( 'err_dbexist', $dbname );
                            if ( $db->query( "alter table ?n change ?n $query", $dbname, $colname ))
                                $db->update( $coldb, 
                                    pars_list( 'alias,idtype,extpar', $ipar ), '', $ipar['id'] ); 

                        }
                    }
                    else
                    {
                        $idcol = $db->insert( CONF_PREFIX.'_columns', 
                            pars_list( 'title,extpar,comment,idtype,alias,sort,visible,align', $ipar ), 
                                     array( "idtable = $idi" ), true );
                        if ( $idcol )
                        {
                            $query = column_query( $idcol, $ipar );
                            $db->query( "alter table ?n add $query", $dbname );
                        }
                    }
//                    print_r( $ipar );
                }
                $fordel = $db->getall("select * from $coldb where idtable=?s && `sort`=30000", 
                                        $idi );
                foreach ( $fordel as $idel )
                    $db->query( "alter table ?n drop ?n", $dbname, $idel['alias'] ? $idel['alias'] : $idel['id'] );
                $db->query( "delete from $coldb where idtable=?s && `sort`=30000", $idi );
            }
        }
    }
}
if ( ANSWER::is_success())
    $xresult['result']['idparent'] = $idi ? $curtbl['idparent'] : $pars['form']['idparent'];
*/    
ANSWER::answer();
