<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

//
require_once APP_EONZA.'lib/simplexlsx.class.php';

define( 'TMP', APP_DOCROOT.'/'.trim( CONF_STORAGE, '/' ).'/tmp/' );

function xlsx_import( $pars )
{
    $db = DB::getInstance();
    $filename = $pars['idfile'];
    $xlsx = new SimpleXLSX( TMP.$filename );
    list($num_cols, $num_rows) = $xlsx->dimension();
    $count = 0;
    
    $dbt = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $pars['idtable'] );
    if ( !$dbt )
        api_error( 'err_id', "id=$pars[idtable]" );
    $dbname = alias( $dbt, ENZ_PREFIX );
    $columns = $db->getall("select * from ?n where idtable=?s", ENZ_COLUMNS, $pars['idtable'] );
    $fields = array();
    for ( $i = 0; $i < count( $pars['fields'] ); $i++ )
    {
        if ( $pars['fields'][$i] )
            if ( $pars['fields'][$i] == SYS_ID )
                $fields[] = array( 'ind' => $i, 'colname' => 'id' );
            else
                foreach ( $columns as $icol )
                    if ( $icol['id'] == $pars['fields'][$i] )
                    {
                        $fields[] = array( 'ind' => $i, 'colname' => alias( $icol ));
                        break;
                    }
    }
    $outext = GS::owner();

    foreach ( $xlsx->rows() as $data )
    {
        $num = $num_cols;//count( $data );
        $out = array();
        $idi = 0;
        foreach ( $fields as $field ) {
            if ( $field['ind'] < $num )
            {
                $value = trim( $pars['encode'] != 'UTF-8' ? iconv($pars['encode'], 'UTF-8', 
                           $data[$field['ind']]) : $data[$field['ind']] );
                if ( $field['colname'] == 'id' )
                    $idi = $db->getone( "select id from ?n where id=?s", $dbname, $value );
                $out[ $field['colname']] = $value;
            }   
        }
        if ( $idi )
            $iditem = $db->update( $dbname, $out, $outext, $idi ); 
        else
            $iditem = $db->insert( $dbname, $out, $outext, true ); 

        if ( $iditem )
        {
            $count++;
            api_log( $pars['idtable'], $iditem, $idi ? 'edit' : 'create' );
        }
    }
    @unlink( TMP.$filename );
//    else
//        api_error( 'Cannot load file' );
    return $count;
}

function xlsx_list( $filename, $pars )
{
    $ret = array();
    $xlsx = new SimpleXLSX( TMP.$filename );

    list($num_cols, ) = $xlsx->dimension();
    foreach ( $xlsx->rows() as $data )
    {
        for ( $c = 0; $c < $num_cols; $c++ ) 
        {
            if ( strlen( $data[$c] ) > 256 )
                    $data[$c] = substr( $data[$c], 0, 256 ).'...';
                $ret[] = array( 'example' => trim( $pars['encode'] != 'UTF-8' ? iconv($pars['encode'], 'UTF-8', $data[$c]) : $data[$c] ), 'id' => 0 );
        }
        break;
    }
    ANSWER::set( 'type', 1 );
    ANSWER::result( $ret );
}