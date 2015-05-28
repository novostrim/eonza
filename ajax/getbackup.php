<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    require_once 'backup_common.php';
    backup_list();
}
ANSWER::answer();