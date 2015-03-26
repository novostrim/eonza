<?php

$mresult = $db->getall( "select * from ?n order by idparent,`sort`,title", ENZ_MENU );
$children = array();
$ret = array();
for ( $i = 0; $i < count( $mresult ); $i++ )// as $mkey => &$value )
{
    $value = $mresult[$i];
    if ( !GS::isroot())
    {
        $matches = array();
        if ( preg_match( '/table?(.*)id=([0-9]+)/i', $value['url'], $matches ))
            if ( !GS::a_read( array_pop( $matches )))
                continue;
    }
    $ret[] = $value;
    if ( $value['idparent'] )
        $children[ $value['idparent'] ][] = count( $ret ) - 1;
}
foreach ( $ret as &$value )
    if ( isset( $children[ $value['id']] ))
        $value['children'] = $children[ $value['id']];

ANSWER::result( $ret );
