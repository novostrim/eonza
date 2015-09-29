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
        $tables = ENZ_TABLES;
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
                                          ENZ_COLUMNS, $idtable );
                foreach ( $columns as &$icol )
                {
                    $icol['idalias'] = alias( $icol );
                    if ( $icol['idtype'] == FT_LINKTABLE )
                    {
                        $extend = json_decode( $icol['extend'], true );
                        if ( !empty( $extend['multi'] ))
                        {
                            $list = $db->getall( "select * from ?n where idcolumn=?s && iditem=?s", 
                                                 ENZ_ONEMANY, $icol['id'], $idi );
                            foreach ( $list as $il )
                                $db->insert( ENZ_ONEMANY, array( 'idcolumn' => $il['idcolumn'],
                                'iditem' => ANSWER::is_success(), 'idmulti' => $il['idmulti'] ), '' );
                        }
                    }
                }

                getitem( $dbt['id'], ANSWER::is_success(), $dbname, $columns );
            }
        }
    }
}
ANSWER::answer();
