<?php

require_once 'ajax_common.php';

$pars = post( 'params' );

if ( ANSWER::is_success() && ANSWER::is_access())
    $db->update( CONF_PREFIX.'_'.$pars['dbname'], pars_list( 'idparent', $pars ), '', $pars['id'] );

ANSWER::answer();

