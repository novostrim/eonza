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
        $set = $this->getall( "select iditem as id, title from ?n where idset=?s order by title", ENZ_SETS, $idset );
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

    public function escape( $value )
    {
        return $this->parse( '?s', $value );
    }
}

class DB {
    protected static $instance;
    protected static $db;

    public static function getInstance( $opt = '' ) { 
        if ( self::$instance === null) 
        { 
            self::$instance = new DB( $opt );
        } 
        return self::$db;
    }
    public  function __construct( $opt ) {
        self::$db = new ExtMySQL( $opt );
    }
    private function __clone() {
    }
    private function __wakeup() {
    }
    public static function getall() {
        $par = func_get_args();  // PHP 5.3
        return call_user_func_array( array( self::$db, 'getAll'), $par );
    }
    public static function getone() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'getOne'), $par );
    }
    public static function getrow() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'getRow'), $par );
    }
    public static function insert() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'insert'), $par );
    }
    public static function parse() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'parse'), $par );
    }
    public static function query() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'query'), $par );
    }
    public static function update() {
        $par = func_get_args();
        return call_user_func_array( array( self::$db, 'update'), $par );
    }
}
