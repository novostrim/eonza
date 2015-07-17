<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/
    
require_once 'ajax_common.php';

define( 'STORAGEURL', '/'.trim( CONF_STORAGE, '/' ).'/' );
define( 'STORAGE', APP_DOCROOT.STORAGEURL );

function fmtsize( $value ) 
{
    if ( $value < 1024 ) 
        return $value . " B";
    if( $value < ( 1024 << 10 )) 
        return round( $value >> 10, 1 ) . " KB";
    return round( $value / ( 1024 << 10 ), 1 ) . " MB";
}

function dirsize( $dir )
{
    $h = opendir( $dir );
    $size = 0;

    while( ( $file = readdir( $h ) ) !== false )
    {
        if ( $file != "." && $file != ".." )
        {
            $path = $dir."/".$file;
            if ( is_dir( $path ))
                $size += dirsize( $path );
            elseif ( is_file( $path ))
                $size += sprintf("%u", filesize( $path ));
        }
    }
    closedir( $h );
    return $size;
}


if ( ANSWER::is_success())
{
	$eonzaver = file_get_contents('http://www.eonza.org/eonza-version.html?ver='.APP_VERSION.'&lang='.get('lang'));

    $dbsize = 0;
    $result = $db->getall( "SHOW TABLE STATUS" );
    foreach ( $result as $ival )
        $dbsize += $ival[ "Data_length" ] + $ival[ "Index_length" ];

    ANSWER::result( array( array( 'name' => 'eonzaver', 'value' => APP_VERSION, 'date' => APP_DATE ), 
    		array( 'name' => 'yourip', 'value' => $_SERVER['REMOTE_ADDR'] ),
            array( 'name' => 'osver', 'value' => php_uname('s').' '.php_uname('r').' '.
                                        php_uname('v').' '.php_uname('m')),
    		array( 'name' => 'phpver', 'value' => phpversion()),
    		array( 'name' => 'dbver', 'value' => $db->getone("select version()")),
            array( 'name' => 'dbsize', 'value' => fmtsize( $dbsize )),
            array( 'name' => 'storagesize', 'value' => fmtsize( dirsize( STORAGE ))),
            array( 'name' => 'storagepath', 'value' => STORAGEURL ),
            
            ));
//    print $eonzaver;
    ANSWER::set( 'latestver', json_decode( $eonzaver, true ));
}

ANSWER::answer();
