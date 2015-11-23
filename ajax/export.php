<?php

define( 'FUNCONLY', 1 );

require_once 'table.php';

function exportmulti( $idcolumn, $iditem )
{
    $mlist = DB::GetInstance()->getall("select idmulti from ?n where idcolumn=?s && iditem=?s",
                            ENZ_ONEMANY, $idcolumn, $iditem );
    if ( $mlist )
    {
        $mbuf = array();
        foreach ( $mlist as $iml )
            $mbuf[] = $iml['idmulti'];
        return ','.implode( ',', $mbuf );
    }
    return '';
}

$id = get( 'id' );
if ( $id && ANSWER::is_success() && ANSWER::is_access( A_READ, $id ))
{
    GS::set( 'urlparam', url_params( 'p' ));
    $sort = (int)get( 'sort' );
    $order = 't._uptime desc';
    $explist = get('exportlist');
    $exportfmt = get('exportfmt');
    $vis = $explist ? explode( ',', $explist ) : array();
    $defsort = array( 0xffff => 't.id', 0xfffe => 't._uptime' );
    if ( isset( $defsort[ abs( $sort )] ))
        $order = $defsort[ abs( $sort )].( $sort < 0 ? ' desc' : '' );

    $sort = (int)$sort;
    $names = array( '-1' => 't.id' );
    $retfilter = array();
    ANSWER::set( 'filter', array());
    $retdb = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $id );
    if ( $retdb )
    {
        $dbname = alias( $retdb, CONF_PREFIX.'_' );
        $fields = array( "t.id", "t._uptime" );
        $leftjoin = '';
        $field2ind = array();
        $columns = $db->getall("select * from ?n where idtable=?s  
                                          order by `sort`", ENZ_COLUMNS, $id );
        $cind = 0;
        $multi = array();
        $many = array();
        $sets = array();
        foreach ( $columns as &$icol )
        {
            $field2ind[ $icol['id'] ] = $cind++;
            $icol['class'] = '';
            $icol['alias'] = alias( $icol );
            $names[ $icol['id']] = 't.'.$icol['alias'];
            
            if ( $icol['idtype'] == FT_PARENT || $icol['idtype'] == FT_FILE || $icol['idtype'] == FT_IMAGE )
                continue;
            
            $extend = json_decode( $icol['extend'], true );
            if ( $icol['idtype'] == FT_CALC )
            {
                $formula = getformula( $icol, $extend );
                $names[ $icol['id']] = substr( $formula, 0, strpos( $formula, 'as'));
            }
            if ( $vis && !in_array( $icol['id'], $vis  ))
                continue;
            if ( $icol['idtype'] == FT_LINKTABLE && !empty( $extend['multi']))
                $multi[ $icol['alias']] = $icol['id'];
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

                $ext = getmultilink( $extend, $link, $alias, $icol['alias'], true );
                if ( $icol['idtype'] == FT_LINKTABLE &&  !empty( $extend['multi'] ))
                    $many[ $cind ]  = $ext;

                $fields[] = ($icol['idtype'] == FT_PARENT ? " t.`_parent` as `_parent_`," : '').$ext;
            }
/*            else
            {
                if ( $icol['idtype'] == FT_TEXT || 
                    ( $icol['idtype'] == FT_VAR && (int)$extend['length'] > 128 ) )
                {
                    $fields[] = "LEFT( t.$icol[alias], 128 ) as `$icol[alias]`";
                }
                else*/
            elseif ($icol['idtype'] == FT_SPECIAL && $extend['type'] == FTM_HASH )
                $fields[] = "HEX( t.$icol[alias] ) as `$icol[alias]`";
            elseif ( $icol['idtype'] == FT_CALC )
            {
                $formula = getformula( $icol, $extend );
                $fields[] = $formula;
//                $names[ $icol['id']] = substr( $formula, 0, strpos( $formula, 'as'));
            }
            elseif ( $icol['idtype'] == FT_ENUMSET || $icol['idtype'] == FT_SETSET )
            {
                $list = $db->getall('select iditem, title from ?n where idset=?s', 
                                     ENZ_SETS, $extend['set'] );
                $sets[$icol['alias']] = $icol['id'];
                foreach ( $list as $il )
                {
                    $icol['list'][$il['iditem']] = $il['title'];
                }
                $fields[] = "t.$icol[alias]";                
            }
            else
                $fields[] = "t.$icol[alias]";

            if ( abs( $sort ) == $icol['id'] )
            {
                $order = $fields[ count($fields) - 1][0] == 't' ? "t.$icol[alias]" :
                                        "`$icol[alias]`";
                if ( $sort < 0 )
                    $order .= ' desc';
            }
        }
        $qwhere = treefilter( $retdb, $columns, $names, $retfilter, $field2ind );
        $order = 'order by '.$order;
        $off = 0;
        $num = 100;
        header('Content-Description: File Transfer');
        header("Cache-Control: public");
        if ( $exportfmt == 1 )
        {
            require_once APP_EONZA.'lib/xlsxwriter.class.php';
    
            header('Content-disposition: attachment; filename="'.$dbname.strftime("-%Y%m%d-%H%M%S").'.xlsx"');
            header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
            header('Content-Transfer-Encoding: binary');
//            header('Cache-Control: must-revalidate');
//            header('Pragma: public');            
            $writer = new XLSXWriter();
            $writer->setAuthor('Eonza');
        }
        else
        {
            header('Content-Disposition: attachement;filename="'.$dbname.strftime("-%Y%m%d-%H%M%S").'.csv";');
            header('Content-Type: application/csv; charset=UTF-8');
            $output = fopen('php://output', 'w');
        }

// output the column headings
        while ( $ret = $db->getall("select t.id as `_id`, ?p from ?n as t ?p ?p ?p limit ?p, ?p", 
                 implode( ',', $fields ), $dbname, $leftjoin, $qwhere, $order, $off, $num ))
        {
            //if ( $icol['idtype'] == FT_ENUMSET || $icol['idtype'] == FT_SETSET )
            if ( $sets )
            {
                foreach ( $ret as &$sim )
                {
                    foreach ( $sets as $sk => $sv )
                    {
                        if ( !empty( $sim[$sk] )) {
                            $listtmp = $columns[ $field2ind[$sv] ]['list'];
                            if ( $columns[ $field2ind[$sv] ]['idtype'] == FT_ENUMSET ) 
                                $sim[$sk] = $listtmp[ $sim[$sk]];
                            if ( $columns[ $field2ind[$sv] ]['idtype'] == FT_SETSET ) 
                            {
                                $out = array();        
                                for ( $is =0; $is<32; $is++ )
                                {
                                    if ( $sim[$sk] & ( 1 << $is ))
                                        $out[] = $listtmp[ $is + 1 ];
                                }
                                $sim[$sk] = implode( ';', $out );
                            }
                        }
                    }
                }
            }
            if ( $many )
            {
                foreach ( $many as $mkey => $mval )
                {
                    $icol = $columns[$mkey-1];
                    $extend = json_decode( $icol['extend'], true );
                    $dblink = api_dbname( $extend['table'] );
                    $ilink = $icol['id'];
                    $alias = alias( $icol );
                    $mval = str_replace( "t.$alias", 't.idmulti', $mval );
                    foreach ( $ret as &$im )
                    {
                        if ( $im[$alias] )
                        {
                            $mout = array( $im[$alias] );
                            $mlist = $db->getall("select $mval from ?n as t 
                                left join ?n as t$ilink on t$ilink.id = t.idmulti
                                where t.idcolumn=?s && t.iditem=?s", 
                                   ENZ_ONEMANY, $dblink, $ilink, $im['id'] );
                            if ( $mlist )
                            {
                                foreach ( $mlist as $imlist )
                                    $mout[] = $imlist[ $alias ];
                                $im[$alias] = implode( ";", $mout );
                            }
                        }
                    }
                }
            }

            foreach ( $ret as $iret )
            {
                $out = array();
                $idret = $iret['_id'];
                unset( $iret['_id'] );
                if ( $vis )
                    foreach ( $vis as $iv )
                    {
                        $name = $iv == SYS_ID ? 'id' : $columns[$field2ind[ $iv ]]['alias'];
//                        if ( $multi && isset( $multi[$name] ) && !empty( $iret[ $name ] ))
//                            $iret[ $name ] .= exportmulti( $multi[ $name ], $idret );
                        $out[] = $iret[ $name ];
                    }
                else
                    foreach ( $iret as /*$ikey =>*/ $iv )
                    {
//                        if ( $multi && isset( $multi[$ikey] ) && !empty( $iv ))
//                            $iv .= exportmulti( $multi[$ikey], $idret );
                        $out[] = $iv;
                    }
                if ( $exportfmt == 1 )
                     $writer->writeSheetRow('Sheet1', $out );
                else
                    fputcsv( $output, $out, ';' );
            }
            if ( count( $ret ) < $num )
                break;
            $off += $num;
//            if ( $off>20 )
//                break;
        }
        if ( $exportfmt == 1 )
            $writer->writeToStdOut();
    }
    else
        ANSWER::success( false );
}

//ANSWER::answer();
