<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

//print_r( $_POST );
//print_r( $_FILES );
if ( ANSWER::is_success( true ) && ANSWER::is_access())
{
    if ( empty( $_FILES ) || !isset( $_FILES[ 'file' ] ))
        api_error( 'Wrong parameters', 1 );
    elseif ( $_FILES['file'][ 'error' ] || !is_uploaded_file( $_FILES['file']['tmp_name'] ))
    {
//        The uploaded file exceeds the upload_max_filesize directive in php.ini
        api_error( '$_FILES error: #temp#', $_FILES['file'][ 'error' ] );
    }
    else
    {
        $pars = postall( true );
        $path = empty( $pars['path'] ) ? '' : $pars['path'].'/';
        if ( !file_exists( STORAGE.$path ))
            mkdir( STORAGE.$path );
        if ( !move_uploaded_file( $_FILES['file']['tmp_name'], 
                                  STORAGE.$path.$_FILES[ 'file' ]['name'] ))
            api_error( 'err_writefile', STORAGE );
        else
            if ( $path == 'backup/' )
            {
                require_once 'backup_common.php';
                backup_list();
            }
    }
}
ANSWER::answer();

