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
    $col = DB::getrow("select id, alias from ?n where id=?s", ENZ_COLUMNS, $idcol );
    if ( !$col )
        return 0;
    return ( $col['alias'] ? $col['alias'] : $idcol );
}

function api_log( $idtable, $idrow, $action )
{
    if ( GS::get( 'options', 'keeplog' ))
        DB::insert( CONF_PREFIX.'_log', array( 'idtable'=>$idtable, 'idrow' => $idrow, 
                'iduser'=> GS::userid(), 'action'=> $action ), array( "uptime=NOW()" ));
}

function getcrumbs( $idparent )
{
    if ( $idparent > 0 && $idparent < SYS_ID )
    {    
        while ( $idparent )
        {
            $owner = DB::getrow("select id,idparent,title from ?n where id=?s",
                   CONF_PREFIX."_tables", $idparent );
            if ( $owner )
            {
                $crumbs[] = $owner;
                $idparent = $owner['idparent'];
            }
            else
                break;
        }
        if ( isset( $crumbs ))
            ANSWER::set( 'crumbs', array_reverse( $crumbs ));
    }
}

function getmultilink( $extend, $link, $alias, $falias, $text = false )
{
    $collist = explode( ',', $extend['column'] );
    $collink = array();
    foreach ( $collist as $cl )
    {
        $colname = api_colname( (int)$cl );
        if ( $colname )
            $collink[] = $colname;  
    }
    if ( $collink )
    {
        $linkout = array();
        if ( !empty( $extend['aslink'] ) && !$text) 
            $linkout[] = "'<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\">'";
        $linkout[] = "t$link.$collink[0]";
        if ( !empty( $extend['aslink'] ) && !$text) 
            $linkout[] = "'</a>'";
        for ( $ilink = 1; $ilink<count( $collink ); $ilink++ )
        {
            $linkout[] = $text ? "' * '" : "' &bull; '";
            $linkout[] = "t$link.".$collink[$ilink];
        }
        if ( !empty( $extend['showid'] )) 
        {
            $linkout[] = $text ? " '['" : "'<span class=\"idcode\">'";
            $linkout[] = "t.$alias";
            $linkout[] = $text ? " ']'" : "'</span>'";
        }
/*                    if ( empty( $extend['aslink'] ))
        {
            $linktitle = empty( $extend['showid'] ) ? "t$link.$collink[0]" : "concat( t$link.$collink[0], '<span class=\"idcode\">', t.$alias, '</span>' )";
        }
        else
        {   $href = "concat('<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\">', t$link.$collink[0], '</a>'";
            $linktitle = empty( $extend['showid'] ) ? $href.")" :
                $href.", '<span class=\"idcode\">', t.$alias, '</span>' )";
        }*/
        if ( count( $linkout ) == 1 )
            $linktitle = $linkout[0];
        else
            $linktitle = 'concat( '.implode(',', $linkout ).')';
        $ext = "if( t$link.$collink[0] is NULL, '', $linktitle ) as `$falias`";
    }
    return $ext;
}

function getformula( $icol, $extend )
{
    $db = DB::getInstance();
    $formula = $extend['formula'];
    if ( !$formula )
        return $db->parse( "'?' as `$icol[alias]`" );    
    else
    {
        $formula = str_replace( array('[', ']', "'", '`'), 
                   array('t.`', '`', '', '`' ), $formula );
        if ( isset( $extend['round'] ) && strlen( $extend['round'] ) > 0 )
            $formula = "ROUND( $formula, ".(int)$extend['round'].')';
        else
            $formula = "($formula)";
        return $db->parse( "$formula as `$icol[alias]`" );
    }
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

//            $collink = api_colname( (int)$extend['column'] );
            $leftjoin .= $db->parse( " left join ?n as t$link on t$link.id=t.?p", $dblink, $icol['idalias']);

            $fields[] = getmultilink( $extend, $link, alias( $icol ), "__$icol[idalias]");
/*            if ( empty( $extend['aslink'] ))
                $fields[] = "ifnull( t$link.$collink, '' ) as `__$icol[idalias]`";
            else
                $fields[] = "if( t$link.$collink is NULL, '', concat('<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\" >', t$link.$collink, '</a>')) as `__$icol[alias]`";*/
            //                $icol['alias'] = $collink.$link;
        }
        elseif  ( $icol['idtype'] == FT_CALC )
        {
            $extend = json_decode( $icol['extend'], true );
            $icol['alias'] = alias( $icol );
            $fields[] = getformula( $icol, $extend );
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
    $multi = array();
    foreach ( $columns as &$icol )
    {
        if ( $icol['idtype'] == FT_LINKTABLE || $icol['idtype'] == FT_PARENT )
        {
            $alias = '__'.$icol['idalias'];
            if ( ANSWER::isresult( $alias ))
            {
                if ( $icol['idtype'] == FT_LINKTABLE )
                {
                    $extend = json_decode( $icol['extend'], true );
                    if ( !empty( $extend['multi'] ))
                    {
                        $mid = ANSWER::resultget( alias( $icol ));
                        $multiid = $mid ? array( $mid ) : array();
                        if ( $multiid )
                        {
                            $dblink = api_dbname( $extend['table'] );
                            $ilink = $icol['id'];
                            $multiquery = getmultilink( $extend, $ilink, 'idmulti'/*alias( $icol )*/, "tmp" );
                            $mout = array( ANSWER::resultget( $alias ) );
                            $mlist = $db->getall("select $multiquery, t.idmulti from ?n as t 
                                left join ?n as t$ilink on t$ilink.id = t.idmulti
                                where t.idcolumn=?s && t.iditem=?s", 
                                   ENZ_ONEMANY, $dblink, $ilink, $id );
                            if ( $mlist )
                            {
                                foreach ( $mlist as $imlist )
                                {
                                    $mout[] = $imlist['tmp'];
                                    $multiid[] = $imlist['idmulti'];
                                }
                                ANSWER::resultset( $alias, implode( ' &sect; ', $mout )); 
                            }
                        }
                        if ( $multiid )
                            $multi[ $icol['idalias'] ] = implode( ',', $multiid );
                    }
                }
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
    if ( $multi )
        ANSWER::set( 'multi', $multi );
    ANSWER::resultset( 'table', $idtable );
}            

function get_linklist( $icol, $offset, $search = '', $parent = 0, $filter=0 )
{
    $db = DB::getInstance();
    $dblink = api_dbname( $icol['extend']['table'] );

    $collink = api_colname( (int)$icol['extend']['column'] );
    $alinks = explode( ',', $icol['extend']['column'] );
    $linkout = array( $collink );
    if ( count( $alinks ) > 1 )
    {
        for ( $ilink=1; $ilink < count( $alinks ); $ilink++ )
        {
            $colname = api_colname( (int)$alinks[$ilink] );
            if ( $colname )
            {
                $linkout[] = "' * '";
                $linkout[] = "t.$colname";
            }
        }
    }
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
/*    if ( $linkout )
        $tree .= $db->parse(", concat( ?p ) as exttitle", implode( ',', $linkout ));
    else
        $tree .= ", '' as exttitle";*/
    $clist = $db->getall("select id, concat( ?p ) as title ?p from ?n as t ?p order by title limit $offset,$onpage", 
                          implode( ',', $linkout ), $tree, $dblink, $wsearch );
    $ret = array( 'offset' => $offset, 'next' => ( $offset + $onpage < $count ? 1 : 0 ),
                'search' => $search, 'parent' => $parent, 'istree' => $istree,
                  'list' => array(), 'crumbs' => array());
       foreach ( $clist as $cil )
           $ret['list'][] = array( 'id'=> $cil['id'], 'title' => $cil['title'],
                                   'count' => $cil['count']  );
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


