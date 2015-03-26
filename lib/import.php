<?php

require_once 'enz_format.php';
/*  $pars parameters
    output - output folder
    filename - output filename, it can contain strftime format.
*/

function column_query( $idfield, &$ifield )
{
    $fname = defval( $ifield['alias'], $idfield );
    $fid = $ifield['idtype'];
//    print_r( $ifield );
    $field = GS::field( $fid );
    $ftype = $field['sql']( $ifield );
    return "`$fname` $ftype";
}

function unpackstr( &$ret, $names, &$in, $off )
{
    $keys = explode( ' ', $names );
    foreach ( $keys as $ik )
    {
        $len = unpack('C', substr( $in, $off, 1 ));
        $val = unpack( 'a*', substr( $in, $off+1, $len[1] )."\0" );
        $ret[$ik] = $val[1];
        $off += $len[1] + 1;
    }
    return $off;
}

function import( $pars )
{
    $db = DB::getInstance();
//    $filename = "$pars[output]/".strftime($pars['filename']);
//    $filename = "$pars[output]/".strftime($pars['filename']);
    $filename = strftime($pars['filename']);
    $in = file_get_contents( $_SERVER['DOCUMENT_ROOT']."$pars[output]/".$filename );
//    if ( substr( $filename, -2 ) == 'gz' )
//        $int = gzdecode( $in );

    $len = strlen( $in );
    $head = unpack( 'a4/vver/vsize/a14time', $in );
    $off = $head['size'];
//    print_r( $head );
    while ( $off < $len )
    {
        $cmd = unpack( 'Ccmd/Vsize', substr( $in, $off, 5 ));
        switch ( $cmd['cmd'] )
        {
            case CMD_TABLE:
                $tbl = unpack( 'Vid/Vuptime/Cistree', substr( $in, $off += 5, 9 ));
                $off = unpackstr( $tbl, 'title alias comment', $in, $off + 9 );
                print "CMD Create Table $tbl[title]<br>";
                $count = unpack( 'v', substr( $in, $off, 2 ));
                $off += 2;
                for ( $i=0; $i < $count[1]; $i++ )
                {
                    $start = $off;
                    $col = unpack( 'vsize/Vid/Cidtype/vsort/Cvisible/Calign', 
                             substr( $in, $off, 11 ));
                    $off = unpackstr( $col, 'title alias comment extend', $in, $off + 11 );
                    if ( $off-$start-2 != $col['size'] )
                    {
                        print "Wrong column info start=$start off=$off size = $col[size]";
                        exit();
                    }
                    $col['ext'] = json_decode($col['extend'], true );
                    $tbl['columns'][] = $col;
                }
                $indcount = unpack( 'vcount', substr( $in, $off, 2 ));
                $off += 2;
                $indlist = array();
                while ( $indcount['count']-- )
                {
                    $off = unpackstr( $indlist, "$indcount[count]", $in, $off );
                }                
                $tblcur = $db->getone("select id from ?n where ( alias != '' && alias=?s )", 
                     ENZ_TABLES, $tbl['alias'] );
                if ( $tblcur || ( $tbl['alias'] && in_array( $tbl['alias'], $db->tables() ) ))
                {
                    print "SKIP table<br>";
                }
                else
                {
                    $idtable = $db->insert( ENZ_TABLES, 
                        pars_list( 'comment,title,alias,istree', $tbl ), GS::owner(), true ); 
                    if ( !$tbl['alias'])
                        $tbl['alias'] = CONF_PREFIX."_$idtable";
                    $query = "CREATE TABLE IF NOT EXISTS `$tbl[alias]` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `_uptime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
  `_owner`  smallint(5) unsigned NOT NULL,\r\n";
                    $treeindex = '';
                    if ( $tbl['istree'] )
                    {
                        $query .= "  `_parent` int(10) unsigned NOT NULL,\r\n";
                        $treeindex = ",\r\n   KEY `_parent` (`_parent`,`_uptime`)";
                    }
                    foreach ( $tbl['columns'] as $ifield )
                    {
                        $idfield = $db->insert( ENZ_COLUMNS, 
                        pars_list( 'title,extend,comment,idtype,alias,visible,align,sort', $ifield ), 
                             array( "idtable = $idtable" ), true );
                        $gsfield = $GS::field( $ifield['idtype'] );
                        if ( isset( $gsfield['sql'] ))
                            $query .= column_query( $idfield, $ifield ).", \r\n";
                    }
                    $query .= "  PRIMARY KEY (`id`),
    KEY `_uptime` (`_uptime`) $treeindex
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
                    if ( !$db->query( $query ))    
                    {
                        print "Error creating $tbl[alias]";
                        exit();
                    }
                    foreach ( $indlist as $ilist )
                    {
                        $qindex = $db->parse( "alter table ?n add ?p", $tbl['alias'], $ilist );
                        if ( !$db->query( "?p", $qindex ))
                            print "Error creating index $qindex <br>";
                    }
                }
//                print_r( $tbl );
                break;
            default:
                print "Unknownd cmd $cmd[cmd] off=$off";
                exit();
        }
//        $off += $cmd['size'];
    }
}