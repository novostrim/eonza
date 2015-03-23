var geapp = angular.module('genteeApp',  [ 'ngCookies', 'ngSanitize',/*'ui.router',*/ 'ui.bootstrap',
               'ngRoute', 'route-segment', 'view-segment', 'pasvaz.bindonce', 'angularFileUpload' ], function($compileProvider) {
    // see example http://docs.angularjs.org/api/ng/service/$compile                
    $compileProvider.directive('compile', function($compile) {
      return function(scope, element, attrs) {
        scope.$watch(
          function(scope) {
            return scope.$eval(attrs.compile);
          },
          function(value) {
            element.html(value);
            $compile(element.contents())(scope);
          }
        );
      };
    })
 } );

geapp
.config( [ '$httpProvider', function( $httpProvider ) {    
    // Use x-www-form-urlencoded Content-Type
    $httpProvider.defaults.headers.post[ 'Content-Type' ] = 'application/x-www-form-urlencoded;charset=utf-8';
    // Override $http service's default transformRequest
    $httpProvider.defaults.transformRequest = function( data ) {
        return angular.isObject( data ) && String( data ) !== '[object File]' ? angular.toParam( data ) : data;
    };
}])
.config(['$sceProvider', function($sceProvider) {
    $sceProvider.enabled(false);
}])
.config( function($routeSegmentProvider, $routeProvider) {
    $routeSegmentProvider.options.autoLoadTemplates = true;
    $routeSegmentProvider
          .when('/',            'index.tables' )
          .when('/install',     'install' )
          .when('/login',       'login' )
          .when('/settings',    'settings' )
          .when('/admin',       'admin' )
          .when('/menu',        'index.menu' )
          .when('/sets',        'index.sets' )
          .when('/appsettings', 'admin.appsettings' )
          .when('/users',       'admin.users' )
          .when('/usergroups',  'admin.usergroups' )
          .when('/accessrights','admin.accessrights' )
          .when('/settings',    'settings' )
          .when('/edittable',   'index.edittable' )
          .when('/table',       'table' )
          .when('/set',         'set' )
          .when('/import',      'import' )
          .segment('settings', {
            templateUrl: tpl('settings.html')
            /*controller: MainCtrl*/ })
          .segment('install', {
            templateUrl: tpl('install.html') })
          .segment('table', {
            templateUrl: tpl('table.html'),
            controller: TableCtrl,
            dependencies: ['id'] })
          .segment('set', {
            templateUrl: tpl('set.html'),
            controller: SetCtrl,
            dependencies: ['id'] })
          .segment('login', {
            templateUrl: tpl('login.html') })
          .segment('import', {
            templateUrl: tpl('import.html'),
            controller: ImportCtrl })
        .segment('admin', {
            templateUrl: tpl('admin.html'),
            controller: AdminCtrl
             })
            .within()
                .segment('appsettings', {
                    templateUrl: tpl('appsettings.html'),
                    controller: AppsettingsCtrl })
                .segment('users', {
                    templateUrl: tpl('table.html'),
                    controller: TableCtrl })
                .segment('usergroups', {
                    templateUrl: tpl('table.html'),
                    controller: TableCtrl })
                .segment('accessrights', {
                    controller: AccessCtrl })
            .up()
          .segment('index', {
            templateUrl: tpl('index.html') })
          .within()
                .segment('tables', {
                    templateUrl: tpl('tables.html'),
                    controller: TablesCtrl,
                    dependencies: ['id']
                      })
                .segment('edittable', {
                    templateUrl: tpl('edittable.html'),
                    controller: EdittableCtrl })
                .segment('menu', {
                    templateUrl: tpl('menu.html'),
                    controller: MenuCtrl
                     })
                .segment('sets', {
                    templateUrl: tpl('sets.html'),
                    controller: SetsCtrl,
                    dependencies: ['id']
                      })
                .up()
});    

geapp.controller( 'GenteeCtrl', function GenteeCtrl($scope, $location, 
    $rootScope, $modal, DbApi, $sce, $http, $filter ) {

    cfg = angular.extend( {}, cfgdefault, cfg );
    if ( typeof( cfg.module ) != 'undefined' )
    {
        cfg.prevurl = $location.path();
        $location.path( cfg.module );
    } 
    if ( !cfg.title.length )
        cfg.title = cfg.appname;   
    
//    cfg.apphead = $sce.trustAsHtml( cfg.appname );
    rootScope = $rootScope;
    $rootScope.filter = $filter;
    $rootScope.cfg = cfg; 
    $rootScope.lng = lng;
    $rootScope.loading = false;
    $rootScope.types = types;
    $rootScope.cnt = cnt;
    $rootScope.uploads = [];

    // Default post action
    $rootScope.postnew = cnt.M_NEW;
    $rootScope.postedit = cnt.M_LIST;

    $rootScope.aligns = [ 'left', 'center', 'right'];    
    $scope.module = cfg.module;

/*    $scope.bigger = function(){
        var  mh = parseInt( document.getElementById("main").clientHeight ) + 100;
        $("#main").css( 'height', mh + 'px' );

    }*/
    $scope.changelng = function( langname )
    {
        js_loadjs( cfg.appdir + "js/l10n/locale_" + langname + '.js', function(){
                $rootScope.lng = lng; 
                $rootScope.$apply()
        });
    }
    $scope.logout = function()
    {
/*        $cookies.enz_pass = '';
        cfg.user = '';
        $timeout( function() { document.location = ''; },  200 );*/
//        $state.go( 'login' );
        $http.post( ajax('logout')).success(function(data) {
            if ( data.success )
                document.location = '';
            else
                $scope.msg_error( $scope.lng[ data.err ] );
        })
    }
    $rootScope.msg = function( dlg_opt )
    {
        if ( angular.isUndefined( dlg_opt.template ))
        {
            if ( angular.isDefined( this.lng[ dlg_opt.body ] ))
                dlg_opt.body = this.lng[ dlg_opt.body ];
            dlg_opt.template = false;
        }
        $scope.dlg_opt = dlg_opt;
        var modalInstance = $modal.open({
            templateUrl: 'dialog.html',
            controller: ModalInstanceCtrl,
            backdrop: true, //'static'
            resolve: {
                dlg_opt: function () {
                    return $scope.dlg_opt;
            }
        }

        });
        modalInstance.result.then( function() {
        }, function () {});
    }
    $rootScope.msg_error = function( text )
    {
        if ( parseInt( text ) )
            text = this.lng[ 'err_system' ] + ' [' + text + ']';
        this.msg( { title: lng.error, body: text, icon: 'fa-times-circle', icon_class: 'red'  } );
    }
    $rootScope.msg_warning = function( text )
    {
        this.msg( { title: lng.warning, body: text, icon: 'fa-exclamation-triangle', icon_class: 'yellow'  } );
    }
    $rootScope.msg_info = function( text )
    {
        this.msg( { title: lng.inform, body: text, icon: 'fa-info-circle', icon_class: 'blue'  } );
    }
    $rootScope.msg_quest = function( text, funcyes )
    {
        this.msg( { title: lng.confirm, body: text, icon: 'fa-question-circle', icon_class: 'blue',
                    btns: [ {text: lng.yes, func: funcyes, class: 'btn-primary btn-near' },
                    {text: lng.no, class: 'btn-default btn-near' }
        ]  } );
    }

    $rootScope.updatepass = function( data, obj )
    {
        this.msg_info( 'updpass' );
    }
    $rootScope.updateuser = function( data, obj )
    {
        for ( var key in obj ) 
           cfg.user[ key ] = obj[ key ];
    }
    $rootScope.updatesettings = function( data, obj )
    {
        for( var k in obj ) { 
            cfg[k] = obj[k];
            break;
        }
    }

    $rootScope.loadmenu = function( ) {
        $rootScope.topmenu = [];
        for ( var i = 0; i < $rootScope.indmenu.length; i++ )
        {
            var obj = $rootScope.indmenu[i];
            if ( obj.idparent == 0 ) 
            {
                obj.expand = true;
                $rootScope.topmenu.push( obj );
            }
            if ( angular.isDefined( obj.children ))
                for ( var k=0; k < obj.children.length; k++ )
                    obj.children[k] = $rootScope.indmenu[ obj.children[k]];
        }
    }
    $rootScope.savemenu = function() {
        if ( $rootScope.form.title == '' )
        {
            $rootScope.msg_warning('war_ename');
            return false;
        }
        DbApi( 'savemenu', $rootScope.form, function( data ) {
            if ( data.success )
            {
                if ( $rootScope.form.id )
                {
                    var objind = $rootScope.indmenu[ $scope.form.index ];
                    objind.title = $rootScope.form.title;   
                    if ( objind.isfolder == 0 )
                    {
                        objind.url = $rootScope.form.url;   
                        objind.hint = $rootScope.form.hint;   
                    }
                }
                else
                {
                    $rootScope.indmenu = data.result;
                    $rootScope.loadmenu();   
                }
            }
        });
    }
    $rootScope.newmenu = function() {
        DbApi( 'gettree', { dbname: 'menu' }, function( data ) {
                if ( data.success )
                {
                    $rootScope.tablefld = data.result;
                    $rootScope.form = {title: $("h1").html(), idparent: 0, id: 0, isfolder: 0,
                                       url: cfg.appenter + '#' + $location.url(), hint:'' };
                    $rootScope.msg( { title: lng.newitem, template: tpl('dlgmenu.html'),
                       btns: [ {text: lng.add, func: $scope.savemenu, class: 'btn-primary btn-near' },
                           {text: lng.cancel, class: 'btn-default btn-near' } ]  })
                }
        })
    }
    if ( cfg.module != 'install' && cfg.module != 'login')
        DbApi( 'getmenu', {}, function( data ) {
            if ( data.success )
            {
                $rootScope.indmenu = data.result;
                $rootScope.loadmenu();
    //            alert( angular.toJson( $rootScope.menu ));
            }
        });

});

var ModalInstanceCtrl = function( $scope, $modalInstance, dlg_opt ) {
    $scope.ok = function () {
        $modalInstance.close();
    };

    $scope.cancel = function() {
        $modalInstance.close();//dismiss();
    };
    $scope.button = function ( obj ) {
        if ( angular.isFunction( obj.func ))
            if ( obj.func() === false )
                return;
        $modalInstance.close();//dismiss();
    };
    $scope.keypress = function( $event )
    {
        if ( $event.keyCode == 13 )
            js_linkpage(0);
    }
    $scope.linkparent = function( parent )
    {
        Scope.linkparent( parent );
    }
    $scope.linkselect = function( id )
    {
        //alert( id  );
        var column = Scope.columns[ Scope.idlink ];
        var alias = column.alias;
        var title = '';
        for ( var i = 0; i< column.link.list.length; i++ )
        {
            if ( column.link.list[i].id == id )
            {
                title = column.link.list[i].title;// + ' ['+ id +']';
                break;
            }
        }
        if ( angular.isDefined( Scope.fltindex ))
            Scope.filter[Scope.fltindex].value = id+':' + title;
        else
        {
            Scope.form[alias] = id;
            Scope.formlink[alias] = title
/*            for ( var i = 0; i< column.link.list.length; i++ )
            {
                if ( column.link.list[i].id == id )
                {
//                    Scope.formlink[alias] = column.link.list[i].title;
    //                Scope.view[alias] = column.link.list[i].title;
                }
            }*/
            for ( i = 0; i< Scope.columns.length; i++ )
            {
                if ( Scope.columns[i].idtype == cnt.FT_LINKTABLE &&
                     Scope.columns[i].extend.filter == column.extend.table )
                {
                    var ialias = Scope.columns[i].alias;
                    Scope.formlink[ialias] = '';
                    Scope.form[ialias] = 0;
    //                Scope.view[alias] = column.link.list[i].title;
                }
            }
        }
        $modalInstance.close();
    }
    $scope.dlg = {
        title: '',
        body: '',
        icon: '',
        icon_class: '',
        btns: [ {text: lng.close, class: 'btn-warning' }
        ]
    }
    angular.extend( $scope.dlg, dlg_opt );
    if ( angular.isDefined( $scope.dlg.body ))
        $scope.dlg.body = $scope.dlg.body.replace( '#temp#', cfg.temp );
};


geapp.factory( 'DbApi', function( $rootScope, $http ) {
    function ajaxerror( data, status )
    {
        $rootScope.msg_error(  this.lng[ 'err_server' ] + ' [' + status + ']' );
    }
    function ajaxcallback( data, status, callback )
    {
        $rootScope.loading = false;
        $rootScope.cfg.temp = data.temp;
        if ( data.success )
        {
            json2num( data );
            if ( angular.isDefined( callback ))
               callback( data );
        } 
        else
            $rootScope.msg_error( data.err );
    }
    function ajaxpost( params, ajaxname, callback )
    {
        $http.post( ajax( ajaxname ), { params: params })
            .success( function( data, status ){ ajaxcallback( data, status, callback )})
            .error( ajaxerror );
    }
    function ajaxget( params, ajaxname, callback )
    {
        $http.get( ajax( ajaxname ), { params: params })
            .success( function( data, status ){ ajaxcallback( data, status, callback )})
            .error( ajaxerror );
    }
/*   POST methods
    var post = [ 'addindex','changefld', 'delfile', 'delmenu', 'dropindex', 'dropitem', 'dropset',
                 'dropsetitem', 'droptable', 'dupitem',
                    'duptable', 'editfile',
                 'export2set', 'gettables', 'import', 'movemenu', 'savedb', 'savefolder', 'saveitem', 
                 'savemenu', 'saveset', 'savesetitem', 'savestruct', 'saveusr', 'truncatetable' ];
*/
    var get = [ 'columns', 'getdb', 'getitem', 'getlink', 'getmenu', 'getsets', 'getstruct', 'gettables', 
                'gettree', 'set', 'table' ];
    function dbapi( method, params, callback ){ 
        var ispost = true;
        var i = get.length;
        while ( i-- )
            if ( get[i] == method )
            {
                ispost = false;
                break;
            }
        $rootScope.loading = true;
        if ( ispost )
            ajaxpost( params, method, callback );
        else
            ajaxget( params, method, callback );
    }

    return dbapi;
});

