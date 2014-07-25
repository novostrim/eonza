<?php
/*
	Watcher4site 
	(c) 2014 Novostrim, OOO. http://www.novostrim.com
	License: MIT
*/
require_once "app.inc.php";
require_once "lib/lib.php";

$lang = '';

if ( file_exists( APP_DOCROOT.APP_ENTER."conf.inc.php"))
{
	require_once APP_DOCROOT.APP_ENTER."conf.inc.php";
	require_once "lib/extmysql.class.php";

	$db = new ExtMySQL( array( 'host' => defined( 'CONF_DBHOST' ) ? CONF_DBHOST : 'localhost', 
		         'db' => CONF_DB, 'user' => defined( 'CONF_USER' ) ? CONF_USER : '',
		         'pass' => defined( 'CONF_PASS' ) ? CONF_PASS : '' ));

	$dbpar = $db->getrow( "select * from ?n where id=?s && pass=?s", APP_DB, 
		                  CONF_DBID, pass_md5( CONF_PSW, true ));
	if ( !$dbpar )
	{
		print "System Error";
		exit();
	}
	$settings = json_decode( $dbpar['settings'], true );
	foreach ( $settings as $skey => $sval )
	{
		if ( !isset( $sval['protect']))
			$conf[ $skey ] = $sval['value'];
	}
//	print pass_md5( '111', true );
//	$conf['title'] = $dbpar['name'];
//	$conf['isalias'] = $dbpar['isalias'];

	$lang = $conf['dblang'];
	login();
	if ( !$USER )
	{
		$conf['module'] = 'login';
	}
	else
	{
		$lang = $USER['lang'];
//		$conf['apitoken'] = $dbpar['apitoken'];
	}
	$conf['user'] = $USER;
//	REQUEST_URI
}
else
{
	$langs = array( 'en', 'ru');
	$ulang = explode( ';', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
	foreach ( $ulang as $iul )
	{
	   	$ul = explode( ',', $iul );	
	   	foreach ( $ul as $iu )
		   	if ( in_array( $iu, $langs ))
		   	{
		   		$lang = $iu;
		   		break;
		   	}
		if ( $lang )
			break;
	}
	$conf['module'] = 'install';
	$conf['title'] = '';
}
$conf['lang'] = $lang ? $lang : 'en';
$conf['appdir'] = APP_DIR;
$conf['appenter'] = APP_ENTER;

$template = file_get_contents( APP_DOCROOT.APP_DIR.'tpl/index.tpl' );

/*foreach ( $FTYPES as $fkey => $fval )
{
	$ftypes[$fkey] = array( 'id' => $fkey, 'name' => $fval['name'] );
}*/

$vars = array(
	'lang' => $conf['lang'],
	'appname' => $conf['appname'],
	'cfg' => json_encode( $conf ),
//	'types' => json_encode( $ftypes ),
	'langlist' => json_encode( $langlist ),
	'appdir' => APP_DIR,
);
if ( LOCALHOST )
  $vars['style'] =  '<link rel="stylesheet/less" type="text/css" href="'.APP_DIR.'css/gentee.less" />
    <script src="'.APP_DIR.'js/less.min.js" type="text/javascript"></script>';
else
  $vars['style'] =  '<link rel="stylesheet" type="text/css" href="'.APP_DIR.'css/gentee.css" />';

foreach ( $vars as $kvar => $ivar )
{
	$afrom[] = '{$'.$kvar.'}';
	$ato[] = $ivar;
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0', FALSE );
header('Pragma: no-cache'); 

print str_replace( $afrom, $ato, $template );

?>