<?php

require_once 'ajax_common.php';

$form = post( 'params' );
//print_r( $form );
if ( ANSWER::is_success())
{
    $dbt = $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $form['table'] );
    if ( $dbt )
    {
        $dbname = alias( $dbt, CONF_PREFIX.'_' );
        $columns = $db->getall("select * from ?n where idtable=?s", 
                                          CONF_PREFIX.'_columns', $form['table'] );
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
