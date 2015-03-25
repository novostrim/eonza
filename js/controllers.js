geapp.controller('UploadCtrl', function ($scope, FileUploader) {
        'use strict';
        // create a uploader with options
        var uploader = $scope.uploader = new FileUploader({
            scope: $scope,                          // to automatically update the html. Default: $rootScope
            url: ajax( 'upload' ),
            removeAfterUpload: true,
            formData: [],
            queueLimit: 100
        });
        // ADDING FILTERS
        uploader.filters.push({
            name: 'customFilter',
            fn: function( item /*{File|FileLikeObject}*/, options) {
                if ( item.size > 10*1024*1024 ) {
                    var text = lng['err_filesize'].replace( '#temp#', '10 Mb' );
                    rootScope.msg_warning( text );
                    return false;
                }
                return true;
//                return this.queue.length < 10;
            }
        });

/*        uploader.filters.push(function(item ) { // user filter
            if ( item.size > 10*1024*1024 ) {
                var text = lng['err_filesize'].replace( '#temp#', '10 Mb' );
                rootScope.msg_warning( text );
                return false;
            }
            return true;
        });*/
        rootScope.uploads.push( uploader );
        // REGISTER HANDLERS
/*        uploader.bind('afteraddingfile', function (event, item) {
            console.info('After adding a file', item);
        });

        uploader.bind('whenaddingfilefailed', function (event, item) {
            console.info('When adding a file failed', item);
        });

        uploader.bind('afteraddingall', function (event, items) {
            console.info('After adding all files', items);
        });
*/
        uploader.onBeforeUploadItem = function(item) {
            item.formData.push( {idcol : item.idcol, iditem: 
                          angular.isDefined( uploader.iditem ) ? uploader.iditem : rootScope.curitem } );
           // console.info('Before upload', item);
        };

/*        uploader.bind('progress', function (event, item, progress) {
            console.info('Progress: ' + progress, item);
        });

        uploader.bind('success', function (event, xhr, item, response) {
            console.info('Success', xhr, item, response);
        });

        uploader.bind('cancel', function (event, xhr, item) {
            console.info('Cancel', xhr, item);
        });

        uploader.bind('error', function (event, xhr, item, response) {
//            item.remove();
//            console.info('Error', xhr, item, response);
        });
*/
        uploader.onCompleteItem = function(item, response, status, headers) {
            if ( !uploader.queue.length && angular.isDefined( uploader.iditem ))
                uploader.iditem = undefined;
//            console.info('Complete', xhr, item, response);
            if ( !response.success )
                rootScope.msg_error( response.err );
            else {
                if ( response.iditem == rootScope.curitem )
                {
                    Scope.form[ response.alias ] = response.result;
                    Scope.$apply();
                }
            }
        };
/*
        uploader.bind('progressall', function (event, progress) {
            console.info('Total progress: ' + progress);
        });

        uploader.bind('completeall', function (event, items) {
//            console.info('Complete all', items);
        });
*/
        // -------------------------------
        var controller = $scope.controller = {
            isImage: function(item) {
                var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                return '|jpg|png|jpeg|gif|'.indexOf(type) !== -1;
            }
        };

 });


geapp.controller( 'IndexCtrl', function IndexCtrl($scope, $http, $routeSegment ) {
    $scope.isroot = cfg.user.id == 1;
    if ( $scope.isroot )
        $scope.menu = [
            { title: lng.tables, icon: 'table', href: '#/', name: 'tables'},
            { title: lng.sets, icon: 'list-alt', href: '#/sets', name: 'sets'},
            { title: lng.menu, icon: 'th-list', href: '#/menu', name: 'menu'},
            { title: lng.admin, icon: 'wrench', href: '#/appsettings', name: 'admin'},
    //        { title: lng.settings, icon: 'cogs', href: '#/appsettings', name: 'appsettings'},
        ];
    else
        $scope.menu = [];
    $scope.$routeSegment = $routeSegment;
//    $state.transitionTo('index.menu');
//    $state.go('index.menu');
});

geapp.controller( 'InstallCtrl', function InstallCtrl($scope, $http ) {
    $scope.langlist = langlist;
    $scope.form = { dbhost: 'localhost',
                    storage: cfg.appenter + 'storage' };
    $scope.submit = function() {
        $http.post( cfg.appdir + 'ajax/waccess.php', { path: cfg.appenter }).success(function(data) {
            if ( data.success )
            {
                $http.post( cfg.appdir + 'ajax/install.php', { form: $scope.form, 
                    lang: $scope.lng.code, path: cfg.appenter }).success(function(data) {
                    if ( data.success )
                    {
                        cfg.user = data.user;
                        document.location = '';
//                        document.location = '/';
                    }
                    else
                    {
                        cfg.temp = data.temp;
                        $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                                         ' [' + data.code + ']' : '' ) );
                    }
                })
            }
            else
            {
                cfg.temp = data.temp;
                $scope.msg_error( $scope.lng[ data.err ] + ( data.err == 'err_system' ? 
                             ' [' + data.code + ']' : '' ) );
            }
        })
    }
});

geapp.controller( 'LoginCtrl', function LoginCtrl($scope, $http ) {
//    $scope.login_top = rootScope.cfg.login_top || '';
    $scope.form = {};
    $scope.submit = function() {
        $http.post( /*'/admin/ajax.php?request=login'*/ajax('login'), { form: $scope.form }).success(function(data) {
            if ( data.success )
                document.location = '';
            else
                $scope.msg_error( $scope.lng[ data.err ] );
        })
    }
});

geapp.controller( 'SettingsCtrl', function SettingsCtrl( $scope, DbApi, $rootScope ) {
    $scope.langlist = langlist;
    $scope.form = { title: cfg.title, login: cfg.user.login, email: cfg.user.email, apitoken: cfg.apitoken };    
    $scope.language = function()
    {
        DbApi( 'saveusr', { lang: $scope.form.lang }, function( data ) {
                        $scope.$parent.changelng( $scope.form.lang ) });
    }
});

function TablesCtrl($scope, $rootScope, $routeSegment, DbApi ) {
    $scope.$routeSegment = $routeSegment;
    $scope.isalias = cfg.isalias;
    $scope.parent = angular.isDefined( $routeSegment.$routeParams.id ) ? $routeSegment.$routeParams.id : 0;
    $scope.droptable = function( index ) {
        cfg.temp = $scope.items[ index].title;
        $rootScope.msg_quest( $scope.items[ index].isfolder == 1 ? 'delfld':'deldb', function(){ 
            DbApi( 'droptable', { id: $scope.items[index].id }, function( data ) {
                $scope.items.splice( index, 1 ) });
            });
        return false;
    }
    $scope.export2set = function( index ) {
        cfg.temp = ' ' + lng.export2set +' ' + $scope.items[ index].title;
        $rootScope.msg_quest( 'sure', function(){ 
            DbApi( 'export2set', { id: $scope.items[index].id }, function( data ) {
                if ( data.success )
                {
                    document.location = '#/set?id=' + data.success;
                } });
            });
        return false;
    }
    $scope.truncate = function( index ) {
        cfg.temp = $scope.items[ index].title;
        $rootScope.msg_quest( 'truncatedb', function(){ 
            DbApi( 'truncatetable', { id: $scope.items[index].id }, function( data ) {
                document.location = '#/table?id=' + data.success;
            });
         });
        return false;
    }
    $scope.importdata = function( newval, callback ) {
        $rootScope.form.importdata = newval;
        callback();
    }
    $scope.duplicate = function( index ) {
        $rootScope.form = { importdata: 1, id: $scope.items[ index].id, 
                            source: $scope.items[ index].title,
                            dest: $scope.items[ index].title + ' - ' + lng.copysuf };
        $rootScope.msg( { title: lng.duplicate, template: tpl('tabledup.html'),
                   btns: [ {text: lng.duplicate, func: function(){
                         DbApi( 'duptable', $rootScope.form, function( data ) {
                            if ( data.success )
                                document.location = '#/edittable?id='+ data.success;
                        })             
                   }, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  });
    }
    $scope.changefld = function( index ) {
        DbApi( 'gettree', { dbname: 'tables', id: $scope.items[ index].id }, function( data ) {
            if ( data.success )
            {
                $rootScope.tablefld = data.result;
                $rootScope.form = { idparent: $scope.items[ index].idparent, id: $scope.items[ index].id,
                                      dbname: 'tables' };
                $rootScope.msg( { title: lng.changefld, template: tpl('changefld.html'),
                   btns: [ {text: lng.savejs, func: function(){
                         DbApi( 'changefld', $rootScope.form, function( data ) {
                            if ( data.success )
                                document.location = '#/?id='+ $rootScope.form.idparent;
                        })             
                   }, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  })
            }
        })
    }
    $scope.savefolder = function() {
        if ( $rootScope.form.title == '' )
        {
            $rootScope.msg_warning('war_ename');
            return false;
        }
        DbApi( 'savefolder', $scope.form, function( data ) {
            if ( data.success )
                if ( $rootScope.form.id )
                    $scope.items[$rootScope.form.index].title = data.result.title;
                else
                    document.location = '#/?id=' + data.success;
        });
        //alert($rootScope.form.cur);
    }
    $scope.editfolder = function( index ) {
        var add = angular.isUndefined( index );
        function foldermsg() {
           $rootScope.msg( { title: add ? lng.newfld : lng.editfld, template: tpl('dlgfolder.html'),
                   btns: [ {text: add ? lng.add : lng.savejs, func: $scope.savefolder, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  })
        }
        if ( add )
        {
            DbApi( 'gettree', { dbname: 'tables' }, function( data ) {
                if ( data.success )
                {
                    $rootScope.tablefld = data.result;
                    $rootScope.form = {title: '', idparent: $scope.parent, id: 0 };
                    foldermsg();
                }
            })
        }
        else
        {
            obj = $scope.items[index];
            $rootScope.form = {title: obj.title, idparent: obj.idparent, id: obj.id,
                                index: index };
            foldermsg();
        }
    }
    if ( $scope.isroot )
    DbApi( 'gettables', { parent: $scope.parent }, function( data ) {
        $scope.items = data.result;
        if ( angular.isDefined( data.crumbs ))
        {
            $scope.crumbs = [{ id:0, title: $rootScope.lng.rootfolder }].concat( data.crumbs );
        }
        else
            $scope.crumbs = [];
    });
}

function TableCtrl($scope, $routeSegment, DbApi, $rootScope, $sce /*, $cookies*/ ) {
    $scope.$routeSegment = $routeSegment;
    $scope.allselect = false;
    $scope.mode = cnt.M_LIST;   
    $scope.currow = 0; // the index of the current item
    $scope.offset = 0; // offset of the first item
    $scope.allcount = 0; // summary count 
    $rootScope.curitem = 0; // the latest current item (id)
    Scope = $scope;
    $rootScope.uploads = [];
    $scope.params = $routeSegment.$routeParams;
    if ( angular.isUndefined( $scope.params.p ))
        $scope.params.p = 1;
    $scope.listitems = '';
    $scope.edititems = '';
    $scope.viewitems = '';
    $scope.carditems = '';
    $scope.compare = compare;
    $scope.logic = logic;
    $scope.filter = [];
    $scope.fltfields = [];
//    $scope.cookies= $cookies;

    $rootScope.cheditform = function( obj, callback ) {
        for(var k in obj)
        {
            $scope.form[k] = obj[k];
            break;
        }
        callback();
    }
    $scope.treemode = function() {
        if ( angular.isDefined( $scope.params.parent ))
        {
            $scope.params.parent = undefined;
            $scope.crumbs = [];
        }
        else
            $scope.params.parent = 0;
        $scope.update();
    }
    $scope.treechange = function( id ) {
        Scope.params.parent = id;
        Scope.update();
    }
    $scope.ismask = function( id, mask ) {
        return $scope.mask[id] & mask ? true: false;
    }
    $scope.iscustom = function( id, custom ) {
        var ind = colindex(id);
        var idtype = ( ind >=0 ? $scope.columns[ ind ].idtype : 0 );
        if ( idtype == cnt.FT_ENUMSET && custom == cnt.FT_SETSET )
            custom = cnt.FT_ENUMSET;
        return idtype == custom;
    }    
    $scope.fltalias = function(id) {
        var ind = colindex(id);
        return ind >=0 ? $scope.columns[ ind ].alias : '';
    }
    $scope.columns = function() {
        DbApi( 'columns', $scope.params, function( data ) {
            if ( data.success )
            {
                if ( data.db.title[0] == ':' )
                    data.db.title = lng[ data.db.title.substr( 1 ) ];

                $scope.db = data.db;
                $scope.columns = data.columns;
                var i = 0;
                $scope.collist = [];
                $scope.fltfields = [ {title: '', id: 0, mask: 0 }, {title: 'ID', id: -1, mask: 0x07 } ];
                $scope.mask = {};
                $scope.colnames = {};
                var listitems = '';
                var viewitems = '';
                while ( i <  $scope.columns.length )
                {
                    var column = $scope.columns[i];
                    if ( column.idtype == cnt.FT_PARENT )
                        column.title = lng.parent;
                    if ( column.title[0] == ':' )
                        column.title = lng[ column.title.substr( 1 ) ];
                    if ( column.idtype == cnt.FT_ENUMSET || column.idtype == cnt.FT_SETSET )
                    {
                        for ( var ikey in column.list )
                            if ( column.list[ ikey ][0] == ':' )
                                column.list[ ikey ] = lng[ column.list[ ikey ].substr( 1 )];
                    }
                    column.number = angular.isDefined( types[column.idtype].number );
                    column.class += $rootScope.aligns[ column.align] + ' ';
                    if ( parseInt( column.visible ) > 0 )
                        $scope.collist.push( column );
                    $scope.colnames[ column.alias ] = column;
                    listitems += js_editpattern( i, $scope.columns );
                    viewitems += js_viewpattern( i, $scope.columns );
                    if ( angular.isDefined( types[column.idtype].filter ))
                    {
                        var filter = types[column.idtype].filter;
                        var mask = filter.mask;
                        if ( mask == 0xffff )
                            mask = filter.extmask[ column.extend[ filter.extend ]];
                        if ( mask > 0 )
                            $scope.fltfields.push( { title: column.title, id: column.id, 
                                mask: mask } ); 
                    }
                    i++;
                }
                i = 0;
                while ( i <  $scope.fltfields.length )
                {
                    $scope.mask[ $scope.fltfields[i].id ] = $scope.fltfields[i].mask;
                    i++;
                }
                $scope.listitems = listitems + js_editpatternbottom();
                $scope.viewitems = viewitems + js_viewpatternbottom();
                $scope.update();
            }
        });
    }
    $scope.pstnew = function( action ){
        $rootScope.postnew = action;
    }
    $scope.pstedit = function( action ){
        $rootScope.postedit = action;
    }
/*    $scope.clearuploads = function() {
        for ( i = 0; i < $rootScope.uploads.length; i++ )
        {
            $rootScope.uploads[i].clearQueue();   
        }
    }*/
    $scope.delfile = function( id ) {
        $rootScope.msg_quest( 'delattach', function(){ 
            DbApi( 'delfile', {id: id}, function( data ) {
                if ( data.success )
                    $scope.form[ data.alias ] = data.result;
            })
        })
        return false;
    }
    $scope.linkpage = function( direct ) {
        var offset = direct == 0 ? 0 : Scope.link.offset + direct*15;
        DbApi( 'getlink', {offset: offset, id: Scope.columns[ rootScope.idlink ].id,
                   search: rootScope.link.search, 
                   filter: rootScope.link.filter, parent: Scope.link.parent }, 
                 function( data ) {
            if ( data.success )
            {
                Scope.columns[ rootScope.idlink ].link = data.result;
                rootScope.link = Scope.columns[ rootScope.idlink ].link;
            }
        })
    }
    $scope.linkparent = function( parent ) {
        Scope.link.parent = parent;
        $scope.linkpage( 0 );
    }
    $scope.editdate = function( date, idcol ) {
        if ( angular.isString( date ))
        {
            var pars = $scope.form[ $scope.columns[ idcol ].alias ].split(' ');
            pars[0] = date;
            $scope.form[ $scope.columns[ idcol ].alias ] = pars.join( ' ' );
            $scope.$apply();
        }
        else
        {
            var itime = moment();
            if ( date > 5 && !!$scope.form[ $scope.columns[ idcol ].alias ] )
            {
                itime = moment( $scope.form[ $scope.columns[ idcol ].alias ], 'YYYY-MM-DD' );
                if ( !itime.isValid())
                    itime = moment();
            }
            switch ( date )
            {   
                case 1: itime.subtract(1, 'days' ); break;
                case 3: itime.add(1, 'days' ); break;
                case 4: $scope.form[ $scope.columns[ idcol ].alias ] = itime.format('YYYY-MM-DD HH:mm'); return false;
                case 5: $scope.form[ $scope.columns[ idcol ].alias ] = ''; return false;
                case 6: itime.add( 1, 'days' ); break;
                case 7: itime.add( 1, 'weeks' ); break;
                case 8: itime.add( 1, 'months' ); break;
                case 9: itime.add( 1, 'years' ); break;
            }
            $scope.form[ $scope.columns[ idcol ].alias ] = itime.format('YYYY-MM-DD');
        }
        return false;
    }
    $scope.filterdate = function( fltindex, e ) {
        js_editdate( e.currentTarget, fltindex, true );
    }
    $scope.editlink = function( idcol, idfilter, fltindex ) {
        if ( idfilter )
            idcol = colindex( idfilter );
        $rootScope.link = $scope.columns[idcol].link;
        $rootScope.link.filter = 0;
        $rootScope.idlink = idcol;  
        $rootScope.fltindex = fltindex;
        if ( !idfilter && parseInt( $scope.columns[idcol].extend.filter ) > 0 )
        {
            for ( var i = 0; i < $scope.columns.length; i++ )
                if ( angular.isDefined( $scope.columns[i].extend.table ) &&
                     parseInt($scope.columns[i].extend.table) == parseInt( $scope.columns[idcol].extend.filter ))
                {
                    if ( $scope.form[ $scope.columns[i].alias ] == 0 )                    
                    {
                        cfg.temp = $scope.columns[i].title;
                        $rootScope.msg_warning( 'war_efilter' );
                        return false;
                    }
                    else
                    {
                        $rootScope.link.filter = parseInt( $scope.form[ $scope.columns[i].alias ] ) + ':' +
                                                    parseInt($scope.columns[idcol].extend.filtercol);
                        $scope.linkpage( 0 );
                    }
                }
        }
        $rootScope.msg( {  title: $scope.columns[idcol].title, template: tpl('editlink.html'),
                       btns: 
                       [ {text: lng.clear, func: function(){
                            if ( !idfilter )
                            {
                                var alias = $scope.columns[idcol].alias;
                                $scope.form[ alias ] = 0;
                                $scope.formlink[ alias ] = '';
                                $scope.view[ alias ] = '';
                            }
                       }, class: 'btn-default btn-near' },
                       {text: lng.close, class: 'btn-primary btn-near' } ] });
        return false;
    }    
    $scope.editfile = function( id ) {
        var i = 0;
        var form = ( $scope.mode == cnt.M_EDIT ? $scope.form : $scope.view );
        var data;
        while ( i <  $scope.columns.length && angular.isUndefined( data ))
        {
            var column = $scope.columns[i];
            if ( column.idtype == cnt.FT_FILE || column.idtype == cnt.FT_IMAGE )
            {
                for ( var k = 0; k < form[ column.alias ].length; k++ )
                    if ( form[ column.alias ][k].id == id )
                    {
                        data = form[ column.alias ][k];
                        break;
                    }    
            }
            i++;
        }
        if ( angular.isDefined( data ))
        {
            $rootScope.form = data;//angular.copy( data );
            $rootScope.msg( { title: lng.fileinfo, template: tpl('editfile.html'),
                       btns: $scope.mode == cnt.M_EDIT ?
                       [ {text: lng.savejs, func: function(){
                             DbApi( 'editfile', $rootScope.form, function( data ) {
                                if ( data.success )
                                {
                                    //data = $rootScope.form;
                                }
                            })             
                       }, class: 'btn-primary btn-near' },
                               {text: lng.cancel, class: 'btn-default btn-near' }
                   ] : [ {text: lng.close, class: 'btn-primary btn-near' }
                   ] });
        }
        return false;
    }
    $scope.saveitem = function(){
        htmleditor( $scope.form, true );
        DbApi( 'saveitem', $scope.form, function( data ) {
            if ( data.success )
            {
                $rootScope.curitem = data.success;
                $scope.proceedform( data.result );
                $scope.action = lng.savejs;
                for ( i = 0; i < $rootScope.uploads.length; i++ )
                {
                    if ( $rootScope.uploads[i].queue.length > 0 )
                    {
                        $rootScope.uploads[i].iditem = data.result.id;
                        $rootScope.uploads[i].uploadAll();   
                    }
                }
                function saveok()
                {
                    nfy_info( $scope.mode == cnt.M_EDIT ? lng.itemupdated : lng.itemadded );
//                    $scope.formtoview();
//                    $scope.clearuploads();
                    js_formtolist( $scope.mode == cnt.M_EDIT ? $scope.currow : 0 );
                    var mode = $scope.mode == cnt.M_EDIT ? $rootScope.postedit : $rootScope.postnew;
                    if ( mode == cnt.M_NEW )
                        $scope.loaditem();
                    else
                        if ( mode < cnt.M_NEW )                    
                           $scope.setmode( mode );
                        else
                        {
                            $scope.setmode( cnt.M_EDIT );
                            $scope.move( mode == cnt.M_PREV ? -1 : 1 );
                        }
                }
                saveok();
            }
        });
    }    
    $scope.formtoview = function( columns ) {
        if ( angular.isUndefined( columns ))
            columns = $scope.columns;
        $scope.view = { id: $scope.form.id };
        var i = columns.length;
        while ( i-- )
        {
            var icol = columns[i];
            var alias = icol.alias;

            $scope.view[ alias ] = '';
            switch ( parseInt( icol.idtype ))
            {
                 case cnt.FT_ENUMSET:
                    var idi = parseInt( $scope.form[alias] );
                    if ( idi > 0 && angular.isDefined( icol['list'][idi] ))
                        $scope.view[ alias ] = icol['list'][idi];
                    break;
                case cnt.FT_DATETIME:
                    $scope.view[ alias ] = js_moment( $scope.form[alias], icol.extend.date );   
                    break;
                case cnt.FT_LINKTABLE:
                case cnt.FT_PARENT:
                    if ( $scope.formlink[ alias ].length > 0 )
                        $scope.view[ alias ]  = '<span class="setitem">' + $scope.formlink[alias] + '</span>';
                    break;
                case cnt.FT_SETSET:
                    $scope.view[ alias ]  = '<span class="setitem">' + js_getset( $scope.form[alias], alias ).join('</span><span class="setitem">') + '</span>';
                    break;                        
                case cnt.FT_FILE:
                case cnt.FT_IMAGE:
                    $scope.view[ alias ] = $scope.form[ alias ];
                    break;
                case cnt.FT_CHECK:
                    $scope.view[ alias ] = $scope.form[ alias ] == '1' ? lng.yes : lng.no;
                    break;
                case cnt.FT_SPECIAL:
                    switch ( parseInt( icol.extend.type ))
                    {
                        case cnt.FTM_WEBSITE:
                            var url = ( $scope.form[ alias ].substr( 0, 4 ) == 'http' ? '' : 'http://' ) + $scope.form[ alias ];
                            $scope.view[ alias ] = '<a href="'+ url +'" >' + $scope.form[ alias ] + '</a>';
                            break;
                        case cnt.FTM_EMAIL:
                            $scope.view[ alias ] = '<a href="mailto:'+ $scope.form[ alias ] +'" >' + $scope.form[ alias ] + '</a>';
                            break;
                        case cnt.FTM_PHONE:
                            var phone = js_phone( $scope.form[ alias ] );

                            $scope.view[ alias ] = phone.length > 0 ? '<a href="tel:+'+ $scope.form[ alias ] +'" class="phonelink">' + 
                                        phone + '</a>' : phone;
                            break;
                        case cnt.FTM_IPV4:
                            $scope.view[ alias ] = js_long2ip( $scope.form[ alias ] );
//                            if ( $scope.form[ alias ] != $scope.view[ alias ] )
//                                $scope.form[ alias ] = $scope.view[ alias ];
                            break;                            
                    }
                    break;
                default:
                    $scope.view[ alias ] = $scope.form[ alias ];
                    if ( $scope.view[ alias ] == '0' && icol.number )
                        $scope.view[ alias ] = '';
//                    else
//                        $scope.view[alias] = $sce.trustAsHtml( $scope.view[alias] );
                    break;
            }
        }
    }
    $scope.proceedform = function( data ) {
        $scope.form = data;
        var i = $scope.columns.length;
        while ( i-- )
        {
            var icol = $scope.columns[i];
            if ( types[icol.idtype].form )
                types[icol.idtype].form( $scope.form, icol );
        }
    }
    $scope.loaditem = function() {
        if ( $scope.mode != cnt.M_NEW )
            $rootScope.curitem = $scope.items[ $scope.currow - 1 ].id;
        else
        {
            $scope.form = { id: 0, table: $scope.db.id };
            $scope.formlink = {};
            var i = $scope.columns.length;
            while ( i-- )
            {
                var icol = $scope.columns[i];
                $scope.form[ icol.alias ] = icol.number ? 0 : '';
                if ( icol.idtype == cnt.FT_LINKTABLE )
                    $scope.formlink[ icol.alias ] = '';
                if ( icol.idtype == cnt.FT_PARENT )
                    $scope.formlink[ icol.alias ] = '';
            }
            $rootScope.curitem = 0;
            $scope.action = lng.add;            
            htmleditor( $scope.form );
//            if ( $scope.mode == cnt.M_VIEW )
//                $scope.formtoview();
            return;
        }
        if ( angular.isDefined( $scope.form ) && $rootScope.curitem == $scope.form.id )
            return;
//        $scope.form = $scope.items[ $scope.currow - 1 ];
        DbApi( 'getitem', {id: $rootScope.curitem,
                 table: $routeSegment.$routeParams.id }, function( data ) {
            if ( data.success )
            {
                $scope.proceedform( data.result );
                $scope.action = $scope.form.id != 0 ? lng.savejs : lng.add;
                $scope.formlink = data.link;

                if ( $scope.mode == cnt.M_VIEW )
                    $scope.formtoview();
                htmleditor( $scope.form );
            }
        });
    }

    $scope.setmode = function( mode ) { 
        if ( $scope.mode == mode )
            return;
        if ( $scope.mode == cnt.M_CARD )
            $scope.cardback();
        $scope.mode = mode;
        // For example, if we use CKeditor, ckeditor.js has not been loaded before compiling lisitems.
        if ( $scope.edititems == '' )
            $scope.edititems = $scope.listitems;
        if ( mode )
        {
            $scope.loaditem();
        }
        if ( mode == cnt.M_LIST )
        {
            $("#card").hide();
            $("#list").show();
        }
        else
        {
            $("#list").hide();
            if ( mode != cnt.M_VIEW )
            {
                $("#cardview").hide();
                $("#cardedit").show();
            }
            else
            {
                if ( $scope.mode == cnt.M_VIEW && angular.isDefined( $scope.form ))
                    $scope.formtoview();
                $("#cardedit").hide();
                $("#cardview").show();
            }
            $("#card").show();
        }
    };
    $scope.card = function( idtable, iditem )
    {
        $scope.prevmode = $scope.mode;
        $scope.mode = cnt.M_CARD;
        $("#list").hide();
        $("#card").hide();
        $scope.carditems = '';
        $scope.prevcolumns = angular.copy( $scope.columns );
        $scope.prevform = angular.copy( $scope.form );
        $scope.prevformlink = angular.copy( $scope.formlink );
        $scope.prevview = angular.copy( $scope.view );
        DbApi( 'columns', {id: idtable}, function( data ) {
            if ( data.success )
            {
                var i = 0;
                var columns = data.columns; 
                $scope.colnames = {};
                while ( i <  data.columns.length )
                {
                    var column = data.columns[i];
                    if ( column.idtype == cnt.FT_PARENT )
                        column.title = lng.parent;
                    column.number = angular.isDefined( types[column.idtype].number );
                    column.class += $rootScope.aligns[ column.align] + ' ';
//                    $scope.colnames[ column.alias ] = column;
                    $scope.carditems += js_viewpattern( i, data.columns, 'card' );
                    i++;
                }
                $scope.columns = columns;
                DbApi( 'getitem', {id: iditem, table: idtable }, function( data ) {
                    if ( data.success )
                    {
                        $scope.proceedform( data.result );
                        $scope.formlink = data.link;
                        $scope.formtoview( columns );
                    }
                });
                $("#linkcard").show();
            }
        });
    }
    $scope.cardback = function()
    {
        $scope.columns = $scope.prevcolumns;
        $scope.form = $scope.prevform;
        $scope.formlink = $scope.prevformlink;
        $scope.view = $scope.prevview;
        $scope.mode = $scope.prevmode;
        if ( $scope.mode == cnt.M_LIST )
            $("#list").show();
        else
            $("#card").show();
        $("#linkcard").hide();
    }
    $scope.move = function( shift ) { // -1 or 1
//        $scope.clearuploads();
        if ( shift > 0 )
        {
            if ( $scope.currow < $scope.items.length )
                $scope.currow++;
            else
            {
                if ( $scope.pages.count == 1 )
                    $scope.currow = 1;
                else
                {
                    $scope.params.p = $scope.pages.curpage < $scope.pages.count ? $scope.params.p + 1 : 1;
                    $scope.update();
                }
            }
        }
        else
            if ( $scope.currow > 1 )
                $scope.currow--;
            else
            {
                if ( $scope.pages.count == 1 )
                    $scope.currow = $scope.items.length;
                else
                {
                    $scope.params.p = $scope.pages.curpage > 1 ? $scope.params.p - 1 : $scope.pages.count;
                    $scope.update( true );
                }
            }
        $scope.loaditem();
    }
    $scope.list = function() {
        var list = $("#mainlist");

        list.html('');
        var htmlitem = '<tr><th class="thead" style="width: 50px;"></th><th class="thead" ><input type="checkbox" onchange="js_listallcheck(this)"></th>';
        for ( var k=0; k< $scope.collist.length; k++ )    
        {
            var thclass = '';
            var arrow = '';
            var title = angular.copy( $scope.collist[k].title );

            if ( $scope.collist[k].idtype == cnt.FT_PARENT && angular.isDefined( $scope.params.parent ) )
                title = '';
            if ( $scope.collist[k].id ==  Math.abs( $scope.params.sort )) 
            {
               thclass = ' sorted';
               arrow = '&nbsp;<i class="fa fa-fw '+ ( $scope.params.sort > 0 ? 'fa-long-arrow-down' :
                'fa-long-arrow-up' ) + '"></i>';
            }
            htmlitem += '<th class="thead'+thclass+'"><a href="#" onclick="return js_listsort('+k+', this );">'+ title+'</a>'+arrow+'</th>';
        }
        list.append( htmlitem + '</tr>' );
        htmlitem = '';
        for (var i = 0; i < $scope.items.length; i++ )
            js_listappend( i, list );
        if ( $scope.total.result )
            js_summary( $scope.total, list, 'calculator' );
    }
    $scope.summary = function( flag ) {
        $scope.params.sum = $scope.params.sum & flag ? $scope.params.sum & ~flag :
                    $scope.params.sum | flag;
        if ( $scope.params.sum )
            $scope.update(); 
        else
        {
            $scope.total.result = undefined;
            $("#intotal").remove();
        }
        return false;  
    }
    $scope.listedit = function( id ) {
        $scope.currow = id + 1;
        $scope.setmode( cnt.M_EDIT ); 
        return false;  
    }
    $scope.listdel = function( id ) {
        var idi = angular.isArray( id ) ? id : $scope.items[ id ].id;
        var textmsg = 'delitem';
        if ( $scope.db.istree )
            if ( angular.isArray( id ) )
            {
                var length = $scope.items.length;
                while ( --length )
                {
                    if ( parseInt( $scope.items[length]._children ) > 0 && 
                            id.indexOf( $scope.items[length].id ) >= 0 )
                    {
                        textmsg = 'delchildren';
                        break;
                    }
                }
            }
            else
                if ( parseInt( $scope.items[ id ]._children ) > 0 )
                    textmsg = 'delchildren';

        $rootScope.msg_quest( textmsg, function(){ 
            DbApi('dropitem', { id: idi, idtable: $scope.db.id }, function( data ) {
                if ( data.success )
                    if ( $scope.items.length == 1 || angular.isArray( idi ))
                        $scope.update();
                    else
                    {
                        $scope.items.splice( id, 1 );
                        $scope.allcount--;
                        $scope.list();
//                    jQuery("#"+id).remove();
                        nfy_info( lng.itemdel );
                    }
                });
        });   
        return false;
    }
    $scope.fltadd = function() {
        $scope.filter.push({field: 0, not: false, compare: 1 , value: '', logic: 2});
    }
    $scope.fltdel = function( ind ) {
        $scope.filter.splice( ind, 1 );
    }
    $scope.fltclear = function() {
        $scope.filter = [];//[{ logic: 0, field: 0, not: false, compare: 0, value: '' }];
        $scope.update();
    }
    $scope.update = function( latest ) {
        if ( $scope.mode == cnt.M_CARD )
            $scope.cardback();
        var filter = [];
        var i = 0;
        while ( i < $scope.filter.length )
        {
            var flt = $scope.filter[i];
            if ( flt.compare && flt.field )
            {            
                var fpar = parseInt( flt.logic ).toString(16) + ( flt.not ? '1' : '0' ) + dec2hex( parseInt( flt.compare ), 2 )
                     + ( parseInt( flt.field ) < 0 ? 'f' + dec2hex( -parseInt(flt.field), 3 ) : dec2hex( parseInt(flt.field), 4 )) + flt.value;
                filter.push( fpar );
            }
            i++;
        }
        $scope.params.filter = filter.length ? filter.join('!') : undefined;
        DbApi( 'table', $scope.params, function( data ) {
            if ( data.success )
            {
                if ( data.db.title[0] == ':' )
                    data.db.title = lng[ data.db.title.substr( 1 ) ];
                $scope.db = data.db;
                $scope.allcount = data.pages.found;
                $scope.offset = data.pages.offset;
                $scope.currow = data.result.length ? 1 : 0;
                i = data.result.length;
                while ( i-- )
                {
                    if ( data.result[i].id == $rootScope.curitem )
                        $scope.currow = i + 1;
                    js_list( data.result[i] );
                }
                $scope.items = data.result;
                $scope.pages = data.pages;
                $scope.total = data.total;
                $scope.filter = data.filter;
                if ( angular.isDefined($scope.params.parent))
                    $scope.crumbs = data.crumbs;
                if ( angular.isDefined( latest ))
                    $scope.currow = $scope.items.length;
                
                js_page();
                $scope.list();

                if ( $scope.mode > cnt.M_LIST )
                    $scope.loaditem();

    /*            i = $scope.items.length;
                while ( i-- )
                {
                    if ( angular.isDefined(  $scope.selectlist[ $scope.items[i].id ]))
                        $scope.items[i].selected = true;
                    $scope.$watch('items['+i+'].selected', function(){
                        alert( i );//$scope.items[ind].selected  );
                    });
                }*/
            }
        });
    }
    $scope.listdup = function( id ) {
        var idi = $scope.items[ id ].id;
        DbApi( 'dupitem', { id: idi, idtable: $scope.db.id }, function( data ) {
            if ( data.success )
            {
                nfy_info( lng.itemadded );
                $rootScope.curitem = data.success;
                $scope.form = data.result;
//                $scope.formtoview();
                js_formtolist( 0 );
                $scope.setmode( cnt.M_EDIT );
            }
        });
        return false;
    }
    $scope.over = 0;
    $scope.$watch('currow', function(){
        $scope.allcur = parseInt($scope.offset) + parseInt( $scope.currow ); // for EDIT & VIEW
        jQuery(".currow").removeClass('currow');
        var ind = $scope.currow - 1;
        var td = jQuery('#' + ind ).children();
        jQuery(".currow").removeClass('currow');
        td.eq(0).addClass('currow');
        td.eq(1).addClass('currow');
    });
    $scope.columns();
/*    $scope.$watch('allselect', function(){
       var i = $scope.items.length;
       while ( i-- )
          $scope.items[i].selected = $scope.allselect;
    });*/
}

/*geapp.module('compile', [], function($compileProvider) {
    // configure new 'compile' directive by passing a directive
    // factory function. The factory function injects the '$compile'
    $compileProvider.directive('compile', function($compile) {
      // directive factory creates a link function
      return function(scope, element, attrs) {
        scope.$watch(
          function(scope) {
             // watch the 'compile' expression for changes
            return scope.$eval(attrs.compile);
          },
          function(value) {
            // when the 'compile' expression changes
            // assign it into the current DOM
            element.html(value);
 
            // compile the new DOM and link it to the current
            // scope.
            // NOTE: we only compile .childNodes so that
            // we don't get into infinite loop compiling ourselves
            $compile(element.contents())(scope);
          }
        );
      };
    })
 });*/
 
/*
function EdititemCtrl($scope, $routeSegment, DbApi, $rootScope ) {
    $scope.$routeSegment = $routeSegment;
    DbApi[ 'edititem' ]( $routeSegment.$routeParams, function( data ) {
        if ( data.success )
        {
            $scope.db = data.db;
            $scope.columns = data.columns;
            $scope.listitems = data.listitems;
            $scope.form = data.result;
            if ( $scope.form.id != 0 )
            {
                $scope.action = lng.savejs;
            }
            else
                $scope.action = lng.add;
        }
    });
    $rootScope.cheditform = function( obj, callback ) {
        for(var k in obj)
        {
            $scope.form[k] = obj[k];
            break;
        }
        callback();
    }
    $scope.saveitem = function(){
//        alert( angular.toJson( $scope.form ));
       DbApi[ 'saveitem' ]( $scope.form, function( data ) {
            if ( data.success )
            {
                document.location = '#/edititem?table='+$scope.form.table+'&id=' + data.success;
            }
        });
    }
}
*/
function MenuCtrl($scope, $routeSegment, DbApi, $rootScope ) {
    $scope.$routeSegment = $routeSegment;
    $scope.savemenu = function() {
        $rootScope.savemenu();
    }
    $scope.editfolder = function( ind, isitem ) {
        var add = angular.isUndefined( ind );
        var it = angular.isDefined( isitem );
        function foldermsg() {
            if ( it )
                title = add ? lng.newitem : lng.edititem;
            else
                title = add ? lng.newfld : lng.editfld;
           $rootScope.msg( { title: title, template: tpl( it ? 'dlgmenu.html' : 'dlgfolder.html' ),
                   btns: [ {text: add ? lng.add : lng.savejs, func: $scope.savemenu, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  })
        }
        if ( add )
        {
            DbApi( 'gettree', { dbname: 'menu' }, function( data ) {
                if ( data.success )
                {
                    $rootScope.tablefld = data.result;
                    $rootScope.form = {title: '', idparent: 0, id: 0, isfolder: it ? 0 : 1 };
                    if ( it )
                        angular.extend( $rootScope.form, { url:'', hint:'' } );
                    foldermsg();
                }
            })
        }
        else
        {
            obj = $rootScope.indmenu[ind];
            $rootScope.form = {title: obj.title, idparent: obj.idparent, id: obj.id, 
                               index: ind, isfolder: obj.isfolder };
            if ( it )
                angular.extend( $rootScope.form, { url: obj.url, hint: obj.hint } );    
            foldermsg();
        }
    }
    $scope.editmenu = function( index ) {
        $scope.editfolder( index, true );
    }
    $scope.edit = function( index ) {
        var i = $rootScope.indmenu.length;
        while ( i-- )
        {
            if (  $rootScope.indmenu[i].id == index )
            {
                obj = $rootScope.indmenu[i];
                break;
            }
        }
        if ( obj.isfolder != 0 )
            $scope.editfolder( i );
        else
            $scope.editmenu( i );
    }
    $scope.delmenu = function( id ) {
        $rootScope.msg_quest( 'delitem', function(){ 
            DbApi( 'delmenu', { id: id }, function( data ) {
                if ( data.success )
                {
                    $rootScope.indmenu = data.result;
                    $rootScope.loadmenu(); 
                }
             })
        })
    }
    $scope.sortfield = function( val ) {
        jQuery( "#fields" ).sortable( { disabled: val } );
    }
    $scope.sortok = function( id ) {
        $scope.moveid = id;
        var i = $rootScope.indmenu.length;
        while ( i-- ) {
            if ( $rootScope.indmenu[i].id == id && $rootScope.indmenu[i].isfolder &&
                 $rootScope.indmenu[i].expand )
            {
                $rootScope.indmenu[i].expand = false;
                break;
            }
        }
        $scope.sortfield( false );
    }
    jQuery( "#fields" ).sortable( { axis: "y", helper: function( event, ui ){ 
         return $('<div class="striphelper">&nbsp;</div>'); },
            forceHelperSize: true,
            disabled: true,
//            forcePlaceholderSize: true,
//            placeholder: "placeholder",
            cursor: "move",
            update: function( event, ui ) { 
                var prev = 0, next = 0, found = 0;
                $("#fields .imenulist:visible").each(function(index) {
                    var item = $(this);
                    var ind = parseInt( item.attr("ind"));
                    if ( found )
                    {
                        next = ind;
                        return false;
                    }
                    if ( ind == $scope.moveid )
                        found = ind;
                    else
                        prev = ind;
                });
                 DbApi( 'movemenu', { prev: prev, id: $scope.moveid, next: next }, function( data ) {
                    if ( data.success )
                    {
                        $rootScope.indmenu = data.result;
                        $rootScope.loadmenu();
                    }
                })
            },
            stop: function( event, ui ) { 
                $scope.sortfield( true );
            },
            /*cancel: ".nosort"*/
            items: "div.imenulist" } );
}

function AdminCtrl($scope, $rootScope, $routeSegment ) {
    $scope.admmenu = [
        { title: lng.settings, icon: 'cogs', href: '#/appsettings', name: 'appsettings'},
        { title: lng.usrgroups, icon: 'users', href: '#/usergroups?id=' + cfg.idgroups, name: 'usergroups'},
        { title: lng.users, icon: 'user', href: '#/users?id=' + cfg.idusers, name: 'users'},
        { title: lng.accrights, icon: 'shield', href: '#/accessrights?id=' + cfg.idaccess, name: 'accessrights'},
    ];
    $scope.$routeSegment = $routeSegment;
}    

function AppsettingsCtrl($scope, $rootScope, $routeSegment, DbApi ) {
    $scope.appsets = [
         { name: 'title', lang: lng.titlejs, visible: true, ctrl:"input", 'class': "form-control wbig" },
         { name: 'isalias', lang: lng.showalias, visible: true, ctrl:"switch"},
         { name: 'perpage', lang: lng.perpage, visible: true, ctrl: "input", 'class': "form-control wshort"},
         { name: 'dblang', lang: lng.language, visible: false, ctrl:"select"},
//         { name: 'apitoken', value: "", visible: false, ctrl: "input", 'class': "form-control whuge"},
         { name: 'keeplog', lang: lng.keeplog, visible: true, ctrl: "switch"},
         { name: 'loginshort', lang: lng.loginshort, visible: true, ctrl :"switch" },
         { name: 'showhelp', lang: lng.showhelp, visible: true, ctrl :"switch" }
    ];
    for ( var i=0; i < $scope.appsets.length; i++ )
        $scope.appsets[i].value = cfgdefault[ $scope.appsets[i].name ];
    DbApi( 'getdb', {}, function( data ) {
        for ( var i=0; i < $scope.appsets.length; i++ )
        {
            var name =  $scope.appsets[i].name;
            if ( angular.isDefined( data.result[ name ] ) && !angular.isObject( data.result[ name ] ))
                $scope.appsets[i].value = data.result[ name ];
        }
//        $scope.items = data.result;
//        console.log( $scope.items );
    });
    $scope.changeswitch = function( value, callback )
    {
        DbApi( 'savedb', value, function( data ) {
            $rootScope.updatesettings( 0, value );
            callback();
         });
    }

    $scope.$routeSegment = $routeSegment;
}    

function EdittableCtrl( $rootScope, $scope, $routeSegment, DbApi ) {
    $scope.$routeSegment = $routeSegment;
    $scope.isalias = cfg.isalias;
  
    $scope.form = {};
    if ( angular.isDefined( $routeSegment.$routeParams.id ))
    {
        $scope.action = lng.savejs;
        $scope.title = lng.edittbl;
        $scope.id = $routeSegment.$routeParams.id;
    }
    else
    {
        $scope.action = lng.create;
        $scope.title = lng.newtbl;
        $scope.id = 0;
    }
    $scope.items = [];
    $scope.addindex = function( ind1, ind2, ind3 ) {
        DbApi( 'addindex', { id: $scope.id, fields: [ind1, ind2, ind3] }, function( data ) {
            if ( data.success )
                $scope.index = data.index;
        });
    }
    $scope.dropindex = function( ind ) {
        DbApi( 'dropindex', { id: $scope.id, field: $scope.index[ind][0] }, function( data ) {
            if ( data.success )
                $scope.index = data.index;
        });
    }
    $scope.submit = function() {

        if ( !$scope.items.length )
            return $rootScope.msg_warning( 'war_efield' );
        if ( $scope.form.title == '' )
            return $rootScope.msg_warning( 'war_ename' );
        if ( $scope.id == 0 )
        {
            $scope.parent = angular.isDefined( $routeSegment.$routeParams.idparent ) ? 
                             $routeSegment.$routeParams.idparent : 0;
            $scope.form.idparent = $scope.parent;
        }
        DbApi( 'savestruct', { id: $scope.id, form: $scope.form, items: $scope.items }, function( data ) {
            if ( data.success )
                document.location = '#/?id=' + data.result.idparent;
        });
    }
    $scope.removefield = function( ind ) {
        $rootScope.msg_quest('delfield', function(){ $scope.items.splice( ind, 1 ) });
    }
    $scope.savefield = function() {

        types[  $rootScope.form.idtype ].verify( $rootScope.form );
        if ( $scope.indf < 0 )
            $scope.items.push( angular.copy( $rootScope.form ));
        else
            $scope.items[$scope.indf] = $rootScope.form;
    }
    $rootScope.chvisible = function( newval, callback ) {
        $rootScope.form.visible = newval;
        callback();
    }
    $rootScope.istree = function( newval, callback ) {
        $scope.form.istree = newval;
        callback();
    }
    $rootScope.setcheck = function( newval, callback ) {
        for ( tkey in newval )
        {
            $rootScope.form.extend[ tkey ] = newval[tkey];
        }
        callback();
    }
    $rootScope.getcols = function() {
        DbApi( 'getstruct', { id: $rootScope.form.extend.table }, function( data ) {
                $rootScope.linkcols = data.result.items;
            });
    }
    $scope.editfield = function( ind ) {
        
        var add = angular.isUndefined( ind );

        $rootScope.isalias = cfg.isalias;
        if ( add )
        {
            $rootScope.form = {title: '', comment: '', visible: 1, id: 0, idtype: 1, 
                         alias: '', align: 0 };
            $scope.indf  = -1;
        }
        else
        {
            $rootScope.form = angular.copy( $scope.items[ind] );
            $scope.indf  = ind;
        }
        if ( angular.isUndefined( $rootScope.form.extend ))
            $rootScope.form.extend = {};
        var extend = $rootScope.form.extend;
        for ( tkey in types )
        {
            for ( var it = 0; it < types[tkey].extend.length; it++ )
            {
                var par = types[tkey].extend[it];
                if ( angular.isUndefined( extend[ par.name ] ))
                    extend[ par.name ] = par.def;
                 if ( angular.isUndefined( par.title ))
                    par.title = lng[par.name];
            }
        }
        $rootScope.getcols();
        $rootScope.msg( { title: add ? lng.newitem : lng.edititem, template: tpl('dlgfield.html'),
              btns: [ {text: add ? lng.add : lng.savejs, func: $scope.savefield, class: 'btn-primary btn-near' },
                     {text: lng.cancel, class: 'btn-default btn-near' }
        ]  } );

//        if ( add )
    }
    $scope.sortfield = function( val ) {
        jQuery( "#fields" ).sortable( { disabled: val } );
    }
    jQuery( "#fields" ).sortable( { axis: "y", helper: function( event, ui ){ 
         return $('<tr class="helper"><td colspan="3"></td></tr>'); },
            forceHelperSize: true,
            disabled: true,
//            forcePlaceholderSize: true,
//            placeholder: "placeholder",
            cursor: "move",
            update: function( event, ui ) { 
                var flength = $scope.items.length;
                $("#fields").children().each(function(index) {
                    var item = $(this);
                    if ( angular.isUndefined( item.attr("ord")) )
                        return true;
                    var old = parseInt( item.attr("ord"));
                    $scope.items.push($scope.items[ old ]);
                });
                $scope.items.splice( 0, flength );
                $scope.$apply();
            },
            stop: function( event, ui ) { 
                $scope.sortfield( true );
            },
            /*cancel: ".nosort"*/
            items: "tr:not(.nosort)" } );

    DbApi( 'getstruct', { id: $scope.id }, function( data ) {
        $scope.items = data.result.items;
        $scope.form = data.result.form;
        $scope.index = data.result.index;
    });
    DbApi( 'gettables', { parent: -1 }, function( data ) {
        $rootScope.tables = data.result;
    });
    DbApi( 'getsets', {}, function( data ) {
        $rootScope.sets = data.result;
    });

//    alert( $stateParams.idi );
}

function ImportCtrl($scope, $routeSegment, DbApi ) {
    $scope.$routeSegment = $routeSegment;
    $scope.form = { importdata: 1 };
    $scope.importdata = function( newval, callback ) {
        $scope.form.importdata = newval;
        callback();
    }
    $scope.submit = function() {
       DbApi( 'import', $scope.form, function( data ) {
            if ( data.success )
            {
                document.location = '#/edittable?id='+ data.success;
            }
        }); 
    }
/*    DbApi[ 'edititem' ]( $routeSegment.$routeParams, function( data ) {
        if ( data.success )
        {
            $scope.db = data.db;
            $scope.columns = data.columns;
            $scope.listitems = data.listitems;
            $scope.form = data.result;
            if ( $scope.form.id != 0 )
            {
                $scope.action = lng.savejs;
            }
            else
                $scope.action = lng.add;
        }
    });
    $scope.saveitem = function(){
       DbApi[ 'saveitem' ]( $scope.form, function( data ) {
            if ( data.success )
            {
                document.location = '#/edititem?table='+$scope.form.table+'&id=' + data.success;
            }
        }); 
    }*/
}

function SetsCtrl($scope, $rootScope, $routeSegment, DbApi ) {
    $scope.$routeSegment = $routeSegment;
    $scope.dropset = function( index ) {
        cfg.temp = $scope.items[ index].title;
        $rootScope.msg_quest( 'delset', function(){ 
            DbApi( 'dropset', { id: $scope.items[index].id }, function( data ) {
                $scope.items.splice( index, 1 ) });
            });
        return false;
    }
    $scope.saveset = function() {
        if ( $rootScope.form.title == '' )
        {
            $rootScope.msg_warning('war_ename');
            return false;
        }
        DbApi( 'saveset', $scope.form, function( data ) {
            if ( data.success )
                if ( $rootScope.form.id )
                    $scope.items[$rootScope.form.index].title = data.result.title;
                else
                {
                    DbApi( 'getsets', {}, function( data ) {
                        $scope.items = data.result;
                    });
                }
        });
        //alert($rootScope.form.cur);
    }
    $scope.editset = function( index ) {
        var add = angular.isUndefined( index );
        function setmsg() {
           $rootScope.msg( { title: add ? lng.create : lng.edit, template: tpl('dlgset.html'),
                   btns: [ {text: add ? lng.add : lng.savejs, func: $scope.saveset, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  })
        }
        if ( add )
        {
            $rootScope.form = {title: '', id: 0 };
            setmsg();
        }
        else
        {
            obj = $scope.items[index];
            $rootScope.form = {title: obj.title, id: obj.id, index: index };
            setmsg();
        }
    }
    DbApi( 'getsets', {}, function( data ) {
        $scope.items = data.result;
    });
}

function SetCtrl($scope, $routeSegment, DbApi, $rootScope, $sce ) {
    $scope.$routeSegment = $routeSegment;
    $scope.idset = $scope.$routeSegment.$routeParams.id;
    DbApi( 'set', $routeSegment.$routeParams, function( data ) {
        if ( data.success )
        {
            $scope.title = data.title;
            $scope.items = data.result;
        }
    });
    $scope.savesetitem = function() {
        if ( $rootScope.form.title == '' )
        {
            $rootScope.msg_warning('war_ename');
            return false;
        }
        DbApi( 'savesetitem', $scope.form, function( data ) {
            if ( data.success )
            {
                if ( $rootScope.form.id !=0 )
                    $scope.items[ $rootScope.form.index ].title = $rootScope.form.title;
                else
                {
                    DbApi( 'set', $routeSegment.$routeParams, function( data ) {
                        $scope.items = data.result;
                    });
                }
            }
        });
    }
    $scope.newi = function( index ) {
        var add = angular.isUndefined( index );
        function setmsg() {
           $rootScope.msg( { title: add ? lng.create : lng.edit, template: tpl('dlgset.html'),
                   btns: [ {text: add ? lng.add : lng.savejs, func: $scope.savesetitem, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' }
               ]  })
        }
        if ( add )
        {
            $rootScope.form = {title: '', id: 0, idset: $scope.idset };
            setmsg();
        }
        else
        {
            obj = $scope.items[index];
            $rootScope.form = {title: obj.title, id: obj.id, index: index, idset: $scope.idset };
            setmsg();
        }
    }
    $scope.delete = function( ) {
        if ( $scope.ind > 0 )
        {
            var lastid = $scope.ind - 1;
            var id = $scope.items[ lastid ].id;
            $rootScope.msg_quest( 'delitem', function(){ 
                DbApi( 'dropsetitem', { id: id, idset: $scope.idset }, function( data ) {
                    if ( data.success )
                        $scope.items.splice( lastid, 1 );
                    });
            });   
        }
    }
    $scope.editi = function( ){
        if ( $scope.ind > 0 )
           $scope.newi( $scope.ind - 1 );
    }
    $scope.over = 0;
    $scope.mouseenter = function( index ){
        if ( $scope.over )
        {
            $scope.mouseleave();
        }
        $scope.over = jQuery('#'+index);
//        alert( angular.toJson( $scope.over ));
        $scope.over.children().addClass('over');
        $scope.overdiv = $scope.over.children().first().children();
        $scope.overdiv.first().show();
        $scope.overdiv.eq(1).hide();
        $scope.ind = index + 1;
    }
    $scope.mouseleave = function(){
        $scope.over.children().removeClass('over');
        $scope.overdiv.first().hide();
        $scope.overdiv.eq(1).show();
        $scope.overdiv = 0;
        $scope.over = 0;
        $scope.ind = 0;
    }
}
