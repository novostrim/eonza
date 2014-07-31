<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
    $result['result'] = array( 'form' => array(), 'items' => array());

    $idi = get( 'id' );
    if ( $idi )
    {
        $result['result']['form'] = $db->getrow("select * from ?n where id=?s",
                                       CONF_PREFIX.'_tables', $idi );
        if ( !$result['result']['form'] )
            api_error( 'err_id', "id=$idi" );
        else
        {
            $result['result']['items'] = $db->getall("select * from ?n where idtable=?s && idtype!=?s order by ?n",
                                       CONF_PREFIX.'_columns', $idi, FT_PARENT, 'sort' );
            foreach ( $result['result']['items'] as &$iext )
                $iext['extend'] = json_decode( $iext['extend'] );
        }
    }
}
print json_encode( $result );
?>