<?php
/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

function eonza_update( $curver, $newver )
{
    for ( $i = $curver; $i< $newver; $i++ )
    {
        $funcname = 'upd'.$i.'to'.($i+1);
        $fname = APP_DOCROOT.APP_DIR."update/$funcname.php";
        if ( file_exists( $fname ))
        {
            require_once $fname;
            $funcname();
        }
    }
}
