<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

//require_once APP_EONZA.'lib/files.php';

define( 'TMP', APP_DOCROOT.'/'.trim( CONF_STORAGE, '/' ).'/tmp/' );

function csv_delim( $filename )
{
    $delim = ';';
    if (($handle = @fopen( TMP.$filename, "r")) !== FALSE) 
    {
        $num = 0;
        $sum = array( 0, 0 );
        $prev = array( 0, 0 );

        while (($buffer = fgets($handle, 4096)) !== false && $num < 2 ) {
            $commas = explode(',', $buffer );
            $default = explode(';', $buffer );
            if ( !$num )
            {
                $prev[0] = count( $commas );
                $prev[1] = count( $default );
            }
            elseif ( $prev[0] > $prev[1] && count( $commas ) == $prev[0] )
                    $delim = ',';
            $sum[0] += count( $commas );
            $sum[1] += count( $default );
            $num++;
        }
        fclose($handle);
        if ( $prev[0] > 0 && count( $commas ) > count( $default ))
            $delim = ',';
    }
    return $delim;
}

function csv_import( $pars )
{
    $db = DB::getInstance();
    $filename = $pars['idfile'];
    $delim = csv_delim( $filename );
    $count = 0;
    if (($handle = @fopen( TMP.$filename, "r")) !== FALSE) 
    {
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
                    {
                        if ( $icol['id'] == $pars['fields'][$i] )
                        {
                            $fields[] = array( 'ind' => $i, 'colname' => alias( $icol ));
                            break;
                        }
                    }
        }
        $outext = GS::owner();
        while (($data = fgetcsv($handle, 32000, $delim )) !== FALSE) 
        {
            if ( !$data )
                continue;
            $num = count( $data );
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
        fclose($handle);
        @unlink( TMP.$filename );
    }
    else
        api_error( 'Cannot load file' );
    return $count;
}

function csv_list( $filename, $pars )
{
    $ret = array();
    $delim = csv_delim( $filename );
    if (($handle = fopen( TMP.$filename, "r")) !== FALSE) 
    {
        $data = fgetcsv( $handle, 32000, $delim );
        $num = count($data);
        for ( $c=0; $c < $num; $c++ ) {
            if ( strlen( $data[$c] ) > 256 )
                $data[$c] = substr( $data[$c], 0, 256 ).'...';
            $ret[] = array( 'example' => trim( $pars['encode'] != 'UTF-8' ? iconv($pars['encode'], 'UTF-8', $data[$c]) : $data[$c] ), 'id' => 0 );
        }
        fclose($handle);
    }

    ANSWER::result( $ret );
}