<?php

function index_list( $dbname, $fields )
{
    $index = DB::getall("show index from ?n", $dbname );
    $indexret = array();
    $last = '';
    foreach ( $index as $ind )
    {
        if ( in_array( $ind['Key_name'], array( 'PRIMARY', '_uptime', '_parent' )))
            continue;
        if ( $last == $ind['Key_name'] )
            $indexret[ count( $indexret ) - 1 ][1] .= ', '.$fields[ $ind['Column_name'] ];
        else
        {
            $indexret[] = array( $ind['Key_name'], $fields[ $ind['Column_name'] ].
               ( $ind['Index_type'] == 'FULLTEXT' ? ' (FULLTEXT)' : '' ));
            $last = $ind['Key_name'];
        }
    }
    return $indexret;
}

function index_list_table( $table )
{
    $ret = array();

    $fields = array();
    $items = DB::getall("select * from ?n where idtable=?s && idtype!=?s order by ?n",
                                   CONF_PREFIX.'_columns', $table['id'], FT_PARENT, 'sort' );
    foreach ( $items as $iext )
        $fields[ alias( $iext )] = $iext['title'];
    $ret = index_list( alias( $table, CONF_PREFIX."_" ), $fields );

    return $ret;
}