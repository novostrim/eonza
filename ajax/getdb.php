<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

if ( ANSWER::is_success())
    ANSWER::result( GS::dbsettings());

ANSWER::answer();
