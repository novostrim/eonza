<?php

require_once 'ajax_common.php';

$form = post( 'params' );
if ( $result['success'] )
{
    $setname = CONF_PREFIX.'_sets';
    $result['success'] = $db->getone( "select id from ?n where id=?s", $setname, $form['idset'] );
    if ( $result['success'] )
    {
        $curlist = $db->getall("select id, iditem, title from ?n where idset=?s order by iditem", $setname, $form['idset'] );
        if ( count( $curlist ) >= 32 )
            api_error('err_limitset');
        else
        {
            if ( $form['id'] )
            {
                $result['success'] = $db->update( $setname, array('title'=> $form['title'] ), 
                            '', $form['id'] ); 
//                if ( $result['success'] )
//                    api_log( $form['table'], $form['id'], 'edit' );
            }
            else
            {
                for ( $i=1; $i<=32; $i++ )
                    if ( !isset( $curlist[ $i-1 ] ) || $i != $curlist[ $i-1 ]['iditem'] )
                        break;
                $result['success'] = $db->insert( $setname, array('title'=> $form['title'], 
                         'idset' => $form['idset'], 'iditem' => $i ), 
                      array( "_owner=$USER[id]" ), true ); 
//                if ( $result['success'] )
//                    api_log( $form['table'], $result['success'], 'create' );
            }
        }
    }
}
print json_encode( $result );
?>