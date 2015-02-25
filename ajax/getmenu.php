<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    require_once 'menu_common.php';
}
ANSWER::answer();
