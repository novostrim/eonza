<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/utf.php';

function pagelink( $page )
{
    global $urlparam;

    $ret = '#/table'.( $urlparam ? '?'.$urlparam : '');
    if ( $page == 1 )
        return $ret;

    return $ret.( $urlparam ? '&' : '?').'p='.$page;
}

function fltcompare( $field, $not, $compare, $value )
{
    global $names, $COMPARE, $db;

    if ( !isset( $COMPARE[ $compare ]) || !isset( $names[ $field ]) )
        return '';
    if ( isset( $COMPARE[$compare][2] ))
        $value = str_replace( 'v_', $value, $COMPARE[$compare][2] );
    return str_replace( array('f_', 'v_'), array( $names[ $field ], $db->parse( '?s', $value )), 
                        $COMPARE[$compare][$not] );
}

$id = get( 'id' );
if ( $id && $result['success'] )
{
    $urlparam = url_params( 'p' );
    $sort = (int)get( 'sort' );
    $filter = get('filter');
    $summary = (int)get( 'sum' );
    $order = 't._uptime desc';
    $names = array( '-1' => 't.id' );
    $result['filter'] = array();
    $result['total'] = array();
    $totallist = array();
    $result['db'] = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $id );
    if ( $result['db'] )
    {
        $dbname = alias( $result['db'], CONF_PREFIX.'_' );
        $fields = array( "t.id", "t._uptime" );
        $leftjoin = '';
        $field2ind = array();
        $columns = $db->getall("select * from ?n where idtable=?s  
                                          order by `sort`", CONF_PREFIX.'_columns', $id );
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
                $collink = api_colname( (int)$extend['column'] );
                $alias = alias( $icol );
                $leftjoin .= $db->parse( " left join ?n as t$link on t$link.id=t.?p", $dblink, $alias );
                if ( empty( $extend['aslink'] ))
                {
                    $linktitle = empty( $extend['showid'] ) ? "t$link.$collink" : "concat( t$link.$collink, '<span class=\"idcode\">', t.$alias, '</span>' )";
                    $ext = "ifnull( $linktitle, '' ) as `$icol[alias]`";
                }
                else
                {   $href = "concat('<a href=\"\" onclick=\"return js_card($extend[table], ', t$link.id, ' )\">', t$link.$collink, '</a>'";
                    $linktitle = empty( $extend['showid'] ) ? $href.")" :
                        $href.", '<span class=\"idcode\">', t.$alias, '</span>' )";
                    $ext = "if( t$link.$collink is NULL, '', $linktitle ) as `$icol[alias]`";
                }
                $fields[] = (  $icol['idtype'] == FT_PARENT ? " t.`_parent` as `_parent_`," : '' ).$ext;
                       // $collink$link";
               }
               elseif ( $icol['idtype'] == FT_FILE || $icol['idtype'] == FT_IMAGE )
               {
                $fields[] = $db->parse( "( select count(id) from ?n where idtable=?s && idcol = ?s && iditem=t.id ) as `$icol[alias]`", 
                        CONF_PREFIX.'_files', $id, $icol['id'] );
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
        if ( $result['db']['istree'] && isset( $_GET['parent'] ))
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
             $result['crumbs'] = array_reverse( $crumbs );
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
                $fout = fltcompare( $field, $not, $compare, $fltvalue );
                if ( $fout )
                {
                    $qwhere .= $db->parse(" ?p ?p", $logic == 1 ? '||' : '&&', $fout );
                    $result['filter'][] = array( 'logic' => $logic, 'field' => $field, 'not' => $not ? true : false,
                                   'compare' => $compare, 'value' => $value );
                }
            }
        }

        $query = $db->parse( "select count(`id`) from ?n as t ?p", $dbname, $qwhere );
        $onpage = $OPTIONS['perpage'];
        if ( $onpage < 1 )
            $onpage = 50;
        $result['pages'] = pages( $query, array( 'onpage' => $onpage, 'page' => (int)get('p') ), 'pagelink' );
/*        $result['items'] = $db->getall("select * from ?n as m ?p
            order by status desc,idowner,name ?p", CONF_PREFIX.'_files', $qwhere, $result['pages']['limit'] );
*/
        $order = 'order by '.$order;
        $result['result'] = $db->getall("select ?p from ?n as t ?p ?p ?p ?p", implode( ',', $fields ), $dbname,
                $leftjoin, $qwhere, $order, $result['pages']['limit'] );
        $result['total']['is'] = count( $totallist );
        if ( $summary & 0x1 )
        {
            foreach ( $totallist as $tl )
                $sumlist[] = "sum( t.$tl ) as `$tl`";
            $result['total']['result'] = $db->getrow("select ?p from ?n as t ?p", 
                 implode( ',', $sumlist ), $dbname, $qwhere );
        }
        if ( !$result['filter'] )
            $result['filter'] = array( array( 'logic' => 0, 'field' => 0, 'not' => false,
                        'compare' => 0, 'value' => '' ));
/*        foreach ( $result['result'] as &$ival )
        {
            foreach ( $result['columns'] as &$icol )
            {
                $idf = $icol['idtype'];
                $ial = $icol['alias'];
                $type = $FTYPES[ $idf ];

                $pattern = isset( $type['list'] ) ? $type['list'] : 'list_default';
                $pattern( $ival[ $ial ], $icol );
//                $ival[ $ial ]['_type']
            }
        }*/
    }
    else
        $result['success'] = false;
}

print json_encode( $result );
