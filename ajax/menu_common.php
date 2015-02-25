<?php

$mresult = $db->getall( "select * from ?n order by idparent,`sort`,title", CONF_PREFIX.'_menu' );
$children = array();
$ids = array();
foreach ( $mresult as $mkey => &$value )
{
    if ( $value['idparent'] )
        $children[ $value['idparent'] ][] = $mkey;
}
foreach ( $mresult as &$value )
{
    if ( isset( $children[ $value['id']] ))
        $value['children'] = $children[ $value['id']];
}

ANSWER::result( $mresult );
