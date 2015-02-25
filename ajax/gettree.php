<?php

require_once 'ajax_common.php';

GS::set( 'dbname', CONF_PREFIX.'_'.get( 'dbname' ));
GS::set( 'id', get( 'id' ));

function subfolder( $idparent, $title  )
{
    $id = GS::get('id');
    $ret = array( 'id' => $idparent, 'title' => $title );
    $list = DB::getall("select title, id from ?n where isfolder=1 && idparent=?s order by idparent,title", 
                         GS::get('dbname'), $idparent );
    if ( $list )
        foreach ( $list as $ilist )
            if ( !$id || $id != $ilist['id'] )
                $ret['children'][] = subfolder( $ilist['id'], $ilist['title'] );
            else
                $ret['expand'] = true; 
    return $ret;
}

if ( ANSWER::is_success())
    ANSWER::result( subfolder( 0, '' ));

ANSWER::answer();
