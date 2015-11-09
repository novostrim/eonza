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
define( 'ENZ_FILES', ENZ_PREFIX.'files' );
define( 'ENZ_GROUP', ENZ_PREFIX.'group' );
define( 'ENZ_MENU', ENZ_PREFIX.'menu' );
define( 'ENZ_MIMES', ENZ_PREFIX.'mimes' );
define( 'ENZ_ONEMANY', ENZ_PREFIX.'onemany' );
define( 'ENZ_SETS', ENZ_PREFIX.'sets' );
define( 'ENZ_SHARE', ENZ_PREFIX.'share' );
define( 'ENZ_SLICES', ENZ_PREFIX.'slices' );
define( 'ENZ_TABLES', ENZ_PREFIX.'tables' );
define( 'ENZ_TAGS', ENZ_PREFIX.'tags' );
define( 'ENZ_TAGLIST', ENZ_PREFIX.'taglist' );
define( 'ENZ_USERS', ENZ_PREFIX.'users' );

define( 'APP_VERSION', '3.2.3' );
define( 'APP_DATE', '2015/11/09' );
define( 'APP_STORAGE', 'storage' ); // Default name of the storage folder
define( 'CONF_HOST', $_SERVER['HTTP_HOST'] );
define( 'APP_DOCROOT', rtrim( $_SERVER['DOCUMENT_ROOT'], '/' ));

if ( !defined( 'APP_ENTER' ))
{
    $dir = dirname( $_SERVER['SCRIPT_NAME'] );
    define( 'APP_ENTER', ( $dir == '/' || $dir =="\\" ? '' : $dir ).'/' );
}
$rootlen = strlen( APP_DOCROOT );
$file = dirname( __FILE__ );
if ( substr( $file, 0, $rootlen ) == APP_DOCROOT )
	$appdir = substr( $file, $rootlen + 1 );
else
	$appdir = basename( $file );

define( 'APP_DIR', !$appdir || basename( APP_DOCROOT ) == $appdir ? '/' : "/$appdir/");
define( 'APP_EONZA', APP_DOCROOT.APP_DIR );

$conf = array(
    'appname' => 'Eonza',
    'website' => 'www.eonza.org',
);

$langlist = array(
    array( 'code' => 'en', 'native'=> 'English' ),
    array( 'code' => 'ru', 'native'=> 'Русский' ),
);
