<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

function deleteitem( $id )
{
    global $db, $curtbl, $files_is, $dbname, $idtable;

    if ( $curtbl['istree'] )
    {
        $children = $db->getall("select id from ?n where _parent=?s", $dbname, $id );
        foreach ( $children as $ic )
            deleteitem( $ic['id'] );
    }
    
    $ret = $db->query("delete from ?n where id=?s", $dbname, $id );
    if ( $ret )
    {
        if ( $files_is )
            files_delitem( $idtable, $id );
        api_log( $idtable, (int)$id, 'delete' );
    }
    return $ret;
}

if ( $result['success'] )
{
    $pars = post( 'params' );
    if ( is_array( $pars['id'] ))
        $what = $pars['id'];
    else
        $what[] = (int)$pars['id'];
    $idtable = (int)$pars['idtable'];
    if ( $what && $idtable );
    {
        $tables = CONF_PREFIX.'_tables';
        $curtbl = $db->getrow("select id,alias,istree from ?n where id=?s", $tables, $idtable );
        $files_is = files_is( $idtable );
        if ( !$curtbl )
            api_error( 'err_id', "idtable=$idtable" );
        else
        {
            $dbname = $curtbl['alias'] ? $curtbl['alias'] : CONF_PREFIX."_$idi";
            foreach ( $what as $id )
            {
                $result['success'] = deleteitem( (int)$id );
            }
        }
    }
}
print json_encode( $result );
