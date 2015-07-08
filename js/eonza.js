/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

Eonza = function() {
	this.website = 'http://www.eonza.org/';
	// Link to How to restore the password
	this.resetPass = this.website + 'how-to-reset-password.html';
    // The custom text of the footer
    this.footer = '';

    this.hostname = window.location.protocol + '//' + window.location.host;
}

Eonza.prototype.DbApi = function( method, params, callback ) {
    var self = this;
    var ajaxmethod = 'post';
    if ( method[0] == '_' )
    {
        ajaxmethod = 'get';
        method = method.substr( 1 );
    }
    self.Spinner( true );
    params.nocache = new Date().getTime(); 
    $.ajax( {
//        complete: function( xhr, status ) {},
        data: params,
        dataType: 'json',
        error: function( xhr, status, error ) {
            self.Spinner( false );
            rootScope.msg_error(  lng[ 'err_server' ] + ' [' + status + ']' );
        },
        method: ajaxmethod,
        success: function( data, status, xhr ) {
            self.Spinner( false );
            rootScope.cfg.temp = data.temp;
            if ( data.success )
            {
                json2num( data );
                if ( callback )
                   callback( data );
            } 
            else
                rootScope.msg_error( data.err );
        },
        url: self.URIApi( method )
    })
}

Eonza.prototype.Spinner = function( status ) {
    rootScope.loading = status;
    rootScope.$apply();
}

Eonza.prototype.URIApi = function( apimethod, ishost )
{
    return ( ishost ? this.hostname : '' ) + cfg.appenter + 'api/' + apimethod;
}

var enz = new Eonza();

