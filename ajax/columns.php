<?php

require_once 'ajax_common.php';

$id = get( 'id' );
if ( $id && ANSWER::is_success())
{
    ANSWER::set( 'db', $db->getrow("select * from ?n where id=?s", CONF_PREFIX.'_tables', $id ));

    if ( ANSWER::get( 'db' ))
    {
        $columns = $db->getall("select * from ?n where idtable=?s order by `sort`", CONF_PREFIX.'_columns', $id );
        $i = 0;
        foreach ( $columns as &$icol )
        {
            $icol['class'] = '';
            $alias = '';
            $icol['extend'] = json_decode( $icol['extend'], true );

            if ( $icol['idtype'] == FT_LINKTABLE || $icol['idtype'] == FT_PARENT )
            {
                $icol['link'] = get_linklist( $icol, 0 );
            }
            else
            {
                if ( $icol['idtype'] == FT_ENUMSET || $icol['idtype'] == FT_SETSET )
                {
                   $list = $db->getall('select iditem, title from ?n where idset=?s', 
                              CONF_PREFIX.'_sets', $icol['extend']['set'] );
                   foreach ( $list as $il )
                       $icol['list'][$il['iditem']] = $il['title'];
                }
            }
//        $alias = alias( $icol );
            $icol['alias'] =  alias( $icol );//$alias;
        }
        ANSWER::set( 'columns', $columns );
/*        if ( $xresult['db']['istree'] )
        {
            array_unshift( $xresult['columns'], array( 'id' => 0xffffff, 'title' => '', 
                'idtype' => FT_PARENT, 'alias' => '_parent', 'extend'=>array( 'table'=> $xresult['db']['id'],
                                   'column'=> $xresult['columns'][0]['id'],
                                   'extbyte' => 2 ),
                'sort' => -1, 'align'=>0, 'visible' => '1' ));
            $xresult['columns'][0]['link'] = get_linklist( $xresult['columns'][0], 0 );
        }*/
    }
}
ANSWER::answer();
