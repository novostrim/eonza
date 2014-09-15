

String.prototype.insert = function( index, string )
{
    return this.slice( 0, index ) + string + this.slice( index );
};

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
