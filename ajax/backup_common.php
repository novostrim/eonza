<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

require_once APP_EONZA.'lib/files.php';

define( 'BACKUP', STORAGE.'backup' );

if ( !file_exists( STORAGE.'backup' ))
    mkdir( STORAGE.'backup' );

function backupcmp($a, $b)
{
    if ( $a['title'] == $b['title']) {
        return 0;
    }
    return ($a['title'] < $b['title']) ? 1 : -1;
}

function backup_list()
{
    $ret = $list = array();
    $ind = 0;
    if ( is_dir( BACKUP ) && $handledir = opendir( BACKUP ))
    {
        while (false !== ( $file = readdir( $handledir ))) 
        { 
            $ext = substr( $file, -2 );
            if( $ext == 'gz' || $ext == 'ql' )
                $ret[] = array( 'id' => ++$ind, 'url'=> STORAGEURL."backup/$file", 'title' => $file,
                                'size' => (int)( filesize( BACKUP."/$file" ) / 1024 ));
        }

        closedir($handledir);
    }
    usort( $ret, 'backupcmp' );

    ANSWER::result( $ret );
}