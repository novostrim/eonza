<?php

require_once 'enz_format.php';

/*  $pars parameters
    output - output folder
    filename - output filename, it can contain strftime format.
    tables - array id 
*/

function packstr( &$out )
{
    $args = func_num_args();
    for ( $i = 1; $i < $args; $i++ )
    {
        $str = func_get_arg( $i ); 
        $out .= pack( 'Ca*', strlen( $str ), $str );
    }
}

function index_export( $table, $columns )
{
    global $db;

    $index = $db->getall("show index from ?n", $table['alias'] ? $table['alias'] : CONF_PREFIX."_$table[id]" );
    $indexret = array();
    $last = '';
    foreach ( $index as $ind )
    {
        if ( in_array( $ind['Key_name'], array( 'PRIMARY', '_uptime', '_parent' )))
            continue;
        if ( $last == $ind['Key_name'] )
            $indexret[ count( $indexret ) - 1 ][1][] = $ind['Column_name'];
        else
        {
            $indexret[] = array( $ind['Index_type'] == 'FULLTEXT' ? 'FULLTEXT' : 'INDEX', 
                                 array( $ind['Column_name'] ));
            $last = $ind['Key_name'];
        }
    }
    return $indexret;
}

function export( $pars )
{
    global $db;

//    $filename = "$pars[output]/".strftime($pars['filename']).'.enz';
    $filename = strftime($pars['filename']).'.enz';
    $out = pack('a3Cv2a*', 'enz', 0, ENZ_VERSION, ENZ_HEADSIZE, strftime( '%Y%m%d%H%M%S'));
    foreach ( $pars['table'] as $tid )
    {
        $cmd = '';
        $table = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $tid );
        $cmd .= pack( 'V2C', $table['id'], strtotime( $table['_uptime'] ), $table['istree'] );
        packstr( $cmd, $table['title'], $table['alias'], $table['comment'] );
        $columns = $db->getall("select * from ?n where idtable=?s && idtype!=?s order by `sort`", 
                           CONF_PREFIX.'_columns', $tid, FT_PARENT );
        $cmd .= pack( 'v', count( $columns ));
        foreach ( $columns as $cid )
        {
            $col = pack( 'VCvCC', $cid['id'], $cid['idtype'], $cid['sort'], $cid['visible'], $cid['align']);
            packstr( $col, $cid['title'], $cid['alias'], $cid['comment'], $cid['extend'] );
            $cmd .= pack( 'v', strlen( $col )).$col;
        }
        $indexes = index_export( $table, $columns );
        $cmd .= pack( 'v', count( $indexes ));
        foreach ( $indexes as $ind )
        {
            $index = '';
            packstr( $index, "$ind[0] (`".join('`,`', $ind[1]).'`)' );
            $cmd .= $index;
        }
        $out .= pack( 'CV', CMD_TABLE, strlen( $cmd ));
        $out .= $cmd;
    }
//    header( 'Content-type:application/x-gzip' );
    header( 'Content-type:application/octet-stream' );
    header( "Content-Disposition: attachment; filename=$filename" );
    header( 'Content-Description: File Transfer' );
    header( 'Content-Transfer-Encoding: binary');
    print $out; //gzencode( $out, 5 );
    exit();
//    file_put_contents( $_SERVER['DOCUMENT_ROOT'].$filename, $out );
/*    $zip = new ZipArchive(); 
    $zipok = $zip->open( $_SERVER['DOCUMENT_ROOT'].$filename, ZipArchive::CREATE ); 
    if ( !$zipok )
    {
        print "Error creating $filename";
        exit();
    }


//foreach ( $files as $f )
//    print $zip->addFile( HOME."tmp/locale_$f.js", "locale_$f.js" );

    $zipok = $zip->close();
    if ( !$zipok )
    {
        print "Error creating $filename";
        exit();
    }*/
    return $filename;
}

