<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/utf.php';

$id = get( 'id' );
if ( $id && ANSWER::is_success() )
{
    $setname = ENZ_SETS;
    ANSWER::resultset( 'title', $db->getone("select title from ?n where id=?s", $setname, $id ));
    if ( ANSWER::resultget('title'))
        ANSWER::result( $db->getall("select * from ?n where idset=?s order by title", $setname, $id ));
    else
        ANSWER::success( false );
}

ANSWER::answer();
