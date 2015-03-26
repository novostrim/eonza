<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    $icol = $db->getrow("select * from ?n where id=?s && ( idtype=?s ||  idtype=?s )", 
                 ENZ_COLUMNS, (int)get( 'id' ), FT_LINKTABLE, FT_PARENT );
    if ( $icol )
    {
        $icol['extend'] = json_decode( $icol['extend'], true );
        ANSWER::result( get_linklist( $icol, (int)get( 'offset' ), get('search'), 
                    (int)get( 'parent' ), get( 'filter' )));
    }
    else
        api_error('Link table');
}
ANSWER::answer();
