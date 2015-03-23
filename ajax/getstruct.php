<?php

require_once 'ajax_common.php';
require_once 'index_common.php';

if ( ANSWER::is_success() && ANSWER::is_access())
{
    ANSWER::result( array( 'form' => array(), 'items' => array()));

    $idi = (int)get( 'id' );
    if ( $idi )
    {
        $form = $db->getrow("select * from ?n where id=?s",
                                       CONF_PREFIX.'_tables', $idi );
        if ( !$form )
            api_error( 'err_id', "id=$idi" );
        else
        {
            ANSWER::resultset( 'form', $form );
            $fields = array();
            $items = $db->getall("select * from ?n where idtable=?s && idtype!=?s order by ?n",
                                       CONF_PREFIX.'_columns', $idi, FT_PARENT, 'sort' );
            foreach ( $items as &$iext )
            {
                $iext['extend'] = json_decode( $iext['extend'] );
                $fields[ alias( $iext )] = $iext['title'];
            }
            ANSWER::resultset( 'items', $items );
            ANSWER::resultset( 'index', index_list( $form['alias'] ? 
                                $form['alias'] : CONF_PREFIX."_$idi", $fields ));
        }
    }
}
ANSWER::answer();
