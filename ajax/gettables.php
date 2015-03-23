<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $idparent = get( 'parent' );
    $query = "select * from ".CONF_PREFIX."_tables where";
    if ( $idparent == -1 )
        ANSWER::result( $db->getall( $query." isfolder=0 && idparent != ?s order by title", SYS_ID ));
    else
        ANSWER::result( $db->getall( $query." idparent=?s order by isfolder desc, title", 
                                         $idparent ));
    if ( $idparent )
    {
        while ( $idparent )
        {
            $owner = $db->getrow("select id,idparent,title from ?n where id=?s",
                   CONF_PREFIX."_tables", $idparent );
            if ( $owner )
            {
                $crumbs[] = $owner;
                $idparent = $owner['idparent'];
            }
            else
                break;
        }
        if ( isset( $crumbs ))
            ANSWER::resultset( 'crumbs', array_reverse( $crumbs ));
    }
}
ANSWER::answer();
