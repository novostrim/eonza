/*
    Eonza 
    (c) 2014 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

geapp.directive( 'geLink', function( $compile, $document, $rootScope )
{
    return {
        restrict: 'A',
        replace: false,
        scope: { geKey:'@',
                 geLink: '=',
        },
        template: '<span class="setitem" ng-model="geLink"></span>'+
        '<a href="" class="formbtn" ng-click="setselect()"><i class="fa fa-fw fa-check-square-o"></i></a>',       
        controller: function($scope, $element, $attrs, $rootScope ) {
//            $scope.geLink = Scope.form['__' + $scope.geKey ];
            varinfo( $scope.geLink );
            varinfo( $scope.geKey );
  /*           '<span class="setitem" ng-model="form['+ '__' + scope.geKey + ']"></span>'+
        '<a href="" class="formbtn" ng-click="setselect()"><i class="fa fa-fw fa-check-square-o"></i></a>',
*/
//            $scope.items = js_getset( $scope.geSet, $scope.geKey );
//            varinfo( $scope.items );
/*
            $scope.setselect = function() {
                $rootScope.checks = [];
                var cols = Scope.colnames[$scope.geKey]['list'];
                for ( var i=0; i<32; i++ )
                    if ( angular.isDefined( cols[ i+1] ))
                        $rootScope.checks.push( { id: i, state: !!($scope.geSet & ( 1<<i )), title: cols[ i+1] } );
                 $rootScope.msg( { title: lng.set, template: tpl('editset.html'),
                       btns: 
                       [ {text: lng.savejs, func: function(){
                            var newVal = 0;
                            for ( var k=0; k < $rootScope.checks.length; k++ )
                                if ( $rootScope.checks[k].state )
                                    newVal |= 1 << $rootScope.checks[k].id;
                            $scope.geSet = newVal;
                       }, class: 'btn-primary btn-near' },
                        {text: lng.cancel, class: 'btn-default btn-near' }
                   ] });
            }
             $scope.$watch( 'geSet', function( newValue ){ 
                 $scope.geSet = newValue;
                $scope.items = js_getset( $scope.geSet, $scope.geKey );
             });*/
        }
    }
})


geapp.directive( 'geSet', function( $compile, $document, $rootScope )
{
    return {
        restrict: 'A',
        replace: false,
        scope: { geKey:'@',
                 geSet: '=',
        },
        template: '<span ng-repeat="item in items" class="setitem">{{item}}</span>'+
        '<a href="" class="formbtn" ng-click="setselect()"><i class="fa fa-fw fa-check-square-o"></i></a>',
        controller: function($scope, $element, $attrs, $rootScope ) {
//            varinfo( $scope.geSet );
            $scope.key = $scope.geKey;
            if ( $scope.geKey == Number( $scope.geKey ))
            {
                for( key in Scope.colnames )
                    if ( Scope.colnames[key].id == $scope.geKey )
                    {
                        $scope.key = key;
                        break;
                    }
            }
            $scope.items = js_getset( $scope.geSet, $scope.key );
//            varinfo( $scope.items );
            $scope.setselect = function() {
//                $scope.geKey = $scope.key;
                $rootScope.checks = [];
                var cols = Scope.colnames[$scope.key]['list'];
                for ( var i=0; i<32; i++ )
                    if ( angular.isDefined( cols[ i+1] ))
                        $rootScope.checks.push( { id: i, state: !!($scope.geSet & ( 1<<i )), title: cols[ i+1] } );
                 $rootScope.msg( { title: lng.set, template: tpl('editset.html'),
                       btns: 
                       [ {text: lng.savejs, func: function(){
                            var newVal = 0;
                            for ( var k=0; k < $rootScope.checks.length; k++ )
                                if ( $rootScope.checks[k].state )
                                    newVal |= 1 << $rootScope.checks[k].id;
                            $scope.geSet = newVal;
                       }, class: 'btn-primary btn-near' },
                        {text: lng.cancel, class: 'btn-default btn-near' }
                   ] });
            }
             $scope.$watch( 'geSet', function( newValue ){ 
                 $scope.geSet = newValue;
                $scope.items = js_getset( $scope.geSet, $scope.key );
             });
        }
    }
})

geapp.directive( 'geTree', function( $compile, $document, $rootScope )
{
    return {
        restrict: 'A',
        replace: false,
        scope: { geTree:'@',
                 geCur: '=',
        },
        template: '',
        controller: function($scope, $element, $attrs, $rootScope ) {
            $scope.clickfolder = function( id, $event ) {
                $scope.title = $scope.items[id].title;
                $scope.geCur = id;
                if ( angular.isDefined( $scope.items[id].children ) && $scope.items[id].id )
                {
                    $scope.items[id].expand = $scope.items[id].expand ? false : true;

                    var elem = jQuery( $event.target );
                    if ( elem.hasClass('fa'))
                        elem = elem.parent();
                    if ( $scope.items[id].expand )
                    {
                        elem.next().show();
                        elem.children(0).removeClass('fa-caret-right');
                        elem.children(0).addClass('fa-caret-down');
                    }
                    else
                    {
                        elem.next().hide();
                        elem.children(0).removeClass('fa-caret-down');
                        elem.children(0).addClass('fa-caret-right');
                    }
                }
            }
            $scope.title = '';
            $scope.items = [];
            angular.element( $element ).addClass('tree');
            function outitem( obj )
            {
                var open = obj.expand || obj.id == 0 ? 'down' : 'right';
                var ret = "<div class='treeitem' ng-click='clickfolder( " + obj.id+", $event )'>";
                var dis = '';
                $scope.items[ obj.id ] = obj;
                if ( angular.isUndefined( obj.children ) || !obj.children.length )
                    dis = "style='color: #fff;'";
                ret += "<i class='fa fa-fw fa-caret-" +open+ "' " + dis + "></i>&nbsp;";
                if ( !obj.id )
                    obj.title = $rootScope.lng.rootfolder;
                ret += obj.title + "</div>";
                if ( obj.id == $scope.geCur )
                    $scope.title = obj.title;
                if ( angular.isDefined( obj.children ))
                {
                    ret += '<div class="treeitems"'+( obj.expand || obj.id == 0  ? '' : "style='display: none'") +'>';
                    for ( var i=0; i<obj.children.length; i++ )
                        ret += outitem( obj.children[i] );
                    ret += '</div>';
                }
                return ret
            }
            $scope.tempHTML = angular.element( '<div class="curtree">{{title}}</div><div>' + 
                                    outitem( $rootScope[ $scope.geTree ] ) + '</div>');
            $compile( $scope.tempHTML.contents() )( $scope );
            angular.element( $element ).prepend( $scope.tempHTML );
//            $scope.$apply();
        },
    };
});

geapp.directive( 'geCheck', function( $document, $rootScope )
{
    return {
        restrict: 'A',
        replace: true,
        scope: { geCheck:'=',
                 geFunc: '=',
                 geField: '@',
        },
        template: '<div class="btn-group"><a href="#" class="btn">{{lng.yes}}</a><a href="#" class="btn">{{lng.no}}</a></div>',
        controller: function($scope, $element, $attrs) {
            $scope.lng = $rootScope.lng;
            var div = angular.element( $element );
            $scope.yes = div.children().first();
            $scope.no = div.children().last();
            if ( $scope.geCheck > 0 )            
                $scope.yes.addClass( 'btn-check' );
            else
                $scope.no.addClass( 'btn-check' );
            
            $scope.$watch( 'geCheck', function( newValue ) {
                if ( ($scope.geCheck >0 && $scope.no.hasClass('btn-check')) ||
                    ( $scope.geCheck == 0 && $scope.yes.hasClass('btn-check')))
                {
                    $scope.no.toggleClass( 'btn-check' );
                    $scope.yes.toggleClass( 'btn-check' );
                }    
            });


            $scope.func = function( event )
            {
                if ( $scope.geCheck != event.data.val )
                {
                    if ( angular.isDefined( $scope.geFunc ))
                    {
                        var obj = {};
                        if ( $scope.geField )
                            obj[ $scope.geField ] = event.data.val;
                        $scope.geFunc( $scope.geField ? obj : event.data.val, function( data ){ 
                            $scope.geCheck = event.data.val;
                        });
                    }
                    else
                        $scope.geCheck = event.data.val;
                    $scope.no.toggleClass( 'btn-check' );
                    $scope.yes.toggleClass( 'btn-check' );
                }
                return false;
            }
        },
        link: function( $scope, element, attrs ){
            $scope.yes.on('click', { val: 1 }, $scope.func );
            $scope.no.on('click', { val: 0 }, $scope.func );
        }
    };
});

 
geapp.directive( 'geEdit', function( $compile, $document, $rootScope, $timeout, DbApi )
{
    return {
        restrict: 'A',
        replace: true,
        scope: { geEdit:'=',
                 geClass:'@',
                 geFunc: '@',
                 geCallback: '@',
                 geField: '@'
        },
        template: '<div class="{{geClass}} popupedit">{{geEdit}}&nbsp;</div>',
//                    '<div class="{{geClass}}">{{geEdit}}</div>',
        controller: function($scope, $element, $attrs) {

            $rootScope.geEdit = false;
            $scope.isedit = false;

            function mykey( event ) {
                if ( event.which === 27 ) { $scope.myedit(); }
            }
            function mymouseel(event) { event.stopPropagation(); }
            function mymouse(event) { $scope.myedit(); }

            $scope.mysave = function()
            {
                var obj = {};
                obj[ $scope.geField ] = $scope.newvalue;
                DbApi( $scope.geFunc, obj, function( data ){ 
                    if ( typeof( $scope.geCallback ) != 'undefined' )
                        $rootScope[ $scope.geCallback ]( data, obj );
                });
                $scope.geEdit = $scope.newvalue;
                $scope.myedit();
            }
            $scope.myedit = function()
            {
                var title = angular.element( $element );
                if ( $rootScope.geEdit && $rootScope.geEdit != $element )
                    $rootScope.geEditScope();

//                var title = angular.element($element.children()[0]);
                $scope.isedit = !$scope.isedit;
                if ( $scope.isedit )
                {
                    $rootScope.geEdit = $element;
                    $rootScope.geEditScope = $scope.myedit;
                    title.unbind('click', $scope.myedit );

                    $scope.newvalue = $scope.geEdit;
                    $scope.tempHTML = angular.element("<div class='popupdiv'><input type='text' class='{{geClass}}' ng-model='newvalue'><br>" +
                        "<nobr><button class='btn btn-primary' ng-click='mysave()'>" + lng.savejs + "</btn>" +
                        "<button class='btn' ng-click='myedit()'>" + lng.cancel + "</btn></nobr></div>" );

                    $compile( $scope.tempHTML.contents() )( $scope );
                    title.prepend( $scope.tempHTML );
//                    title.after( $scope.tempHTML );
                    $scope.$apply();
                    $document.bind('keydown', mykey );
                    $document.bind('click', mymouse );
                    $scope.tempHTML.bind('click', mymouseel );
                    $scope.tempHTML.children()[0].focus();
                }
                else
                {
                    $rootScope.geEdit = false;
                    $scope.tempHTML.unbind('click', mymouseel );
                    $document.unbind('click', mymouse );
                    $document.unbind('keydown', mykey );
                    $scope.tempHTML.remove();
    //                title.next().remove();                
                    $timeout( function(){ title.bind('click', $scope.myedit );}, 100 );
                }
                return false;
            }

        },
        link: function( scope, element, attrs ){
            angular.element(element).bind('click', scope.myedit );
//            angular.element(element.children()[0]).bind('click', scope.myedit );
        }
    };
});


geapp.directive( 'geMain', function( $window )
{
    return {
        restrict: 'A',
        link: function( scope, element, attrs ){
            scope.height = 0;
            scope.$watch( function() {
                if ( scope.height != element.height() )
                {
                    scope.height = element.height();
                    var margin = $window.innerHeight - document.getElementById("footer").clientHeight;
                    if ( scope.height < margin - 10 )
                        element.css( 'margin-bottom', margin - scope.height  + 'px' );
                    else
                        element.css( 'margin-bottom', '10px' );
                }
            } );
        }
    };
});

geapp.directive('ckEditor', [function () {
// source http://habrahabr.ru/post/200058/  http://jsfiddle.net/jWANb/2/   
        return {
            require: '?ngModel',
            restrict: 'C',
            link: function (scope, elm, attr, model) {
                var isReady = false;
                var data = [];
                var ck = CKEDITOR.replace(elm[0], {filebrowserBrowseUrl : '/elfinder/elfinder.html' } );
                
                function setData() {
                    if (!data.length) {
                        return;
                    }
                    
                    var d = data.splice(0, 1);
                    ck.setData(d[0] || '<span></span>', function () {
                        setData();
                        isReady = true;
                    });
                }

                ck.on('instanceReady', function (e) {
                    if (model) {
                        setData();
                    }
                });
                
                elm.bind('$destroy', function () {
                    ck.destroy(false);
                });

                if (model) {
                    ck.on('change', function () {
                        scope.$apply(function () {
                            var data = ck.getData();
                            if (data == '<span></span>') {
                                data = null;
                            }
                            model.$setViewValue(data);
                        });
                    });

                    model.$render = function (value) {
                        if (model.$viewValue === undefined) {
//                            commented because it gives some error  
//                            model.$setViewValue(null);
                            model.$viewValue = null;
                        }

                        data.push(model.$viewValue);

                        if (isReady) {
                            isReady = false;
                            setData();
                        }
                    }
                }
            }
        }
}]);