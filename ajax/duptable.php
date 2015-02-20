<?php

require_once 'ajax_common.php';

if ( $result['success'] )
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
            $result['success'] = $db->insert( $tables, $curtable, GS::owner(), true ); 
            if ( $result['success'] )
            {
                $cols = $db->getall( "select * from ?n where idtable=?s", $columns, $idi );
                foreach ( $cols as $icol )
                {
                    $icol['idtable'] = $result['success'];
                    unset( $icol['id'] );
                    $db->insert( $columns, $icol, '' );
                }
                api_log( $result['success'], 0, 'create' );
                $dbname = api_dbname( $idi );
                $newname = CONF_PREFIX.'_'.$result['success'];
                $ret = $db->query("create table ?n like ?n", $newname, $dbname );
                if ( $pars['importdata'])
                    $ret = $db->query("insert into ?n select * from ?n", $newname, $dbname );
            }
        }
    }
}
print json_encode( $result );
