<?php

require_once 'ajax_common.php';

$pars = post( 'params' );
$idi = $pars['id'];

if ( ANSWER::is_success() && ANSWER::is_access())
{
    if ( !$idi )
    {
        ANSWER::success( $db->insert( CONF_PREFIX.'_sets', pars_list( 'title', $pars ), 
              GS::owner(), true )); 
    }
    else
    {
        ANSWER::result( array());
        if ( $db->update( CONF_PREFIX.'_sets', 
                    pars_list( 'title', $pars ), '', $idi ))
        {
            ANSWER::resultset( 'title', $pars['title'] );
        }
    }
}
ANSWER::answer();
