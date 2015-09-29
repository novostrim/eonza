<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $dest = $pars['dest'];
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $idi );
        if ( !$curtable || $curtable['isfolder'])
            api_error( 'err_id', "id=$idi" );
        else
        {
            $curtable['title'] = $dest;
            foreach ( array( 'alias', '_uptime', 'id', '_owner' ) as $iun )
                unset( $curtable[ $iun ] );
            ANSWER::success( $db->insert( ENZ_TABLES, $curtable, GS::owner(), true )); 
            if ( ANSWER::is_success())
            {
                api_log( ANSWER::is_success(), 0, 'create' );
                $dbname = api_dbname( $idi );
                $newname = CONF_PREFIX.'_'.( ANSWER::is_success());

                $cols = $db->getall( "select * from ?n where idtable=?s", ENZ_COLUMNS, $idi );
                $after = array();
                foreach ( $cols as &$icol )
                {
                    $column = $icol;
                    $icol['idtable'] = ANSWER::is_success();
                    unset( $icol['id'] );
                    $newid = $db->insert( ENZ_COLUMNS, $icol, '', true );
                    if ( !$column['alias'] )
                    {
                        $field = GS::field( $icol['idtype'] );
                        $decl = $field['sql']( $icol );
                        $after[] = $db->parse("alter table ?n change ?n ?n ?p", 
                                    $newname, $column['id'], $newid, $decl );
                    }
                    $icol['newid'] = $newid;
                    $icol['id'] = $column['id'];
                }
                $ret = $db->query("create table ?n like ?n", $newname, $dbname );
                if ( $pars['importdata'])
                    $ret = $db->query("insert into ?n select * from ?n", $newname, $dbname );
                foreach ( $after as $iafter )
                    $db->query( $iafter );

                foreach ( $cols as $cil ) 
                    if ( $cil['idtype'] == FT_LINKTABLE )
                    {
                        $extend = json_decode( $cil['extend'], true );
                        if ( !empty( $extend['multi'] ))
                        {
                            $list = $db->getall( "select iditem, idmulti from ?n where idcolumn=?s", 
                                                  ENZ_ONEMANY, $cil['id'] );
                            foreach ( $list as $il )
                                $db->insert( ENZ_ONEMANY, array( 'idcolumn' => $cil['newid'],
                                    'iditem' => $il['iditem'], 'idmulti' => $il['idmulti'] ) );
                        }
                    }
            }
        }
    }
}
ANSWER::answer();
