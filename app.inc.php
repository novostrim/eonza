<?php 
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

define( 'APP_DB', 'enz_db');
//define( 'APP_NAME', 'Eonza');
define( 'APP_VERSION', '1.2.0' );
define( 'APP_PREFIX', 'enz' );
define( 'APP_STORAGE', 'storage' ); // Default name of the storage folder
define( 'CONF_HOST', $_SERVER['HTTP_HOST'] );
define( 'APP_DOCROOT', $_SERVER['DOCUMENT_ROOT'] );

if ( !defined( 'APP_ENTER' ))
{
    $dir = dirname( $_SERVER['SCRIPT_NAME'] );
    define( 'APP_ENTER', ( $dir == '/' || $dir =="\\" ? '' : $dir ).'/' );
}
$appdir = basename( dirname( __FILE__ ));
define( 'APP_DIR', basename( APP_DOCROOT ) == $appdir ? '/' : "/$appdir/");
define( 'APP_EONZA', APP_DOCROOT.APP_DIR );

$conf = array(
    'appname' => 'Eonza',
    'website' => 'www.eonza.org',
);

$langlist = array(
    array( 'code' => 'en', 'native'=> 'English' ),
    array( 'code' => 'ru', 'native'=> 'Русский' ),
);
