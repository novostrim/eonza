<?php

require_once 'ajax_common.php';

function sortmenu( $idparent, $id, $sort )
{
    $db = DB::getInstance();

    $i = 1;
    $list = $db->getall( "select id from ?n where idparent = ?s order by idparent,`sort`,title", 
                         ENZ_MENU, $idparent );
    $isort = $sort;
    foreach ( $list as $il )
    {
        if ( $il['id'] == $sort )
            $isort = $i++;
        $db->query( "update ?n set sort=?s where id=?s", ENZ_MENU, $i++, $il['id'] );
    }
    $db->query( "update ?n set sort=?s, idparent=?s  where id=?s", ENZ_MENU, 
              $isort == -1 ? $i + 1 : $isort, $idparent, $id );
}

function getmenuparent( $id )
{
    return DB::getone("select idparent from ?n where id=?s", ENZ_MENU, $id );
}

$pars = post('params');
if ( ANSWER::is_success() && ANSWER::is_access())
{
    if ( !$pars['prev'] )
        sortmenu( 0, $pars['id'], 0 );
    elseif ( !$pars['next'] )
    {
        $idparent = getmenuparent( $pars['prev'] );
        sortmenu( $idparent, $pars['id'], -1 );
    }
    else
    {
        $idprev = getmenuparent( $pars['prev'] );
        $idnext = getmenuparent( $pars['next'] );
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
