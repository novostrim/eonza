<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( $result['success'] )
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $tables = CONF_PREFIX.'_tables';
    $columns = CONF_PREFIX.'_columns';
    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s", $tables, $idi );
        if ( !$curtable )
            api_error( 'err_id', "id=$idi" );
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
                $dbname = $curtable['alias'] ? $curtable['alias'] : CONF_PREFIX."_$idi"; 
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
                     $result['success'] = $db->query( "drop table ?n", $dbname );
                     if ( $result['success'] )
                        api_log( $idi, 0, 'delete' );
                }
            }
            if ( $result['success'] )
            {
                $db->query("delete from ?n where id=?s", $tables, $idi );
                $db->query("delete from ?n where idtable=?s", $columns, $idi );
                files_deltable( $idi );
            }
        }
    }
}
print json_encode( $result );
