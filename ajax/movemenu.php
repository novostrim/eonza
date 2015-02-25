<?php

require_once 'ajax_common.php';

function sortmenu( $idparent, $id, $sort )
{
    $db = DB::getInstance();

    $dbname = CONF_PREFIX.'_menu';
    $i = 1;
    $list = $db->getall( "select id from ?n where idparent = ?s order by idparent,`sort`,title", 
                         $dbname, $idparent );
    $isort = $sort;
    foreach ( $list as $il )
    {
        if ( $il['id'] == $sort )
        {
            $isort = $i++;
        }    
        $db->query( "update ?n set sort=?s where id=?s", $dbname, $i++, $il['id'] );
    }
    $db->query( "update ?n set sort=?s, idparent=?s  where id=?s", $dbname, 
              $isort == -1 ? $i + 1 : $isort, $idparent, $id );
}

$pars = post('params');
if ( ANSWER::is_success())
{
    $dbname = CONF_PREFIX.'_menu';
    if ( !$pars['prev'] )
    {
        sortmenu( 0, $pars['id'], 0 );
    }
    elseif ( !$pars['next'] )
    {
        $idparent = $db->getone("select idparent from ?n where id=?s", $dbname, $pars['prev'] );
        sortmenu( $idparent, $pars['id'], -1 );
    }
    else
    {
        $idprev = $db->getone("select idparent from ?n where id=?s", $dbname, $pars['prev'] );
        $idnext = $db->getone("select idparent from ?n where id=?s", $dbname, $pars['next'] );
        if ( $idprev != $idnext )
        {
            if ( $idnext == $pars['prev'] )
                sortmenu( $idnext, $pars['id'], 0 );
            else
                sortmenu( $idprev, $pars['id'], -1 );
        }
        else
            sortmenu( $idprev, $pars['id'], $pars['next'] );
    }
    require_once 'menu_common.php';
}
ANSWER::answer();
