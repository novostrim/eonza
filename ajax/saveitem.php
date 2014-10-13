<?php

require_once 'ajax_common.php';

$form = post( 'params' );
//print_r( $form );
if ( $result['success'] )
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
            if ( !empty( $FIELDS[ $icol['idtype']]['save'] ))
                $FIELDS[ $icol['idtype']]['save']( $out, $form, $icol, $outext );
            elseif ( isset( $form[$colname] ) && isset( $FIELDS[ $icol['idtype']]['sql'] ))
                $out[ $colname ] = $form[$colname];
        }
//        print_r( $out );
        if ( $form['id'] )
        {
            if ( $out )
            {
                $result['success'] = $db->update( $dbname, $out, $outext, $form['id'] ); 
                if ( $result['success'] )
                    api_log( $form['table'], $form['id'], 'edit' );
            }
        }
        else
        {
            if ( !$outext )
                $outext = array( /*'_uptime=CURRENT_TIMESTAMP',*/ "_owner=$USER[id]" );
            else
                $outext[] = "_owner=$USER[id]";
            $result['success'] = $db->insert( $dbname, $out, $outext, true ); 
            if ( $result['success'] )
                api_log( $form['table'], $result['success'], 'create' );
        }
        if ( $result['success'] )
            getitem( $dbt, $result['success'] );
    }
}
print json_encode( $result );
