var cnt = {
// Access
    A_READ: 1,
    A_CREATE: 2,
    A_EDIT: 4,
    A_DEL: 8,

// Table mode    
    M_LIST: 0,
    M_VIEW: 1,
    M_EDIT: 2,
    M_NEW: 3,
    M_CARD: 4,

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
    FT_SPECIAL: 13,
    FT_CALC: 14,
    FT_SQL: 99,

// Subtypes
    FTM_WEBSITE: 1,
    FTM_EMAIL: 2,
    FTM_PHONE: 3,
    FTM_HASH: 4,
    FTM_IPV4: 5,
    FTM_IMAGELINK: 6,

// Date
    FTM_DATETIME: 1,
    FTM_DATE: 2,
    FTM_TIMESTAMP: 3,
    FTM_CALENDAR: 4,

// Extend
    ET_NUMBER: 1,
    ET_COMBO: 2, 
    ET_CHECK: 3,
    ET_EDIT: 4,
    ET_TABLE: 5,
    ET_COLUMN: 6,
    ET_HIDDEN: 7,
    ET_SET: 8,
    ET_TEXT: 9,
}
"use strict";  

var cfgdefault = {
    title: 'Eonza',
    isalias: 0,
    perpage: 25,
    dblang: 'en',
    loginshort: 0,
    keeplog: 0,
    showhelp: 1
}

var logic = [
    { title: lng.or, id: 1 },
    { title: lng.and, id: 2 },
];

var compare = [
    { title: '', id: 0, mask: 0xff },
    { title: '=', id: 1, mask: 0x1 },
    { title: '>', id: 2, mask: 0x2 },
    { title: '<', id: 3, mask: 0x4 },
    { title: lng.zero, id: 4, mask: 0x8 },
    { title: lng.empty, id: 5, mask: 0x10 },
    { title: lng.startswith, id: 6, mask: 0x20 },
    { title: lng.contains, id: 7, mask: 0x40 },
    { title: lng['length'] + ' =', id: 8, mask: 0x80 },
    { title: lng['length'] + ' >', id: 9, mask: 0x100 },
    { title: lng['length'] + ' <', id: 10, mask: 0x200 },
    { title: lng.endswith, id: 11, mask: 0x400 },
    { title: '<=', id: 12, mask: 0x800 },
    { title: '>=', id: 13, mask: 0x1000 },
    { title: '=', id: 14, mask: 0x2000 },
    { title: lng.yes, id: 15, mask: 0x4000 },
    { title: lng.no, id: 16, mask: 0x8000 },
    { title: lng.thisweek, id: 17, mask: 0x10000 },
    { title: lng.thismonth, id: 18, mask: 0x20000 },
    { title: lng.lastndays, id: 19, mask: 0x40000 },
];

var types = {
    1 : { id: cnt.FT_NUMBER, name: 'fnumber', number: 1, verify: number_verify,
            filter: { mask: 0x0f },
         extend: [ { name: 'range', type: cnt.ET_COMBO, def: 7, 
                     list: [ {id: 1, title: '-128...127' }, { title: '0...255', id: 2},
                 {id: 3, title: '–32768...32767' }, {id: 4, title: '0...65535' },
                 {id: 5, title: '–8388608...8388607' }, {id: 6, title: '0...16777215' },                         
                 {id: 7, title: '-2147483648...2147483647' }, {id: 8, title: '0...4294967295' },                         
            ] } 
        ] 
    },
    2 : { id: cnt.FT_VAR, name: 'fstext', verify: var_verify, filter: { mask: 0x77 },
         extend: [ { name: 'length', type: cnt.ET_NUMBER, def: 32 } ] 
    },
    3 : { id: cnt.FT_DATETIME, name: 'fdatetime', verify: number_verify,
            filter: { mask: 0x70007 }, edit: edit_datetime,
         extend: [ { name: 'date', title: lng.more, type: cnt.ET_COMBO, def: 2, 
                     list: [ {id: 1, title: lng.fdtime }, { title: lng.fdate, id: 2},
                      { title: 'Timestamp', id: 3}, { title: lng.calendar, id: 4 }
            ] },
            { name: 'timenow', type: cnt.ET_CHECK, def: 0 }  
        ] 
    },
    4 : { id: cnt.FT_TEXT, name: 'ftext', verify: number_verify, filter: { mask: 0x3d0 },
            edit: edit_text, pattern: pattern_wide, patternview: patternview_wide, 
         extend: [  { name: 'weditor', type: cnt.ET_COMBO, def: 2, 
                     list: [ {id: 1, title: '---' }, { title: lng.htmleditor, id: 2 }, 
                             { title: 'Markdown', id: 3 }
            ] },  
            { name: 'bigtext', type: cnt.ET_CHECK, def: 0 } 
        ] 
    },
    5 : { id: cnt.FT_LINKTABLE, name: 'flinktable', verify: number_verify, number: 1,
            edit: edit_linktable, view: view_linktable,
             filter: { mask: 0x01 },
         extend: [  { name: 'table', type: cnt.ET_TABLE, def: 0 },  
            { name: 'column', type: cnt.ET_COLUMN, def: 0 },
            { name: 'extbyte', type: cnt.ET_HIDDEN, def: 0 },
            { name: 'multi', title: lng.multiselect, type: cnt.ET_CHECK, def: 0 }, 
            { name: 'aslink', title: lng.showaslink, type: cnt.ET_CHECK, def: 0 }, 
            { name: 'showid', title: lng.showid, type: cnt.ET_CHECK, def: 0 }, 
            { name: 'filter', title: lng.filter, type: cnt.ET_TABLE, def: 0 },
//            { name: 'options', title: lng.moreoptions, type: cnt.ET_TEXT, def: '' }
        ] 
    },
    6 : { id: cnt.FT_CHECK, name: 'fcheck', verify: number_verify, number: 1,
         edit: edit_check, filter: { mask: 0xC000 },
         extend: [] 
    },
    7 : { id: cnt.FT_DECIMAL, name: 'fdecimal', number: 1, verify: number_verify,
            filter: { mask: 0x0f },
         extend: [ { name: 'dtype', title: lng.type, type: cnt.ET_COMBO, def: 1, 
                     list: [ {id: 1, title: lng.decfloat }, { title: lng.decdouble, id: 2}
            ] },  
            { name: 'dlen', title: lng.length, type: cnt.ET_EDIT, def: '' } 
        ] 
    },    
    8 : { id: cnt.FT_ENUMSET, name: 'fenumset', verify: number_verify, number: 1,
            edit: edit_enumset, filter: { mask: 0x2000 }, 
         extend: [  { name: 'set', type: cnt.ET_SET, def: 0 },  
        ] 
    },
    9 : { id: cnt.FT_SETSET, name: 'fsetset', verify: number_verify, number: 1,
            edit: edit_setset, view: view_setset, filter: { mask: 0x1801 }, 
         extend: [  { name: 'set', type: cnt.ET_SET, def: 0 },  
        ] 
    },
    10 : { id: cnt.FT_PARENT, name: 'owner', verify: number_verify, number: 1,
            edit: edit_linktable, view: view_linktable, hidden: true,
         extend: [] 
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
                    { name: 'options', title: lng.moreoptions, type: cnt.ET_TEXT, def: '' } 
                 ] 
    },
    13 : { id: cnt.FT_SPECIAL, name: 'fspecial', form: special_form, verify: number_verify, /*view: view_special,*/
         filter: { mask: 0xffff, extend: 'type', extmask: { 1: 0x77, 2: 0x77, 3: 0x471, 5: 0x1, 6: 0x77 }},
         extend: [ { name: 'type', title: lng.more, type: cnt.ET_COMBO, def: 1, 
                     list: [ {id: 1, title: lng.website }, { title: lng.email, id: 2  },
                      { title: lng.phone, id: 3}, { title: lng.fhash, id: 4},
                      { title: lng.ipv4, id: 5}, { title: lng.imagelink, id: 6}
            ] },
            { name: 'options', title: lng.moreoptions, type: cnt.ET_TEXT, def: '' } 
        ] 
    },    
    14 : { id: cnt.FT_CALC, name: 'fcalc', verify: number_verify,
         view: view_default, edit: edit_default, 
         extend: [ { name: 'formula', type: cnt.ET_EDIT, def: '' },
                   { name: 'round', type: cnt.ET_NUMBER, def: '' } ] 
    },
    99 : { id: cnt.FT_SQL, name: 'fsql', verify: sql_verify,
         extend: [ { name: 'sqlcmd', title: lng.fsql, type: cnt.ET_EDIT, def: '' } ] 
    },
}

function special_form( form, column )
{
    if ( column.extend.type == cnt.FTM_IPV4 )
        form[ column.alias ] = js_long2ip( form[ column.alias ] );
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
/*function js_access( idtable, action )
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
//    alert( ch + ' ' + hfoot + ' ' + h );
    if ( parseInt( ch ) < h  )
    {
        $("#main").css( 'min-height', h + 'px' );
        ch = h;
    }
}*/

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

function js_moment( value, type )
{
    var ret;
    if ( parseInt( value ))
    {
        var m = moment( value );
        switch ( type )    
        {
            case cnt.FTM_DATE:
                ret = m.format('L');
                break;
            case cnt.FTM_DATETIME:
                ret = m.format('L LT');
                break;
            case cnt.FTM_TIMESTAMP:
                ret = m.format('L H:mm:ss');
                break;
            case cnt.FTM_CALENDAR:
                ret = m.calendar();
                break;
            default:
                ret = value;
        }
    }
    else
        ret = '';
    return ret;
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
                item[key] = String( item[key] ).replace( /<[^>]+>/ig,"" );
//                item[key] = jQuery(item[key]).text()
                break;
            case cnt.FT_DATETIME:
                item[key] = js_moment( item[key], colnames[key].extend.date );
/*                var filter = 'short';
                if ( colnames[key].extend.date == cnt.FTM_DATE )
                    filter = 'shortDate';
                item[key] = rootScope.filter('date')( item[key].replace(' ', "T"), filter );*/
                break;                
            case cnt.FT_PARENT:
                var tmp = !item._children ? '' :
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
                    if ( angular.isDefined( colnames[key]['listext'] ))
                        item[key] = '<i class="fa fa-'+colnames[key]['listext'][idi]+'" title="'+ colnames[key]['list'][idi] +'"></i>';
                    else
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
            case cnt.FT_SPECIAL:
                switch ( parseInt( colnames[key].extend.type ))
                {
                    case cnt.FTM_WEBSITE:
                        var url = ( item[key].substr( 0, 4 ) == 'http' ? '' : 'http://' ) + item[key];
                        item[key] = '<a href="'+ url +'" >' + item[key] + '</a>';
                        break;
                    case cnt.FTM_EMAIL:
                        item[key] = '<a href="mailto:'+ item[key] +'" >' + item[key] + '</a>';
                        break;
                    case cnt.FTM_PHONE:
                        var phone = js_phone( item[key] );

                        item[key] = phone.length > 0 ? '<a href="tel:+'+ item[key] +'" class="phonelink">' + 
                                    phone + '</a>' : phone;
                        break;
                    case cnt.FTM_IPV4:
                        item[key] = js_long2ip( item[key] );
                        break;
                    case cnt.FTM_IMAGELINK:
                        if ( item[key] )
                            item[key] = view_imagelink( item[key], colnames[key] );
                        break;
                }
                break;
        }
        if ( item[key] == '0' && colnames[key]['number'] )
            item[key] = '';
    }
}

function view_imagelink( value, icol )
{
    if ( icol.extend.options )
    {
        if ( icol.extend.options.url )
            value = icol.extend.options.url + value;
        if ( icol.extend.options.ext )
            value = value + '.' + icol.extend.options.ext;
    } 
    return '<a class="viewbox" onclick="return viewbox()" rel="emb" href="' + value + '"><img src="' + value + '" class="listimg"></a>';
}

function edit_default( i, icol )
{
    var iclass = 'wnormal';
    var length = angular.isDefined( icol.extend.length ) ? parseInt( icol.extend.length ) : 16;
    if ( icol.number || length < 12 )
        iclass = 'wshort'; 
    else if ( icol.idtype == cnt.FT_VAR ) 
    {
        if ( length >128 )
            return "<textarea name='"+icol.alias+"' ng-model='form[columns["+i+"].alias]' class='form-control whuge' style='height: 5em;'></textarea><span class='length'>{{form[columns["+i+"].alias].length}}</span>";
        else
            if ( length >= 80 )
                iclass = 'whuge'; 
            else
                if ( length >=40)
                    iclass = 'wbig'; 
    } 
    else if ( icol.idtype == cnt.FT_SPECIAL && icol.extend.type == 1 )
    {
        iclass = 'whuge'; 
    }
    else if ( icol.idtype == cnt.FT_CALC ) 
    {
        return "<div class='view-control' ng-bind-html='form[columns["+i+"].alias]'></div>";
    }
    return "<input type='text' name='"+icol.alias+"' ng-model='form[columns["+i+"].alias]' class='form-control " + iclass + "'>" +
           ( icol.idtype == cnt.FT_VAR ? "<span class='length'>{{form[columns["+i+"].alias].length}}</span>" : '' );
}

function edit_check( i, icol )
{
    return "<div ge-check='form[columns["+i+"].alias]' ge-func='cheditform' ge-field='"+icol.alias+"'></div>";
}

function edit_enumset( i, icol )
{
    var out = "<select name='"+icol['alias']+"' ng-model='form[columns["+i+"].alias]' class='form-control'>";
    out += "<option value='0'></option>";
    for ( var i in icol['list'] )
    {
         out += "<option value='"+i+"'>"+icol['list'][i]+"</option>";
    }
    return out + "</select>";
}

function edit_setset( i, icol )
{
    return "<div ge-key='"+icol.alias+"' ge-set='form[columns["+i+"].alias]'></div>";
}

function edit_linktable( i, icol )
{
    return "<div class='multidiv' ng-if='formlink[columns["+i+"].alias]' ng-bind-html='formlink[columns["+i+"].alias]'></div>" +
     '<a href="" class="formbtn" ng-click="editlink('+i+')"><i class="fa fa-fw fa-th-list"></i></a>';
}

function js_redactorpaste( html )
{
    var para = html.split("<br><br>");
    if ( para.length > 1 )
        html = '<p>' + para.join('</p><p>') + '</p>';
    return html;
}

function edit_text( i, icol )
{
    var alias = icol.alias;
    if ( icol.extend.weditor != 2 )
        return "<textarea name='"+alias +"' ng-model='form[columns["+i+"].alias]' class='form-control whuge' style='height: 5em;'></textarea><span class='length'>{{form[columns["+i+"].alias].length}}</span>";

    var iclass = angular.isDefined( cfg.htmleditor ) ? cfg.htmleditor.class : 'redactor';
    var out = "<textarea class='"+iclass+"' id='id-"+alias+"' name='"+alias + "' ng-model='form[columns["+i+"].alias]' style='width: 90%;height: 400px;'></textarea>";
    if ( angular.isUndefined( cfg.htmleditor ))
        out += '<script type="text/javascript">$("textarea[name=\''+ alias +'\']").redactor({buttonSource: true, replaceDivs: false, paragraphize: false,' +
            "deniedTags: ['html', 'head', 'link', 'body', 'meta', 'style', 'applet'], pasteCallback: js_redactorpaste," +
            "plugins: ['table','fontcolor','fontsize','fullscreen', ]});</script>";
    return out;    
}

function edit_datetime( i, icol )
{
    function ahref( id, txt )
    {
        return '&nbsp;<a href="" class="softlink" ng-click="editdate( '+id+', '+i+')">'+lng[txt].toLowerCase()+'</a>';
    }
    var iclass = 'wnormal';
    var ext = '<a href="" class="formbtn" onclick="return js_editdate(this, ' + i + ')"><i class="fa fa-fw fa-calendar"></i></a>'
    switch ( icol.extend.date )
    {
        case cnt.FTM_DATE:
            iclass = 'wshort';
            ext = ext + ahref( 1, 'yesterday') + ahref( 2, 'today') + ahref( 3, 'tomorrow');
            break;
        case cnt.FTM_CALENDAR:
            iclass = 'wshort';
            ext = ext + '<i class="fa fa-plus fa-fw" style="margin: 0px 5px;"></i>' 
                       + ahref( 6, 'day') + ahref( 7, 'week') 
                       + ahref( 8, 'month') + ahref( 9, 'year'); 
            break;
        case cnt.FTM_TIMESTAMP:
            ext = '';
        case cnt.FTM_DATETIME:
            ext = ext + ahref( 4, 'now') + ahref( 5, 'clear');
            break;
        default:
    }
    return "<input type='text' name='"+icol.alias+"' ng-model='form[columns["+i+"].alias]' class='form-control " + iclass + "' >" + ext;
}

function edit_file( i, icol )
{
    var out = common_file( 'form', icol );

    out = out + '<div ng-controller="UploadCtrl" uploader="uploader" ><div class="drop-zone" ng-show="uploader.isHTML5">'+
            '<div nv-file-over nv-file-drop options="{idcol:' + icol.id + ' }" uploader="uploader">'+
                '<i class="fa fa-upload"></i>&nbsp;&nbsp;{{lng.dropzone}}'+
            '</div>'+
        '</div><input nv-file-select options="{idcol:' + icol.id + ' }" uploader="uploader" id="zxc" type="file" multiple />'+
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
    return "<div class='view-control' ng-bind-html='view[\""+icol.alias+"\"]'></div>";
}


function viewbox()
{
    $.colorbox.remove();
    $.extend( $.colorbox.settings, { current: "{current} / {total}" });
    $('.viewbox').colorbox( { open: true, photo: true } );
    return false;
}

function common_file( isview, icol )
{
    var out = "<div class='file-control' ng-repeat='fitem in "+isview+"[\"" + icol.alias +"\"]' >";

    var href = enz.URIApi('download') + '&id={{fitem.id}}';//cfg.appenter+'api/download';
    if ( icol.idtype == cnt.FT_IMAGE )
        out += '<a class="viewbox" title="{{fitem.comment}}" rel="'+( isview == 'view' ? 'viewgal' : 'editgal' ) + 
               icol.id +'" ng-if="fitem.ispreview" onclick="return viewbox()" href="' + href + 
               '&view=1"><img src="'+href+
               '&view=1&thumb=1" class="thumb" />{{fitem.filename}}</a>';
    else    
    {
        out += '<span ng-bind-html="viewfile( fitem.id, fitem.filename )"></span>';
//        <a href="'+href+'?id={{fitem.id}}">{{fitem.filename}}</a>';
    }
    out += '<br>'+
        '<a href="'+href+'&view=1"><i class="fa fa-fw fa-file"></i></a>'+
        '<a href="'+href+'"><i class="fa fa-fw fa-download"></i></a>';
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
    return "<div ng-bind-html='view[\"" +icol.alias + "\"]'></div>";
}

function view_linktable( i, icol )
{
    return "<div ng-bind-html='view[\"" +icol.alias + "\"]'></div>";
}

function pattern_default( i, control, icol )
{
    return "<tr valign='top'><td class='formtxt'>{{columns["+i+"].title}}:</td><td class='formval'>"+control+"</td></tr>";
}

function pattern_wide( i, control, icol )
{
    if ( icol.extend.weditor > 1 )
        return "<tr><td class='formtxt'>{{columns["+i+"].title}}:</td><td></td></tr><tr><td colspan='2' class='formval'>" + control + "</td></tr>";
    else
        return pattern_default( i, control, icol );
}

function pattern_file( i, control, icol )
{
    return "<tr><td class='formtxt'>{{columns["+i+"].title}}:</td><td></td></tr><tr><td colspan='2' class='formval'>" +
     control + "</td></tr>";
}

function patternview_file( i, control, icol )
{
    return "<tr ng-if='view[columns["+i+"].alias]'><td class='formtxt'>{{columns["+i+"].title}}:</td><td></td></tr>" +
           "<tr ng-if='view[columns["+i+"].alias]'><td colspan='2' class='formval'>" + control + "</td></tr>";
}

function patternview_default( i, control, icol )
{
    return "<tr ng-if='view[columns["+i+"].alias]' valign='top'><td class='formtxt'>{{columns["+i+"].title}}:</td><td class='formval'>"+control+"</td></tr>";
}

function patternview_wide( i, control, icol )
{
    if ( icol.extend.weditor > 1 )
        return "<tr ng-if='view[columns["+i+"].alias]'><td class='formtxt'>{{columns["+i+"].title}}:</td><td></td></tr>" +
           "<tr ng-if='view[columns["+i+"].alias]'><td colspan='2' class='formval'>" + control + "</td></tr>";
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
     return ret + '</div><a href="" ng-click="undo()" style="float:right;" class="btn btn-primary btn-lg" title="{{::lng.cancel}}"><i class="fa fa-rotate-left fa-lg" style="margin:0;vertical-align: 0%"></i></a></td></tr>';;
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

function js_summary( src, list, ico )
{
    var htmlitem = '<tr class="summary" id="intotal" valign="top"><td style="text-align:center;'+
               'vertical-align: middle;"><i class="fa fa-fw fa-' + ico + '"></i></td><td></td>';
    for ( var k=0; k< Scope.collist.length; k++ )   
    {
        var value = angular.isDefined( src.result[ Scope.collist[k].alias ]) ?
                        src.result[ Scope.collist[k].alias ] : ''
        htmlitem += '<td class="'+Scope.collist[k].class+'">'+ value + '</td>';
    }
    list.append( htmlitem );    
}

function js_formtolist( i )
{
    var colnames = Scope.colnames;
    var fitem = Scope.form;

    var item = i ? Scope.items[i-1] : { id: fitem.id, _uptime: fitem._uptime };
    for ( var key in colnames )
    {
        if ( !parseInt( colnames[key].visible ))
            continue;
        if ( colnames[key].idtype == cnt.FT_IMAGE || colnames[key].idtype == cnt.FT_FILE )
            item[key] = fitem[key].length;
        else if ( colnames[key].idtype == cnt.FT_LINKTABLE && Scope.resultlink && 
                    angular.isDefined( Scope.resultlink[key] )) {
            item[key] = Scope.resultlink[key].replace( /&sect;/g, '<br>');  
        } else if ( fitem[key].length > 128 )
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
        var td = jQuery('#' + i).children().eq(3);
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
'</div><span class="idtext">'+item.id+'</span></td><td class="uptime'+ (enz.table.uptime ? '' : ' hidden') +'"><span class="idtext">'+moment( item._uptime ).format( enz.uptimefmt )+'</span></td><td><input type="checkbox" class="listcheck" name="ch[]" value="'+i+'"></td>';
    for ( var k=0; k< Scope.collist.length; k++ )   
    {
        htmlitem += '<td class="'+Scope.collist[k].class+'">'+ item[Scope.collist[k].alias]+'</td>';
    }
    list.append( htmlitem );
}

function js_listsort( ind, obj ) 
{
    Scope.params.p = 1;
    if ( ind == 0xffff || ind == 0xfffe)
        Scope.params.sort = ind ==  Math.abs( Scope.params.sort ) ? -Scope.params.sort : ind;
    else    
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


function js_card( idtable, iditem )
{
    Scope.card( idtable, iditem );
    return false;
}

function js_closeback( obj )
{
    $(obj).prev().remove();
    $(obj).remove();
}

function js_editdate( obj, ind, filter )
{
    var start = '';
    var calendar = $(obj).after('<div class="calendar"></div><div onclick="js_closeback(this)" class="modal-backdrop"></div>').next();
    var val = $(obj).prev().val();
    if ( !!val )
    {        
        start = moment( val.substr( 0, 10 ), 'YYYY-MM-DD' );
        if ( !start.isValid())
            start = '';
    }
    calendar.ionCalendar( {
        //lang: moment.locale(),
        sundayFirst: moment.localeData()._week.dow != 1,
        startDate: start,
        years: '1900-' + moment().add( 2, 'y').format( 'YYYY' ),
        format: 'YYYY-MM-DD',
        onClick: function(date){        // клик по дням вернет сюда дату
            if ( !!filter )
            {
                var pars = Scope.filter[ ind ].value.split(' ');
                pars[0] = date;
                Scope.filter[ ind ].value = pars.join( ' ' );
                Scope.$apply();
            }
            else
                Scope.editdate( date, ind );
            calendar.next().remove();
            calendar.remove();
        }
    });
}

function js_getchecked()
{
    
    return ret;
}

function htmleditor( form, get )
{
    var iclass = angular.isDefined( cfg.htmleditor ) ? cfg.htmleditor.class : 'redactor';

    $( "."+iclass ).each( function() {
        var attr = $(this).attr('name');
        if ( !get )
            if ( angular.isDefined( cfg.htmleditor ))
                CKEDITOR.instances['id-'+attr].setData( form[ attr ] );
            else
                $(this).redactor('code.set', form[ attr ] );
//                $(this).redactor('set', form[ attr ] );
        else
        {
            if ( angular.isDefined( cfg.htmleditor ))
                form[attr] =  CKEDITOR.instances['id-'+attr].getData();
            else
                form[attr] =  $(this).redactor('code.get' );
//                form[attr] =  $(this).redactor('get' );
        }
    })
}

function ajax( phpfile )
{
   return cfg.appenter + 'ajax.php?request=' + phpfile;  // 'api/' + phpfile; 
//   return cfg.appdir + 'ajax/' + phpfile + '.php';
}

function tpl( pattern )
{
   return cfg.appdir + 'tpl/' + pattern;
}

function dec2hex( val, length )
{
    return ('00000000' + val.toString( 16 )).substr( -length );
}

function js_filterfield( obj )
{
    var ind = $(obj).attr('ind');
    var mask = 1;
    Scope.filter[ind].title = '';    
    for ( var i = 0; i < Scope.fltfields.length; i++ )
        if ( Scope.fltfields[i].id == $(obj).val())
        {
            mask = Scope.fltfields[i].mask;
            break;
        }
    for ( i = 1; i < Scope.compare.length; i++ )
        if ( Scope.compare[i].mask & mask )
        {
            Scope.filter[ind].compare=Scope.compare[i].id;
            break;
        }
}

function colindex( id )
{
    for ( var i = 0; i <  Scope.columns.length; i++ )
        if ( Scope.columns[i].id == id )
            return i;
    return -1;
}

function js_office( id )
{
    enz.DbApi( 'setshare', { idfile: id, during: 60, first: true }, function( data ) {
        if ( data.success )
            window.location = 'https://view.officeapps.live.com/op/view.aspx?src=' + 
                encodeURIComponent( enz.URIApi( 'share', true ) +'&uid=' + data.result.code );
    })
    return false;
}

function js_firstlink() {
    var acolumn = rootScope.form.extend.acolumn;
    var links = rootScope.linkcols;
    for ( i=0; i< links.length; i++ )
        if ( acolumn.indexOf( links[i].id ) == -1 )
            return links[i].id;
    return 0;
}

function js_changecolumn( obj ) {
    var acolumn = rootScope.form.extend.acolumn;
    var links = rootScope.linkcols;
    var val = parseInt( $(obj).val());
    var ind = $(obj).attr('rel');
    for ( i=0; i< acolumn.length; i++ )
        if ( val == acolumn[i] && i != ind )
        {
            $(obj).val( 0 );
            val = 0;
        }
    acolumn[ ind ] = val ? val : js_firstlink();
    $(obj).val( acolumn[ ind ] );
}

function js_multidel( obj )
{
    var alias = $(obj).attr('alias');
    var idi = $(obj).attr('ids');
    for ( i=0; i<Scope.multiform[alias].ids.length; i++ )
        if ( Scope.multiform[alias].ids[i] == idi )
        {
            Scope.multiform[alias].ids.splice( i, 1 );
            Scope.multiform[alias].txt.splice( i, 1 );
            $(obj).parent().remove();
            break;
        }
    return false;
}