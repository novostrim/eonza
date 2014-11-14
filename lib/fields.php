<?php
/*
   Eonza
   (c) 2014 Novostrim, OOO. http://www.novostrim.com
   License: MIT
*/

define('FT_UNKNOWN', 0 );
define('FT_NUMBER', 1 );
define('FT_VAR' , 2 );
define('FT_DATETIME', 3 );
define('FT_TEXT', 4 );
define('FT_LINKTABLE', 5 );
define('FT_CHECK', 6 );
define('FT_DECIMAL', 7 );
define('FT_ENUMSET', 8 );
define('FT_SETSET', 9 );
define('FT_PARENT', 10 );
define('FT_FILE', 11 );
define('FT_IMAGE', 12 );
define('FT_SPECIAL', 13 );
define('FT_SQL', 99 );

define('FTM_WEBSITE', 1 );
define('FTM_EMAIL', 2 );
define('FTM_PHONE', 3 );
define('FTM_HASH', 4 );
//define('FT_DATE', 7 );
//define('FT_HTML', 10 );
//define('FT_UBYTE', 80 );
//define('FT_USHORT', 81 );

/* Patterns
ptn_edit - function which returns pattern for edit mode. By default: pattern_default
ptn_view - function which returns pattern for view mode. By default: equals ptn_edit
edit - function which returns the pattern of the control for edit mode. By default: edit_default
view - function which returns the pattern of the control for edit mode. By default: view_default
list - function which returns the pattern of the control for list mode. By default: list_default
*/

$COMPARE = array(
    1 => array( "f_ = v_", "f_ != v_" ),
    2 => array( "f_ > v_", "f_ <= v_" ),
    3 => array( "f_ < v_", "f_ >= v_" ),
    4 => array( "f_ = 0", "f_ != 0" ),
    5 => array( "f_ = ''", "f_ != ''" ),
    6 => array( "f_ LIKE v_", "f_ NOT LIKE v_", 'v_%' ),
    7 => array( "f_ LIKE v_", "f_ NOT LIKE v_", '%v_%' ),
    8 => array( "LENGTH( f_ ) = v_", "LENGTH( f_ ) != v_" ),
    9 => array( "LENGTH( f_ ) > v_", "LENGTH( f_ ) <= v_" ),
    10 => array( "LENGTH( f_ ) < v_", "LENGTH( f_ ) >= v_" ),
    );

$FIELDS = array(
   FT_NUMBER => array( 'pars'=>'range', 'sql' => 'number_sql'  /*'sql' => 'int(10)' , 'number' => 1 */ ),
   FT_VAR => array( 'pars' => 'length', 'sql' => 'var_sql'/* 'varchar(%par%)' */ ),
   FT_DATETIME => array( 'pars' => 'date,timenow', 'sql' => 'date_sql', /* 'varchar(%par%)' */
                              'save' => 'date_save' ),
   FT_TEXT => array( 'pars' => 'weditor,bigtext', 'sql' => 'text_sql' ),
   FT_LINKTABLE => array( 'pars' => 'table,column,extbyte,filter,aslink', 'sql' => 'linktable_sql',
                           'save' => 'linktable_save'),
   FT_CHECK => array( 'pars' => '', 'sql' => 'check_sql' ),
   FT_DECIMAL => array( 'pars'=>'dtype,dlen', 'sql' => 'decimal_sql' /*'sql' => 'int(10)' , 'number' => 1 */ ),
   FT_ENUMSET => array( 'pars' => 'set', 'sql' => 'enumset_sql' ),
   FT_SETSET => array( 'pars' => 'set', 'sql' => 'setset_sql' ),
   FT_PARENT => array( 'pars' => '', 'sql' => '',
                           'save' => 'parent_save'),
   FT_FILE => array( 'pars' => 'storedb' ),
   FT_IMAGE => array( 'pars' => 'storedb,max,min,ratio,side,thumb,thumb_ratio,thumb_side' ),
   FT_SPECIAL => array( 'pars' => 'type', 'sql' => 'special_sql', 
                              'save' => 'special_save' ),
   FT_SQL => array( 'pars' => 'sqlcmd', 'sql' => 'sql_sql' ),
/*   3 => array( "name" => 'fdatetime', 'sql' => 'datetime' ),
   4 => array( "name" => 'ftext', 'sql' => 'text', 'edit' => 'edit_text' ),
   5 => array( "name" => 'flinktable', 'sql' => 'custom', 'edit' => 'edit_linktable', 'number' => 1,
               'save' => 'save_linktable' ),
   6 => array( "name" => 'fcheck', 'sql' => 'tinyint(3) unsigned', 'number' => 1,
               'edit' => 'edit_check', 'list' => 'list_check' ),
   7 => array( "name" => 'fdate', 'sql' => 'date' ),

   8 => array( "name" => 'fenumset', 'sql' => 'tinyint(3) unsigned', 'list' => 'list_enumset',
                  'edit' => 'edit_enumset', 'number' => 1 ),
   9 => array( "name" => 'fsetset', 'sql' => 'int(10)', 'edit' => 'edit_setset', 'number' => 1,
               'savex' => 'save_setset' ),
   10 => array( "name" => 'fhtmlcont', 'sql' => 'text', 'edit' => 'edit_text', 'ptn_edit' => 'pattern_span' ),
   11 => array( "name" => 'ffile', 'sql' => '', 'save' => 'save_file' ),
   12 => array( "name" => 'fimage', 'sql' => '', 'save' => 'save_file' ),
   80 => array( "name" => 'fubyte', 'sql' => 'tinyint(3) unsigned', 'number' => 1 ),
   81 => array( "name" => 'fushort', 'sql' => 'smallint(5) unsigned', 'number' => 1 ),
   99 => array( "name" => 'fsql', 'sql' => '%par%' ),*/
);

function check_sql( $form )
{
    return "tinyint(3) NOT NULL";
}

function date_sql( $form )
{
    $dtype = (int)defval( $form['ext']['date'], 1 );
    $type = $dtype == 1 ? 'datetime' : ( $dtype == 2 || $dtype==4 ? 'date' : 'timestamp default 0' );
    return "$type NOT NULL";
}

function special_sql( $form )
{
    $dtype = (int)defval( $form['ext']['type'], 1 );

    switch ( $dtype ) {
        case FTM_EMAIL: $type = 'varchar(48)';break;
        case FTM_PHONE: $type = 'bigint';break;
        case FTM_HASH: $type = 'binary(16)';break;
        default: 
            $type = 'varchar(256)';
    }
    return "$type NOT NULL";
}

function enumset_sql( $form )
{
    return "tinyint(3) unsigned NOT NULL";
}

function linktable_sql( &$form )
{
    global $db;

    $colname = CONF_PREFIX."_columns";
    $extbyte = $form['ext']['extbyte'];
    $extupd = false;
    $maxid = $db->getone("select max(id) from ?n", api_dbname( $form['ext']['table'] ));
    if ( $maxid < 250 )
        $ftype = 'tinyint(3)';
    elseif ( $maxid < 65000 )
    {
        $ftype = 'smallint(5)';
        $extbyte = 1;
    }
    else
    {
        $ftype = 'mediumint(8)';
        $extbyte = 2;
    }
    if ( !empty( $form['ext']['filter'] ))
    {
        $links = $db->getall("select extend,id from ?n where idtable=?s && idtype=?s",
                              $colname, $form['ext']['table'], FT_LINKTABLE );
        $fltok = false;
        foreach ( $links as $il )
        {
            $ext = json_decode( $il['extend'], true );
            if ( (int)$ext['table'] == (int)$form['ext']['filter'] )
            {
                $fltok = true;
                $form['ext']['filtercol'] = $il['id'];
                $extupd = true;
                break;
            }
        }
        if ( !$fltok )
        {
            $form['ext']['filter'] = '0';
            $extupd = true;
        }
    }
    if ( $extbyte != $form['ext']['extbyte'] ) 
    {
        $form['ext']['extbyte'] = $extbyte;
        $extupd = true;
    }
    if ( $extupd )
    {
        $form['extend'] = json_encode( $form['ext'] );
        $db->update( $colname, array('extend' => $form['extend'] ), '', $form['id'] );
    }
    return "$ftype unsigned NOT NULL";
}

function number_sql( $form )
{
    $range = (int)defval( $form['ext']['range'], 7 );
    if ( $range < 3 )
        $type = 'tinyint(3)';
    elseif ( $range < 5 )
        $type = 'smallint(5)';
    elseif ( $range < 7 )
        $type = 'mediumint(9)';
    else
        $type = 'int(10)';
    $unsigned = $range & 1 ? '' : 'unsigned';
    return "$type $unsigned NOT NULL";

}

function decimal_sql( $form )
{
    $dtype = (int)defval( $form['ext']['dtype'], 1 );
    if ( $dtype == 2 )
        $type = 'double';
    else
        $type = 'float';
    if ( $form['ext']['dlen'] )
        $type .= "(".$form['ext']['dlen'].")";
    return "$type NOT NULL";

}

function setset_sql( $form )
{
    return "int(10) unsigned NOT NULL";
}

function sql_sql( $form )
{
    $def = $form['ext']['sqlcmd'];
    if ( strtolower( $form['ext']['sqlcmd'] ) == 'timestamp' )
        $def .= ' NOT NULL DEFAULT 0';
    return $def;
}

function text_sql( $form )
{
    $type = (int)defval( $form['ext']['bigtext'], 0 ) ? 'mediumtext' : 'text';
    return "$type NOT NULL";
}

function var_sql( $form )
{
    $length = (int)defval( $form['ext']['length'], 32 );
    $length = min( 1024, max( 2, $length ));
    return "varchar( $length ) NOT NULL";
}

function date_save( &$out, $form, $icol, &$outext )
{
    global $db;

    $alias = alias( $icol );
    $val = $form[$alias];
    $extend = json_decode( $icol['extend'], true );
    if ( empty( $val ) && $extend['timenow'])
        if ( $outext )
            $outext[] = $db->parse("?n=NOW()", $alias );
        else
            $outext = array( $db->parse("?n=NOW()", $alias ));
    else
        $out[ $alias ] = $val;
}

function special_save( &$out, $form, $icol, &$outext )
{
    global $db;

    require_once "utf.php";

    $alias = alias( $icol );
    $val = $form[$alias];
    $extend = json_decode( $icol['extend'], true );
    switch ( $extend['type'] ) {
        case FTM_EMAIL: $val = utf_lower( $val );break;
        case FTM_PHONE: 
            $len = strlen( $val );
            $tmp = '';
            for ( $i=0; $i < $len; $i++ )
                if ( $val[$i] >='0' && $val[$i] <= '9' )
                    $tmp .= $val[$i];
            $val = $tmp;
            break;
        case FTM_HASH: 
            unset( $out[ $alias ] );
            if ( !$val )
                $val = time();
            if ( strlen($val) == 32 )
                return;
            $tmp = $db->parse("?n=?p", $alias, "X'".pass_md5( $val, true )."'" );
            if ( $outext )
                $outext[] = $tmp;
            else
                $outext = array( $tmp );
            return;
        default: 
            $val = str_replace('http://', '', utf_lower( $val ));
    }    
    $out[ $alias ] = $val;
}

function linktable_save( &$out, $form, $icol, &$outext )
{
    global $db;
    
    $alias = alias( $icol );
    $val = $form[$alias];
    $extend = json_decode( $icol['extend'], true );
    $extbyte = $extend['extbyte'];
    if ( ( $val > 65000 && $extbyte < 2 ) ||
         ( $val > 250 && $extbyte < 1 ))
    {
        $colname = CONF_PREFIX."_columns";
        if ( $val > 65000 )
        {
            $ftype = 'mediumint(8)';
            $extend['extbyte'] = 2;
        }
        else
        {
            $ftype = 'smallint(5)';
            $extend['extbyte'] = 1;
        }
        $dbname = api_dbname( $icol['idtable'] );
        if ( $db->query( "alter table ?n change ?n ?n $ftype unsigned NOT NULL", $dbname, $alias, $alias ))
            $db->update( $colname, array('extend' => json_encode( $extend )), '', $icol['id'] );
    }
    $out[ $alias ] = empty( $form[$alias] ) ? 0 : $val;
}

function parent_save( &$out, $form, $icol, &$outext )
{
    global $db;
    
    $alias = alias( $icol );
    $val = empty( $form[$alias] ) ? 0 : $form[$alias];
    $dbname = api_dbname( $icol['idtable'] );

    if ( $form['id'] == $val )
        return;
    $root = $db->getone("select _parent from ?n where id=?s", $dbname, $form['id'] );

    $row = $db->getrow("select id, _parent from ?n where id=?s", $dbname, $val );
    while ( $row['_parent'] )
    {
        if ( $row['_parent'] == $form['id'] )
        {
            $db->update( $dbname, array( '_parent' => $root ), '', $row['id']);
            break;
        }
        $row = $db->getrow("select id, _parent from ?n where id=?s", $dbname, $row['_parent'] );
    }
    $out[ $alias ] = $val;
}


