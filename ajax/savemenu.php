<?php

require_once 'ajax_common.php';

$pars = post( 'params' );
$idi = $pars['id'];

if ( ANSWER::is_success() && ANSWER::is_access())
{
    if ( !$idi )
    {
        ANSWER::success( $db->insert( ENZ_MENU, pars_list( 'title,idparent,isfolder'.
                         ( $pars['isfolder'] ? '' : ',hint,url'), $pars ), 
              array( 'sort=1000' ), true )); 
        if ( ANSWER::is_success())
            require_once 'menu_common.php';
    }
    else
    {
//        print "=$idi=$pars[title]";
        ANSWER::success( $db->update( ENZ_MENU, pars_list( 'title'.
            ( $pars['isfolder'] ? '' : ',hint,url'), $pars ), '', $idi ));
    }
}
ANSWER::answer();
