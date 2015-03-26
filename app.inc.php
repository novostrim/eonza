<?php 
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

define( 'ENZ_PREFIX', 'enz_' );
// Names of system tables
define( 'ENZ_ACCESS', ENZ_PREFIX.'access' );
define( 'ENZ_DB', ENZ_PREFIX.'db' );
define( 'ENZ_COLUMNS', ENZ_PREFIX.'columns' );
define( 'ENZ_MENU', ENZ_PREFIX.'menu' );
define( 'ENZ_SETS', ENZ_PREFIX.'sets' );
define( 'ENZ_TABLES', ENZ_PREFIX.'tables' );
define( 'ENZ_USERS', ENZ_PREFIX.'users' );

define( 'APP_VERSION', '1.2.1' );
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
