<?php
/*
   Eonza
   (c) 2014-15 Novostrim, OOO. http://www.eonza.org
   License: MIT
*/

define( 'STORAGE', APP_DOCROOT.CONF_STORAGE.( substr( CONF_STORAGE, -1 ) != '/' ? '/' : '' ));

function files_download( $id, $browser = false, $thumb = false, $public = '' )
{
    $file = DB::getrow("select idtable,folder,filename,size,storage,preview, w, h,
                     ifnull( m.name, 'application/octet-stream' ) as mime from ?n as f
                    left join ?n as m on m.id=f.mime
                    where f.id=?s",
                        ENZ_FILES, ENZ_MIMES, $id );
    if ( !$file || ( $public && !in_array( $file['idtable'], $public )))
        return;

    // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
    // если этого не сделать файл будет читаться в память полностью!
/*    if (ob_get_level()) {
      ob_end_clean();
    }*/
    header('Content-Type: '.$file['mime']);
    if ( $browser )
    {
        header('Content-Disposition: inline; filename='.$file['filename']);
    }
    else
    {
        header('Content-Description: File Transfer');
//        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$file['filename']);
    }
    header('Content-Transfer-Encoding: binary');
//    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    if ( !$file['w'] || !$file['h'] )
        header('Content-Length: '.$file['size']);
//        header('Content-Length: 10000');
//    else
//        header('Content-Length: '.filesize( STORAGE."$file[idtable]/$file[folder]/".( $thumb ? '_' : '' )."$id" ));
    if ( !$file['folder'] )
    {
        print $file[ $thumb ? 'preview' : 'storage'];
    }
    else
    {
//        print_r( $file );
        print file_get_contents( STORAGE."$file[idtable]/$file[folder]/".( $thumb ? '_' : '' )."$id" );
//        readfile( STORAGE."$file[idtable]/$file[folder]/".( $thumb ? '_' : '' )."$id");
    }
    exit;
}

function files_getfolder( $idtable )
{
    $idfolder = 0;
    $count = DB::getrow("select count( id ) as count, folder from ?n where idtable=?s group by folder order by count", 
           ENZ_FILES, $idtable );
    if ( $count && $count['count'] < 500 )
        $idfolder = $count['folder'];
    $path = STORAGE.$idtable;
    if ( !$idfolder )
    {
        $idfolder = DB::getone("select ifnull( max( folder ) +1, 1 ) from ?n where idtable=?s", 
                                   ENZ_FILES, $idtable );
        if ( !is_dir( $path ))
               mkdir( $path, 0777 );
           $folder = "$path/$idfolder";
        if ( !is_dir( $folder ))
               mkdir( $folder, 0777 );

           if ( !is_dir( $folder ))
               return 0;
    }
    return $idfolder;
}

// Returns true if the table has FT_FILE or FT_IMAGE fields
function files_is( $idi )
{
    return DB::getone( "select count(*) from ?n where idtable=?s && ( idtype=?s || idtype=?s )",
                                       ENZ_COLUMNS, $idi, FT_FILE, FT_IMAGE ) ? 1 : 0;
}

function files_delcolumn( $col )
{
    $list = DB::getall("select id from ?n 
                         where idtable=?s && idcol=?s", ENZ_FILES, $col['idtable'], $col['id'] );
    foreach ( $list as $il )
        files_delfile( $il['id'], false );
}

function files_delitem( $idtable, $id )
{
    $list = DB::getall("select id from ?n 
                         where idtable=?s && iditem=?s", ENZ_FILES, $idtable, $id );
    foreach ( $list as $il )
        files_delfile( $il['id'], false );
}

function files_deltable( $idtable )
{
    $list = DB::getall("select id from ?n 
                         where idtable=?s", ENZ_FILES, $idtable );
    foreach ( $list as $il )
        files_delfile( $il['id'], false );
}

function files_delfile( $idi, $toresult )
{
    $fitem = DB::getrow("select id, idtable, idcol, ispreview, iditem, folder from ?n where id=?s",
                          ENZ_FILES, $idi );
    if ( $fitem )
    {
        if ( $fitem['folder'] )
        {
            $path = STORAGE."$fitem[idtable]/$fitem[folder]/";
            @unlink( $path.$idi );
            if ( $fitem['ispreview'] )
                @unlink( $path.'_'.$idi );
        }
        DB::query("delete from ?n where id=?s", ENZ_FILES, $idi );
        if ( $toresult )
        {
            $col = DB::getrow("select * from ?n where id=?s", ENZ_COLUMNS, (int)$fitem['idcol'] );
            if ( $col )
                ANSWER::result( files_result( $fitem['idtable'], $col, $fitem['iditem'], true ));
        }
        return;
    }
    api_error('delfile');
}

function files_edit( $data )
{
    $par =  array( 'comment' => $data['comment'] );
    if ( !empty( $data['filename'] ))
        $par['filename'] = $data['filename'];
    return DB::update( ENZ_FILES, $par, '', $data['id'] );
}

function files_result( $idtable, $col, $iditem, $full = false )
{
    if ( $full )
    {
        ANSWER::set( 'iditem', $iditem );
        ANSWER::set( 'alias', alias( $col ));
    }
    return DB::getall("select id, filename, comment, size, w, h, ispreview from ?n
        where idtable=?s && idcol=?s && iditem=?s order by `sort`", ENZ_FILES, 
        $idtable, $col['id'], $iditem );    
}

