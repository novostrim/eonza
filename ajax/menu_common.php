<?php
    $result['result'] = $db->getall( "select * from ?n order by idparent,`sort`,title", CONF_PREFIX.'_menu' );
    $children = array();
    $ids = array();
    foreach ( $result['result'] as $mkey => &$value )
    {
        if ( $value['idparent'] )
            $children[ $value['idparent'] ][] = $mkey;
    }
    foreach ( $result['result'] as &$value )
    {
        if ( isset( $children[ $value['id']] ))
            $value['children'] = $children[ $value['id']];
    }
?>