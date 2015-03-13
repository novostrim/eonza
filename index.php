<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/
require_once "app.inc.php";
require_once "lib/lib.php";
 
function cmp_version( $curver ) 
{
    $newver = (int)APP_VERSION;
    $prev = (int)$curver;
    if ( $newver > $prev )
    {
        require_once "update/index.php";
        eonza_update( $prev, $newver );
    }
    $_POST = array( 'params' => array( 'version' => APP_VERSION ));
    require_once "ajax/answer.php";
    require_once "ajax/savedb.php";
}

$lang = '';

if ( file_exists( APP_DOCROOT.APP_ENTER."conf.inc.php"))
{
    require_once APP_DOCROOT.APP_ENTER."conf.inc.php";
    require_once "lib/extmysql.class.php";

    $options = array(
        'host' => defined( 'CONF_DBHOST' ) ? CONF_DBHOST : 'localhost',
        'port' => defined( 'CONF_PORT' ) ? CONF_PORT : NULL,
        'db'   => CONF_DB,
        'user' => defined( 'CONF_USER' ) ? CONF_USER : '',
        'pass' => defined( 'CONF_PASS' ) ? CONF_PASS : '',
    );
    $db = DB::getInstance( $options );

    $dbpar = $db->getrow( "select * from ?n where id=?s && pass=?s", APP_DB, 
                          CONF_DBID, pass_md5( CONF_PSW, true ));
    if ( !$dbpar )
    {
        print "System Error";
        exit();
    }
    // Change from below in 2015 
//    $conf = array_merge( $conf, json_decode( $dbpar['settings'], true ));
    /* Change to above in 2015 */
    $conf['dblang'] = 'en';
    $settings = json_decode( $dbpar['settings'], true );
    foreach ( $settings as $skey => $sval )
    {
        if ( !is_array( $sval )) 
            $conf[ $skey ] = $sval;
    }
    $curver = empty( $conf['version']) ? '0.0.0' : $conf['version'];
    if ( APP_VERSION != $curver )
        cmp_version( $curver );
    /**/
//    $conf['title'] = $dbpar['name'];
//    $conf['isalias'] = $dbpar['isalias'];

    $lang = $conf['dblang'];
    if ( !GS::login())
        $conf['module'] = 'login';
    else
    {
        $lang = GS::user('lang');
//        $conf['apitoken'] = $dbpar['apitoken'];
    }
    $conf['user'] = GS::user();
//    REQUEST_URI
}
else
{
    $langs = array( 'en', 'ru');
    $ulang = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
    foreach ( $ulang as $iul )
    {
        $iu = substr( $iul, 0, 2 );
        if ( in_array( $iu, $langs ))
        {
            $lang = $iu;
            break;
        }
    }
    $conf['module'] = 'install';
    $conf['title'] = '';
}
$conf['lang'] = $lang ? $lang : 'en';
$conf['appdir'] = APP_DIR;
$conf['appenter'] = APP_ENTER;

$template = file_get_contents( APP_DOCROOT.APP_DIR.'tpl/index.tpl' );

$vars = array(
    'lang' => $conf['lang'],
    'appname' => $conf['appname'],
    'cfg' => json_encode( $conf ),
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
