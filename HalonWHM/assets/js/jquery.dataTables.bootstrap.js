/*! DataTables Bootstrap integration
 * ÂŠ2011-2014 SpryMedia Ltd - datatables.net/license
 */

/**
 * DataTables integration for Bootstrap 3. This requires Bootstrap 3 and
 * DataTables 1.10 or newer.
 *
 * This file sets the defaults and adds options to DataTables to style its
 * controls using Bootstrap. See http://datatables.net/manual/styling/bootstrap
 * for further information.
 */
(function(window, document, undefined){

var factory = function( $, DataTable ) {
"use strict";
/* Set the defaults for DataTables initialisation */
$.extend( true, DataTable.defaults, {
	dom:
		"<'row'<'col-xs-6'l><'col-xs-6'f>r>"+
		"t"+
		"<'row'<'col-xs-6'i><'col-xs-6'p>>",
	renderer: 'bootstrap'
});
/* Default class modification */
$.extend( DataTable.ext.classes, {
	sWrapper:      "dataTables_wrapper form-inline dt-bootstrap",
	sFilterInput:  "form-control input-sm",
	sLengthSelect: "form-control input-sm"
} );
/* Bootstrap paging button renderer */
DataTable.ext.renderer.pageButton.bootstrap = function ( settings, host, idx, buttons, page, pages ) {
	var api     = new DataTable.Api( settings );
	var classes = settings.oClasses;
	var lang    = settings.oLanguage.oPaginate;
	var btnDisplay, btnClass, i, len, lennode;
	
	lennode = '<ul class="table-length">';
	for ( i=0, len = settings.aLengthMenu[0].length; i < len; i++) {
		lennode += '<li data-value="'+settings.aLengthMenu[1][i]+'">'+settings.aLengthMenu[1][i]+'</li>';
	}
	lennode += '</ul>';
	//$('.dataTables_length').html(lennode);

	var attach = function( container, buttons ) {
		var i, ien, node, button; 
		var clickHandler = function ( e ) {
			e.preventDefault();
			if ( e.data.action !== 'ellipsis' ) {
				api.page( e.data.action ).draw( false );
			}
		};
		
		for ( i=0, ien=buttons.length ; i<ien ; i++ ) {
			button = buttons[i];

			if ( $.isArray( button ) ) {
				attach( container, button );
			}
			else {
				btnDisplay = '';
				btnClass = '';

				switch ( button ) {
					case 'ellipsis':
						btnDisplay = '&hellip;';
						btnClass = 'disabled';
						break;

					case 'first':
						btnDisplay = lang.sFirst;
						btnClass = button + (page > 0 ?
							'' : ' disabled');
						break;

					case 'previous':
						btnDisplay = '<i class="glyphicon glyphicon-chevron-left"></i>';
						btnClass = button + (page > 0 ?
							'' : ' disabled');
						break;

					case 'next':
						btnDisplay = '<i class="glyphicon glyphicon-chevron-right"></i>';
						btnClass = button + (page < pages-1 ?
							'' : ' disabled');
						break;

					case 'last':
						btnDisplay = lang.sLast;
						btnClass = button + (page < pages-1 ?
							'' : ' disabled');
						break;

					default:
						btnDisplay = button + 1;
						btnClass = page === button ?
							'active' : '';
						break;
				}

				if ( btnDisplay ) {
					node = $('<li>', {
							'class': classes.sPageButton+' '+btnClass,
							'aria-controls': settings.sTableId,
							'tabindex': settings.iTabIndex,
							'id': idx === 0 && typeof button === 'string' ?
								settings.sTableId +'_'+ button :
								null
						} )
						.append( $('<a>', {
								'href': '#'
							} )
							.html( btnDisplay )
						)
						.appendTo( container );

					settings.oApi._fnBindAction(
						node, {action: button}, clickHandler
					);
				}
			}
		}
	};
	attach(
		$(host).empty().html('<ul class="pagination"/>').children('ul'),
		buttons
	);
};

/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ( DataTable.TableTools ) {
	// Set the classes that TableTools uses to something suitable for Bootstrap
	$.extend( true, DataTable.TableTools.classes, {
		"container": "DTTT btn-group",
		"buttons": {
			"normal": "btn btn-default",
			"disabled": "disabled"
		},
		"collection": {
			"container": "DTTT_dropdown dropdown-menu",
			"buttons": {
				"normal": "",
				"disabled": "disabled"
			}
		},
		"print": {
			"info": "DTTT_print_info modal"
		},
		"select": {
			"row": "active"
		}
	} );

	// Have the collection use a bootstrap compatible drop down
	$.extend( true, DataTable.TableTools.DEFAULTS.oTags, {
		"collection": {
			"container": "ul",
			"button": "li",
			"liner": "a"
		}
	} );
}

}; // /factory

// Define as an AMD module if possible
if ( typeof define === 'function' && define.amd ) {
	define( ['jquery', 'datatables'], factory );
}
else if ( typeof exports === 'object' ) {
    // Node/CommonJS
    factory( require('jquery'), require('datatables') );
}
else if ( jQuery ) {
	// Otherwise simply initialise as normal, stopping multiple evaluation
	factory( jQuery, jQuery.fn.dataTable );
}

 
$.fn.dataTableExt.oApi.fnLengthChange = function ( oSettings, iDisplay ) {
  oSettings._iDisplayLength = iDisplay;
  oSettings.oApi._fnCalculateEnd( oSettings );
   
  /* If we have space to show extra rows (backing up from the end point - then do so */
  if ( oSettings._iDisplayEnd == oSettings.aiDisplay.length ) {
    oSettings._iDisplayStart = oSettings._iDisplayEnd - oSettings._iDisplayLength;
    if ( oSettings._iDisplayStart < 0 ) {
      oSettings._iDisplayStart = 0;
    }
  }
     
  if ( oSettings._iDisplayLength == -1 ) {
    oSettings._iDisplayStart = 0;
  }
     
  oSettings.oApi._fnDraw( oSettings );
};
$.fn.dataTable.LengthLinks = function ( oSettings ) {
  var container = $('<ul class="table-length"></ul>').addClass( oSettings.oClasses.sLength );
  var lastLength = -1;
  var draw = function () {
    // No point in updating - nothing has changed
    if ( oSettings._iDisplayLength === lastLength ) {
      return;
    }
    var menu = oSettings.aLengthMenu;
    var lang = menu.length===2 && $.isArray(menu[0]) ? menu[1] : menu;
    var lens = menu.length===2 && $.isArray(menu[0]) ? menu[0] : menu;
 
    var out = $.map( lens, function (el, i) {
      return el == oSettings._iDisplayLength ?
        '<li class="active" data-length="'+lens[i]+'">'+lang[i]+'</li>' :
        '<li  data-length="'+lens[i]+'">'+lang[i]+'</li>';
    } );
 
    container.html(out);
    lastLength = oSettings._iDisplayLength;
  };
 
  // API, so the feature wrapper can return the node to insert
  this.container = container;
 
  // Update on each draw
  oSettings.aoDrawCallback.push( {
    "fn": function () {
      draw();
    },
    "sName": "PagingControl"
  } );
 
  // Listen for events to change the page length
  container.on( 'click', 'li', function (e) {
    e.preventDefault();
    oSettings.oInstance.fnLengthChange( parseInt( $(this).attr('data-length'), 10 ) );
  } );
};
 
// Subscribe the feature plug-in to DataTables, ready for use
$.fn.dataTableExt.aoFeatures.push( {
  "fnInit": function( oSettings ) {
    var l = new $.fn.dataTable.LengthLinks( oSettings );
    return l.container[0];
  },
  "cFeature": "L",
  "sFeature": "LengthLinks"
} );
 
 
})(window, document, jQuery);
 
 

