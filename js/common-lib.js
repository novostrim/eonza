

String.prototype.insert = function( index, string )
{
    return this.slice( 0, index ) + string + this.slice( index );
};

function js_long2ip(ip) {
  if ( !ip )
    return '';
  if ( angular.isString( ip ) && ip.indexOf('.') >= 0 )
    return ip;
  return [ip >>> 24, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.');
}

function js_phone( phone )
{
    if ( parseInt( phone ) == 0 )
        return '';
    var len = phone.length;
    var result = phone;
    if ( len > 10 )
        result = result.insert( -10, ' (' ).insert( -7, ') ');
    if ( len > 4 )
        result = result.insert( -4, '-' ).insert( -2 , '-' );
    return ( len > 10 ? '+' : '' ) + result;
}

function js_required( name, resource, callback )
{
    var result = true;
    $('.' + name ).each( function(){
        if ( $(this).val() == '' )
        {
            var text = resource.replace( '#temp#', $(this).attr( name ));
            result = false;
            $(this).focus();
            return callback( text );
        }
    });
    return result;
}

function json2num( obj )
{
    for (var key in obj ) {
       if (obj.hasOwnProperty(key) && !!obj[key] ) {
            var objkey = obj[key];
            if ( objkey instanceof Array && objkey.length )
                objkey.forEach( json2num );
            else if ( objkey.constructor === Object )    
                json2num( objkey );
            else if ( typeof objkey == 'string' && objkey == Number( objkey ))
                obj[key] = Number( objkey );
        }
    }
}

function js_menuover( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','block');
}

function js_menuout( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','none');
}