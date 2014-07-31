<?php
/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.novostrim.com
    License: MIT
*/

require_once "safemysql.class.php";

class ExtMySQL extends SafeMySQL
{
    function __construct($opt = array())
    {
        parent::__construct( $opt );
    }
    
    public function getset( $idset )
    {
        $set = $this->getall( "select iditem as id, title from ?n where idset=?s order by title", CONF_PREFIX.'_sets', $idset );
        $ret = array();
        foreach ( $set as $iset )
            $ret[ $iset['id']] = $iset;
        return $ret;
    }

    public function insert( $dbname, $fields, $parse = '', $lastid = false )
    {
        $pf = $fields ? $this->parse("?u", $fields ) : '';
        $ret = $this->query( "insert into ?n set ?p ?p", $dbname, $pf, 
                             $parse ? ( $pf ? ',' : '' ).implode( ', ', $parse ) : '');
        if ( $ret && $lastid )
            $ret = $this->insertId();
        return $ret;
    }

    public function tables()
    {
        $ret = array();
        $list = $this->getall("show tables");
        foreach ( $list as $ilist )
            $ret[] = $ilist[ 'Tables_in_'.CONF_DB ];
        return $ret;
    }

    public function update( $dbname, $fields, $parse = '', $idi )
    {
        $pf = $fields ? $this->parse("?u", $fields ) : '';
        return $this->query( "update ?n set ?p ?p where id=?s", $dbname, $pf, 
                             $parse ? ( $pf ? ',' : '' ).implode( ', ', $parse ) : '', $idi ) ? $idi : 0;
    }

}




?>
