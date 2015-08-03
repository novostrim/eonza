<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/utf.php';

GS::set( 'compare', array(
    1 => array( "f_ = v_", "f_ != v_" ),
    2 => array( "f_ > v_", "f_ <= v_" ),
    3 => array( "f_ < v_", "f_ >= v_" ),
    4 => array( "f_ = 0", "f_ != 0" ),
    5 => array( "f_ = ''", "f_ != ''" ),
    6 => array( "f_ LIKE v_", "f_ NOT LIKE v_", 'v_%' ),
    7 => array( "f_ LIKE v_", "f_ NOT LIKE v_", '%v_%' ),
    8 => array( "LENGTH( f_ ) = v_", "LENGTH( f_ ) != v_" ),
    9 => array( "LENGTH( f_ ) > v_", "LENGTH( f_ ) <= v_" ),
    10 => array( "LENGTH( f_ ) < v_", "LENGTH( f_ ) >= v_" ),
    11 => array( "f_ LIKE v_", "f_ NOT LIKE v_", '%v_' ),
    12 => array( "f_ & v_", "!( f_ & v_ )" ),
    13 => array( "(f_ & v_) = v_", "(f_ & v_) != v_" ),
    14 => array( "((1<<(f_ - 1 )) & v_)", "!((1<<(f_ - 1 )) & v_)" ),
    15 => array( "f_ != 0", "f_ = 0" ),
    16 => array( "f_ = 0", "f_ != 0" ),
    17 => array( "YEARWEEK(f_) = YEARWEEK(NOW())", "YEARWEEK(f_) != YEARWEEK(NOW())" ),
    18 => array( "( YEAR(f_) = YEAR(NOW()) && MONTH(f_) = MONTH(NOW()))", 
                    "( YEAR(f_) != YEAR(NOW()) || MONTH(f_) != MONTH(NOW()))" ),
    17 => array( "YEARWEEK(f_) = YEARWEEK(NOW())", "YEARWEEK(f_) != YEARWEEK(NOW())" ),
    18 => array( "( YEAR(f_) = YEAR(NOW()) && MONTH(f_) = MONTH(NOW()))", 
                    "( YEAR(f_) != YEAR(NOW()) || MONTH(f_) != MONTH(NOW()))" ),
    19 => array( "( f_ >= DATE_SUB( CURDATE(), INTERVAL v_ DAY) && DATE(f_) != CURDATE())", 
                 "( f_ < DATE_SUB( CURDATE(), INTERVAL v_ DAY) || DATE(f_) = CURDATE())" ),
));


function pagelink( $page )
{
    $urlparam = GS::get( 'urlparam' );

    $ret = '#/table'.( $urlparam ? '?'.$urlparam : '');
    if ( $page == 1 )
        return $ret;

    return $ret.( $urlparam ? '&' : '?').'p='.$page;
}

function fltcompare( $field, $not, $compare, $value, $names )
{
    if ( !GS::ifget( 'compare', $compare ) || !isset( $names[ $field ]) )
        return '';
    $cmp = GS::get( 'compare', $compare );
    if ( isset( $cmp[2] ))
        $value = str_replace( 'v_', $value, $cmp[2] );
    return str_replace( array('f_', 'v_'), array( $names[ $field ], DB::parse( '?s', $value )), 
                        $cmp[$not] );
}

$id = get( 'id' );
if ( $id && ANSWER::is_success() && ANSWER::is_access( A_READ, $id ))
{
    GS::set( 'urlparam', url_params( 'p' ));
    $sort = (int)get( 'sort' );
    $filter = get('filter');
    $summary = (int)get( 'sum' );
    $order = 't._uptime desc';
    $defsort = array( 0xffff => 't.id', 0xfffe => 't._uptime' );
    if ( isset( $defsort[ abs( $sort )] ))
        $order = $defsort[ abs( $sort )].( $sort < 0 ? ' desc' : '' );

    $sort = (int)$sort;
    $names = array( '-1' => 't.id' );
    $retfilter = array();
    ANSWER::set( 'filter', array());
    $total = array();
    ANSWER::set( 'total', array());
    $totallist = array();
    $retdb = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $id );
    if ( $retdb )
    {
        $dbname = alias( $retdb, CONF_PREFIX.'_' );
        ANSWER::set( 'db', $retdb );
        $fields = array( "t.id", "t._uptime" );
        $leftjoin = '';
        $field2ind = array();
        $columns = $db->getall("select * from ?n where idtable=?s  
                                          order by `sort`", ENZ_COLUMNS, $id );
        $cind = 0;
        foreach ( $columns as &$icol )
        {
            $field2ind[ $icol['id'] ] = $cind++;
            $icol['class'] = '';
            $icol['alias'] = alias( $icol );
            $names[ $icol['id']] = 't.'.$icol['alias'];
            if ( !$icol['visible'] )            
                continue;
            if ( $icol['idtype'] == FT_NUMBER || $icol['idtype'] == FT_DECIMAL )
                $totallist[] = $icol['alias'];
            $extend = json_decode( $icol['extend'], true );
               if ( $icol['idtype'] == FT_PARENT )
               {
                $fields[] = "(select count(id) from $dbname where `_parent` = t.id ) as `_children`";
               }
            if ( $icol['idtype'] == FT_LINKTABLE || ( $icol['idtype'] == FT_PARENT && !isset( $_GET['parent'] )))
            {
                $dblink = api_dbname( $extend['table'] );
                $link = $icol['id'];

//                $collink = api_colname( (int)$extend['column'] );
                $alias = alias( $icol );
                $leftjoin .= $db->parse( " left join ?n as t$link on t$link.id=t.?p", $dblink, $alias );
/*                $collist = explode( ',', $extend['column'] );
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
                    if ( !empty( $extend['aslink'] )) 
                        $linkout[] = "'<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\">'";
                    $linkout[] = "t$link.$collink[0]";
                    if ( !empty( $extend['aslink'] )) 
                        $linkout[] = "'</a>'";
                    for ( $ilink = 1; $ilink<count( $collink ); $ilink++ )
                    {
                        $linkout[] = "' &bull; '";
                        $linkout[] = "t$link.".$collink[$ilink];
                    }
                    if ( !empty( $extend['showid'] )) 
                    {
                        $linkout[] = "'<span class=\"idcode\">'";
                        $linkout[] = "t.$alias";
                        $linkout[] = "'</span>'";
                    }
                    if ( count( $linkout ) == 1 )
                        $linktitle = $linkout;
                    else
                        $linktitle = 'concat( '.implode(',', $linkout ).')';
                    $ext = "if( t$link.$collink[0] is NULL, '', $linktitle ) as `$icol[alias]`";
                }*/
                $ext = getmultilink( $extend, $link, $alias, $icol['alias'] );
                $fields[] = (  $icol['idtype'] == FT_PARENT ? " t.`_parent` as `_parent_`," : '' ).$ext;
                       // $collink$link";
               }
               elseif ( $icol['idtype'] == FT_FILE || $icol['idtype'] == FT_IMAGE )
               {
                $fields[] = $db->parse( "( select count(id) from ?n where idtable=?s && idcol = ?s && iditem=t.id ) as `$icol[alias]`", 
                        ENZ_FILES, $id, $icol['id'] );
               }
               else
               {
                if ( $icol['idtype'] == FT_TEXT || 
                     ( $icol['idtype'] == FT_VAR && (int)$extend['length'] > 128 ) )
                {
                    $fields[] = "LEFT( t.$icol[alias], 128 ) as `$icol[alias]`";
                }
                elseif ($icol['idtype'] == FT_SPECIAL && $extend['type'] == FTM_HASH )
                    $fields[] = "HEX( t.$icol[alias] ) as `$icol[alias]`";
                else
                    $fields[] = "t.$icol[alias]";
            }
            if ( abs( $sort ) == $icol['id'] )
            {
                $order = $fields[ count($fields) - 1][0] == 't' ? "t.$icol[alias]" :
                                        "`$icol[alias]`";
                if ( $sort < 0 )
                    $order .= ' desc';
            }
        }
        $qwhere = '';//$db->parse("where idtask=?s && status>0", $task['id'] );
        if ( $retdb['istree'] && isset( $_GET['parent'] ))
        {
            $parent = (int)get( 'parent' );
            $qwhere = $db->parse( 'where t.`_parent`=?s', $parent );
            $crumbs = array();
            while ( $parent )
            {
                $par = $db->getrow("select id, _parent, ?n as title from ?n where id=?s", 
                     $columns[1]['alias'], $dbname, $parent );
                $parent = $par['_parent'];
                $crumbs[] = array( $par['title'], $par['id'] );
            }
            ANSWER::set( 'crumbs', array_reverse( $crumbs ));
        }
        if ( $filter )
        {
            $flt = explode( '!', $filter );
            if ( !$qwhere )
                $qwhere .= $db->parse("where 1");                
            foreach ( $flt as $ifilter )
            {
                $logic = hexdec( $ifilter[0] );
                $not = (int)$ifilter[1];
                $compare = hexdec( substr( $ifilter, 2, 2 ));
                $fld = substr( $ifilter, 4, 4 );
                $field = $fld[0] == 'f' ? -hexdec( substr( $fld, 1 )) : hexdec( $fld );
                $value = strlen( $ifilter ) > 8 ? substr( $ifilter, 8 ) : '';
                if ( !$compare || !$field )
                    continue;
                $fltvalue = $value;
                if ( isset( $field2ind[ $field ] )) 
                {
                    if ( $columns[$field2ind[ $field ]]['idtype'] == FT_LINKTABLE )
                        $fltvalue = (int)$value;
                }
                $fout = fltcompare( $field, $not, $compare, $fltvalue, $names );
                if ( $fout )
                {
                    $qwhere .= $db->parse(" ?p ?p", $logic == 1 ? '||' : '&&', $fout );
                    $retfilter[] = array( 'logic' => $logic, 'field' => $field, 'not' => $not ? true : false,
                                   'compare' => $compare, 'value' => $value );
                }
            }
        }
        if ( ANSWER::is_own())
           $qwhere .=  $db->parse( (!$qwhere ? "where 1":'')." && _owner =?s", GS::userid());

        $query = $db->parse( "select count(`id`) from ?n as t ?p", $dbname, $qwhere );
        $onpage = (int)get( 'op' );
        if ( !$onpage )
            $onpage = GS::get( 'options', 'perpage' );
        if ( $onpage < 1 )
            $onpage = 50;
        $pages = pages( $query, array( 'onpage' => $onpage, 'page' => (int)get('p') ), 'pagelink' );
        $order = 'order by '.$order;
        ANSWER::result( $db->getall("select ?p from ?n as t ?p ?p ?p ?p", implode( ',', $fields ), $dbname,
                $leftjoin, $qwhere, $order, $pages['limit'] ));
        ANSWER::set( 'pages', $pages );
        $total['is'] = count( $totallist );
        if ( $summary & 0x1 )
        {
            foreach ( $totallist as $tl )
                $sumlist[] = "sum( t.$tl ) as `$tl`";
            $total['result'] = $db->getrow("select ?p from ?n as t ?p", 
                 implode( ',', $sumlist ), $dbname, $qwhere );
        }
        ANSWER::set( 'total', $total );
        if ( !$retfilter )
            $retfilter = array( array( 'logic' => 0, 'field' => 0, 'not' => false,
                        'compare' => 0, 'value' => '' ));
        ANSWER::set( 'filter', $retfilter );
        ANSWER::set( 'op', $onpage );
    }
    else
        ANSWER::success( false );
}

ANSWER::answer();
