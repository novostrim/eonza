<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $set = ENZ_SETS;
    if ( $idi )
    {
        $curset = $db->getrow("select * from ?n where id=?s", $set, $idi );
        if ( !$curset )
            api_error( 'err_id', "id=$idi" );
        else
        {
            $islink = 0;

            $links = $db->getall("select col.extend, col.title as icol, t.title as itable from ?n as col
                    left join ?n as t on t.id = col.idtable
                    where ( col.idtype=?s || col.idtype=?s )", ENZ_COLUMNS, 
                    ENZ_TABLES, FT_ENUMSET, FT_SETSET );
            foreach ( $links as $il )
            {
                $extend = json_decode( $il['extend'], true );
                if ( isset( $extend['set'] ) &&  (int)$extend['set'] == $idi )
                {
                    $islink = "$il[itable] - $il[icol]";
                    break;
                }
            }
            if ( $islink )
                api_error( 'err_dellink', $islink );
            else 
            {
                ANSWER::success( $db->query("delete from ?n where id=?s || idset=?s", $set, $idi, $idi ));
//                     if ( ANSWER::is_success())
//                        api_log( $idi, 0, 'delete' );
            }
        }
    }
}
ANSWER::answer();
