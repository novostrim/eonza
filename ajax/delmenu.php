<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $tables = CONF_PREFIX.'_menu';
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", $tables, $idi );
        if ( !$curtable )
            api_error( 'err_id', "id=$idi" );
        else
        {
            if ( $curtable['isfolder'])
            {
                $count = $db->getone("select count(*) from ?n where idparent=?s", $tables, $idi );
                if ( $count )
                    api_error( 'err_notempty' );
            }
            if ( ANSWER::is_success())
            {
                $db->query("delete from ?n where id=?s", $tables, $idi );
                require_once "menu_common.php";
            }
        }
    }
}
ANSWER::answer();
