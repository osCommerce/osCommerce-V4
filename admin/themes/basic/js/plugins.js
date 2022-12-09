/**
 * Core script to handle plugins
 */

var Plugins = function() {

	"use strict";

	/**
	 * $.browser for jQuery 1.9
	 */
	var initBrowserDetection = function() {
		$.browser={};(function(){$.browser.msie=false;
		$.browser.version=0;if(navigator.userAgent.match(/MSIE ([0-9]+)\./)){
		$.browser.msie=true;$.browser.version=RegExp.$1;}})();
	}

	/**
	 * Daterangepicker
	 */
	var initDaterangepicker = function() {
		if ($.fn.daterangepicker) {
			$('.range').daterangepicker({
				startDate: moment().subtract('days', 29),
				endDate: moment(),
				minDate: '01/01/2012',
				maxDate: '12/31/2014',
				dateLimit: { days: 60 },
				showDropdowns: true,
				showWeekNumbers: true,
				timePicker: false,
				timePickerIncrement: 1,
				timePicker12Hour: true,
				ranges: {
				   'Today': [moment(), moment()],
				   'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
				   'Last 7 Days': [moment().subtract('days', 6), moment()],
				   'Last 30 Days': [moment().subtract('days', 29), moment()],
				   'This Month': [moment().startOf('month'), moment().endOf('month')],
				   'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
				},
				opens: 'left',
				buttonClasses: ['btn btn-default'],
				applyClass: 'btn-sm btn-primary',
				cancelClass: 'btn-sm',
				format: 'MM/DD/YYYY',
				separator: ' to ',
				locale: {
					applyLabel: 'Submit',
					fromLabel: 'From',
					toLabel: 'To',
					customRangeLabel: 'Custom Range',
					daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
					monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
					firstDay: 1
				}
			},

			function (start, end) {
				var range_updated = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');

				App.blockUI($("#content"));
				setTimeout(function () {
					App.unblockUI($("#content"));
					noty({
						text: '<strong>Dashboard updated to ' + range_updated + '.</strong>',
						type: 'success',
						timeout: 1000
					});
					//App.scrollTo();
				}, 1000);

				$('.range span').html(range_updated);
			});

			$('.range span').html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
		}
	}

	/**
	 * Sparklines
	 */
	var initSparklines = function() {
		if ($.fn.sparkline) {
			// Set default options
			$.extend(true, $.fn.sparkline.defaults, {
				line: {
					highlightSpotColor: App.getLayoutColorCode('green'),
					highlightLineColor: App.getLayoutColorCode('red')
				},
				bar: {
					barColor: App.getLayoutColorCode('blue'),
					negBarColor: App.getLayoutColorCode('red'),
					barWidth: 5,
					barSpacing: 2
				},
				tristate: {
					posBarColor: App.getLayoutColorCode('green'),
					negBarColor: App.getLayoutColorCode('red')
				},
				box: {
					medianColor: App.getLayoutColorCode('red')
				}
			});

			$(window).resize(function () {
				$.sparkline_display_visible();
			}).resize();

			// Initialize statbox sparklines
			$('.statbox-sparkline').each(function () {
				$(this).sparkline('html', Plugins.getSparklineStatboxDefaults());
			})
		}
	}

	/**************************
	 * Tooltips               *
	 **************************/
	var initTooltips = function() {
		// Set default options

		// TODO: $.extend does not work since BS3!

		// This fixes issue #5865
		// (When using tooltips and popovers with the Bootstrap input groups,
		// you'll have to set the container option to avoid unwanted side effects.)
		$.extend(true, $.fn.tooltip.defaults, {
			container: 'body'
		});

		$('.bs-tooltip').tooltip({
			container: 'body'
		});
		$('.bs-focus-tooltip').tooltip({
			trigger: 'focus',
			container: 'body'
		});
	}

	/**************************
	 * Popovers               *
	 **************************/
	var initPopovers = function() {
		$('.bs-popover').popover();
	}

	/**************************
	 * Noty                   *
	 **************************/
	var initNoty = function() {
		if ($.noty) {
			// Set default options
			$.extend(true, $.noty.defaults, {
				type: 'alert',
				timeout: false,
				maxVisible: 5,
				animation: {
					open: {
						height:'toggle'
					},
					close: {
						height:'toggle'
					},
					easing: 'swing',
					speed: 200
				}
			});
		}
	}

	/**************************
	 * Easy Pie Chart         *
	 **************************/
	var initCircularCharts = function() {
		if ($.easyPieChart) {
			// Set default options
			$.extend(true, $.easyPieChart.defaultOptions, {
				lineCap: 'butt',
				animate: 500,
				barColor: App.getLayoutColorCode('blue')
			});

			// Initialize defaults
			$('.circular-chart').easyPieChart({
				size: 110,
				lineWidth: 10
			});
		}
	}

	/**************************
	 * DataTables             *
	 **************************/
	var initDataTables = function() {
		if ($.fn.dataTable) {
                    $.fn.dataTableExt.oPagination.listbox = {
    /*
     * Function: oPagination.listbox.fnInit
     * Purpose:  Initalise dom elements required for pagination with listbox input
     * Returns:  -
     * Inputs:   object:oSettings - dataTables settings object
     *             node:nPaging - the DIV which contains this pagination control
     *             function:fnCallbackDraw - draw function which must be called on update
     */
    "fnInit": function (oSettings, nPaging, fnCallbackDraw) {
        var nFirst = document.createElement( 'span' );
        var nPrevious = document.createElement( 'span' );
        var nNext = document.createElement( 'span' );
        var nLast = document.createElement( 'span' );
        
        var nInput = document.createElement('select');
        var nPage = document.createElement('span');
        var nOf = document.createElement('span');
        nOf.className = "paginate_of";
        nPage.className = "paginate_page";
        if (oSettings.sTableId !== '') {
            nPaging.setAttribute('id', oSettings.sTableId + '_paginate');
        }
        
        nFirst.innerHTML = oSettings.oLanguage.oPaginate.sFirst;
        nPrevious.innerHTML = oSettings.oLanguage.oPaginate.sPrevious;
        nNext.innerHTML = oSettings.oLanguage.oPaginate.sNext;
        nLast.innerHTML = oSettings.oLanguage.oPaginate.sLast;
          
        nFirst.className = "paginate_button first";
        nPrevious.className = "paginate_button previous";
        nNext.className="paginate_button next";
        nLast.className = "paginate_button last";
          
        if ( oSettings.sTableId !== '' )
        {
            nFirst.setAttribute( 'id', oSettings.sTableId+'_first' );
            nPrevious.setAttribute( 'id', oSettings.sTableId+'_previous' );
            nNext.setAttribute( 'id', oSettings.sTableId+'_next' );
            nLast.setAttribute( 'id', oSettings.sTableId+'_last' );
        }
        
        nInput.style.display = "inline";
        nPage.innerHTML = "";
        nPaging.appendChild( nFirst );
        nPaging.appendChild( nPrevious );
        //nPaging.appendChild(nPage);
        nPaging.appendChild(nInput);
        //nPaging.appendChild(nOf);
        nPaging.appendChild( nNext );
        nPaging.appendChild( nLast );
        
        $(nFirst).click( function () {
            window.scroll(0,0); //scroll to top of page
            oSettings.oApi._fnPageChange( oSettings, "first" );
            fnCallbackDraw( oSettings );
        } );
          
        $(nPrevious).click( function() {
            window.scroll(0,0); //scroll to top of page
            oSettings.oApi._fnPageChange( oSettings, "previous" );
            fnCallbackDraw( oSettings );
        } );
          
        $(nNext).click( function() {
            window.scroll(0,0); //scroll to top of page
            oSettings.oApi._fnPageChange( oSettings, "next" );
            fnCallbackDraw( oSettings );
        } );
          
        $(nLast).click( function() {
            window.scroll(0,0); //scroll to top of page
            oSettings.oApi._fnPageChange( oSettings, "last" );
            fnCallbackDraw( oSettings );
        } );
        
        $(nInput).change(function (e) { // Set DataTables page property and redraw the grid on listbox change event.
            window.scroll(0,0); //scroll to top of page
            if (this.value === "" || this.value.match(/[^0-9]/)) { /* Nothing entered or non-numeric character */
                return;
            }
            var iNewStart = oSettings._iDisplayLength * (this.value - 1);
            if (iNewStart > oSettings.fnRecordsDisplay()) { /* Display overrun */
                oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                fnCallbackDraw(oSettings);
                return;
            }
            oSettings._iDisplayStart = iNewStart;
            fnCallbackDraw(oSettings);
        }); /* Take the brutal approach to cancelling text selection */
        $('span', nPaging).bind('mousedown', function () {
            return false;
        });
        $('span', nPaging).bind('selectstart', function () {
            return false;
        });
    },
      
    /*
     * Function: oPagination.listbox.fnUpdate
     * Purpose:  Update the listbox element
     * Returns:  -
     * Inputs:   object:oSettings - dataTables settings object
     *             function:fnCallbackDraw - draw function which must be called on update
     */
    "fnUpdate": function (oSettings, fnCallbackDraw) {
        if (!oSettings.aanFeatures.p) {
            return;
        }
        var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
        var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1; /* Loop over each instance of the pager */
        var an = oSettings.aanFeatures.p;
        for (var i = 0, iLen = an.length; i < iLen; i++) {
            var spans = an[i].getElementsByTagName('span');
            var inputs = an[i].getElementsByTagName('select');
            var elSel = inputs[0];
            if(elSel.options.length != iPages) {
                elSel.options.length = 0; //clear the listbox contents
                for (var j = 0; j < iPages; j++) { //add the pages
                    var oOption = document.createElement('option');
                    oOption.text = j + 1;
                    oOption.value = j + 1;
                    try {
                        elSel.add(oOption, null); // standards compliant; doesn't work in IE
                    } catch (ex) {
                        elSel.add(oOption); // IE only
                    }
                }
                //spans[1].innerHTML = "";
            }
          elSel.value = iCurrentPage;
          var buttons = an[i].getElementsByTagName('span');
            if ( oSettings._iDisplayStart === 0 )
            {
                buttons[0].className = "paginate_disabled_previous";
                buttons[1].className = "paginate_disabled_previous";
            }
            else
            {
                buttons[0].className = "paginate_enabled_previous";
                buttons[1].className = "paginate_enabled_previous";
            }
 
            if ( oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay() )
            {
                buttons[2].className = "paginate_disabled_next";
                buttons[3].className = "paginate_disabled_next";
            }
            else
            {
                buttons[2].className = "paginate_enabled_next";
                buttons[3].className = "paginate_enabled_next";
            }
        }
    }
};
			// Set default options
			$.extend(true, $.fn.dataTable.defaults, {
				"oLanguage": {
					"sSearch": "",
                                        "sLengthMenu": "_MENU_",
                                        "sInfo": "Displaying _START_ to _END_ (of _TOTAL_ records)",
                                        "oPaginate": {
                                            "sNext": "<i class='fa fa-arrow-right'></i>",
                                            "sPrevious": "<i class='fa fa-arrow-left'></i>"
                                        }
				},
                                "sPaginationType": "listbox",
                                "stateSave": true,
                                //"sScrollX": true,
                                /*"stateSaveCallback": function (settings, data) {
                                    console.log(settings);
                                    console.log(data);
                                },*/
				"sDom": "<'row'<'dataTables_header clearfix'<'col-md-6'><'col-md-6 col-md-6-new'f>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'li><'col-md-6'p>>>",
				// set the initial value
				"iDisplayLength": 25,
				fnDrawCallback: function () {
					if ($.fn.uniform) {
						$(':radio.uniform, :checkbox.uniform').uniform();
					}

					if ($.fn.select2) {
						$('.dataTables_length select').select2({
							minimumResultsForSearch: "-1"
						});
					}
if ( !$('.table tbody tr').hasClass('selected') ) {
    if ($("#row_id").val() == undefined) {
        $('.table tbody tr:eq(0)').click();
    } else {
        var sel = $('.table > tbody > tr:eq(' + $("#row_id").val() + ')');
        if (sel[0] == undefined) {
            $("#row_id").val(0);
        }
        $('.table > tbody > tr:eq(' + $("#row_id").val() + ')').click();
    }
}
$('tr td .uniform').click(function() { 
    if(getTableSelectedCount() > 0){
        $('.order-box-list .btn-wr').removeClass('disable-btn');
    }else{
        $('.order-box-list .btn-wr').addClass('disable-btn');
    }
    if (typeof afterClickBatchSelection==='function') {
        afterClickBatchSelection();
    }
}); 
if (typeof onDrawCallbackEvent==='function') {
    onDrawCallbackEvent();
}
 setTimeout(function(){ 
            $('.right_column .widget.box').removeAttr('style');
            var wrap_height = $('.order-wrap').height();
            var scol_height = $('.widget.box .scroll_col').height();
            if(wrap_height > scol_height){
                $('.row .widget.box').css('min-height', wrap_height);
            }else{
                $('.row .widget.box').css('min-height', scol_height);
            }
        }, 700);
 if ($('.wtres table.table.tabl-res').length==0) {
 	$('table.table.tabl-res').wrap('<div class="wtres"></div>');
 	if ($('.wtres .sh-scloll').length==0){
 		$('.wtres').before('<div class="sh-scloll">Table scrolled</div>');
 	}
 }

					// SEARCH - Add the placeholder for Search and Turn this into in-line formcontrol
					var search_input = $(this).closest('.dataTables_wrapper').find('div[id$=_filter] input');

					// Only apply settings once
					if (search_input.parent().hasClass('input-group')) return;

					//search_input.attr('placeholder', 'Search')
					search_input.addClass('form-control')
					search_input.wrap('<div class="input-group input-group-order"></div>');
					search_input.parent().prepend('<span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span>');
                                        //$('.table tbody tr:eq(0)').click();
                                        /*$('tr td .uniform').click(function() { 
                                            if(getTableSelectedCount() > 0){
                                                $('.order-box-list .btn-wr').removeClass('disable-btn');
                                            }else{
                                                $('.order-box-list .btn-wr').addClass('disable-btn');
                                            }
                                        }); */
                                        
					//search_input.parent().prepend('<span class="input-group-addon"><i class="icon-search"></i></span>').css('width', '250px');

					// Responsive
					/*if (typeof responsiveHelper != 'undefined') {
						responsiveHelper.respond();
					}*/
				}
			});

//			$.fn.dataTable.defaults.aLengthMenu = [[5, 10, 25, 50, -1], [5, 10, 25, 50]];
      $.fn.dataTable.defaults.aLengthMenu = [[5, 10, 25, 50, 100, 500, 1000, -1], [5, 10, 25, 50, 100, 500, 1000, "All"]];

			// Initialize default datatables
			$('.datatable').each(function () {
				var self = $(this);
				var options = {};

				/*
				 * Options via data-attribute
				 */

				// General Wrapper
				var data_dataTable = self.attr('datatable');//data
				if (typeof data_dataTable != 'undefined') {
					$.extend(true, options, data_dataTable);
				}

				// Display Length
				var data_displayLength = self.attr('displayLength');//data

				if (typeof data_displayLength != 'undefined') {
					$.extend(true, options, {
						"iDisplayLength": data_displayLength
					});
				}

				// Vertical Scrolling
				var data_verticalHeight = self.attr('verticalHeight');//data
				if (typeof data_verticalHeight != 'undefined') {
					$.extend(true, options, {
                                            "scrollY":        data_verticalHeight,
                                            "scrollCollapse": true,
                                            "paging":         false
					});
				}
                                
				// Horizontal Scrolling
				var data_horizontalWidth = self.attr('horizontalWidth');//data
				if (typeof data_horizontalWidth != 'undefined') {
					$.extend(true, options, {
						"sScrollX": "100%",
						"sScrollXInner": data_horizontalWidth,
						"bScrollCollapse": true
					});
				}

				/*
				 * Other
				 */
                                if ($('.datatable').hasClass('table-texts-table')) {
					var height_texts_table = $(window).height() - 300;
					$.extend(true, options, {
						"sScrollX": true,
						"sScrollY": height_texts_table,
                                                "bScrollCollapse": true
					});
				}
				// Checkable Tables                                
                                if (self.hasClass('table-selectable')) {
                                    
                                    $.extend(true, options, {
                                        'aoColumnDefs': [
                                            {'bSortable': false, 'aTargets': ['_all']}
                                       ]
                                    });
                                    $.extend(true, options, {
                                        "bSort": false,
                                        //"bInfo": false,
                                        //"bFilter": false,
                                        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                                            $(nRow).addClass('checkbox-column');
                                            return nRow;
                                        }
                                    });
                                } else if (self.hasClass('table-checkable')) {
                                        var data_checkable_list = self.attr('checkable_list');//data
                                        if (data_checkable_list == '') {
                                            $.extend(true, options, {
						                        'aoColumnDefs': [
                                                    {'bSortable': false, 'aTargets': ['_all']}
                                               ]
                                            });
                                            /*$.extend(true, options, {
						"bSort": false,
                                                //"bInfo": false,
                                                //"bFilter": false,
                                                "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                                                    $(nRow).addClass('checkbox-column');
                                                    return nRow;
                                                }
                                            });*/
                                        } else {
                                            var column_index_list = data_checkable_list.split(',');
                                            var aoColumnDefs = [];
                                            for(var column_key in column_index_list) {
                                                aoColumnDefs.push({ 'bSortable': true, 'aTargets': [ parseInt(column_index_list[column_key]) ] });
                                            }
                                            aoColumnDefs.push({ 'bSortable': false, 'aTargets': ['_all'] });
                                            $.extend(true, options, {
												"bSort": true,
                                                    'aoColumnDefs': aoColumnDefs
                                            });
                                        }
				}

        //orderSequence
        if (self.hasClass('table-ordering')) {
                                        var data_order_list = self.attr('order_list');//data
                                        var data_order_by = self.attr('order_by');//data
                                        var column_index_list = data_order_list.split(',');
                                        var column_index_by = data_order_by.split(',');
                                        var aoColumnDefs = [];
                                        for(var column_key in column_index_list) {
                                            aoColumnDefs.push([parseInt(column_index_list[column_key],10), column_index_by[column_key]]);
                                        }
					$.extend(true, options, {
						'order': aoColumnDefs
					});                                        
        } else {
/*            
            $.extend(true, options, {
                    'ordering': false
            });   
*/
        }
       
				// TableTools
				if (self.hasClass('table-tabletools')) {
					$.extend(true, options, {
						"sDom": "<'row'<'dataTables_header clearfix'<'col-md-4'l><'col-md-8'Tf>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'i><'col-md-6'p>>>", // T is new
						"oTableTools": {
							"aButtons": [
								"copy",
								"print",
								"csv",
								"xls",
								"pdf"
							],
							"sSwfPath": "plugins/datatables/tabletools/swf/copy_csv_xls_pdf.swf"
						}
					});
				}

				// ColVis
				if (self.hasClass('table-colvis')) {
					$.extend(true, options, {
						"sDom": "<'row'<'dataTables_header clearfix'<'col-md-6'l><'col-md-6'Cf>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'i><'col-md-6'p>>>", // C is new
						"oColVis": {
							"buttonText": "Columns <i class='icon-angle-down'></i>",
							"iOverlayFade": 0
						}
					});
				}

				// If ColVis is used with checkable Tables
				if (self.hasClass('table-checkable') && self.hasClass('table-colvis')) {
					$.extend(true, options, {
						"oColVis": {
							"aiExclude": [0]
						}
					});
				}
                                
                                var data_rowClass = self.attr('rowClass');
				if (data_rowClass == 'orderEdit') {
                                    $.extend(true, options, {
						"createdRow": function (row, data, index) {
                                                    $('td', row).eq(0).addClass('dataTableContent plus_td box_al_center');
                                                    $('td', row).eq(1).addClass('dataTableContent left');
                                                    $('td', row).eq(4).addClass('dataTableContent left');
                                                    $('td', row).eq(5).addClass('dataTableContent');
                                                    $('td', row).eq(6).addClass('dataTableContent');
                                                    $('td', row).eq(7).addClass('dataTableContent no-right-border');
                                                    $('td', row).eq(8).addClass('dataTableContent no-left-border result-price');
                                                    $('td', row).eq(9).addClass('dataTableContent');
                                                    $('td', row).eq(10).addClass('dataTableContent');
                                                    $('td', row).eq(11).addClass('dataTableContent adjust-bar');
						}
					});
                                }
                                
                                if (self.hasClass('table-colored')) {
                                    $.extend(true, options, {
						"createdRow": function (row, data, index) {
                                                    var color = $(row).find('.row_colored').val();
                                                    if (color != '') {
                                                        $(row).css('background-color', color);
                                                    }
						}
					});
                                }

        if (self.hasClass('table-statuses')) {
          $.extend(true, options, {
            "createdRow": function (row, data, index) {
              var cls = $(row).find('.tr-status-class').val();
              //console.log('class ' + cls);
              if (cls != '') {
                $(row).addClass(cls)
              }
						}
					})
        }
				
				
				// Responsive Tables
				if (self.hasClass('table-responsive')) {
					var responsiveHelper;
					var breakpointDefinition = {
						tablet: 1024,
						phone: 480
					};

					// Preserve old function from $.extend above
					// to extend a function
					var old_fnDrawCallback = $.fn.dataTable.defaults.fnDrawCallback;

					$.extend(true, options, {
						bAutoWidth: false,
						fnPreDrawCallback: function () {
							// Initialize the responsive datatables helper once.
							if (!responsiveHelper) {
								responsiveHelper = new ResponsiveDatatablesHelper(this, breakpointDefinition);
							}
						},
						fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                                                        if (self.hasClass('sortable-grid')) {
                                                            var cell_identify = $(nRow).find('.cell_identify').val();
                                                            var cell_type = $(nRow).find('.cell_type').val();
                                                            $(nRow).attr('id', cell_type + '-' + cell_identify);
                                                        }
                                                        if (self.hasClass('table-selectable')) {
                                                            $(nRow).addClass('checkbox-column');
                                                        }
							responsiveHelper.createExpandIcon(nRow);
						},
						fnDrawCallback: function (oSettings) {
                                                    if (self.hasClass('catelogue-grid')) {
                                                        $("#list_bread_crumb").html(oSettings.json.breadcrumb || '');
                                                        $("#categories_counter").text(oSettings.json.categories);
                                                        $("#products_counter").text(oSettings.json.products);
                                                        $('.prod_name_double').dblclick(function() {
                                                            var url_edit = $(this).data('clickDouble');
                                                            $(location).attr('href',url_edit);
                                                        });
                                                    }
                                                    
                                                    if (self.hasClass('double-grid')) {
                                                        $('.double-grid > tbody > tr > td').dblclick(function() {
                                                            var _tmp = $(this).find('.click_double');
                                                            
                                                            if ($(_tmp).data('clickDouble') != 'undefined' && $(_tmp).data('clickDouble') != null){
                                                              var url_edit = $(_tmp).data('clickDouble');
                                                              $(location).attr('href',url_edit);
                                                            } else if ($(_tmp).data('clickFunction') != 'undefined' && $(_tmp).data('clickFunction') != null){
                                                              try {
                                                                eval( $(_tmp).data('clickFunction'));
                                                              }catch (err){
                                                                console.log(err);
                                                              }
                                                            }
                                                        });
                                                    }
                                                    
							// Extending function
							old_fnDrawCallback.apply(this, oSettings);

							responsiveHelper.respond();
                                                        
                                                        if ( typeof productsGridInit === 'function' ) {
                                                            productsGridInit();
                                                        }
						}
					});
				}


				if (self.hasClass('table-no-search')) {
					$.extend(true, options, {
						searching: false
					});
				}

                                if (self.hasClass('table-no-pagination')) {
                                    var height_texts_table = $(window).height() - 300;
                                    $.extend(true, options, {
                                            "searching": false,
                                            "sScrollY": height_texts_table,
                                            "scrollCollapse": true,
                                            "paging":         false,
                                    });
				}

				
                                // Ajax
                                var data_charts = self.attr('data_charts');
                                var data_ajax = self.attr('data_ajax');
                                if (typeof data_ajax != 'undefined') {
                                    var data_type = self.attr('data_type');
                                    if (typeof data_type == 'undefined') {
                                        data_type = "GET";
                                    }
                                    $.extend(true, options, {
                                        "processing": true,
                                        "serverSide": true,
                                        "ajax": {
                                            "url" : data_ajax,
                                            "type": data_type,
                                            "dataSrc": function ( json ) {
                                              if (typeof data_charts != 'undefined' && typeof onDraw == 'function') {
                                                onDraw(json.data);
                                              }
                                              if (typeof json.head == 'object' && typeof onDraw == 'function' ){
                                              onDraw(json, table);
                                              }
											  
                                              if (typeof(rData) == 'object' && rData != null) rData = json;
											  
                                               return json.data;
                                            },
                                            "data" : function ( d ) {
                                                d.id = $('#global_id').val();
                                                d.filter = $('#filterForm, #filterFormHead').serialize();
                                                // d.custom = $('#myInput').val();
                                                // etc
                                            },
                                        },
                                        "initComplete": function( settings, json ) { 
                                            if (self.attr('callback') !== 'undefined' && typeof eval(self.attr('callback')) == 'function') {
                                                eval(self.attr('callback')).call(this, json);
                                            }
                                        },
                                    });
                                }
								
                
                if (typeof $tranlations == 'object'){
                  options.language = {
                    "paginate":{},
                  };
                  if ($tranlations.hasOwnProperty('DATATABLE_FIRST')) {options.language.paginate.first = $tranlations.DATATABLE_FIRST;}
                  if ($tranlations.hasOwnProperty('DATATABLE_LAST')) options.language.paginate.last = $tranlations.DATATABLE_LAST;
                  if ($tranlations.hasOwnProperty('DATATABLE_INFO')) options.language.info = $tranlations.DATATABLE_INFO;
                  if ($tranlations.hasOwnProperty('DATATABLE_INFO_EMPTY')) options.language.infoEmpty = $tranlations.DATATABLE_INFO_EMPTY;
                  if ($tranlations.hasOwnProperty('DATATABLE_EMPTY_TABLE')) options.language.emptyTable = $tranlations.DATATABLE_EMPTY_TABLE;
                }

              var multiSelect = self.hasClass('table-multi-select');
              var msLatestSelectedIdx;

              var table = $(this).dataTable(options);

              $(this).find('tbody').on( 'click', 'tr', function (event) {
                if (multiSelect) {
                  if (event.ctrlKey || event.metaKey) {
                      $(this).toggleClass("selected");
                      if ($(this).hasClass('selected')){
                        msLatestSelectedIdx = $(this).index();
                      }
                  } else {
                    if (event.shiftKey ) {
                      $(this).addClass("selected");
                      var i, n, endIdx = $(this).index();
                      if (msLatestSelectedIdx<endIdx) {
                        i = msLatestSelectedIdx+1;
                        n = endIdx-1;
                      } else {
                        i = endIdx;
                        n = msLatestSelectedIdx-1;
                      }

                      for (i; i<=n; i++) {
                        $($(this).siblings()[i]).addClass('selected');
                      }
                    } else {
                      $(this).addClass("selected").siblings().removeClass('selected');
                    }
                    msLatestSelectedIdx = $(this).index();
                  }

                } else {
                  if ( $(this).hasClass('selected') ) {
                      $(this).removeClass('selected');
                      if (typeof onUnclickEvent==='function') {
                          if (onUnclickEvent(this, table, event) === false){
                              $(this).addClass('selected');
                          }
                      }
                  } else {
                      if ( typeof onClickEvent === 'function' ) {
                          var $selectedRows = table.find('tr.selected');
                          $selectedRows.removeClass('selected');
                          $(this).addClass('selected');
                          if (onClickEvent(this, table, event) === false) {
                              table.$('tr.selected').removeClass('selected');
                              $selectedRows.addClass('selected')
                          }
                      }else{
                          table.$('tr.selected').removeClass('selected');
                          $(this).addClass('selected');
                      }
                  }
                }
              } );
              $(this).find('tbody').on( 'dblclick', 'tr', function (event) {
                  if (typeof onDblClickEvent==='function') {
                      onDblClickEvent(this, table, event);
                  }
              } );

			});
		}
	}

	/**************************
	 * Flot Defaults          *
	 **************************/
	var defaultPlotOptions = {
		colors: [App.getLayoutColorCode('blue'), App.getLayoutColorCode('red'), App.getLayoutColorCode('green'), App.getLayoutColorCode('purple'), App.getLayoutColorCode('grey'), App.getLayoutColorCode('yellow')],
		legend: {
			show: true,
			labelBoxBorderColor: "", // border color for the little label boxes
			backgroundOpacity: 0.95 // set to 0 to avoid background
		},
		series: {
			points: {
				show: false,
				radius: 3,
				lineWidth: 2, // in pixels
				fill: true,
				fillColor: "#ffffff",
				symbol: "circle" // or callback
			},
			lines: {
				// we don't put in show: false so we can see
				// whether lines were actively disabled
				show: true,
				lineWidth: 2, // in pixels
				fill: false,
				fillColor: { colors: [ { opacity: 0.4 }, { opacity: 0.1 } ] },
			},
			bars: {
				lineWidth: 1, // in pixels
				barWidth: 1, // in units of the x axis
				fill: true,
				fillColor: { colors: [ { opacity: 0.7 }, { opacity: 1 } ] },
				align: "left", // or "center"
				horizontal: false
			},
			pie: {
				show: false,
				radius: 1,
				label: {
					show: false,
					radius: 2/3,
					formatter: function(label, series){
						return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;text-shadow: 0 1px 0 rgba(0, 0, 0, 0.6);">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
					},
					threshold: 0.1
				}
			},
			shadowSize: 0
		},
		grid: {
			show: true,
			borderColor: "#efefef", // set if different from the grid color
			tickColor: "rgba(0,0,0,0.06)", // color for the ticks, e.g. "rgba(0,0,0,0.15)"
			labelMargin: 10, // in pixels
			axisMargin: 8, // in pixels
			borderWidth: 0, // in pixels
			minBorderMargin: 10, // in pixels, null means taken from points radius
			mouseActiveRadius: 5 // how far the mouse can be away to activate an item
		},
		tooltipOpts: {
			defaultTheme: false
		},
		selection: {
			color: App.getLayoutColorCode('blue')
		}
	};

	var defaultPlotWidgetOptions = {
		colors: ['#ffffff'],
		legend: {
			show: false,
			backgroundOpacity: 0
		},
		series: {
			points: {
			}
		},
		grid: {
			tickColor: 'rgba(255, 255, 255, 0.1)',
			color: '#ffffff',
		},
		shadowSize: 1
	};

	/**************************
	 * Circle Dial (Knob)     *
	 **************************/
	var initKnob = function() {
		if ($.fn.knob) {
			$(".knob").knob();

			// All elements, which has no color specified, apply default color
			$('.knob').each(function () {
				if (typeof $(this).attr('data-fgColor') == 'undefined') {
					$(this).trigger('configure', {
						'fgColor': App.getLayoutColorCode('blue'),
						'inputColor': App.getLayoutColorCode('blue')
					});
				}
			});
		}
	}

	/**************************
	 * Sparkline Statbox Defaults
	 **************************/
	var defaultSparklineStatboxOptions = {
		type: 'bar',
		height: '19px',
		zeroAxis: false,
		barWidth: '4px',
		barSpacing: '1px',
		barColor: '#fff'
	}

	/**************************
	 * ColorPicker            *
	 **************************/
	var initColorPicker = function() {
		if ($.fn.colorpicker) {
			$('.bs-colorpicker').colorpicker();
		}
	}

	/**************************
	 * Template               *
	 **************************/
	var initTemplate = function() {
		if ($.fn.template) {
			// Set default options
			$.extend(true, $.fn.template.defaults, {

			});
		}
	}

	return {

		// main function to initiate all plugins
		init: function () {
			initBrowserDetection(); // $.browser for jQuery 1.9
			initDaterangepicker(); // Daterangepicker for dashboard
			initSparklines(); // Small charts
			initTooltips(); // Bootstrap tooltips
			initPopovers(); // Bootstrap popovers
			initNoty(); // Notifications
			initDataTables(); // Managed Tables
			initCircularCharts(); // Easy Pie Chart
			initKnob(); // Circle Dial
			initColorPicker(); // Bootstrap ColorPicker
			//initTemplate(); // Template
		},

		getFlotDefaults: function() {
			return defaultPlotOptions;
		},

		getFlotWidgetDefaults: function() {
			return $.extend(true, {}, Plugins.getFlotDefaults(), defaultPlotWidgetOptions);
		},

		getSparklineStatboxDefaults: function() {
			return defaultSparklineStatboxOptions;
		}

	};

}();