<?php

require_once 'ajax_common.php';

$pars = post( 'params' );
$idi = $pars['id'];

if ( ANSWER::is_success())
{
    if ( !$idi )
    {
        ANSWER::success( $db->insert( CONF_PREFIX.'_tables', pars_list( 'title,idparent', $pars ), 
              GS::owner('isfolder=1'), true )); 
    }
    else
    {
        ANSWER::result( array());
        if ( $db->update( CONF_PREFIX.'_tables', pars_list( 'title', $pars ), '', $idi ))
            ANSWER::resultset( 'title', $pars['title'] );
    }
}
ANSWER::answer();
