<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", ENZ_MENU, $idi );
        if ( !$curtable )
            api_error( 'err_id', "id=$idi" );
        else
        {
            if ( $curtable['isfolder'])
            {
                $count = $db->getone("select count(*) from ?n where idparent=?s", ENZ_MENU, $idi );
                if ( $count )
                    api_error( 'err_notempty' );
            }
            if ( ANSWER::is_success())
            {
                $db->query("delete from ?n where id=?s", ENZ_MENU, $idi );
                require_once "menu_common.php";
            }
        }
    }
}
ANSWER::answer();
