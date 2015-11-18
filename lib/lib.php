<?php
/*
   Eonza
   (c) 2014 Novostrim, OOO. http://www.novostrim.com
   License: MIT
*/

define( 'SITEURL', strtolower( $_SERVER['HTTP_HOST'] ));
//define( 'LOCALHOST', empty( $_SERVER['HTTP_SERVER_ADDR'] ));// == '127.0.0.1' );
define( 'LOCALHOST', $_SERVER['REMOTE_ADDR' ] == '127.0.0.1' && !isset( $_SERVER['HTTP_X_REAL_IP'] ));

require_once "fields.php";

error_reporting( LOCALHOST ? E_ALL | E_STRICT : E_ALL & ~E_NOTICE );

class GS {
    protected static $instance;
    protected static $user;
    protected static $glob;
    protected static $alias;

    public static function getInstance() { 
        if ( self::$instance === null) 
        { 
            self::$instance = new GS;
        } 
        return self::$instance;
    }
    public  function __construct() {
        $user = array();
        $glob = array();
    }
    private function __clone() {
    }
    private function __wakeup() {
    }
    private static function getaccess( $idtable )
    {
        $ret = array( A_READ => 0, A_CREATE => 0, A_EDIT => 0, A_DEL => 0 );
        $access = DB::getall("select * from ?n where idgroup=?s && (idtable=?s || idtable=0 ) && active", 
                            ENZ_ACCESS, self::$user['idgroup'], $idtable );
        self::$alias = DB::getone("select alias from ?n where id=?s",
                                 ENZ_TABLES, $idtable );
        if ( !self::$alias )
            self::$alias = ENZ_PREFIX.$idtable;
        foreach ( $access as $iacc ) 
        {
            if ( !$iacc['idtable'] )
                if ( !preg_match( '/'.$iacc['mask'].'/i', self::$alias ))
                    continue;
            $ret[ A_READ ] |= $iacc['read'];
            $ret[ A_CREATE ] |= $iacc['create'];
            $ret[ A_EDIT ] |= $iacc['edit'];
            $ret[ A_DEL ] |= $iacc['del'];
        }
        return $ret;
    }
    public static function accessflag( $idtable )
    {
        if ( self::isroot())
            return 0xff;
        $ret = 0;
        $access = self::getaccess( $idtable );
        foreach ( $access as $ak => $av )
          if ( $av == 1 )
            $ret |= (1<<( $ak-1 ));
          elseif ( $av == 2 )
            $ret |= (1<<( 8 + $ak-1 ));
        return $ret;
    }
    public static function login() 
    {
        // id=1 &&  TIMESTAMPDIFF( HOUR, uptime, NOW()) as lastdif
        self::$user = DB::getrow( "select id, login, email, idgroup, name,
            ( DATE_ADD( uptime, INTERVAL 1 HOUR ) < NOW()) as lastdif, lang from ?n 
            where pass=X?s && id=?s", 
                        ENZ_USERS, pass_md5( cookie('pass')), cookie('iduser'));
        if ( self::$user )
        {
            self::$user['access'] = array();
            if ( self::$user['lastdif'] )
            {
                DB::query( "update ?n set uptime = NOW() where id=?i", 
                           ENZ_USERS, self::$user['id'] );
                cookie_set( 'pass', cookie('pass'), 120 );
                cookie_set( 'iduser', self::$user['id'], 120 );
            }
        }
        return self::$user ? true : false;
    }
    public static function isroot()
    {
        return isset( self::$user[ 'id' ] ) && self::$user[ 'id' ] == 1;
    }
    public static function access( $action, $idtable=0, $iditem = 0 )
    {
        if ( self::isroot())
            return true;
        if ( $action == A_ROOT )
            return false;
        if ( $action == A_FILEGET || $action == A_FILESET )
        {
            $fi = DB::getrow("select id, idtable, iditem from ?n where id=?s", ENZ_FILES, $idtable ); 
            if ( !$fi )
              return false;
            $idtable = $fi['idtable'];
            $iditem = $fi['iditem'];
            $action = $action == A_FILEGET ? A_READ : A_EDIT;
        }
        $acc = self::getaccess( $idtable );
        if ( !$iditem || ( $acc[ $action ] & 1 ))
            return $acc[ $action ];
        else
        {
            $owner = DB::getone("select _owner from ?n where id=?s", self::$alias, $iditem );
            if ( $owner == self::$user['id'] )
                return $acc[ $action ];
        }
        return false;
    }
    public static function a_read( $idtable=0, $iditem = 0 )
    {
        return self::access( A_READ, $idtable, $iditem );
    }    
    public static function a_create( $idtable )
    {
        return self::access( A_CREATE, $idtable, $iditem );
    }
    public static function a_edit( $idtable, $iditem = 0 )
    {
        return self::access( A_EDIT, $idtable, $iditem );
    }
    public static function a_delete( $idtable, $iditem = 0 )
    {
        return self::access( A_DELETE, $idtable, $iditem );
    }
    public static function user( $par = '' )
    {
        return $par ? self::$user[ $par ] : self::$user;
    }
    public static function userid()
    {
        return self::$user ? self::$user[ 'id' ] : 0;
    }
    public static function owner( $more = '' )
    {
        $ret = array( "_owner=".GS::userid() );
        if ( $more )
            $ret[] = $more;
        return $ret;
    }
    public static function set( $name, $value )
    {
        self::$glob[ $name ] = $value;
    }
    public static function get( $name, $par = '' )
    {
        if ( $par )
            return self::$glob[ $name ][ $par ];
        return self::$glob[ $name ];
    }
    public static function ifget( $name, $par = '' )
    {
        if ( $par )
            return isset( self::$glob[ $name ][ $par ] );
        return isset( self::$glob[ $name ] );
    }
    public static function field( $index )
    {
        return self::$glob['fields'][ $index ];
    }
    public static function fieldset( $index, $name, $val )
    {
        self::$glob['fields'][$index][$name] = $val;
    }
    public static function dbsettings()
    {
        $settings = DB::getone( "select settings from ?n where id=1 && pass=?s", 
                                ENZ_DB, pass_md5( CONF_PSW, true ));
        if ( !$settings )
        {
            print "System Error";
            exit();
        }
        return json_decode( $settings, true );
    }
}

GS::getInstance();

function alias( $pars, $prefix = '' )
{
   return empty( $pars['alias'] ) ? $prefix.$pars['id'] : $pars['alias'];
}

function cookie( $name, $default = '' )
{
   $icook = ENZ_PREFIX.$name;
   return isset( $_COOKIE[ $icook ] ) ? $_COOKIE[ $icook ] : $default;
}

function cookie_set( $name, $value='', $time = 1, $domain = APP_ENTER )
{
   $icook = ENZ_PREFIX.$name;
   setcookie( $icook, $value, $value ? time() + 3600*$time : 0, $domain );
   if ( $value )
      $_COOKIE[ $icook ] = $value;
   elseif ( isset( $_COOKIE[ $icook ] ))
      unset( $_COOKIE[ $icook ] );
}

function defval( &$what, $value )
{
   if ( empty( $what ))
      $what = $value;
   return $what;
}

function addfname( $path, $dir, $fname='' )
{
   $slash = strpos( $path, '\\' ) === false && strpos( $dir, '\\' ) === false ? '/' : '\\';
   $ret = $path.( $path[ strlen( $path ) - 1] != $slash && $dir[0] != $slash ? $slash : '' ).$dir;
   if ( $fname )   
      $ret .= ( $dir[ strlen( $dir ) - 1] != $slash && $fname[0] != $splash ? $slash : '' ).$fname;
   return $ret;
}

function pages( $query, $page, $link )
{
  /*
      $page = array(
        in
         onpage => items on page
         page => the current page
         center => the pages at the center
      )
      link => the name of callback function link( page );
  */

   $result = array();
   $count = ( is_numeric( $query ) ? $query : DB::getOne( $query ));
   $onpage = max( 1, isset( $page['onpage'] ) ? $page['onpage'] : 50 );
   $pages = max( 1, ceil( $count/$onpage ));
   $curpage = min( ( isset( $page['page'] ) ? max( $page['page'], 1 ) : 1 ), $pages );
   $result['curpage'] = $curpage;
   $result['found'] = $count;
   $result['offset'] = 0;
   $result['limit'] = '';
   $middle = isset( $page['center']) ? $page['center'] : 5;
   $ret = array();
   if ( $pages > 1 )
   {
        $ret[] = array( -1, $curpage == 1 ? '' : $link( $curpage - 1 ), $curpage-1 );
        if ( $curpage >= $middle )
        {
            $ret[] = array( 1, $curpage == 1 ? '' : $link( 1 ), 1 );
            if ( 1 + $middle != $pages )
                $ret[] = array( 0, '' );
        }
        if ( $curpage < $middle)
            $off = 1;
        elseif ( $pages - $curpage < $middle -1 )
            $off = max( 1, $pages - $middle + 1 );
        else 
            $off = $curpage - floor($middle/2);
//        print "$curpage=$off";
        for ( $i = $off; $i <= min( $off + $middle-1, $pages ); $i++ )
        {
            if ( $i == $curpage )
               $ret[] = array( $i, '' );
            else
               $ret[] = array( $i, $link( $i ), $i );
        }
        if ( $off + $middle <= $pages )
        { 
            if ( $off + $middle != $pages )
            $ret[] = array( 0, '' );
            $ret[] = array( $pages, $curpage == $pages ? '' : $link( $pages ), $pages );
        }
      $ret[] = array( -2, $curpage < $pages ? $link( $curpage+1 ) : '', $curpage + 1 );
      $result['offset'] = $onpage * ( $curpage-1 );
      $result['limit'] = " limit $result[offset],".$onpage;
   }
   $result['count'] = $pages;
   $result['plist'] = $ret;
   return $result;
}

function pass_generate( $i = 6, $safechar = false )
{
   $newpass = '';
   $from = $safechar ? 0x41 : 0x32;
   $rep = $safechar ? '23459fk678' : '#$%&+-23459fk678';
   $torep = $safechar ? 'lOoD`_[]^\\' : 'lOoIDJ`:;<>?[]^\\';
   while ( $i-- )
   {
        $char = chr(mt_rand( $from, 0x7A )); 
        $pos = strrpos( $torep, $char );
        $newpass .= $pos === false ? $char : $rep[$pos];
   }
   return $newpass;
}

function pass_md5( $pass, $full = false )
{
    if ( $full )
        $pass = md5( $pass );
    return md5( md5( CONF_SALT ).$pass );
}

function post_val( $val, $strict = false )
{
//   if ( !( $flag & DBF_NOHTML ))
   if ( is_array( $val ))
   {
        foreach ( $val as &$ival )
            $ival = post_val( $ival, $strict );
   }
   else
   {
        if ( CONF_QUOTES )
            $val = stripslashes($val);
        if ( $strict )
            $val = htmlspecialchars( $val, ENT_QUOTES /*ENT_NOQUOTES*/, 'UTF-8' );
   }
   return $val;
}

function post( $name, $default = '', $get = false, $strict = false )
{
    if ( $get )
       $val = ( !isset(  $_GET[ $name ] ) ? $default :  $_GET[ $name ] );
    else
       $val = ( !isset(  $_POST[ $name ] ) ? $default :  $_POST[ $name ] );

   return post_val( $val, $strict );
}

function postall( $strict = false, $get = false )
{
    $result = array();
    foreach ( $_POST as $ikey => $ipost )
        $result[ $ikey ] = post( $ikey, '', $get, $strict );
    return $result;
}

function poststrict( $name )
{
    return post( $name, '', false, true );
}

function get( $name, $default = '', $strict = false )
{
   return post( $name, $default, true, $strict );
}

function getstrict( $name )
{
    return post( $name, '', true, true );
}

function getall( $strict = false )
{
    $result = array();
    foreach ( $_GET as $ikey => $ipost )
        $result[ $ikey ] = get( $ikey, '', $strict );
    return $result;
}

function pars_list( $list, $src )
{
   $alist = explode( ',', $list );
   $ret = array();
   foreach ( $alist as $ival )
      $ret[ $ival ] = isset( $src[ $ival ] ) ? $src[ $ival ] : '';
   return $ret;
}

function stripslashes_gpc(&$value)
{
   $value = stripslashes($value);
}

function url_params( $ignore = '', $only = '' )
{
   $pars = '';
   $ignarr = array();
   $onarr = array();
   if ( $ignore )
      $ignarr = explode( ',', $ignore );
   if ( $only )
      $onarr = explode( ',', $only );
   foreach ( $_GET as $gkey => $gval )
   {
      if ( in_array( $gkey, $ignarr ) || $gval=='')
         continue;
      if ( $onarr && !in_array( $gkey, $onarr ))
         continue;
      if ( is_array( $gval ))
      {
         foreach ( $gval as $ival )
           $pars .= ( $pars ? '&' : '' )."$gkey"."[]=$ival";
      }     
      else
         $pars .= ( $pars ? '&' : '' )."$gkey=".urlencode( $gval );
   }
   return $pars ;
}

/*
function lib_posts( $names, $named = false )
{
    $aname = explode( ',', $names );
    $ret = array();
    foreach ( $aname as $aval )
    {
        $val =  isset( $_POST[ $aval ] ) ? lib_postval( $_POST[ $aval ] ) : '';
        if ( $named )
            $ret[ $aval ] = $val;
        else
            $ret[] = $val;
    }
    return $ret;
}

function lib_get( $name, $default = '' )
{
    return lib_post( $name, $default, true );
}
*/

