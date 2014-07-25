var cnt = {
// Table mode    
    M_LIST: 0,
    M_VIEW: 1,
    M_EDIT: 2,
    M_NEW: 3,

//  After Insert Update
    M_NEXT: 10,
    M_PREV: 11,

// Types
    FT_UNKNOWN: 0,
    FT_NUMBER: 1,
    FT_VAR: 2,
    FT_DATETIME: 3,
    FT_TEXT: 4,
    FT_LINKTABLE: 5,
    FT_CHECK: 6,
    FT_DECIMAL: 7,
    FT_ENUMSET: 8,
    FT_SETSET: 9,
    FT_PARENT: 10,
    FT_FILE: 11,
    FT_IMAGE: 12,
    FT_SQL: 99,

// Extend
    ET_NUMBER: 1,
    ET_COMBO: 2, 
    ET_CHECK: 3,
    ET_EDIT: 4,
    ET_TABLE: 5,
    ET_COLUMN: 6,
    ET_HIDDEN: 7,
    ET_SET: 8,
}
"use strict";

var types = {
    1 : { id: cnt.FT_NUMBER, name: 'fnumber', number: 1, verify: number_verify,
         extend: [ { name: 'range', type: cnt.ET_COMBO, def: 7, 
                     list: [ {id: 1, title: '-128...127' }, { title: '0...255', id: 2},
                 {id: 3, title: '–32768...32767' }, {id: 4, title: '0...65535' },
                 {id: 5, title: '–8388608...8388607' }, {id: 6, title: '0...16777215' },                         
                 {id: 7, title: '-2147483648...2147483647' }, {id: 8, title: '0...4294967295' },                         
            ] } 
        ] 
    },
    2 : { id: cnt.FT_VAR, name: 'fstext', verify: var_verify,
         extend: [ { name: 'length', type: cnt.ET_NUMBER, def: 32 } ] 
    },
    3 : { id: cnt.FT_DATETIME, name: 'fdatetime', verify: number_verify,
         extend: [ { name: 'date', title: lng.more, type: cnt.ET_COMBO, def: 2, 
                     list: [ {id: 1, title: lng.fdtime }, { title: lng.fdate, id: 2},
                      { title: 'Timestamp', id: 3}
            ] } 
        ] 
    },
    4 : { id: cnt.FT_TEXT, name: 'ftext', verify: number_verify,
            edit: edit_text, pattern: pattern_wide, patternview: patternview_wide, 
         extend: [  { name: 'weditor', type: cnt.ET_COMBO, def: 2, 
                     list: [ {id: 1, title: '---' }, { title: lng.htmleditor, id: 2 }
            ] },  
            { name: 'bigtext', type: cnt.ET_CHECK, def: 0 } 
        ] 
    },
    5 : { id: cnt.FT_LINKTABLE, name: 'flinktable', verify: number_verify, number: 1,
            edit: edit_linktable, view: view_linktable,
         extend: [  { name: 'table', type: cnt.ET_TABLE, def: 0 },  
            { name: 'column', type: cnt.ET_COLUMN, def: 0 },
            { name: 'extbyte', type: cnt.ET_HIDDEN, def: 0 }
        ] 
    },
    6 : { id: cnt.FT_CHECK, name: 'fcheck', verify: number_verify, number: 1,
         edit: edit_check,
         extend: [] 
    },
    7 : { id: cnt.FT_DECIMAL, name: 'fdecimal', number: 1, verify: number_verify,
         extend: [ { name: 'dtype', title: lng.type, type: cnt.ET_COMBO, def: 1, 
                     list: [ {id: 1, title: lng.decfloat }, { title: lng.decdouble, id: 2}
            ] },  
            { name: 'dlen', title: lng.length, type: cnt.ET_EDIT, def: '' } 
        ] 
    },    
    8 : { id: cnt.FT_ENUMSET, name: 'fenumset', verify: number_verify, number: 1,
            edit: edit_enumset, 
         extend: [  { name: 'set', type: cnt.ET_SET, def: 0 },  
        ] 
    },
    9 : { id: cnt.FT_SETSET, name: 'fsetset', verify: number_verify, number: 1,
            edit: edit_setset, view: view_setset,
         extend: [  { name: 'set', type: cnt.ET_SET, def: 0 },  
        ] 
    },
    10 : { id: cnt.FT_PARENT, name: 'fsetset', verify: number_verify, number: 1,
            edit: edit_parent, view: view_linktable, extend: [] 
    },    
    11 : { id: cnt.FT_FILE, name: 'ffile', verify: number_verify,
         view: view_file, edit: edit_file, pattern: pattern_file, patternview: patternview_file,  
         extend: [ { name: 'storedb', type: cnt.ET_CHECK, def: 0 } ] 
    },
    12 : { id: cnt.FT_IMAGE, name: 'fimage', verify: number_verify,
         view: view_file, edit: edit_file, pattern: pattern_file, patternview: patternview_file,  
         extend: [ { name: 'storedb', type: cnt.ET_CHECK, def: 0 },
                { name: 'max', title: lng.maxsize, type: cnt.ET_NUMBER, def: 700 },
                { name: 'min', title: lng.minsize, type: cnt.ET_NUMBER, def: 0 },
                { name: 'ratio', type: cnt.ET_NUMBER, def: 0 },
                { name: 'side', title: lng.mainside, type: cnt.ET_COMBO, def: 0,
                    list: [ {id: 0, title: lng.longside }, { title: lng.width, id: 1 },
                    { title: lng.height, id: 2 },
                    ] },
                { name: 'thumb', title: lng.thumbsize, type: cnt.ET_NUMBER, def: 0 },
                { name: 'thumb_ratio', title: lng.thumbimage +' - ' + lng.ratio, type: cnt.ET_NUMBER, def: 0 },
                { name: 'thumb_side', title: lng.thumbimage +' - ' + lng.mainside, type: cnt.ET_COMBO, def: 0,
                    list: [ {id: 0, title: lng.longside }, { title: lng.width, id: 1 },
                    { title: lng.height, id: 2 },
                    ] },
                 ] 
    },
    99 : { id: cnt.FT_SQL, name: 'fsql', verify: sql_verify,
         extend: [ { name: 'sqlcmd', title: lng.fsql, type: cnt.ET_EDIT, def: '' } ] 
    },
}

function number_verify( field )  // NUMBER DATETIME TEXT
{

}

function sql_verify( field )
{
    if ( field.extend.sqlcmd.length < 2 )
        field.extend.sqlcmd = 'int(10)';
}

function var_verify( field )
{
    if ( field.extend.length > 1024 )
        field.extend.length = 1024;
    if ( field.extend.length < 2 )
        field.extend.length = 2;
}

/*
pattern = {
    5 : { 'edit': edit_enumset },
    6 : { 'edit': edit_check },
    8 : { 'edit': edit_enumset },
    11 : { 'view': view_file, 'edit': edit_file, pattern: pattern_file, patternview: patternview_file  },
    12 : { 'view': view_file, 'edit': edit_image, pattern: pattern_file, patternview: patternview_file  },
};
*/
function js_access( idtable, action )
{
    return true;
}

function js_footer( hoff ) {
    var w, h, ch;
    w = (window.innerWidth ? window.innerWidth : (document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body.offsetWidth));
    h = (window.innerHeight ? window.innerHeight : (document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.offsetHeight));
    //document.getElementById("main").style.width = w;
    var hfoot = parseInt( document.getElementById("xfooter").clientHeight );
    alert( document.getElementById("xfooter").clientHeight  );
    h = h - hfoot - hoff;  
    ch = document.getElementById("main").clientHeight;
    alert( ch + ' ' + hfoot + ' ' + h );
    if ( parseInt( ch ) < h  )
    {
        $("#main").css( 'min-height', h + 'px' );
        ch = h;
    }
}

function js_getset( val, key )
{
    var colnames = Scope.colnames;
    var idi = parseInt( val );
    var ret = [];
    if ( idi > 0 && angular.isDefined( colnames[key]['list'] ))
    {
        var list = colnames[key]['list'];
        for ( var i=0; i<32; i++ )
            if ( idi & ( 1<<i ) && angular.isDefined( list[ i+1]  ))
                ret.push( list[i+1] );
    }
    return ret;
}

function js_treechange( id ) {
    Scope.treechange( id );
    return false;
}

function js_list( item )
{
    var colnames = Scope.colnames;

    for ( var key in colnames )
    {
        if ( !parseInt( colnames[key].visible ))
            continue;
        switch ( parseInt( colnames[key]['idtype'] ))
        {
            case cnt.FT_VAR:
                if ( item[key].length == 128 )
                    item[key] += '...';
                break;
            case cnt.FT_TEXT:
                if ( item[key].length == 128 )
                    item[key] += '...';
                item[key] = item[key].replace( /<[^>]+>/ig,"" );
//                item[key] = jQuery(item[key]).text()
                break;
            case cnt.FT_PARENT:
                var tmp = item._children == '0' ? '' :
                    '<a href="" onclick="return js_treechange(' + item.id + ')"><i class="fa fa-fw fa-folder"></i>' + item._children + '</a>';
                if ( angular.isUndefined( Scope.params.parent ))
                    item[key] = '<a href="" onclick="return js_treechange(' + item._parent_ + ')">' + item[key] + '</a>' + 
                                ( tmp == '' ? '' : ' / ' + tmp  );
                else               
                    item[key] = tmp;
                break;
            case cnt.FT_CHECK:
                item[key] = item[key] == '1' ? '<i class="fa fa-check"></i>' : '';
                break;
            case cnt.FT_ENUMSET:
                var idi = parseInt( item[key] );
                if ( idi > 0 && angular.isDefined( colnames[key]['list'][idi] ))
                    item[key] = colnames[key]['list'][idi];
                break;
            case cnt.FT_SETSET:
                item[key] = js_getset( item[key], key ).join(', ');
                break;
            case cnt.FT_FILE:
            case cnt.FT_IMAGE:
                if ( item[key] == '0' )
                    item[key] = '';
                break;
        }
        if ( item[key] == '0' && colnames[key]['number'] )
            item[key] = '';
    }
}

function edit_default( i, icol )
{
    var iclass = 'wnormal';
    var length = angular.isDefined( icol.extend.length ) ? parseInt( icol.extend.length ) : 16;
    if ( icol.number || length < 12 )
        iclass = 'wshort'; 
    else
        if ( icol.idtype == cnt.FT_VAR ) 
        {
            if ( length >128 )
                return "<textarea name='"+icol.alias+"' ng-model='form[columns."+i+".alias]' class='form-control whuge' style='height: 5em;'></textarea>";
            else
                if ( length >= 80 )
                    iclass = 'whuge'; 
                else
                    if ( length >=40)
                        iclass = 'wbig'; 
        } 
    return "<input type='text' name='"+icol.alias+"' ng-model='form[columns."+i+".alias]' class='form-control " + iclass + "'>";
}

function edit_check( i, icol )
{
    return "<div ge-check='form[columns."+i+".alias]' ge-func='cheditform' ge-field='"+icol.alias+"'></div>";
}

function edit_enumset( i, icol )
{
    var out = "<select name='"+icol['alias']+"' ng-model='form[columns."+i+".alias]' class='form-control'>";
    out += "<option value='0'></option>";
    for ( var i in icol['list'] )
    {
         out += "<option value='"+i+"'>"+icol['list'][i]+"</option>";
    }
    return out + "</select>";
}

function edit_setset( i, icol )
{
    return "<div ge-key='"+icol.alias+"' ge-set='form[columns."+i+".alias]'></div>";
}

function edit_linktable( i, icol )
{
    return "<div class='setitem' ng-if='formlink[columns."+i+".alias]' ng-bind='formlink[columns."+i+".alias]'></div>" +
     '<a href="" class="formbtn" ng-click="editlink('+i+')"><i class="fa fa-fw fa-th-list"></i></a>';
}

function edit_parent( i, icol )
{
    return "<div class='setitem' ng-if='formlink[columns."+i+".alias]' ng-bind='formlink[columns."+i+".alias]'></div>" +
     '<a href="" class="formbtn" ng-click="editlink('+i+')"><i class="fa fa-fw fa-th-list"></i></a>';
}

function edit_text( i, icol )
{
    var alias = icol.alias;
    if ( icol.extend.weditor < 2 )
        return "<textarea name='"+alias +"' ng-model='form[columns."+i+".alias]' class='form-control whuge' style='height: 5em;'></textarea>";

    var iclass = angular.isDefined( cfg.htmleditor ) ? cfg.htmleditor.class : 'redactor';
    var out = "<textarea class='"+iclass+"' id='id-"+alias+"' name='"+alias + "' ng-model='form[columns."+i+".alias]' style='width: 90%;height: 400px;'></textarea>";
    if ( angular.isUndefined( cfg.htmleditor ))
        out += '<script type="text/javascript">$("textarea[name=\''+ alias +'\']").redactor({plugins: [\'fullscreen\']});</script>';
    return out;    
}


function edit_file( i, icol )
{
    var out = common_file( 'form', icol );

    out = out + '<div ng-controller="UploadCtrl" ng-file-drop><div class="drop-zone" ng-show="uploader.isHTML5">'+
            '<div ng-file-over ng-file-drop="{idcol:' + icol.id + ' }" >'+
                '<i class="fa fa-upload"></i>&nbsp;&nbsp;{{lng.dropzone}}'+
            '</div>'+
        '</div><input ng-file-select="{idcol:' + icol.id + ' }" id="zxc" type="file" multiple />'+
        '<table class="toupload"><tr><th>{{lng.filename}}</th><th>{{lng.size}}</th><th></th><th>&nbsp;</th></tr>'+
        '<tr ng-repeat="item in uploader.queue"><td>{{item.file.name}}</td><td>{{item.file.size/1024/1024|number:2}} Mb</td>'+
        '<td><div ng-show="uploader.isHTML5"><div class="item-progress-box">'+
                        '<div class="item-progress" ng-style="{ '+"'width': item.progress + '%'"+' }">{{ item.progress }}</div>'+
                    '</div></div></td>'+
        '<td><div class="fabtn-group"><a href="" class="fabtn" ng-click="item.upload()" ng-hide="item.isReady || item.isUploading || item.isSuccess || form.id == 0"><i class="fa fa-upload fa-fw"></i></a>'+
//                    '<button ng-click="item.cancel()" ng-disabled="!item.isUploading">Cancel</button>'+
                    '<a href="" class="fabtn" ng-click="item.remove()"><i class="fa fa-times fa-fw"></i></a>'+
        '</div></td></tr><tr ng-if="uploader.queue.length"><td>{{lng.allfiles}}: {{uploader.queue.length}}</td><td></td><td>'+
        '<div class="item-progress-box">'+
        '<div class="item-progress" ng-style="{' +" 'width': uploader.progress + '%' "+' }">{{ uploader.progress }}</div>'+
        '</div>'+
        '</td><td><div class="fabtn-group"><a href="" class="fabtn" ng-click="uploader.uploadAll()" ng-hide="!uploader.getNotUploadedItems().length || form.id == 0"><i class="fa fa-upload fa-fw"></i></a>'+
//                    '<button ng-click="item.cancel()" ng-disabled="!item.isUploading">Cancel</button>'+
                    '<a href="" class="fabtn" ng-click="uploader.clearQueue()" ng-disabled="!uploader.queue.length"><i class="fa fa-times fa-fw"></i></a>'+
        '</div></td></tr></table></div>';
//            '<button ng-click="uploader.cancelAll()" ng-disabled="!uploader.isUploading">Cancel all</button>'+
      
    return out;
}

function view_default( i, icol )
{
    //"+icol['alias']+"
    return "<div class='view-control' ng-bind-html='view."+icol.alias+"'></div>";
}

function common_file( isview, icol )
{
    var out = "<div class='file-control' ng-repeat='fitem in "+isview+"." + icol.alias +"' >";

    var href = cfg.appenter+'api/download';

    if ( icol.idtype == cnt.FT_IMAGE )
        out += '<a ng-if="fitem.ispreview" href="' + href + '?id={{fitem.id}}&view=1"><img src="'+href+'?id={{fitem.id}}&view=1&thumb=1" class="thumb" /></a>';
    out += '<a href="'+href+'?id={{fitem.id}}">{{fitem.filename}}</a><br>'+
        '<a href="'+href+'?id={{fitem.id}}&view=1"><i class="fa fa-fw fa-file"></i></a>'+
        '<a href="'+href+'?id={{fitem.id}}""><i class="fa fa-fw fa-download"></i></a>';
    out += '<a href="" ng-click="editfile( fitem.id )"><i class="fa fa-fw fa-pencil"></i></a>';
    if ( isview == 'form' )
    {        
        out += '<a href="" ng-click="delfile( fitem.id )"><i class="fa fa-fw fa-times"></i></a>';
    }
    return out  + '<span style="color:#999;font-size: 10px;">&nbsp;{{fitem.id}}</span>' + '</div>';

}

function view_file( i, icol )
{
    return common_file( 'view', icol );
}

function view_setset( i, icol )
{
    return "<div ng-bind-html='view."+icol.alias+"'></div>";
}

function view_linktable( i, icol )
{
    return "<div ng-bind-html='view."+icol.alias+"'></div>";
}

function pattern_default( i, control, icol )
{
    return "<tr valign='top'><td class='formtxt'>{{columns."+i+".title}}:</td><td class='formval'>"+control+"</td></tr>";
}

function pattern_wide( i, control, icol )
{
    if ( icol.extend.weditor > 1 )
        return "<tr><td class='formtxt'>{{columns."+i+".title}}:</td><td></td></tr><tr><td colspan='2' class='formval'>" + control + "</td></tr>";
    else
        return pattern_default( i, control, icol );
}

function pattern_file( i, control, icol )
{
    return "<tr><td class='formtxt'>{{columns."+i+".title}}:</td><td></td></tr><tr><td colspan='2' class='formval'>" +
     control + "</td></tr>";
}

function patternview_file( i, control, icol )
{
    return "<tr ng-if='view[columns."+i+".alias]'><td class='formtxt'>{{columns."+i+".title}}:</td><td></td></tr>" +
           "<tr ng-if='view[columns."+i+".alias]'><td colspan='2' class='formval'>" + control + "</td></tr>";
}

function patternview_default( i, control, icol )
{
    return "<tr ng-if='view[columns."+i+".alias]' valign='top'><td class='formtxt'>{{columns."+i+".title}}:</td><td class='formval'>"+control+"</td></tr>";
}

function patternview_wide( i, control, icol )
{
    if ( icol.extend.weditor > 1 )
        return "<tr ng-if='view[columns."+i+".alias]'><td class='formtxt'>{{columns."+i+".title}}:</td><td></td></tr>" +
           "<tr ng-if='view[columns."+i+".alias]'><td colspan='2' class='formval'>" + control + "</td></tr>";
    else
        return patternview_default( i, control, icol );
}

function js_editpattern( i, columns )
{
    var icol = columns[i];
    var idtype = icol.idtype;

    if ( angular.isUndefined( types[idtype].edit ))
        types[ idtype ].edit = edit_default;
    if ( angular.isUndefined( types[idtype].pattern ))
        types[ idtype ].pattern = pattern_default;

    var control = types[ idtype ].edit( i, icol );

    return types[ idtype ].pattern( i, control, icol );
}

function js_viewpattern( i, columns )
{
    var icol = columns[i];
    var idtype = icol.idtype;
    if ( angular.isUndefined( types[idtype].view ))
        types[ idtype ].view = view_default;
    if ( angular.isUndefined( types[idtype].patternview ))
        types[ idtype ].patternview = patternview_default;

    var control = types[ idtype ].view( i, icol );

    return types[ idtype ].patternview( i, control, icol );
}


function js_editpatternbottom()
{
    var i;
    var pstnew = [ [ 'M_NEW', 'fa-plus-circle'], ['M_EDIT','fa-pencil'], ['M_VIEW','fa-list-alt'], ['M_LIST', 'fa-table' ]];
    var pstedit = [ ['M_EDIT','fa-pencil'], ['M_PREV','fa-chevron-left'], ['M_NEXT','fa-chevron-right'], ['M_VIEW','fa-list-alt'], ['M_LIST', 'fa-table' ]];
    var ret = '<tr><td align="left"><span ng-if="mode==cnt.M_EDIT"><small>{{form.id}}<br>{{form._uptime}}</small></span>&nbsp;</td><td class="formval"><input type="submit" value="{{action}}"'+ 
     ' class="btn btn-primary btn-lg" ><i class="fa fa-hand-o-right fa-lg" style="margin: 0px 20px;"></i>' +
     '<div class="btn-group" ng-if="mode==cnt.M_NEW">';
    for ( i = 0; i<pstnew.length; i++ )
        ret += '<a href="" class="btn" ng-class="{btnpush:postnew==cnt.'+ pstnew[i][0]+'}" title="" ng-click="pstnew( cnt.'+ pstnew[i][0]+' )" ><i class="fa '+ pstnew[i][1]+'"></i></a>';
    ret += '</div><div class="btn-group" ng-if="mode==cnt.M_EDIT">';  
    for ( i = 0; i<pstedit.length; i++ )
        ret += '<a href="" class="btn" ng-class="{btnpush:postedit==cnt.'+ pstedit[i][0]+'}" title="" ng-click="pstedit( cnt.'+ pstedit[i][0]+' )" ><i class="fa '+ pstedit[i][1]+'"></i></a>';
     return ret + '</div></td></tr>';;
}

function js_viewpatternbottom()
{
    return '<tr><td align="left"><span><small>{{form.id}}<br>{{form._uptime}}</small></span>&nbsp;</td><td class="formval"></td></tr>';
}

function nfy_info( text )
{
    $("#nfy_info span").html( text );
    $("#nfy_info").show();
    setTimeout( function(){ $("#nfy_info").hide(); }, 2000 );
}

function js_menuover( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','block');
}

function js_menuout( objdiv )
{
    $(objdiv).children().eq( 1 ).css('display','none');
}

var Scope = {};
var rootScope = {};

function topage( newpage )
{
    Scope.params.p = newpage;
    Scope.update();
    return false;
}

function js_page(  )
{
    var page = '';
    var pages = Scope.pages;

    for ( var i = 0; i < pages.plist.length; i++ )
    {
        var ipage = pages.plist[i];
        page += '<div class="ipage">';
        if ( ipage[0] == pages.curpage )
            page += '<span>'+ ipage[0] + '</span>';
        if ( ipage[1] != '' )
        {
            if ( ipage[0] == -1 )
                page += '<a href="" onclick="return topage('+ipage[2]+');"><i class="fa fa-arrow-left"></i></a>';
            if ( ipage[0] > 0  )
                page += '<a href="" onclick="return topage('+ipage[2]+');">'+ipage[0] + '</a>';
            if ( ipage[0] == -2 )
                page += '<a href="" onclick="return topage('+ipage[2]+');"><i class="fa fa-arrow-right"></i></a>';
        }
        if ( ipage[0] == 0 )
            page += '<i class="fa fa-ellipsis-h dots" ></i>';

        page += '</div>';
    }
    $('.pages').html( page );
}


function js_formtolist( i )
{
    var colnames = Scope.colnames;
    var fitem = Scope.form;

    var item = i ? Scope.items[i-1] : { id: fitem.id };

    for ( var key in colnames )
    {
        if ( !parseInt( colnames[key].visible ))
            continue;
        if ( colnames[key].idtype == cnt.FT_IMAGE || colnames[key].idtype == cnt.FT_FILE )
            item[key] = fitem[key].length;
        else
            if ( fitem[key].length > 128 )
                item[key] = fitem[key].substr( 0, 128 );
            else
                item[key] = fitem[key];
    }
    if ( !i )
    {
        Scope.items.push( item );
        Scope.currow = Scope.items.length;
    }
    js_list( item );
    if ( i )
    {
        i--;
        var td = jQuery('#' + i).children().eq(2);
        for ( var k=0; k< Scope.collist.length; k++ )    
        {
            td.html( item[Scope.collist[k].alias] );
            td = td.next();
        }
    }
    else
    {
        Scope.allcount++;
        js_listappend( Scope.currow - 1, jQuery( '#mainlist' ));
    }
//    alert( angular.toJson( item ));
}

function js_listappend( i, list )
{
    var item = Scope.items[i];
    var htmlitem = '<tr id="'+i+'" onMouseOver="js_listover(this)" onMouseOut="js_listout()" class="strip" valign="top">'+
'<td><div class="btnlist"><nobr><a href="" onclick="return Scope.listedit('+i+');" class="fabtn"><i class="fa fa-pencil fa-fw"></i></a>'+
'<div class="btnover fabtn" onMouseOver="js_menuover( this );" onMouseOut="js_menuout( this );"><i class="fa fa-ellipsis-h fa-fw"></i>'+
    '<div class="popover"><a href="" onclick="return Scope.listdup('+i+');"><i class="fa fa-copy fa-fw"></i>'+lng.duplicate +'</a>'+
        '<a href=""  onclick="return Scope.listdel('+i+');"><i class="fa fa-trash-o fa-fw"></i>' + lng.del + '</a>'+
    '</div>'+
'</div></nobr>'+
'</div><span class="idtext">'+item.id+'</span></td><td><input type="checkbox" class="listcheck" name="ch[]" value="'+i+'"></td>';
    for ( var k=0; k< Scope.collist.length; k++ )   
    {
        htmlitem += '<td class="'+Scope.collist[k].class+'">'+ item[Scope.collist[k].alias]+'</td>';
    }
    list.append( htmlitem );
}

function js_listsort( ind, obj ) 
{
    Scope.params.p = 1;
    Scope.params.sort = Scope.collist[ind].id ==  Math.abs( Scope.params.sort ) ? -Scope.params.sort : Scope.collist[ind].id;
    Scope.update( false );
    return false;
}

function js_listover( obj )
{
    if ( Scope.over )
        js_listout();
    Scope.over = jQuery( obj ).children().first().children().first();
    Scope.over.next().hide();
    Scope.over.show();
}

function js_listout()
{
    Scope.over.next().show();
    Scope.over.hide();
    Scope.over = 0;
}

function js_listallcheck( obj )
{
    $(".listcheck").prop( 'checked', $(obj).is( ":checked" ) ? 'on': false);
    return true;
}

function js_linkpage( direct )
{
    return Scope.linkpage( direct );
}

function js_listselect( obj )
{
    var action = $(obj).val();

    var ret = [];
    $(".listcheck:checked").each( function(){
        ret.push( Scope.items[$(this).val()].id );
    });
    if ( !ret.length )
        rootScope.msg_warning( lng.nosel );
    else
    {
        if ( action == 'delete' )
            Scope.listdel( ret );
    }
    $(obj).val(0);
}

function js_getchecked()
{
    
    return ret;
}

function ajax( phpfile )
{
   return cfg.appenter + 'api/' + phpfile; //ajax.php';//'ajax/' + phpfile + '.php';
//   return cfg.appdir + 'ajax/' + phpfile + '.php';
}

function tpl( pattern )
{
   return cfg.appdir + 'tpl/' + pattern;
}