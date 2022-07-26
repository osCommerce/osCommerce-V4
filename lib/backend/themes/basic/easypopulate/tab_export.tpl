{use class="yii\helpers\Html"}
<div class="widget box">
    <div class="widget-header">
        <h4><i class="icon-download"></i><span id="easypopulate_download_files_title">{$smarty.const.DOWNLOAD_EP_FILES}</span>
        </h4>
    </div>
        <div class="widget-content fields_style" id="easypopulate_download_files_data" style="min-height: 122px;">
        <form id="frmDownload" action="{$download_form_action}" method="post">
          {Html::hiddenInput('directory_id',{$current_directory_id},['class'=>'js-currentDirectoryId'])}
          {Html::hiddenInput('selected_fields','')}
          {if $currentDirectory->cron_enabled}
              {Html::hiddenInput('new_job','1')}
          {/if}
        <div class="row">
            <div class="col-md-6">
                <label>{$smarty.const.TEXT_DOWNLOAD_FILE_SELECT}</label>
                {Html::dropDownList('export_provider', $export_options['selection'], $export_options['items'], $export_options['options'])}
            </div>
            <div class="col-md-6">
                <label>{$smarty.const.TEXT_EXPORT_FILE_FORMAT}</label>
                {Html::dropDownList('format', $download_format_down_data['selection'], $download_format_down_data['items'], ['class' => 'form-control'])}
            </div>
        </div>
        <div class="row export_filter_row js-filter-project">
            <div class="col-md-12">
                <label>XML projects</label>
                {Html::dropDownList('filter[projectId]', $filter_defaults['project']['value'], $filter_defaults['project']['items'], ['class'=>'form-control form-control-small'])}
            </div>
        </div>
        <div class="row export_filter_row js-filter-platform">
            <div class="col-md-12">
                <label>{$smarty.const.TABLE_HEADING_PLATFORM}</label>
                <span class="select_filter_platforms select_filter">
                  <input type="hidden" name="filter[platform_id]" value="">
                  <input type="text" id="platforms_ac" name="none" value="">
                </span>
            </div>
        </div>
        <div class="row export_filter_row js-filter-category">
          <div class="col-md-12">
            <label>{$smarty.const.TEXT_SELECT_CATEGORY}</label>
              <span class="select_filter_categories select_filter">
                  <input type="hidden" name="filter[category_id]" value="">
                  <input type="text" id="categories_ac" name="none" value="">
              </span>
          </div>
        </div>
        <div class="row export_filter_row js-filter-products">
            <div class="col-md-12">
                <label>Products</label>
                <div id="filtered-product-list"></div>
                <script id="productSelected" type="text/tpl">
                <div class="filtered-product">
                    <input type="hidden" name="filter[products_id][]" value="%%products_id%%">
                    <a href="javascript:void(0)" class="job-button js-remove-filtered-product"><i class="icon-trash"></i></a>
                    <span>%%products_name%%</span>
                </div>
                </script>
                <span class="select_filter_products">
                  <input type="text" id="products_ac" name="none" value="">
                </span>
            </div>
        </div>
        <div class="row export_filter_row js-filter-properties">
            <div class="col-md-12">
                <label>{$smarty.const.TEXT_SELECT_PROPERTIES}</label>
                <span class="select_filter_properties">{$select_filter_properties}</span>
            </div>
        </div>
        <div class="row export_filter_row js-filter-with-images">
            <div class="col-md-12">
                <label>{$smarty.const.TEXT_INCLUDE_IMAGE_FILES}
                  <input type="checkbox" name="filter[with_images]" value="1" id="chkZipContainer">
                </label>
            </div>
        </div>
        <div class="row export_filter_row js-filter-price_tax">
            <div class="col-md-12">
                <label>Price with tax
                    <input type="checkbox" name="filter[price_tax]" value="1">
                </label>
            </div>
        </div>

        <div class="row export_filter_row js-filter-orders-date-range">
            <div class="col-md-12">
                <div class="tl_filters_title">{$smarty.const.TEXT_ORDER_PLACED}</div>
                <div class="wl-td w-tdc js-select-radio-parent">
                    <label class="radio_label"><input type="radio" class="js-disable-date-ctrl" name="filter[order][date_type_range]" value="presel" {if $filter_defaults['order']['date_type_range']['value']=='presel'}checked{/if} /> {$smarty.const.TEXT_PRE_SELECTED}</label>
                    {Html::dropDownList('filter[order][interval]', $filter_defaults['order']['interval']['value'], $filter_defaults['order']['interval']['items'], ['class'=>'form-control'])}
                </div>
                <div class="wl-td w-tdc js-select-radio-parent">
                    <label class="radio_label"><input type="radio" class="js-disable-date-ctrl" name="filter[order][date_type_range]" value="year/month" {if $filter_defaults['order']['date_type_range']['value']=='group'}checked{/if} /> Year/Month:</label>
                    <table cellpadding="0" cellspacing="0" style="width: auto"><tr><td>
                    {Html::dropDownList('filter[order][year]', $filter_defaults['order']['year']['value'], $filter_defaults['order']['year']['items'], ['class'=>'form-control form-control-small'])}
                    </td><td>
                    &nbsp;/&nbsp;
                    </td><td>
                    {Html::dropDownList('filter[order][month]', $filter_defaults['order']['month']['value'], $filter_defaults['order']['month']['items'], ['class'=>'form-control form-control-small'])}
                    </td></tr></table>
                </div>
                <div class="wl-td wl-td-from w-tdc js-select-radio-parent">
                    <label class="radio_label">
                        <input type="radio" class="js-disable-date-ctrl" name="filter[order][date_type_range]" value="exact" {if $filter_defaults['order']['date_type_range']['value']=='exact'}checked{/if} /> {$smarty.const.TEXT_EXACT_DATES}</label>
                        <table cellpadding="0" cellspacing="0" style="width: auto"><tr><td><span>{$smarty.const.TEXT_FROM}</span><input type="text" value="" autocomplete="off" name="filter[order][date_from]" class="datepicker form-control form-control-small" /></td><td>&nbsp;<span class="sp_marg">{$smarty.const.TEXT_TO}</span><input type="text" value="" autocomplete="off" name="filter[order][date_to]" class="datepicker form-control form-control-small"/></td></tr></table>
                </div>
            </div>
        </div>
        {if $currentDirectory->cron_enabled}
        <div class="row">
            <div class="col-md-12">
                <label>Filename</label>
                <input type="text" name="export_filename" value="" class="form-control">
            </div>
        </div>

        <div class="row" style="margin-top:10px">
            <div class="col-md-12 text-right">
                <button type="submit" onclick="$(this.form).trigger('submit',[false, true]);return false;" class="btn btn-primary">Save as export job</button>
            </div>
        </div>
        {else}
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary" >{$smarty.const.IMAGE_DOWNLOAD}</button>
            </div>
        </div>
        {/if}
        </form>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function(){
    // ----- filters -----
    $('#categories_ac, #platforms_ac, #products_ac').on('keydown',function(event){
        if (event.which == 13 || event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
    $('#categories_ac').autocomplete({
        //source: source_array,
        appendTo: '.select_filter_categories',
        source: "{$select_filter_categories_auto_complete_url}",
        minLength: 0,
        autoFocus: true,
        delay: 400,
        search: function( event, ui ) {
            $('.js-filter-category input[name="filter\[category_id\]"]').val(0);
        },
        select: function( event, ui ) {
            event.preventDefault();
            $('.js-filter-category input[name="filter\[category_id\]"]').val(ui.item.id);
            $('#categories_ac').val(ui.item.value);
            $('#categories_ac').trigger('blur');
        }
    }).focus(function () {
        $('#categories_ac').val('');
        $(this).autocomplete("search");
    });

    $('#products_ac').autocomplete({
        //source: source_array,
        appendTo: '.select_filter_products',
        //source: "{$select_filter_products_auto_complete_url}",
        source: function(request, response) {
            var exclude_pids = [];
            $('#filtered-product-list input[name="filter[products_id][]"]').map(function(){
                exclude_pids.push($(this).val());
            });
            $.ajax({
                url: "{$select_filter_products_auto_complete_url}",
                dataType:'json',
                data: {
                    exclude_pids: exclude_pids,
                    term: request.term
                },
                success: function (data){
                    response(data);
                }
            });
        },
        minLength: 0,
        autoFocus: true,
        delay: 400,
        select: function( event, ui ) {
            event.preventDefault();
            var rowTemplate = $('#productSelected').html();
            rowTemplate = rowTemplate.replace(/%%products_id%%/g,ui.item.id);
            rowTemplate = rowTemplate.replace(/%%products_name%%/g, (ui.item.model?('['+ui.item.model+']&nbsp;'):'')+ui.item.text);
            $('#filtered-product-list').append(rowTemplate);
            //$('#products_ac').val(ui.item.value);
            $('#products_ac').trigger('blur');
        }
    }).focus(function () {
        //$('#products_ac').val('');
        $(this).autocomplete("search");
    });
    $('#products_ac').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        if ( this.term && this.term!='>' ) {
            item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<span class="hilite_match">$1</span>');
        }
        return $( "<li>" )
            .data("item.autocomplete", item)
            .append( "<a>"+(item.model?('['+item.model+']&nbsp;'):'') + item.text + "</a>" )
            .appendTo( ul );
    };
    $('#filtered-product-list').on('click', '.js-remove-filtered-product', function(e){
        $(e.currentTarget).parents('.filtered-product').remove();
        return false;
    });

    $('#platforms_ac').autocomplete({
        //source: source_array,
        appendTo: '.select_filter_platforms',
        source: {$select_filter_platform_variants|json_encode},
        minLength: 0,
        autoFocus: true,
        delay: 400,
        search: function( event, ui ) {
            $('.js-filter-platform input[name="filter\[platform_id\]"]').val(0);
        },
        select: function( event, ui ) {
            event.preventDefault();
            $('.js-filter-platform input[name="filter\[platform_id\]"]').val(ui.item.id);
            $('#platforms_ac').val(ui.item.value);
            $('#platforms_ac').trigger('blur');
        }
    }).focus(function () {
        $('#platforms_ac').val('');
        $(this).autocomplete("search");
    });
    $('#categories_ac').autocomplete().data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        if ( this.term && this.term!='>' ) {
            item.text = item.text.replace(new RegExp('(' + $.ui.autocomplete.escapeRegex(this.term) + ')', 'gi'), '<span class="hilite_match">$1</span>');
        }
        return $( "<li>" )
            .data("item.autocomplete", item)
            .append( "<a>" + item.text + "</a>" )
            .appendTo( ul );
    };

    $('#categories_ac, #platforms_ac, #products_ac').autocomplete().data('ui-autocomplete')._renderMenu = function( ul, items ) {
         var that = this;
         $.each( items, function( index, item ) {
           that._renderItemData( ul, item );
         });
         $( ul ).removeClass('ui-autocomplete').addClass( "ui-autocomplete_f_important" );
    };
    $( ".datepicker" ).datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}',
            onSelect: function() {
                if ($(this).val().length > 0) {
                    $(this).siblings('span').addClass('active_options');
                }else{
                    $(this).siblings('span').removeClass('active_options');
                }
            }
        });

    var $frmDownload = $('#frmDownload');
    $frmDownload.find('[name="format"]').find('option[value!=""]').not('[selected]').hide();
    $frmDownload
        .find('[name="export_provider"]')
        .on('change',function() {
            $frmDownload.find('[name="selected_fields"]').val('');
            var $format = $frmDownload.find('[name="format"]');
            var selectedOption = this.options[this.selectedIndex];
            var $filterRows = $frmDownload.find('.export_filter_row');
            $filterRows.hide();
            if ( selectedOption.attributes && selectedOption.attributes.length>0 ) {
                for(var i = selectedOption.attributes.length - 1; i >= 0; i--) {
                    if ( selectedOption.attributes[i].name.indexOf('data-allow-select-')===0 && selectedOption.attributes[i].value=='true' ) {
                        $filterRows.filter('.js-filter-'+(selectedOption.attributes[i].name.replace('data-allow-select-',''))).show();
                    }
                    if ( $format.length==1 && selectedOption.attributes[i].name.indexOf('data-allow-format')===0 ) {
                        $format.find('option').hide();
                        var allowedFormats = selectedOption.attributes[i].value.split(',');
                        allowedFormats.push('');
                        for( var __i=0; __i<allowedFormats.length; __i++ ) {
                            $format.find('option[value="'+allowedFormats[__i]+'"]').show();
                        }
                        if (('|'+allowedFormats.join('|')+'|').indexOf('|'+$format.val()+'|')===-1) {
                            $format.val(allowedFormats[0]).trigger('change');
                        }
                    }
                }
            }
        });
    $frmDownload
        .on('submit',function(event, selection_complete, saveAsExportJob) {
            // select export fields or/and start download
            selection_complete = selection_complete || false;
            saveAsExportJob = saveAsExportJob || false;
            var $_form = $(this);
            var $export_provider = $_form.find('[name="export_provider"]');
            if ( $export_provider.val()=='' ) {
                // select export_provider
                return false;
            }
            if ( saveAsExportJob || (!selection_complete && ($($export_provider[0].options[$export_provider[0].selectedIndex]).attr('data-select-fields') || '')=='true') ) {
                presetHide();
                $('#popupSelectExportFields').show();
                var $form = $('#frmDownload');
                var formData = $form.serializeArray();
                if (saveAsExportJob){
                    formData.push({
                        name: 'saveAsExportJob',
                        value: 1
                    });
                }
                $.ajax({
                    url:'{$get_fields_action}',
                    type:$form.attr('method'),
                    data:formData,
                    success:function(data){
                        var $table_target = $('#popupSelectExportFields .js-export_columns');

                        var $table = $table_target.DataTable();
                        $table.clear();
                        if ( data.length>0 ) {
                            var row_data = [];
                            var all_checked = true;
                            for (var i = 0; i < data.length; i++) {
                                row_data.push([
                                    '<input type="checkbox" class="uniform" name="field" value="' + data[i].db_key + '" id="chkExp' + data[i].db_key + '" ' + (data[i].selected ? ' checked="checked" ' : '') + '>',
                                    '<label for="chkExp' + data[i].db_key + '">' + data[i].title + '</label>'
                                ]);
                                if (!data[i].selected) {
                                    all_checked = false;
                                }
                            }
                            $table_target.css('width', '100%');
                            $table.rows.add(row_data).draw();
                            $table_target.trigger('checkboxes:init');
                            /*$table.rows.add($.map(data,function(row){
                                return [[
                                    row.db_key,
                                    row.title,
                                ]];
                            })).draw();*/
                        }else{
                            $('#frmDownload').trigger('export_fields_confirmed');
                        }
                    }
                });
                return false;
            }
            {if $currentDirectory->cron_enabled}
            $.ajax({
                url:$frmDownload.attr('action'),
                type:$frmDownload.attr('method'),
                data:$frmDownload.serializeArray(),
                success:function(data){
                    if ( data.status=='ok' ) {
                        uploader('reload_file_list');
                    }else if(data.dialog){
                        bootbox.dialog($.extend(true, bootboxDefaults,data.dialog || { }));
                    }
                }
            });
            return false;
            {else}
            return true;
            {/if}
        });
    $('#chkZipContainer').on('click',function() {
        if ( this.checked ) {
            var $formatSelect = $('#frmDownload [name="format"]');
            var value = $formatSelect.val();
            var $ZipOptions = $formatSelect.find('option[value$="ZIP"]').filter(function(i, elem){
                return $(elem).css('display')!=='none';
            });
            if ( $ZipOptions.length==1 ) {
                $formatSelect.val($ZipOptions.val());
                return true;
            }else if( $ZipOptions.length>1 ) {
                var $preffered = $formatSelect.filter('option[value^="'+value+'"]');
                if ( $preffered.length==1 ) {
                    $formatSelect.val($preffered.val());
                    return true;
                }
            }
            return false;
        }
    });
    $('#frmDownload [name="format"]').on('change',function(){
        if ( (''+$(this).val()).indexOf('ZIP')===-1 && $('#chkZipContainer').length>0 ) {
            $('#chkZipContainer').get(0).checked = false;
        }
    });
    $('.js-select-radio-parent :input').on('focus',function(){
        if ( (this.type||'').toLowerCase()=='radio' ) return;
        $(this).parents('.js-select-radio-parent').find('input[type="radio"]').first().trigger('click');
    });
});
</script>