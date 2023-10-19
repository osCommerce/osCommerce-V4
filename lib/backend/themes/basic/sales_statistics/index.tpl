{use class="\common\helpers\Html"}
{use class="\yii\helpers\Url"}
{use class="backend\components\Currencies"}
{Currencies::widget()}
{\backend\assets\BDPAsset::register($this)|void}
{\backend\assets\XLSAsset::register($this)|void}
<!--=== Page Header ===-->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div class="page-header">
    <div class="page-title">
        <h3>{$app->controller->view->headingTitle}</h3>
    </div>
</div>
<!-- /Page Header -->
<div class="sale-report">
           <!--=== Page Content ===-->
				<div class="widget box box-wrapp-blue widget-closed filter-wrapp widget-fixed">
					<div class="widget-header filter-title">
                        <h4 class="s_filter_title">{$app->controller->view->headingTitle}</h4>
                        <div class="s_filter_saved">
                            <label>{$app->controller->view->headingTitle}</label>
                            {Html::dropDownList('filters', $selected_filter, $filters, ['class' => 'form-control', 'prompt'=> TEXT_SELECT, 'onchange' => 'document.location=this.value'])}
                            {if strlen($filters[$selected_filter]) > 0} <button class="btn" onClick="deleteFilter();">{$smarty.const.IMAGE_DELETE}</button>&nbsp;{/if}
                            <label>{$smarty.const.TEXT_SAVE_AS_FILTER}</label>{tep_draw_input_field('filter_name', '','class="form-control"')}
                            <button class="btn" onClick="saveFilter();">{$smarty.const.IMAGE_SAVE}</button>
                        </div>
                        <div class="toolbar no-padding">
                          <div class="btn-group">
                            <span class="btn btn-xs widget-collapse"><i class="icon-angle-up"></i></span>
                          </div>
                        </div>
                    </div>
					<div class="widget-content filter_values">
                    {tep_draw_form('sales', 'sales_statistics', '', 'get')}
                    <div class="wrap_filters after wrap_filters_4">
                        <div class="item_filter item_filter_1">
                            <div class="tl_filters_title">{$smarty.const.TEXT_CHOOSE_PRECISION}</div>
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_PRECISION}</label>
                                {Html::dropDownList('type', $app->controller->view->filter->precision_selected, $app->controller->view->filter->precision, ['class'=>'form-control', 'onchange'=>'updatePrecision(this.value);'])}
                            </div>
                            <div class="report-details-range">
                                {$options}
                            </div>
                            <div class="report-details-options">
                            </div>
                            <div class="wl-td">
                                {Html::checkbox('with_products', $with_products)}<label>{$smarty.const.TEXT_PROD_DET}</label>
                            </div>
                        </div>
                        <div class="item_filter item_filter_2">
                            <div class="tl_filters_title">{$smarty.const.TEXT_CHOOSE_ORDERS}</div>
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_ORDER_GROUP_STATUS}</label>
                                {Html::dropDownList('status[]', $selected_statuses, $app->controller->view->filter->statuses, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                            </div>
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_PAYMENT_MODULES}</label>
                                {Html::dropDownList('payment_methods[]', $selected_payments, $app->controller->view->filter->payment_methods, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                            </div>
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_SHIPPING_MODULES}</label>
                                {Html::dropDownList('shipping_methods[]', $selected_shippings, $app->controller->view->filter->shipping_methods, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                            </div>
                            <div class="wl-td">
                                <label>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</label>
                                {Html::dropDownList('platforms[]', $selected_platforms, $app->controller->view->filter->platforms, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                            </div>
                            {if count($app->controller->view->filter->customer_groups)>0}
                            <div class="wl-td">
                                <label>{$smarty.const.BOX_CUSTOMERS_GROUPS}</label>
                                {Html::dropDownList('customer_groups[]', $selected_customer_groups, $app->controller->view->filter->customer_groups, ['class' => '', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                            </div>
                            {/if}
                            <div class="wl-td">
                                {$geo_details}
                            </div>
                            {if $app->controller->view->filter->admin}
                                <div class="wl-td w-tdc">
                                    <label>{$smarty.const.TEXT_WALKIN_ORDER}</label>
                                    {\yii\helpers\Html::dropDownList('walkin[]', $walkin, $app->controller->view->filter->admin, ['class' => 'form-control', 'multiple' => 'multiple', 'data-role' => 'multiselect'])}
                                </div>
                            {/if}
                        </div>
                        <div class="item_filter item_filter_3">
                            <div class="tl_filters_title">{$smarty.const.TEXT_SHOW_CHARTS}</div>
                            <div class="chart_group_wrap after">
                                {assign var="i" value="0"}
                                <div class="chart_group_column">
                                    {foreach $app->controller->view->filter->charts as $item}
                                        {if $i%3 == 0 && $i > 0}
                                            </div><div class="chart_group_column">
                                        {/if}
                                        <div class="chart_group" data-id="{$i}">
                                            <strong>{$smarty.const.TEXT_CHART} {$i+1}{$i++|void}</strong>
                                        {foreach $item as $_m => $value}
                                            <label><input type="checkbox" name="chart_group_item[{$_m}]" {if $value['selected']}checked{/if} class="chart_item" data-group="{$i}" data-name="{$_m}" data-color="{$value['color']}" {if isset($value['disabled']) && $value['disabled']} disabled {/if} style="display: inline-block;"><span class="square" style="background-color:{if isset($value['disabled']) && $value['disabled']}#caccd3{else}{$value['color']}{/if};" data-color="{$value['color']}"></span>{$value['label']}</label>
                                        {/foreach}
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                        <div class="filters_btn">
                            <a href="{Url::to('sales_statistics/')}" class="btn" >{$smarty.const.TEXT_RESET}</a>
                            <a href="javascript:void(0)" onclick="updateData();" class="btn btn-primary" >{$smarty.const.IMAGE_UPDATE}</a>
                        </div>
                    </div>
                    </form>
					</div>
				</div>
				<div class="row table-row">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-area-chart"></i><span id="table_title">{$table_title}</span><span class="range1">{$range}</span></h4>
                                <div class="export-block export-data"><span>{$smarty.const.TEXT_EXPORT}</span><label>{Html::radio('exportO', true, ['value'=>'CSV', 'class' => 'export'])}CSV</label><label>{Html::radio('exportO', false, ['value'=>'XLS', 'class' => 'export'])}XLS</label><a href="{\yii\helpers\Url::to('sales_statistics/export')}" class="btn export-btn">{$smarty.const.TEXT_EXPORT}</a><a class="blind" style="display:none;"></a></div>
                                <div class="toolbar no-padding">
								<div class="btn-group">
									<span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
								</div>
							    </div>
							</div>
							<div class="widget-content after with-legend">
                                <table class="table sale-report-table table-texts-table"></table>
							</div>
						</div>

					</div>
				</div>
                
                <div class="charts">
                
                </div>
                
                <div class="btn-bar edit-btn-bar">
                    <div class="btn-right"><a class="btn btn-primary show-map" href="{Url::to('sales_statistics/map-show')}">{$smarty.const.TEXT_SHOW_ON_MAP}</a></div>
                </div>
                
				<div class="row clone" style="display:none;">
					<div class="col-md-12">
						<div class="widget box">
							<div class="widget-header">
								<h4><i class="icon-area-chart"></i><span></span></h4>
                                <div class="export-block export-data"><span>{$smarty.const.TEXT_EXPORT}</span><label>{Html::radio('export', true, ['value'=>'CSV', 'class' => 'export'])}CSV</label><label>{Html::radio('export', false, ['value'=>'XLS', 'class' => 'export'])}XLS</label><a href="{\yii\helpers\Url::to('sales_statistics/export')}" class="btn export-btn">{$smarty.const.TEXT_EXPORT}</a><a class="blind" style="display:none;"></a></div>
								<div class="toolbar no-padding">
								<div class="btn-group">
									<span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
								</div>
							    </div>
							</div>
							<div class="widget-content after chart_holder" style='height: 500px;'>
							</div>
							<div class="widget-content after under_holder">
							</div>
						</div>

					</div>
				</div>
                <div style="display:none;">
                    <canvas id=canvas ></canvas>
                </div>
                <div style="display:none;">
                    <table class='table1' id="products-template">
                    <thead>
                        <tr>
                            <th style="width:70%;">{$smarty.const.TEXT_PRODUCTS_NAME}</th>
                            <th style="width:10%;">{$smarty.const.TABLE_HEADING_QUANTITY}</th>
                            <th style="width:10%;">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}</th>
                            <th style="width:10%;">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <table>
                </div>
    </div>
<script>
    
    function getWrapedMessage(message){
        return '<div class="widget box"><div class="widget-content">'+message+'</div><div class="noti-btn"><div><span class="btn btn-cancel">{$smarty.const.TEXT_OK}</span></div></div></div>'
    }
    
    function setFilterState() {
        var orig = $('form[name=sales]').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '')
        window.history.replaceState({ }, '', url);
    }
    
    function saveFilter(){
        var filter_name = $('input[name=filter_name]').val();
        if (!filter_name.length){
            alertMessage(getWrapedMessage('{$smarty.const.ERROR_WARNING}'));
            return false;
        } else {
            $.post('sales_statistics/save-filter', {
                'filter_name': filter_name,
                'options': $('form[name=sales]').serialize()
            }, function(data, status){
                alertMessage(getWrapedMessage(data.message));
            }, "json");
        }
    }

    function deleteFilter(){
        $.post('sales_statistics/delete-filter', {
            'filter_vals': $('select[name=filters]').val()
        }, function(data, status){
            alertMessage(getWrapedMessage(data.message));
        }, "json");
    }

    var oTable;
    var chartsArray = {};
    var $settings = {
        updatePrecision: function(type){
            $.get('sales_statistics/load-range', 
            {
                'type':type,
            },
            function(data, status){
                if (status == 'success'){
                    $('.report-details-range').html(data.range);
                    if (Array.isArray(data.undisabled)){
                        if (data.undisabled.indexOf($('select[name=type]').val()) == -1){
                            $('input[name="chart_group_item[orders_avg]"]').attr('disabled', true);
                            $('input[name="chart_group_item[total_avg]"]').attr('disabled', true);
                            $('input[name="chart_group_item[orders_avg]"]').prop('checked', false);
                            $('input[name="chart_group_item[total_avg]"]').prop('checked', false);
                            $('input[name="chart_group_item[orders_avg]"]').next().css('background-color', '#caccd3');
                            $('input[name="chart_group_item[total_avg]"]').next().css('background-color', '#caccd3');
                        } else {
                            $('input[name="chart_group_item[orders_avg]"]').attr('disabled', false);
                            $('input[name="chart_group_item[total_avg]"]').attr('disabled', false);
                            $('input[name="chart_group_item[orders_avg]"]').next().css('background-color', $('input[name="chart_group_item[orders_avg]"]').next().attr('data-color'));
                            $('input[name="chart_group_item[total_avg]"]').next().css('background-color', $('input[name="chart_group_item[total_avg]"]').next().attr('data-color'));
                            
                            $settings.setDependance();
                        }
                    }
                }
            },'json');
        },
        setDependance: function(){
            $('input[name="chart_group_item[orders_avg]"]').change(function(){
                if ($(this).prop('checked')){
                    $('input[name="chart_group_item[orders]"]').prop('checked', true);
                }
            });
            $('input[name="chart_group_item[orders]"]').change(function(){
                if (!$(this).prop('checked') && $('input[name="chart_group_item[orders_avg]"]').prop('checked')){
                    $('input[name="chart_group_item[orders_avg]"]').prop('checked', false);
                }
            });
            $('input[name="chart_group_item[total_avg]"]').change(function(){
                if ($(this).prop('checked')){
                    $('input[name="chart_group_item[ot_total]"]').prop('checked', true);
                }
            });
            $('input[name="chart_group_item[ot_total]"]').change(function(){
                if (!$(this).prop('checked') && $('input[name="chart_group_item[total_avg]"]').prop('checked')){
                    $('input[name="chart_group_item[total_avg]"]').prop('checked', false);
                }
            });
        },
        class_range:[],
        updateData:function(){
            $('body').append("<div class='popup-box-wrap'><div class='around-pop-up'><div class='preloader'></div></div>");
            $.get('sales_statistics', 
             $('form[name=sales]').serialize(),
            function(data, status){
                if (status == 'success'){
                    $('.popup-box-wrap').remove();
                    $settings.data = {};
                    $settings.class_range = data.class_range;
                    $('#table_title').html(data.table_title);
                    if (Array.isArray(data.data)){
                        $settings.clearData();
                        $.each(data.data, function(i, e){
                            $settings.setData(e);
                        });                        
                    }
                    if (Array.isArray(data.columns)){
                        $settings.clearColumns();
                        $.each(data.columns, function(i, e){
                            $settings.setColumns({ data:e.class, title :e.title });
                        });
                    }                    
                    if (data.hasOwnProperty('range')){
                        $settings.range = data.range;
                    }
                    if (data.hasOwnProperty('rows')){
                        $settings.rows = data.rows;
                    }
                    drawCharts();
                }                
            }, 'json');
        },
        data: [],
        columns: [],
        getColumns: function(){
            return this.columns;
        },
        setColumns: function(value){
            $settings.columns.push(value);
        },
        clearColumns: function(){
            this.columns = [];
        },
        filterColumns: function(_columns){
            var selected_charts = $('.chart_item:checked');
            var need_columns = [];
            var new_columns = _columns;
            if (selected_charts.length > 0){
                $.each(selected_charts, function(i, e){
                    need_columns.push($(e).attr('data-name'));
                });
            }
            if (need_columns.length >0){
                new_columns = [];
                $.each(_columns, function(i,e){
                    if (e.hasOwnProperty('data') && e.data == 'period'){
                        new_columns.push(e);
                    } else if (e.hasOwnProperty('data')){
                        if (need_columns.indexOf(e.data) != -1 ){
                            new_columns.push(e);
                        }
                    }                    
                });
            }
            return new_columns;
        },
        clearData: function(){
             this.data = [];
             return;
        },
        setData:function(values){
            this.data.push(values);
            return;
        },
        getData: function(){
            return this.data;
        },
        getFormatted:function(value){
            if (typeof accounting == 'object'){
                return accounting.formatMoney(value, curr_hex[currency_id].symbol_left,curr_hex[currency_id].decimal_places,curr_hex[currency_id].thousands_point,curr_hex[currency_id].decimal_point);
            } 
            return value;
        },
        showProducts:function(products){
            if (Array.isArray(products)){
                var table = $('#products-template').clone();
                $(table).attr('id','').addClass('table');
                $.each(products, function(i, product){
                    $("tbody", table).append("<tr><td>" + product.products_name + (product.products_model.length > 0? " (" + product.products_model + ")" : '') + "</td><td>" + product.products_quantity +"</td><td>" + $settings.getFormatted(product.final_price) +"</td><td>"+$settings.getFormatted(product.final_price_tax)+"</td></tr>");
                });
                $(table).dataTable();
                return $(table).get(0);
            }
            return false;
        },
        renderTable: function(){
            var _columns = this.getColumns();
            _columns = this.filterColumns(_columns);
            var _data = this.getData();
            var _totals = {};
            $.each(_data, function(i, e){
               if (typeof e == 'object'){
                $.each(e, function (ii, ee){
                    if (!_totals.hasOwnProperty(ii)) _totals[ii] = 0;
                    if (isNaN(ee) || ee.length == 0) ee = 0;
                    _totals[ii] = parseFloat(_totals[ii]) + parseFloat(ee);
                    if (ii.indexOf('ot_') != -1 || ii == 'total_avg') {
                        _data[i][ii] = $settings.getFormatted(ee);
                    }
                    if ((ii == 'cost_amount' || ii == 'profit_amount') && parseFloat(ee) > 0) {
                        _data[i][ii] = $settings.getFormatted(ee);
                    }
                    if (ii == 'profit_percent' && parseFloat(ee) > 0) {
                        _data[i][ii] = parseFloat(ee).toFixed(2) + '%';
                    }
                });
               }
            });
            
           if ($.fn.dataTable.isDataTable(oTable)){ //redraw
                $('.row.table-row .widget-content').html('').append('<table class="table"></table>');
                $('.row.table-row .widget-header h4 .range1').html($settings.range);
           }
           var colored = [];
           var use_coloring = false;
           var min_colored = 0;
           var holidays = [];
           {if is_array($holidays)}
            holidays = ['{implode("','", $holidays)}'];
           {/if}
           
           if ($('select[name=type]').val() == 'daily' || $('select[name=type]').val() == 'weekly'){
              use_coloring = true;
              min_colored = '{DAYLI_MINIMAL_ORDERS}';
              if (min_colored.lenght == 0 && !holidays.length){
                use_coloring = false;
              }
           }
           if (use_coloring){
              $('.with-legend').append('<div class="clegend"><div class="clegend_line"><div class="exceed square"></div>&nbsp;{$smarty.const.TEXT_MINIMAL_ORDERS}</div><div class="clegend_line"><div class="isholiday square"></div>&nbsp;{$smarty.const.BANK_HOLIDAYS}</div><div class="clegend_line"><div class="isweekend square"></div>&nbsp;Weekend</div></div>');
           }
           
           if (!_data.length ){
               $('.row.table-row .widget-content').html('').append('No data available');
           } else {
               _totals.period = '';            
                $.each(_totals, function(i, e){
                    if (i.indexOf('ot_') != -1) {
                        _totals[i] = $settings.getFormatted(e);
                    }
                    if ((i == 'cost_amount' || i == 'profit_amount') && parseFloat(e) > 0) {
                        _totals[i] = $settings.getFormatted(e);
                    }
                    _totals[i] = "<b>" + _totals[i] + "</b>";
                    if (i == 'orders_avg') _totals[i] = '';
                    if (i == 'total_avg') _totals[i] = '';
                    if (i == 'profit_percent') _totals[i] = '';
                });
               _data.push(_totals);
               
               oTable = $('.table').DataTable({
                        "aaData": _data,
                        "aoColumns": _columns,
                        "ordering": false,
                        "searching": false,
                        "bDestroy": true,
                        "bAutoWidth": true,
                        "iDisplayLength": $settings.rows,
                        'bLengthChange': true,
                         'paging': false,
                         'scroller': true, //not for all DT :(
                         'scrollY': 409
                    });
                oTable.rows().every(function(a){
                    var _row = oTable.row(a);
                    var _data = _row.data();
                    if (_data.hasOwnProperty('products')){
                        if (Array.isArray(_data.products)){
                            _row.child($settings.showProducts(_data.products)).show();
                        }
                    }
                    if (_data.hasOwnProperty('cur_row') && _data.cur_row == 1) {
                      var _scrollTo = Math.max(0, a);
                      if (_scrollTo>0) {
                        try {
                          $('.table').parent().scrollTop(parseInt($('.table tbody tr:nth-child('+_scrollTo+')').offset().top- $('.table tbody').offset().top));
                        } catch (e) { }
                      }
                    }
                })
               if ($('select[name=type]').val() == 'hourly') {
                   var _rows = oTable.rows();
                   var _row;
                   $.each(_rows[0], function (i, e) {
                     if (i > 0){
                       _row = oTable.row(i).data();
                       if(_row.period.indexOf('00:00') > 0){
                           _row = oTable.row(i).node();
                           $(_row).addClass('full_date_tr');
                       }
                     }
                   })
               }
                if (use_coloring){
                    var _rows = oTable.rows();
                    var _row;
                    $.each(_rows[0], function(i, e){
                        _row = oTable.row(i).data();
                        if (parseInt(_row.orders) >= parseInt(min_colored)){
                            _row = oTable.row(i).node();
                            $(_row).addClass('exceed');
                        }
                        if (holidays.indexOf(_row.period_full) != -1){
                            _row = oTable.row(i).node();
                            $(_row).addClass('isholiday');
                        }
                        if ([0,6].indexOf(new Date(_row.period_full).getDay()) !== -1){
                            _row = oTable.row(i).node();
                            $(_row).addClass('isweekend');
                        }
                    })
                }
                if (Array.isArray(this.class_range)){
                    $.each(this.class_range, function(i, e){
                        if (i > 0){
                            _row = oTable.row(e).node();
                            $(_row).addClass('full_date_tr');
                        }
                    })
                }
           }
            
        },
        groups:[],
        collectGroups:function(){
                var selected_charts = $('.chart_item:checked');
                var id = 0;
                var _groups = $settings.groups;
                _groups = [];
                if (selected_charts.length > 0){
                    $.each(selected_charts, function(i, e){
                        id = $(e).parents('.chart_group').attr('data-id');
                        if (typeof _groups[id] != 'object') _groups[id] = { 'elements':[], 'colors':[] };
                        _groups[id].elements.push($(e).attr('data-name'));
                        _groups[id].colors.push($(e).attr('data-color'));
                    });
                }
                return _groups;
        },
        charts:{
            drawCollection:function(){
                var _groups = $settings.collectGroups();
                var holder = $('.charts');
                var clone;
                var _columns = $settings.getColumns();
                var chart_header;
                if (_groups.length > 0){
                    $(holder).html('');
                    $.each(_groups, function(i, e){
                        if (e != undefined && e.hasOwnProperty('elements')){
                            if (e.elements.length >0){
                                clone = $('.clone').clone();
                                chart_header = '';
                                $(clone).removeClass('clone').attr('id', 'group_ids'+i).find('.chart_holder').attr('id', 'group_chart'+i);
                                $(clone).find('input.export').attr('name','export_'+i);
                                $(clone).find('.export-data').attr('data-chart', e.elements.join("|"));

                                var titles = [];
                                var minmnax = '';
                                $.each(e.elements, function(_gi, _gelement){
                                    _columns.map(function(_col, ii){
                                        if (_col.data == _gelement){
                                            chart_header += " " + _col.title + ','; 
                                            titles.push(_col.title);
                                            minmnax += "<div class='mm_"+_gelement+"'><head><center><div style='width:10px;height:10px;background-color:"+e.colors[_gi]+"'></div>"+_col.title+"</center></head><body><min><span>{$smarty.const.TEXT_MIN}</span><value></value><date></date></min><max><span>{$smarty.const.TEXT_MAX}</span><value></value><date></date></max></body></div>";
                                        }
                                    });
                                    
                                });
                                $('.widget-header h4 span', clone).html("<b>"+chart_header.substr(0, chart_header.length-1)+"</b> "+$settings.range);                                
                                $(clone).find('.under_holder').html(minmnax);
                                $(holder).append(clone);
                                $('#group_ids'+i).find('.widget-collapse').on('click', function () {
                                    if($('#group_ids'+i).find('.widget-content').is(':visible')){
                                        $('i', this).addClass('icon-angle-up').removeClass('icon-angle-down');
                                    }else{
                                        $('i', this).addClass('icon-angle-down').removeClass('icon-angle-up');
                                    }
                                    $('#group_ids'+i).find('.widget-content').slideToggle();
                                });
                                if (chart_header.length > 0){
                                    $(clone).show();
                                    $settings.charts.drawChart('group_chart'+i, e.elements, e.colors, titles);
                                }
                            }
                        }
                    })
                }
            },
            drawChart:function(chart_holder_id, fields, colors, titles){
                var data = new google.visualization.DataTable();
                var $use_colors = [];
                var skip_fmt_for = new Array('orders');
                var skip = false;
                var _data = $settings.getData();
                data.addColumn('date', 'Date');
                $.each(fields, function(i,e ){
                    data.addColumn('number', titles[i]);
                    $use_colors.push(colors[i]);
                    if (skip_fmt_for.indexOf(e) != -1) skip = true;
                });
                
                var row = new Array();
                var $k = 0, $v = 0, $_tmpd = '';
                var rows = [];
                var min = {}, max = {};
                var monthNames = {
                    1:'january',
                    2:'february',
                    3:'march',
                    4:'april',
                    5:'may',
                    6:'june',
                    7:'july',
                    8:'august',
                    9:'september',
                    10:'october',
                    11:'november',
                    12:'december',
                };
                var _period;
                
                $.each(_data, function(i,e ){
                    row = new Array();
                    $k = 0;
                    row[$k] = new Date(e.period_full);
                    if ($('select[name=type]').val() == 'hourly' || $('select[name=type]').val() == 'quarterly'){
                        try{
                            $_tmpd = new Date(e.period_full);
                            if ($('select[name=type]').val() == 'hourly'){
                              _period = $_tmpd.getDate() + ' ' + $tranlations['DATEPICKER_MONTH_' + monthNames[$_tmpd.getMonth()+1].toUpperCase()] + ' ' + $_tmpd.getFullYear() + ' ' + e.period;
                            } else if ($('select[name=type]').val() == 'quarterly'){
                              _period = $tranlations['DATEPICKER_MONTH_' + monthNames[$_tmpd.getMonth()+1].toUpperCase()] + ' ' + $_tmpd.getFullYear();
                            }
                        } catch(err){
                            _period = '';
                        }
                    } else {
                        _period = e.period;
                    }
                    $.each(fields, function(ii, fieldname){
                        $k++; 
                        $v = parseFloat(e[fieldname]);
                        $v = (!isNaN($v)? $v: 0);
                        if (!min.hasOwnProperty(fieldname)) {
                            min[fieldname] = {};
                            min[fieldname]['value'] = $v;
                            min[fieldname]['date'] = _period;
                            min[fieldname]['color'] = $use_colors[ii];
                         }
                        if (!max.hasOwnProperty(fieldname)) {
                            max[fieldname] = {};
                            max[fieldname]['value'] = $v;
                            max[fieldname]['date'] = _period;
                            max[fieldname]['color'] = $use_colors[ii];
                        }
                        if ($v <= min[fieldname]['value']) {
                            min[fieldname]['value'] = $v;
                            min[fieldname]['date'] = _period;
                            min[fieldname]['color'] = $use_colors[ii];
                        }
                        if ($v >= max[fieldname]['value']) {
                            max[fieldname]['value'] = $v;
                            max[fieldname]['date'] = _period;
                            max[fieldname]['color'] = $use_colors[ii];
                        }
                        row[$k] = $v;
                    });
                   rows.push(row);
                   
                });
    
                data.addRows(rows);
                
                var mm_holder = $('#'+chart_holder_id).next();
                $.each(min, function(mi, me){
                    $(".mm_"+mi, mm_holder).find('min value').html((skip?me.value:$settings.getFormatted(me.value))).css('color', me.color);
                    $(".mm_"+mi, mm_holder).find('min date').html(me.date);
                });
                $.each(max, function(mi, me){
                    $(".mm_"+mi, mm_holder).find('max value').html((skip?me.value:$settings.getFormatted(me.value))).css('color', me.color);
                    $(".mm_"+mi, mm_holder).find('max date').html(me.date);
                });

                var chart = new google.visualization.AnnotationChart(document.getElementById(chart_holder_id));

                var options = {
                  allowHtml:true,
                  displayAnnotations: true,
                  displayExactValues: true,
                  displayAnnotationsFilter:  false,
                  displayZoomButtons: false,
                  colors: $use_colors,
                  fill:10,
                  scaleColumns:[1],
                  scaleFormat: (!skip ? curr_hex[currency_id].symbol_left+"#"+curr_hex[currency_id].symbol_right:'#'),
                  scaleType: 'maximized',
                  min:0,
                  numberFormats:'#.##',
                };

                chart.draw(data, options);
                var _prev = $('#'+chart_holder_id).prev();
                chartsArray[$(_prev).find('.export-block').data('chart')] = chart;
            }
        },
        range: '',
        rows: 25,
    };
    var canvas, mainObj;
    var $exports = {
        downloadCSV: function(parent, href){
            //var params = $('form[name=sales]').serialize()+'&ex_type='+$(parent).find('input.export:checked').val()+'&ex_data='+$(parent).attr('data-chart');
            var form = $('form[name=sales]')[0];
            form.setAttribute('action', href);
            form.setAttribute('method', 'get');
            var ex_type = document.createElement('input');
            ex_type.setAttribute('type', 'hidden');
            ex_type.setAttribute('name', 'ex_type');
            ex_type.value = $(parent).find('input.export:checked').val();
            try{
                form.removeChild($('input[name=ex_type]'));
            }catch(err){}
            form.appendChild(ex_type);
            var ex_data = document.createElement('input');
            ex_data.setAttribute('name', 'ex_data');
            ex_data.setAttribute('type', 'hidden');
            ex_data.value = $(parent).attr('data-chart');
            try{
                form.removeChild($('input[name=ex_data]'));
            }catch(err){}            
            form.appendChild(ex_data);
            //form.append('<input type="hidden" name="ex_type" value="'+$(parent).find('input.export:checked').val()+'">');
            //form.append('<input type="hidden" name="ex_data" value="'+$(parent).attr('data-chart')+'">');
            var chart_range;
            if (chartsArray.hasOwnProperty($(parent).attr('data-chart'))){
                chart_range = chartsArray[$(parent).attr('data-chart')].getVisibleChartRange();
                var chart_range_start = new Date(chart_range.start);
                var chart_range_end = new Date(chart_range.end);  
                //params = params+'&start='+chart_range_start.getTime()+'&end='+chart_range_end.getTime();
                //form.append('<input type="hidden" name="start" value="'+chart_range_start.getTime()+'">');
                var start = document.createElement('input');
                start.setAttribute('name', 'start');
                start.setAttribute('type', 'hidden');
                start.value = chart_range_start.getTime();
                
                try{
                    form.removeChild($('input[name=start]'));
                }catch(err){}
                form.appendChild(start);
                //form.append('<input type="hidden" name="end" value="'+chart_range_end.getTime()+'">');
                var end = document.createElement('input');
                end.setAttribute('name', 'end');
                end.setAttribute('type', 'hidden');
                end.value = chart_range_end.getTime();
                try{
                    form.removeChild($('input[name=end]'));
                }catch(err){}
                form.appendChild(end);
            } 
            form.submit();
            /*$.get(href, 
                params,
                function(data, status, e){
                        var filename = e.getResponseHeader('Content-Filename');
                        var reader = new FileReader();
                        reader.onload = function(e) {
                          $(parent).find('a.blind').attr({ "href": e.target.result, "download": filename }).get(0).click();
                        }
                        reader.readAsDataURL(new Blob([data]));
                });*/
        },
        svgs:[],
        downloadXLS: function(parent, href){
                    function datenum(v, date1904) {
                        if(date1904) v+=1462;
                        var epoch = Date.parse(v);
                        return (epoch - new Date(Date.UTC(1899, 11, 30))) / (24 * 60 * 60 * 1000);
                    }
                     
                    function sheet_from_array_of_arrays(data, opts) {
                        var ws = {};
                        var range = { s: { c:10000000, r:10000000 }, e: { c:0, r:0 } };
                        for(var R = 0; R != data.length; ++R) {
                            for(var C = 0; C != data[R].length; ++C) {
                                if(range.s.r > R) range.s.r = R;
                                if(range.s.c > C) range.s.c = C;
                                if(range.e.r < R) range.e.r = R;
                                if(range.e.c < C) range.e.c = C;
                                var cell = { v: data[R][C] };
                                if(cell.v == null) continue;
                                var cell_ref = XLSX.utils.encode_cell({ c:C,r:R });
                                
                                if(typeof cell.v === 'number') cell.t = 'n';
                                else if(typeof cell.v === 'boolean') cell.t = 'b';
                                else if(cell.v instanceof Date) {
                                    cell.t = 'n'; cell.z = XLSX.SSF._table[14];
                                    cell.v = datenum(cell.v);
                                }
                                else cell.t = 's';
                                cell.s = { font: { bold : true } };//??doesn't work
                                ws[cell_ref] = cell;
                            }
                        }
                        if(range.s.c < 10000000) ws['!ref'] = XLSX.utils.encode_range(range);
                        return ws;
                    }
                     
                    /* original data */
                    var data = [];
                    var header = [];
                    var row = [], unaviableFields = ['period_full'];
                    
                    data.push([$(parent).parents('.widget-header').find('h4 span b').text() + " " + $(parent).parents('.widget-header').find('h4 span small i').text()]);
                    $.each($settings.getColumns(), function (i, e){
                        if (unaviableFields.indexOf(e.data) == -1){
                            header.push(e.title);
                        }
                    });
                    data.push(header);
                    function RowPush(e){
                        $.each(e, function(ii, ee){
                            if (Array.isArray(ee)){
                            } else {
                                if (unaviableFields.indexOf(ii) == -1){
                                    if (ee.length > 0 ){
                                        row.push(ee.replace(/(<([^>]+)>)/ig,""));
                                    } else {
                                        row.push(ee);
                                    }
                                }
                            }
                        });
                        data.push(row);
                    }
                    
                    var info_date;
                    var chart_range;
                    if (chartsArray.hasOwnProperty($(parent).attr('data-chart'))){
                        chart_range = chartsArray[$(parent).attr('data-chart')].getVisibleChartRange();
                        var chart_range_start = new Date(chart_range.start);
                        var chart_range_end = new Date(chart_range.end);
                    }
                    $.each($settings.getData(), function(i, e){
                        row = [];
                        info_date = new Date(e.period_full);
                        if (chart_range != undefined){
                            if ( info_date.getTime() >= chart_range_start.getTime()-86400 && info_date.getTime() <= chart_range_end.getTime() ) {
                                RowPush(e);
                            }
                        } else {
                            RowPush(e);
                        }
                        if (e.hasOwnProperty('products') && Array.isArray(e.products)){
                            var _name;
                            $.each(e.products, function(ii, product){
                                _name = product.products_name;
                                if (product.products_model.length>0){ _name = _name + ' ('+product.products_model+')'; }
                                data.push([ _name, product.products_quantity, product.final_price ]);
                            })
                        }
                    } );
                    
                    var ws_name = "Export";
                     
                    function Workbook() {
                        if(!(this instanceof Workbook)) return new Workbook();
                        this.SheetNames = [];
                        this.Sheets = {};
                    }
                     
                    var wb = new Workbook(), ws = sheet_from_array_of_arrays(data);
                     
                    /* add worksheet to workbook */
                    wb.SheetNames.push(ws_name);
                    wb.Sheets[ws_name] = ws;
                    var svgs = $(parent).parents('.widget.box').find('svg'), s = '', img;
                    wb.Sheets[ws_name]['!images'] = [];
                    
                    function saveToFile(wb){
                        var wbout = XLSX.write(wb, { bookType:'xlsx', bookSST:true, type: 'binary' });

                        function s2ab(s) {
                            var buf = new ArrayBuffer(s.length);
                            var view = new Uint8Array(buf);
                            for (var i=0; i!=s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
                            return buf;
                        }
                        var d = new Date();
                        saveAs(new Blob([s2ab(wbout)],{ type:"application/octet-stream" }),  "sale_statistics_" +d.getDate() + d.getMonth() + "_" + d.getFullYear() + d.getHours() + d.getMinutes() + d.getSeconds() + ".xlsx");
                    }
                                        
                    if (svgs.length > 0){
                        var serializer = new XMLSerializer();
                        var svgStr = serializer.serializeToString($(svgs[0]).get(0));
                        canvas = new fabric.Canvas('canvas');
                        canvas.setHeight($(svgs[0]).height()+$(svgs[1]).height()+15);
                        canvas.setWidth($(svgs[0]).width());
                        var path = fabric.loadSVGFromString(svgStr,function(objects, options) {
                          mainObj = fabric.util.groupSVGElements(objects, options);
                          mainObj.scaleToWidth(canvas.width-50)
                            .setLeft((mainObj.width-canvas.width)/2)
                            .setTop((mainObj.height-canvas.height)/2 + 25)
                            .setCoords();
                          canvas.add(mainObj).renderAll();
                        });
                        svgStr = serializer.serializeToString($(svgs[1]).get(0));
                        path = fabric.loadSVGFromString(svgStr,function(objects, options) {
                          var obj = fabric.util.groupSVGElements(objects, options);
                          obj.scaleToWidth(canvas.width-50)
                             .setLeft((obj.width-canvas.width)/2)
                             .setTop(canvas.height - obj.height + 25)
                             .setCoords();
                          canvas.add(obj).renderAll();
                        });
                        
                        var _img = new Image();
                        _img.picSrc = canvas.toDataURL("image/png");
                        _img.picBlob = _img.picSrc.split(',')[1];
                        img = { 
                                name: 'image1.jpg',
                                data: _img.picBlob,
                                opts: { base64: true },
                                position: {
                                    type: 'twoCellAnchor',
                                    attrs: { editAs: 'oneCell' },
                                    from: { col: 0, row : data.length+3 },
                                    to: { col: 15, row: data.length+23 }
                                }
                        };
                        wb.Sheets[ws_name]['!images'].push( img );
                        saveToFile(wb);
                    } else {
                        saveToFile(wb);
                    }
                    
        },
        
    }
    
    var updatePrecision = function(value){
        $settings.updatePrecision(value);
    }
    
    var updateData = function(value){
        if (typeof checkSelection == 'function'){
            var result = checkSelection();
            if (!result) {
                alertMessage(getWrapedMessage('{$smarty.const.ERROR_DATE_RANGE}'));
                return false;
            }
        }
        setFilterState();
        $settings.updateData();
    }
    var ex;
    jQuery(function($){
    
        {foreach $columns as $value}
        $settings.setColumns({ data:'{$value.class}', title :'{$value.title}' });
        {/foreach}
        
        var $row = {};
        
        {foreach $data as $value}
            $row = {};
            
            {foreach $value as $k => $v}
                {if is_array($v)}
                    $row.{$k} = JSON.parse('{json_encode($v)}');
                {else}
                    $row.{$k} = "{$v}";
                {/if}
            {/foreach}
            
        $settings.setData($row);
        
        {/foreach}  

        $settings.range = "{$range}";
        $settings.rows = {$rows};
        {if is_array($class_range)}
            {foreach $class_range as $cr}
                $settings.class_range.push({$cr});
            {/foreach}
        {/if}
        
        
        $settings.setDependance();

        $('.show-map').popUp({ box_class: 'map-stat'});
        
        $('body').on('click', '.export-btn', function(e){
            e.preventDefault();
            var parent = $(this).parents('.export-block');
            var href = $(this).attr('href');
            if ($(parent).find('input.export:checked').val() == 'XLS'){
                $exports.downloadXLS(parent, href);
            } else {
                $exports.downloadCSV(parent, href);
            }
            
        });
        
    });

    google.charts.load('current', { 'packages':['annotationchart'] });
    google.charts.setOnLoadCallback(drawCharts);    
    
    function drawCharts(){
        $settings.charts.drawCollection();
        $settings.renderTable();    
    }

    /*$("form select[data-role=multiselect]").multiselect({
                selectedList: 1 // 0-based index
    });*/
    
    $(document).ready(function(){
        $("form select[data-role=multiselect]").multipleSelect({
                multiple: true,
                filter: true
        });
    })
    
    
            
</script>

<div style="height: 50px;clear: left;"></div>
