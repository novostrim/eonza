<?php
/*
    Eonza 
    (c) 2014-15 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

if ( defined('NOANSWER'))
{
    ANSWER::isajax( true );
    $db = DB::getInstance();
    return;
}

require_once APP_EONZA.'lib/lib.php';
require_once APP_EONZA.'lib/extmysql.class.php';
require_once 'answer.php';

function api_error( $err, $temp ='' )
{
    ANSWER::set( 'success', false );      
    ANSWER::set( 'err', $err );
    ANSWER::set( 'temp', $temp );
    return false;
}

function api_dbname( $idtable )
{
    $alias = DB::getone("select alias from ?n where id=?s", ENZ_TABLES, $idtable );
    return ( $alias ? $alias : CONF_PREFIX."_$idtable" );
}

function api_colname( $idcol )
{
    $alias = DB::getone("select alias from ?n where id=?s", ENZ_COLUMNS, $idcol );
    return ( $alias ? $alias : $idcol );
}

function api_log( $idtable, $idrow, $action )
{
    if ( GS::get( 'options', 'keeplog' ))
        DB::insert( CONF_PREFIX.'_log', array( 'idtable'=>$idtable, 'idrow' => $idrow, 
                'iduser'=> GS::userid(), 'action'=> $action ), array( "uptime=NOW()" ));
}

function getitem( $idtable, $id, $dbname, $columns )
{
    $db = DB::getInstance();
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
            if ( empty( $extend['aslink'] ))
                $fields[] = "ifnull( t$link.$collink, '' ) as `__$icol[idalias]`";
            else
                $fields[] = "if( t$link.$collink is NULL, '', concat('<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\" >', t$link.$collink, '</a>')) as `__$icol[alias]`";
            //                $icol['alias'] = $collink.$link;
        }
    }
    if ( $id )
    {
        ANSWER::result( $db->getrow("select t.* ?p from ?n as t ?p where t.id=?s", 
            $fields ? ','.implode( ',', $fields ) : '', $dbname, $leftjoin, $id ));
    }
    else
    {
        ANSWER::result( array( 'id'=> 0 ));
        foreach ( $columns as $xcol )
        {
/*            if ( isset( $FXXTYPES[ $xcol['idtype']]['number']))
                result['result'][ $xcol['idalias'] ] = 0;
            else*/
                ANSWER::resultset( $xcol['idalias'], '' );
        }
    }
    $link = array();
    foreach ( $columns as &$icol )
    {
        if ( $icol['idtype'] == FT_LINKTABLE || $icol['idtype'] == FT_PARENT )
        {
            $alias = '__'.$icol['idalias'];
            if ( ANSWER::isresult( $alias ))
            {
                $link[ $icol['idalias'] ] = ANSWER::resultget( $alias );
                ANSWER::unsetresult( $alias );
            }
            else
                $link[ $icol['idalias'] ] = '';
        }
        elseif ( $icol['idtype'] == FT_IMAGE || $icol['idtype'] == FT_FILE )
        {
            $files = array();
            if ( $id )
            {
                require_once APP_EONZA.'lib/files.php';

                $files = files_result( $idtable, $icol, $id );
            }
            ANSWER::resultset( $icol['idalias'], $files );
        }
        elseif ( $icol['idtype'] == FT_SPECIAL )
        {
            $extend = json_decode( $icol['extend'], true );
            if ( $extend['type'] == FTM_HASH )
                ANSWER::resultset( $icol['idalias'], bin2hex( ANSWER::resultget( $icol['idalias'] )));
        }
    }
    ANSWER::set( 'link', $link );
    ANSWER::resultset( 'table', $idtable );
}            

function get_linklist( $icol, $offset, $search = '', $parent = 0, $filter=0 )
{
    $db = DB::getInstance();
    $dblink = api_dbname( $icol['extend']['table'] );
    $collink = api_colname( (int)$icol['extend']['column'] );
    $istree = $db->getone("select istree from ?n where id=?s", ENZ_TABLES, $icol['extend']['table'] );
    $onpage = 15;

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

$db = DB::getInstance( array( 'host' => defined( 'CONF_DBHOST' ) ? CONF_DBHOST : 'localhost',
                'db' => CONF_DB, 'user' => defined( 'CONF_USER' ) ? CONF_USER : '',
                 'pass' => defined( 'CONF_PASS' ) ? CONF_PASS : '' ));
GS::login();

if ( CONF_QUOTES ) {
     array_walk_recursive($_GET, 'stripslashes_gpc');
     array_walk_recursive($_POST, 'stripslashes_gpc');
     array_walk_recursive($_COOKIE, 'stripslashes_gpc');
     array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

if ( !GS::userid() )
    api_error( 'err_login' );
else
    GS::set( 'options', GS::dbsettings());

if ( isset( $_POST['params']['nocache']))
    unset( $_POST['params']['nocache'] );


