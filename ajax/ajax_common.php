<?php
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

$result = array( 'success'=> true, 'err' => 1, 'result' => 0, 'temp' => '' );

require_once APP_EONZA.'lib/lib.php';
require_once APP_EONZA.'lib/extmysql.class.php';

function api_error( $err, $temp ='' )
{
    global $result;
       $result['success'] = false;      
       $result['err'] = $err;
       $result['temp'] = $temp;

       return false;
}

function api_dbname( $idtable )
{
    global $db;
    $alias = $db->getone("select alias from ?n where id=?s", CONF_PREFIX.'_tables', $idtable );
    return ( $alias ? $alias : CONF_PREFIX."_$idtable" );
}

function api_colname( $idcol )
{
    global $db;
    $alias = $db->getone("select alias from ?n where id=?s", CONF_PREFIX.'_columns', $idcol );
    return ( $alias ? $alias : $idcol );
}

function api_log( $idtable, $idrow, $action )
{
    global $db, $USER, $OPTIONS;

    if ( $OPTIONS['keeplog'] )
        $db->insert( CONF_PREFIX.'_log', array( 'idtable'=>$idtable, 'idrow' => $idrow, 
                'iduser'=> $USER['id'], 'action'=> $action ), array( "uptime=NOW()" ));
}

function getitem( $table, $id )
{
    global $result, $db, $columns, $dbname;

    $fields = array();
    $leftjoin = '';
    foreach ( $columns as &$icol )
    {
        if ( $icol['idtype'] == FT_LINKTABLE || $icol['idtype'] == FT_PARENT )
        {
            $extend = json_decode( $icol['extend'], true );
            $dblink = api_dbname( $extend['table'] );
            $link = $icol['id'];
            $collink = api_colname( (int)$extend['column'] );
               $leftjoin .= $db->parse( " left join ?n as t$link on t$link.id=t.?p", $dblink, $icol['idalias']);
            $fields[] = "ifnull( t$link.$collink, '' ) as `__$icol[idalias]`";//$collink$link";
//                $icol['alias'] = $collink.$link;
        }
    }
    if ( $id )
    {
        $result['result'] = $db->getrow("select t.* ?p from ?n as t ?p where t.id=?s", 
            $fields ? ','.implode( ',', $fields ) : '', $dbname, $leftjoin, $id );
    }
    else
    {
        $result['result'] = array( 'id'=> 0 );
        foreach ( $columns as $xcol )
        {
            if ( isset( $FTYPES[ $xcol['idtype']]['number']))
                $result['result'][ $xcol['idalias'] ] = 0;
            else
                $result['result'][ $xcol['idalias'] ] = '';
        }
    }
    $result['link'] = array();
    foreach ( $columns as &$icol )
    {
        if ( $icol['idtype'] == FT_LINKTABLE || $icol['idtype'] == FT_PARENT )
        {
            $alias = '__'.$icol['idalias'];
            if ( isset( $result['result'][ $alias ] ))
            {
                $result['link'][ $icol['idalias'] ] = $result['result'][ $alias ];
                unset( $result['result'][ $alias ] );
            }
            else
                $result['link'][ $icol['idalias'] ] = '';
        }
        if ( $icol['idtype'] == FT_IMAGE || $icol['idtype'] == FT_FILE )
        {
            $files = array();
            if ( $id )
            {
                require_once APP_EONZA.'lib/files.php';

                $files = files_result( $table['id'], $icol, $id );
            }
            $result['result'][ $icol['idalias']] = $files;
        }
    }
    $result['result'][ 'table' ] = $table['id'];
}            

function get_linklist( $icol, $offset, $search = '', $parent = 0, $filter=0 )
{
    global $db;
    //                $icol['link_table'] = api_dbname( $icol['extend']['table'] );
//                $icol['link_column'] = api_colname( (int)$icol['extend']['column'] );
    $dblink = api_dbname( $icol['extend']['table'] );
    $collink = api_colname( (int)$icol['extend']['column'] );
    $istree = $db->getone("select istree from ?n where id=?s", CONF_PREFIX.'_tables', $icol['extend']['table'] );
    $onpage = 15;
//                $link = $icol['id'];
//                $alias = $collink.$link;
    $wsearch = $istree ? 'where _parent='.(int)$parent : 'where 1';
    if ( $filter )
    {
        $fi = explode( ':', $filter );
        if ( count( $fi ) == 2 )
            $wsearch .= $db->parse( ' && ?n=?s', api_colname( (int)$fi[1] ), (int)$fi[0] );
    }
    if ( $search )
        $wsearch .= $db->parse( ' && lower( ?n ) like lower( ?s )', $collink, $db->parse( '?p%', $search ));
    
    $count = $db->getone("select count(id) from ?n ?p", $dblink, $wsearch );
    if ( $offset >= $count-1 )
        $offset = 0;
    if ( $istree )
        $tree = $db->parse(", (select count(id) from ?n where _parent=t.id) as count", $dblink );
    else
        $tree = ', 0 as count';
    $clist = $db->getall("select id, ?n as title ?p from ?n as t ?p order by title limit $offset,$onpage", 
                          $collink, $tree, $dblink, $wsearch );
    $ret = array( 'offset' => $offset, 'next' => ( $offset + $onpage < $count ? 1 : 0 ),
                'search' => $search, 'parent' => $parent, 'istree' => $istree,
                  'list' => array(), 'crumbs' => array());
       foreach ( $clist as $cil )
           $ret['list'][] = array( 'id'=> $cil['id'], 'title' => $cil['title'], 'count' => $cil['count']  );
       if ( $istree && $parent )
       {
        $crumbs = array();
        while ( $parent )
        {
            $par = $db->getrow("select id, _parent, ?n as title from ?n where id=?s", 
                     $collink, $dblink, $parent );
            $parent = $par['_parent'];
            $crumbs[] = array( $par['title'], $par['id'] );
        }
         $ret['crumbs'] = array_reverse( $crumbs );
       }
       return $ret;
}

$db = new ExtMySQL( array( 'host' => defined( 'CONF_DBHOST' ) ? CONF_DBHOST : 'localhost',
                'db' => CONF_DB, 'user' => defined( 'CONF_USER' ) ? CONF_USER : '',
                 'pass' => defined( 'CONF_PASS' ) ? CONF_PASS : '' ));
login();

function stripslashes_gpc(&$value)
{
   $value = stripslashes($value);
}

if ( CONF_QUOTES ) {
     array_walk_recursive($_GET, 'stripslashes_gpc');
     array_walk_recursive($_POST, 'stripslashes_gpc');
     array_walk_recursive($_COOKIE, 'stripslashes_gpc');
     array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

if ( !$USER )
{
    api_error( 'err_login' );
//    $result['err'] = 'err_login';
//    $result['code'] = false;
}
else
{
    $dbsets = $db->getone( "select settings from ?n where id=?s && pass=?s", APP_DB, 
                              CONF_DBID, pass_md5( CONF_PSW, true ));
    if ( $dbsets )
    {
        foreach ( json_decode( $dbsets, true ) as $okey => $oval )
            $OPTIONS[ $okey ] = $oval['value'];
    }
}

?>