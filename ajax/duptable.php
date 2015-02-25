<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $dest = $pars['dest'];
    $tables = CONF_PREFIX.'_tables';
    $columns = CONF_PREFIX.'_columns';
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", $tables, $idi );
        if ( !$curtable || $curtable['isfolder'])
            api_error( 'err_id', "id=$idi" );
        else
        {
            $curtable['title'] = $dest;
            foreach ( array( 'alias', '_uptime', 'id', '_owner' ) as $iun )
                unset( $curtable[ $iun ] );
            ANSWER::success( $db->insert( $tables, $curtable, GS::owner(), true )); 
            if ( ANSWER::is_success())
            {
                api_log( ANSWER::is_success(), 0, 'create' );
                $dbname = api_dbname( $idi );
                $newname = CONF_PREFIX.'_'.( ANSWER::is_success());

                $cols = $db->getall( "select * from ?n where idtable=?s", $columns, $idi );
                $after = array();
                foreach ( $cols as $icol )
                {
                    $column = $icol;
                    $icol['idtable'] = ANSWER::is_success();
                    unset( $icol['id'] );
                    $newid = $db->insert( $columns, $icol, '', true );
                    if ( !$column['alias'] )
                    {
                        $field = GS::field( $icol['idtype'] );
                        $decl = $field['sql']( $icol );
                        $after[] = $db->parse("alter table ?n change ?n ?n ?p", 
                                    $newname, $column['id'], $newid, $decl );
                    }
                }
                $ret = $db->query("create table ?n like ?n", $newname, $dbname );
                if ( $pars['importdata'])
                    $ret = $db->query("insert into ?n select * from ?n", $newname, $dbname );
                foreach ( $after as $iafter )
                    $db->query( $iafter );
            }
        }
    }
}
ANSWER::answer();
