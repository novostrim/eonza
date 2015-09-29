<?php

require_once 'ajax_common.php';
require_once APP_EONZA.'lib/files.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    if ( $idi )
    {
        if ( $db->query("truncate table ?n", api_dbname( $idi ) ))
        {
            $collist = $db->getall("select col.id, col.extend from ?n as col
                             where col.idtable=?s && col.idtype=?s", ENZ_COLUMNS, $idi, FT_LINKTABLE  );
            foreach ( $collist as $cil )
            {
                $extend = json_decode( $cil['extend'], true );
                if ( !empty( $extend['multi'] ))
                    $db->query("delete from ?n where idcolumn = ?s", ENZ_ONEMANY, (int)$cil['id'] );
            }

            $db->query("delete from ?n where idtable=?s", ENZ_SHARE, $idi );
            files_deltable( $idi );
            ANSWER::success( $idi );
            api_log( ANSWER::is_success(), 0, 'truncate' );
        }
    }
}
ANSWER::answer();
