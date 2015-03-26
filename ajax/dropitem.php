<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

function deleteitem( $id )
{
    $idtable = GS::get('idtable');
    $dbname = GS::get('dbname');
    $db = DB::getInstance();

    if ( GS::get( 'istree' ))
    {
        $children = $db->getall("select id from ?n where _parent=?s", $dbname, $id );
        foreach ( $children as $ic )
            deleteitem( $ic['id'] );
    }
    
    $ret = $db->query("delete from ?n where id=?s", $dbname, $id );
    if ( $ret )
    {
        if ( GS::get( 'files_is' ))
            files_delitem( $idtable, $id );
        api_log( $idtable, (int)$id, 'delete' );
    }
    return $ret;
}

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    if ( is_array( $pars['id'] ))
        $what = $pars['id'];
    else
        $what[] = (int)$pars['id'];
    $idtable = (int)$pars['idtable'];
    if ( $what && $idtable );
    {
        $tables = ENZ_TABLES;
        $curtbl = $db->getrow("select id,alias,istree,idparent from ?n where id=?s", $tables, $idtable );
        GS::set( 'files_is', files_is( $idtable ));
        if ( !$curtbl )
            api_error( 'err_id', "idtable=$idtable" );
        elseif ( defined( 'DEMO' ) && $curtbl['idparent'] == SYS_ID )
            api_error('This feature is disabled in the demo-version.');
        else
        {
            GS::set('dbname', alias( $curtbl, CONF_PREFIX.'_' ));
            GS::set('istree', $curtbl['istree'] );
            GS::set('idtable', $idtable );
            foreach ( $what as $id )
                if ( ANSWER::is_access( A_DEL, $idtable, $id ))
                    ANSWER::success( deleteitem( (int)$id ));
        }
    }
}
ANSWER::answer();
