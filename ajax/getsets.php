<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
    ANSWER::result( $db->getall( "select * from ?n where idset = 0 order by title", 
                                  ENZ_SETS ));

ANSWER::answer();
