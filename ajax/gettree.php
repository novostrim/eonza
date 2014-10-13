<?php

require_once 'ajax_common.php';

$dbname = get( 'dbname' );
$id = get( 'id' );

function subfolder( $idparent, $title  )
{
    global $dbname, $db, $id;

    $ret = array( 'id' => $idparent, 'title' => $title );
    $list = $db->getall("select title, id from ?n where isfolder=1 && idparent=?s order by idparent,title", 
                         CONF_PREFIX.'_'.$dbname, $idparent );
    if ( $list )
        foreach ( $list as $ilist )
            if ( !$id || $id != $ilist['id'] )
                $ret['children'][] = subfolder( $ilist['id'], $ilist['title'] );
            else
                $ret['expand'] = true; 
    return $ret;
}

if ( $result['success'] )
{
    $result['result'] = subfolder( 0, '' );
}
print json_encode( $result );
