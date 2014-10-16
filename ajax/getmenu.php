<?php

require_once 'ajax_common.php';

if ( $result['success'] )
{
    require_once 'menu_common.php';
}
print json_encode( $result, JSON_NUMERIC_CHECK );
