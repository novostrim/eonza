<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $tables = ENZ_TABLES;
    $columns = ENZ_COLUMNS;
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", $tables, $idi );
        if ( !$curtable )
            api_error( 'err_id', "id=$idi" );
        elseif ( defined( 'DEMO' ) && $curtable['idparent'] == SYS_ID )
            api_error('This feature is disabled in the demo-version.');
        else
        {
            if ( $curtable['isfolder'])
            {
                $count = $db->getone("select count(*) from ?n where idparent=?s", $tables, $idi );
                if ( $count )
                    api_error( 'err_notempty' );
            }
            else
            {
                $dbname = alias( $curtable, CONF_PREFIX.'_' );
                $islink = 0;
                $links = $db->getall("select col.extend, col.title as icol, t.title as itable from ?n as col
                    left join ?n as t on t.id = col.idtable
                    where col.idtype=?s", $columns, $tables, FT_LINKTABLE  );
                foreach ( $links as $il )
                {
                    $extend = json_decode( $il['extend'], true );
                    if ( isset( $extend['table'] ) &&  (int)$extend['table'] == $idi )
                    {
                        $islink = "$il[itable] - $il[icol]";
                        break;
                    }
                }
                if ( $islink )
                    api_error( 'err_dellink', $islink );
                elseif ( in_array( $dbname, $db->tables()))
                {
                     ANSWER::success( $db->query( "drop table ?n", $dbname ));
                     if ( ANSWER::is_success())
                        api_log( $idi, 0, 'delete' );
                }
            }
            if ( ANSWER::is_success())
            {
                $db->query("delete from ?n where id=?s", $tables, $idi );
                $db->query("delete from ?n where idtable=?s", $columns, $idi );
                files_deltable( $idi );
            }
        }
    }
}
ANSWER::answer();
