<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    $idi = (int)$pars['id'];
    $idtable = (int)$pars['idtable'];
    if ( $idi && $idtable && ANSWER::is_access( A_CREATE, $idtable ) &&
                ANSWER::is_access( A_READ, $idtable, $idi ))
    {
        $tables = CONF_PREFIX.'_tables';
        $dbt = $db->getrow("select * from ?n where id=?s", $tables, $idtable );
        if ( !$dbt )
            api_error( 'err_id', "idtable=$idtable" );
        else
        {
            $dbname = alias( $dbt, CONF_PREFIX.'_' );

            $fields = $db->getrow("select * from ?n where id=?s", $dbname, $idi );
            foreach ( array( 'id', '_uptime', '_owner') as $fi )
                unset( $fields[$fi] );
            ANSWER::success( $db->insert( $dbname, $fields, GS::owner(), true )); 
            if ( ANSWER::is_success())
            {
                api_log( $idtable, ANSWER::is_success(), 'create' );
                $columns = $db->getall("select * from ?n where idtable=?s", 
                                          CONF_PREFIX.'_columns', $idtable );
                foreach ( $columns as &$icol )
                    $icol['idalias'] = alias( $icol );

                getitem( $dbt['id'], ANSWER::is_success(), $dbname, $columns );
            }
        }
    }
}
ANSWER::answer();
