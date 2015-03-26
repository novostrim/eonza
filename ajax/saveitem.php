<?php

require_once 'ajax_common.php';

$form = post( 'params' );
//print_r( $form );
if ( ANSWER::is_success() && ANSWER::is_access( A_EDIT, $form['table'], $form['id'] ))
{
    $dbt = $db->getrow("select * from ?n where id=?s", ENZ_TABLES, $form['table'] );
    if ( !$dbt )
        api_error( 'err_id', "id=$form[table]" );
    elseif ( defined( 'DEMO' ) && $dbt['idparent'] == SYS_ID )
        api_error('This feature is disabled in the demo-version.');
    else
    {

        $dbname = alias( $dbt, CONF_PREFIX.'_' );
        $columns = $db->getall("select * from ?n where idtable=?s", 
                                          ENZ_COLUMNS, $form['table'] );
        $out = array();
        $outext = '';
        foreach ( $columns as &$icol )
        {
            $icol['idalias'] = alias( $icol );
            $colname = $icol['idalias'];
            $field = GS::field( $icol['idtype'] );
            if ( !empty( $field['save'] ))
                $field['save']( $out, $form, $icol, $outext );
            elseif ( isset( $form[$colname] ) && isset( $field['sql'] ))
                $out[ $colname ] = $form[$colname];
        }
//        print_r( $out );
        if ( $form['id'] )
        {
            if ( $out )
            {
                ANSWER::success( $db->update( $dbname, $out, $outext, $form['id'] )); 
                if ( ANSWER::is_success())
                    api_log( $form['table'], $form['id'], 'edit' );
            }
        }
        else
        {
            if ( !$outext )
                $outext = GS::owner();
            else
                $outext[] = "_owner=".GS::userid();
            ANSWER::success( $db->insert( $dbname, $out, $outext, true )); 
            if ( ANSWER::is_success())
                api_log( $form['table'], ANSWER::is_success(), 'create' );
        }
        if ( ANSWER::is_success())
            getitem( $dbt['id'], ANSWER::is_success(), $dbname, $columns );
    }
}
ANSWER::answer();
