<?php

if ( !isset($_GET['id']))
    exit;
$id = (int)$_GET['id'];
if ( !$id )
    exit;

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success() && ANSWER::is_access( A_FILEGET, $id ))
    files_download( $id, !empty( $_GET['view'] ), !empty( $_GET['thumb'] ));

