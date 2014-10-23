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

function export( $pars )
{
    global $db;

    $filename = "$pars[output]/".strftime($pars['filename']).'.enz';

    $out = '';
    foreach ( $pars['table'] as $tid )
    {
        $cmd = '';
        $table = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $tid );
        $cmd .= pack( 'V2C', $table['id'], strtotime( $table['_uptime'] ), $table['istree'] );
        packstr( $cmd, $table['title'], $table['alias'], $table['comment'] );
        $columns = $db->getall("select * from ?n where idtable=?s order by `sort`", CONF_PREFIX.'_columns', $tid );
        $cmd .= pack( 'v', count( $columns ));
        foreach ( $columns as $cid )
        {
            $col = pack( 'VCvCC', $cid['id'], $cid['idtype'], $cid['sort'], $cid['visible'], $cid['align']);
            packstr( $col, $cid['title'], $cid['alias'], $cid['comment'], $cid['extend'] );
            $cmd .= pack( 'v', strlen( $col )).$col;
        }
        $out .= pack( 'CV', CMD_TABLE, strlen( $cmd ));
        $out .= $cmd;
    }
    file_put_contents( $_SERVER['DOCUMENT_ROOT'].$filename, $out );
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

