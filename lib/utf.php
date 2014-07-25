<?php
/*
   Eonza
   (c) 2014 Novostrim, OOO. http://www.novostrim.com
   License: MIT
*/

define( 'UTF', 'utf-8' );

// получение символа в $pos или замена на $val 
function utf_char( $str, $pos, $val = false ) 
{
  	if ( $val !== false )
  	{
  		$out = utf_substr( $str, 0, $pos );
  		$out .= $val;
  		$out .= utf_substr( $str, $pos + 1, utf_len( $str ) - $pos - 1 );
  		return $out;
  	}
  	return utf_substr( $str, $pos, 1 );
}

/*// Поиск независимо от регистра 
function utf_find( $str, $what, $len = 3 ) // ???
{
	if ( utf_strpos( utf_lower( $str ), utf_lower( utf_substr( $what, 0, $len))) 
	           === false )
	   return false;
	return true;
}*/

function utf_len( $str )
{
	return mb_strlen( $str, UTF );
}

function utf_lower( $str )
{
	return mb_strtolower( $str, UTF );
}

function utf_strpos( $where, $what, $pos = 0 )
{
	return mb_strpos( $where, $what, $pos, UTF );
}

function utf_substr( $str, $from, $len )
{
	return mb_substr( $str, $from, $len, UTF ); 
}

// Первый символ с заглавной буквы
function utf_ucfirst( $str )
{
	return utf_char( $str, 0, utf_upper( utf_substr( $str, 0, 1 )));
} 

function utf_upper( $str )
{
	return mb_strtoupper( $str, UTF );
}
/*
function utf_pathinfo($path) 
{ 
     if (strpos($path, '/') !== false) $basename = end(explode('/', $path)); 
     elseif (strpos($path, '\\') !== false) $basename = end(explode('\\', $path)); 
     else $basename = $path; 
     if (empty($basename)) return false; 

     $dirname = substr($path, 0, strlen($path) - strlen($basename) - 1); 

     if (strpos($basename, '.') !== false) 
     { 
      	$fe = explode('.', $path);
       $extension = end( $fe ); 
       $filename = substr( $basename, 0, strlen($basename) - strlen($extension) - 1); 
     } 
     else 
     { 
       $extension = ''; 
       $filename = $basename; 
     } 

     return array 
     ( 
       'dirname' => $dirname, 
       'basename' => $basename, 
       'extension' => $extension, 
       'filename' => $filename 
     ); 
}
*/

?>