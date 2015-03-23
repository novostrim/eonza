<?php

require_once 'ajax_common.php';

$form = post( 'params' );
if ( ANSWER::is_success() && ANSWER::is_access( A_FILESET, $form['id'] ))
{
    require_once APP_EONZA.'lib/files.php';
    ANSWER::success( files_edit( $form ));
}
ANSWER::answer();
