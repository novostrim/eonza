<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = (int)$pars['id'];
    $idset = (int)$pars['idset'];
    if ( $idi && $idset );
    {
        $setname = CONF_PREFIX.'_sets';
        ANSWER::success( $db->query("delete from ?n where id=?s && idset=?s", $setname, $idi, $idset ));
//            if ( ANSWER::is_success())
//                api_log( $idtable, $idi, 'delete' );
    }
}
ANSWER::answer();
