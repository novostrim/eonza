<?php

require_once 'ajax_common.php';

if ( ANSWER::is_success())
{
    $pars = post( 'params' );
    $idi = $pars['id'];
    $tables = CONF_PREFIX.'_tables';
    $columns = CONF_PREFIX.'_columns';

    if ( $idi )
    {
        $curtable = $db->getrow("select * from ?n where id=?s && isfolder=0", $tables, $idi );
        if ( !$curtable )
            api_error( 'err_id', "id=$idi" );
        else
        {
            $column = $db->getrow("select * from ?n where idtable=?s && idtype=?s order by `sort`", 
                         $columns, $idi, FT_VAR );
            if ( $column )
            {
                $colname = alias( $column );
                $list = $db->getall( "select ?n from ?n", $colname, alias( $curtable, CONF_PREFIX.'_' ));
                if ( count( $list ) > 32 )
                    api_error( 'err_limitset' );
                else
                {
                    ANSWER::success( $db->insert( CONF_PREFIX.'_sets', 
                                   array( 'title' => $curtable['title'] ), GS::owner(), true )); 
                    if ( ANSWER::is_success())
                    {
                        $i = 1;
                        foreach ( $list as $val )
                            $db->insert(  CONF_PREFIX.'_sets', array('title'=> $val[ $colname ], 
                                 'idset' => ANSWER::is_success(), 'iditem' => $i++ ), GS::owner()); 
                    }
                }
            }
            else
                api_error( 'err_column' );

//            $dbname = $curtable['alias'] ? $curtable['alias'] : CONF_PREFIX."_$idi"; 
        }
    }
}
ANSWER::answer();
