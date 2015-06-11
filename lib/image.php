<?php

class Image
{
    private $opt;
    private $src = 0;
    private $dest = 0;
    private $thumb = 0;
    private $file;
    private $ftype;
    private $options;
    private $x, $y, $offx, $offy, $lenx, $leny, $w, $h;
    private $defaults = array(
                    'max' => 0,
                    'min' => 0,
                    'ratio' => 0,
                    'side' => 0, // 0 - longest, 1 - width, 2 - height
                    'thumb' => 0,
                    'thumb_ratio' => 1,
                    'thumb_side' => 0,
                    'options' => array(),
    );

    function __construct($opt = array())
    {
        $this->opt = array_merge( $this->defaults, $opt);
        $this->options = $this->opt['options'];
        if ( empty( $this->options['quality'] ))
            $this->options['quality'] = 85;
    }

    function __destruct() 
    {
           if ( $this->src )
               imagedestroy( $this->src );
           if ( $this->dest )
               imagedestroy( $this->dest );
           if ( $this->thumb )
               imagedestroy( $this->thumb );
    }

    function calculate()
    {
        $this->offx = 0;
        $this->offy = 0;
        $this->lenx = $this->x;
        $this->leny = $this->y;
        $max = $this->opt['max'];
        $min = $this->opt['min'];
        $side =  $this->opt['side'];
        $ratio = $this->opt['ratio'];
        if ( !$ratio )
            $ratio = $this->x/$this->y;
        elseif ( $side == 2 || ( !$side && $this->y > $this->x ))
            $ratio = 1/$ratio;
        if ( $this->x > $this->y*$ratio )
        {
            $this->lenx = floor( $this->y * $ratio );
            $this->offx = floor( ($this->x - $this->lenx)/2 );
        }
        else
        {
            $this->leny = floor( $this->x/$ratio );
            $this->offy = floor(( $this->y - $this->leny )/2 );
        }
        if (( !$side && $this->lenx >= $this->leny ) || $side == 1 )
        {
            if ( $max && $this->lenx > $max )
                $this->w = $max;
            elseif ( $min && $this->lenx < $min )
                $this->w = $min;
            else
                $this->w = $this->lenx;
            $this->h = floor( $this->w / $ratio );
        }
        else
        {
            if ( $max && $this->leny > $max )
                $this->h = $max;
            elseif ( $min && $this->leny < $min )
                $this->h = $min;
            else
                $this->h = $this->leny;
            $this->w = floor( $this->h * $ratio );
        }
    }

    function savetofile( $filename, $thumb = false )
    {
    //    glob $istrans, $transcolor;
        if ( is_file( $filename ))
            unlink( $filename );
        if ( $this->ftype == 'jpeg' )
            imagejpeg( $thumb ? $this->thumb : $this->dest, $filename, $this->options['quality'] );
        elseif ( $this->ftype == 'png' )
            imagepng( $thumb ? $this->thumb : $this->dest, $filename );
        elseif ( $this->ftype == 'gif' )
            imagegif( $thumb ? $this->thumb : $this->dest, $filename );
    /*        if ( $it == 1 )
        {
    //        $trans = imagecolorat( $image,1,1 );
    //      if ( $istrans )
    //            imagecolortransparent( $image, $transcolor );  
            imagepng( $image, $name );
        }*/
    }

    function watermark( $filename )
    {
        $left = empty( $this->options['waterleft']) ? 0 : (int)$this->options['waterleft'];
        $top = empty( $this->options['watertop']) ? 0 : (int)$this->options['watertop'];

        $width = imagesx( $this->dest ? $this->dest : $this->src );
        $height = imagesy( $this->dest ? $this->dest : $this->src );

        $waterdest = imagecreatetruecolor( $width, $height );
        imagecopy( $waterdest, $this->dest ? $this->dest : $this->src, 0, 0, 0, 0, $width, $height );
        $watermark = imagecreatefrompng( APP_DOCROOT.$this->options['watermark'] ); 
        $water_width = imagesx($watermark);
        $water_height = imagesy($watermark); 
        if ( !$left )
            $w_x = $width/2 - $water_width/2;
        elseif ( $left > 0 )
            $w_x = $left;
        elseif ( $left < 0 )
            $w_x = $width + $left - $water_width;
        if ( !$top )
            $w_y = $height/2 - $water_height/2;
        elseif ( $top > 0 )
            $w_y = $top;
        elseif ( $top < 0 )
            $w_y = $height + $top - $water_height;

//                imagealphablending($this->dest ? $this->dest : $this->src, true);
//                imagealphablending($watermark, true);
        imagecopy( $waterdest, $watermark, $w_x, $w_y, 0, 0, $water_width, $water_height);
        if ( is_file( $filename ))
            unlink( $filename );
        if ( $this->ftype == 'jpeg' )
            imagejpeg( $waterdest, $filename, 85 );
        elseif ( $this->ftype == 'png' )
            imagepng( $waterdest, $filename );
        imagedestroy( $waterdest );
     }

    public function check( $file )
    {
        $this->file = $file;
        $info = pathinfo( $this->file['name'] );
        $this->ftype = strtolower( $info[ 'extension' ] );
        if ( $this->ftype == 'jpg' )
              $this->ftype = 'jpeg';

        if ( strtolower( substr( $this->file['type'], 0, 5 )) 
           != 'image' || !in_array( $this->ftype, array( 'jpeg', 'png', 'gif' )) )
        {
            return api_error( 'No image' ); 
        }
        $size = getimagesize( $this->file['tmp_name'] );
        if ( !$size[0] || !$size[1] )
            return api_error( 'Empty image' ); 
        $this->x = $size[0];
        $this->y = $size[1];

        return true;
    }

    public function original( $filename )
    {
        $side = $this->opt['side'] == 2 || (!$this->opt['side'] && $this->y > $this->x ) ? 
                             $this->y : $this->x;
        if ( $this->opt['max'] < $side || $this->opt['min'] > $side || $this->opt['thumb'] ||
             !empty( $this->options['watermark'] ))
        {
            $load = "imagecreatefrom".$this->ftype;
            $this->src = $load( $filename );
            $this->calculate();
            if ($this->x != $this->w || $this->y != $this->h ) 
            {
                $this->dest = imagecreatetruecolor( $this->w, $this->h );
                imagecopyresampled( $this->dest, $this->src, 0,0, $this->offx, $this->offy, 
                                    $this->w, $this->h, $this->lenx, $this->leny );

                $this->savetofile( $filename );
                imagedestroy( $this->src );
                $this->src = 0;
            }
        }
        if ( !empty( $this->options['watermark'] ))
            $this->watermark( $filename );
    }

    public function finish( $idfile, $path )
    {

        $params = array('w' => $this->w, 'h' => $this->h );
        if ( $this->opt['thumb'] )
        {
            if ( !$this->src )
            {
                $this->x = $this->w;
                $this->y = $this->h;
            }
            $this->opt['max'] = $this->opt['thumb'];
            $this->opt['ratio'] = $this->opt['thumb_ratio'];
            $this->opt['side'] = $this->opt['thumb_side'];
            $this->calculate();
            $this->thumb = imagecreatetruecolor( $this->w, $this->h );
            imagecopyresampled( $this->thumb, $this->src ? $this->src : $this->dest, 0,0, 
                   $this->offx, $this->offy, $this->w, $this->h, $this->lenx, $this->leny );
            if ( $path )
                $this->savetofile( "$path/_$idfile", true );
            else
            {
                $func = 'image'.$this->ftype;
                ob_start();
                $func( $this->thumb );
                $params['preview'] = ob_get_contents();
                ob_end_clean();
            }
            $params['ispreview'] = 1;
        }

        DB::update( ENZ_FILES, $params, '', $idfile );
    }
}

