{use class="common\helpers\Html"}
{use class="yii\helpers\Url"}
{\backend\assets\MultiSelectAsset::register($this)|void}

{$message}

<div id="platforms_management_data">
    <form action="edit.tpl" name="save_item_form" id="save_item_form" enctype="multipart/form-data" onsubmit="return saveItem();">

        <div class="widget box box-no-shadow">
            <div class="widget-header widget-header-theme"><h4>{$smarty.const.TEXT_LANGUAGES_} &amp; {$smarty.const.TEXT_CURRENCIES}</h4></div>
            <div class="widget-content">
                {include $pass|cat: '/themes/basic/platforms/edit/language.tpl'}
                {include $pass|cat: '/themes/basic/platforms/edit/currency.tpl'}
            </div>
        </div>


        {if $pInfo->platform_id }
            <div class="widget box box-no-shadow">
                <div class="widget-header widget-header-company"><h4>{$smarty.const.CATEGORY_FORMATS}</h4></div>
                <div class="widget-content">
                    <div class="w-line-row w-line-row-2-big">
                        <div class="format_wr">
                            <center><a href="{Yii::$app->urlManager->createUrl(['platforms/define-formats', 'id'=>$pInfo->platform_id, 'no_redirect'=>1])}" class="btn popup" data-class="define-date-formats">{$smarty.const.TEXT_DEFINE_FORMATS}</a></center>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <div class="widget box box-no-shadow">
            <div class="widget-header widget-header-theme"><h4>{$smarty.const.TEXT_LOCATION_SERVICE}</h4></div>
            <div class="widget-content">
                <table class="tl-grid js-platform-locations">
                    <thead>
                    <tr>
                        <th>{$smarty.const.BOX_TAXES_COUNTRIES}</th>
                        <th>{$smarty.const.BOX_LOCALIZATION_LANGUAGES}</th>
                        <th>{$smarty.const.BOX_LOCALIZATION_CURRENCIES}</th>
                        <th style="width: 30px">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody data-rows-count="{count($pInfo->platform_locations)}">
                    {foreach from=$pInfo->platform_locations item=platform_location key=idx}
                        {assign var="row_index" value=$idx+1}
                        <tr>
                            <td>{Html::hiddenInput('platform_locations['|cat:$row_index|cat:'][platforms_locations_id]', $platform_location['platforms_locations_id'])}
                                {Html::dropDownList('platform_locations['|cat:$row_index|cat:'][country]', $platform_location['country'], \common\helpers\Country::new_get_countries('', false), ['class' => 'form-control'])}</td>
                            <td>{Html::dropDownList('platform_locations['|cat:$row_index|cat:'][language]',$platform_location['language'],$app->controller->view->languages, ['class' => 'form-control'])}</td>
                            <td>{Html::dropDownList('platform_locations['|cat:$row_index|cat:'][currency]',$platform_location['currency'],$app->controller->view->currencies, ['class' => 'form-control'])}</td>
                            <td><button type="button" class="btn js-remove-platform-location">-</button></td>
                        </tr>
                    {/foreach}
                    </tbody>
                    <tfoot style="display: none">
                    <tr>
                        <td>{Html::dropDownList('_unhide_platform_locations[%idx%][country]', $addresses->entry_country_id, \common\helpers\Country::new_get_countries('', false), ['class' => 'form-control'])}</td>
                        <td>{Html::dropDownList('_unhide_platform_locations[%idx%][language]',$smarty.const.DEFAULT_LANGUAGE,$app->controller->view->languages, ['class' => 'form-control'])}</td>
                        <td>{Html::dropDownList('_unhide_platform_locations[%idx%][currency]',$smarty.const.DEFAULT_CURRENCY,$app->controller->view->currencies, ['class' => 'form-control'])}</td>
                        <td><button type="button" class="btn js-remove-platform-location">-</button></td>
                    </tr>
                    </tfoot>
                </table>
                &nbsp;
                <div class="buttons_hours">
                    <button type="button" class="btn js-add-platform-location">{$smarty.const.TEXT_ADD_MORE}</button>
                </div>
            </div>
        </div>

        {include $pass|cat: '/themes/basic/platforms/edit/restriction.tpl'}

        {Html::input('hidden', 'id', $pInfo->platform_id)}
        <div class="btn-bar">
            <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
            <div class="btn-right"><button type="submit" class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
        </div>
    </form>

</div>

{*<link href="{$app->request->baseUrl}/plugins/jquery-ui/multiple-select.css" rel="stylesheet" type="text/css" />*}
{*<script type="text/javascript" src="{$app->request->baseUrl}/plugins/jquery-ui/multiple-select.js"></script>*}
<script>
    function saveItem() {
        $.post("{Url::toRoute('configure-localization')}", $('#save_item_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#platforms_management_data').html(data);
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
    function backStatement() {
        window.history.back();
        return false;
    }
    $(document).ready(function(){
        $("select[data-role=multiselect]").multipleSelect({
            //selectedList: 1 // 0-based index
        });

        $('.format_wr .popup').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box theme_popup'><div class='pop-up-close pop-up-close-alert'></div><div class='popup-heading theme_choose'>{$smarty.const.CATEGORY_FORMATS}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });

        $('.js-rate-margin').on('keyup keydown', function(){
            var rate_margin = $(this).val()+ $(this).next().val();
            $('.currency_'+$(this).data('code')).find('.currency_margin').text(rate_margin);
        })
        $('.js-rate-margin').on('focusout', function(){
            if($(this).val() == ''){
                $(this).val(0);
                $('.currency_'+$(this).data('code')).find('.currency_margin').text(0+$(this).next().val());
            }

        })
        $('.margin_type').change(function(){
            var rate_margin = $(this).val()+ $(this).prev().val();
            $('.currency_'+$(this).prev().data('code')).find('.currency_margin').text(rate_margin);
        })

        $('.p_languages, .d_languages').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
        });

        $('.d_languages').on('switchChange.bootstrapSwitch',function(e, data){
            if(!$(this).parents('tr').find('.p_languages').is(':checked')){
                $(this).parents('tr').find('.p_languages').trigger('click');
            }
            $('.p_languages').bootstrapSwitch('disabled', false);
            $(this).parents('tr').find('.p_languages').bootstrapSwitch('disabled', false);
            $('.countries-table .default_check').html('');
            $('.lang_'+$(this).val()).find('.default_check').html('<span class="check"></span>');
            $('.lang_'+$(this).val()).removeClass('hide_row');
        });

        var fn_default_language_enable_switch = function(obj){
            if (obj.checked) {
                $(':radio[name=default_language][value='+$(obj).val()+']').prop('disabled', false);
                if ( $('input[name=default_language]:radio:checked').length ==0 )
                    $(':radio[name=default_language][value='+$(obj).val()+']').prop('checked', true);
                $('.lang_'+$(obj).val()).removeClass('hide_row');
                if(!$('.lang_'+$(obj).val()).hasClass('hide_row')){
                    $('.lang_'+$(obj).val()).find('.lang_status').html('<span class="check"></span>');
                }
            }else {
                if ($('.p_languages').length && $(':radio[name=default_language]:checked').val() == $(obj).val()){
                    var _ch = $('.p_languages:checked')[0];
                    $(':radio[name=default_language][value='+$(_ch).val()+']').prop('checked', true);
                }
                $(':radio[name=default_language][value='+$(obj).val()+']').prop('checked', false);
                $(':radio[name=default_language][value='+$(obj).val()+']').prop('disabled', true);
                $('.lang_'+$(obj).val()).addClass('hide_row');
            }
        }

        $.each($('.p_languages'), function(i, e){
            if(!$(e).prop('checked')){
                $(':radio[name=default_language][value='+$(e).val()+']').prop('disabled', true);
            }
        });

        $('.p_languages').on('click switchChange.bootstrapSwitch',function(){
            fn_default_language_enable_switch(this);
        });

        $('.p_currencies, .d_currencies').bootstrapSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px',
        });
        $('.d_currencies').on('switchChange.bootstrapSwitch',function(e, data){
            if(!$(this).parents('tr').find('.p_currencies').is(':checked')){
                $(this).parents('tr').find('.p_currencies').trigger('click');
            }
            $('.p_currencies').bootstrapSwitch('disabled', false);
            $(this).parents('tr').find('.p_currencies').bootstrapSwitch('disabled', true);
            $('.js-currency-table .currency_default').html('');
            $('.currency_'+$(this).val()).find('.currency_default').html('<span class="check"></span>');
            $('.currency_'+$(this).val()).removeClass('hide_row');
        });

        var fn_default_currency_enable_switch = function(obj){
            if (obj.checked) {
                $(':radio[name=default_currency][value='+$(obj).val()+']').prop('disabled', false);
                if ( $('input[name=default_currency]:radio:checked').length ==0 )
                    $(':radio[name=default_currency][value='+$(obj).val()+']').prop('checked', true);
                $('.currency_'+$(obj).val()).removeClass('hide_row');
                if($(obj).parents('tr').find('.js-custom-rate').is(':visible')){
                    var js_custom = $(obj).parents('tr').find('.js-custom-rate').val();
                }else{
                    var js_custom = $(obj).parents('tr').find('.form-group-sm label').text();
                }
                $('.currency_'+$(obj).val()).find('.currency_value label').text(js_custom);
                var js_rate = $(obj).parents('tr').find('td:last-child input').val() + $(obj).parents('tr').find('td:last-child select').val();
                $('.currency_'+$(obj).val()).find('.currency_margin').text(js_rate);
                //console.log($(obj));
            }else {
                if ($('.p_currencies').length && $(':radio[name=default_currency]:checked').val() == $(obj).val()){
                    var _ch = $('.p_currencies:checked')[0];
                    $(':radio[name=default_currency][value='+$(_ch).val()+']').prop('checked', true);
                }
                $(':radio[name=default_currency][value='+$(obj).val()+']').prop('checked', false);
                $(':radio[name=default_currency][value='+$(obj).val()+']').prop('disabled', true);
                $('.currency_'+$(obj).val()).addClass('hide_row');
            }
        }

        $('.d_currencies:checked').each(function(){
           $('.p_currencies[value='+$(this).val()+']').bootstrapSwitch('disabled', true);
        });

        $('.p_currencies').on('click switchChange.bootstrapSwitch',function(){
            fn_default_currency_enable_switch(this);
        });

        $('.js-currency-table').on('click','.js-use_default',function(){
            var $refControl = $(this).parents('td').find('.js-custom-rate');
            if ( this.checked ){
                $refControl.hide();
                $('.currency_'+$(this).data('code')).find('.currency_value').text($(this).parent('label').text());
            }else{
                $refControl.val($refControl.attr('data-default-value'));
                $refControl.show();
                $refControl.on('keyup keydown', function(){
                    $('.currency_'+$(this).data('code')).find('.currency_value').text($(this).val());
                })
                $refControl.on('focusout', function(){
                    if($(this).val() == $(this).attr('data-default-value')){
                        $(this).hide();
                        $(this).prev().find('.js-use_default').prop('checked',true);
                    }
                })
            }
        });
        $('.js-currency-table').find('.js-use_default:checked').each(function(){
            $(this).parents('tr').find('.js-custom-rate').hide();
        });


        $('.js-platform-locations').on('add_row',function(){
            var skelHtml = $(this).find('tfoot').html();
            var $body = $(this).find('tbody');
            var counter = parseInt($body.attr('data-rows-count'),10)+1;
            $body.attr('data-rows-count',counter);
            skelHtml = skelHtml.replace(/_unhide_/g,'',skelHtml);
            skelHtml = skelHtml.replace(/%idx%/g, counter,skelHtml);
            $body.append(skelHtml);
        });
        $('.js-platform-locations').on('click', '.js-remove-platform-location',function(event){
            $(event.target).parents('tr').remove();
        });
        $('.js-add-platform-location').on('click',function(){
            $('.js-platform-locations').trigger('add_row');
        });

        function muliCheckedSel(popup){
            var items = [];
            $('.multiselect option:selected', popup).each(function(){
                if($(this).text() != ''){
                    items.push($(this).text());
                }
            });
            var result = items.join(', ');
            popup.parents('.wl-td').find('.geo_zones').text(result);
        }
        muliCheckedSel($('#geo_zones'));
        muliCheckedSel($('#countries'));
        function muliChecked(popup){
            var items1 = [];

            $('.ms-drop .selected', popup).not('.group, .ms-select-all').each(function(){
                if($(this).text() != ''){
                    items1.push($(this).text());
                }
            });
            var result1 = items1.join(', ');
            popup.parents('.wl-td').find('.geo_zones').text(result1);
        }
        $('.popup_zones').off().on('click',function(){
            var popup = $($(this).attr('href'));
            popup.removeClass('hide_popup');
            $('#content, .content-container').css({ 'position': 'relative', 'z-index': '100'});
            var prev_zones = { };
            var prev_countries = { };
            var prev_zones = $('select[name="zones[]"]').val();
            var prev_countries = $('select[name="countries[]"]').val();

            var height = function(){
                var h = $(window).height() - $('.popup-heading', popup).height() - $('.popup-buttons', popup).height() - 120;
                $('.popup-content', popup).css('max-height', h);
            };
            height();
            $(window).on('resize', height);

            $('.pop-up-close-page, .apply-popup', popup).off().on('click', function(){
                popup.addClass('hide_popup');
                $(window).off('resize', height);
                $('#content, .content-container').css({ 'position': '', 'z-index': ''});
                return false;
            });

            $('.cancel-popup', popup).off().on('click', function(){
                if(!$.isEmptyObject(prev_zones)){
                    if($('.geo_zones_block',popup).length > 0) {
                        $('select[name="zones[]"]').multipleSelect('setSelects', prev_zones);
                    }
                }else{
                    if($('.geo_zones_block',popup).length > 0) {
                        $('select[name="zones[]"]').multipleSelect('setSelects', '');
                    }
                }
                if(!$.isEmptyObject(prev_countries)){
                    if ($('.countries_block', popup).length > 0) {
                        $('select[name="countries[]"]').multipleSelect('setSelects', prev_countries);
                    }
                }else{
                    if ($('.countries_block', popup).length > 0) {
                        $('select[name="countries[]"]').multipleSelect('setSelects', '');
                    }
                }
                popup.addClass('hide_popup');
                $(window).off('resize', height);
                $('#content, .content-container').css({ 'position': '', 'z-index': ''});
                muliChecked(popup);
                return false;
            })
            $('.ui-multiselect',popup).remove();
            if(!$('.ms-parent',popup).length){
                $('.multiselect', popup).multipleSelect({
                    filter: true,
                    place:'{$smarty.const.TEXT_SEARCH_ITEMS}',
                    isOpen: true,
                    onClick: function (element) {
                        muliChecked(popup);
                    },
                    onOptgroupClick:function(element){
                        muliChecked(popup);
                    },
                    onCheckAll: function () {
                        muliChecked(popup);
                    },
                    close: function (event, ui) {
                        $('.multiselect', popup).multipleSelect("open");
                    }
                });
            }
            return false;
        })
        $('.popup_lang').off().on('click',function(){
            var popup = $($(this).attr('href'));
            popup.removeClass('hide_popup');
            var popup_class = $('.'+$(this).data('class'));
            $('#content, .content-container').css({ 'position': 'relative', 'z-index': '100'});

            var height = function(){
                var h = $(window).height() - $('.popup-heading', popup).height() - $('.popup-buttons', popup).height() - 120;
                $('.popup-content', popup).css('max-height', h);
            };
            height();
            $(window).on('resize', height);
            var plang = { };
            $('.popup-content .countries-table tr').not(':first').each(function(){
                var lang_code = $(this).find('.p_languages').val();
                var lang_checked = $(this).find('.p_languages').prop('checked');
                var lang_default = $(this).find('.d_languages').prop('checked');
                plang[lang_code] = {
                    'p_languages': lang_checked,
                    'd_languages': lang_default
                }
            })

            var currncy_array = {};
            $('.popup-content .currency-table tr').not(':first').each(function(){
                var _cur = $(this).find('.p_currencies').val();
                var _cur_checked = $(this).find('.p_currencies').prop('checked');
                var d_cur = $(this).find('.d_currencies').prop('checked');
                var default_cur = $(this).find('.js-use_default').prop('checked');
                var custom_cur = $(this).find('.js-custom-rate').data('default-value');
                var rate_cur = $(this).find('.js-rate-margin').val();
                var type_cur = $(this).find('.margin_type').val();
                currncy_array[_cur] = {
                    'p_currencies':_cur_checked,
                    'd_currencies':d_cur,
                    'js-use_default':default_cur,
                    'js-custom-rate':custom_cur,
                    'js-rate-margin':rate_cur,
                    'margin_type':type_cur
                };
            })
            popup.find('.cancel-popup').off().on('click', function(){
                if($('.countries-table',popup).length > 0) {
                    $.each(plang, function (key, value) {
                        $.each(value, function (lang_key, lang_value) {
                            $('tr.popup_lang_' + key).find('.' + lang_key).bootstrapSwitch('state', lang_value);
                        })
                    })
                }
                if($('.currency-table',popup).length > 0) {
                    $.each(currncy_array, function (key, value) {
                        $.each(value, function (k, l) {
                            if (k == 'p_currencies' || k == 'd_currencies') {
                                $('tr.popup_cur_' + key).find('.' + k).bootstrapSwitch('state', l);
                            } else if (k == 'js-use_default') {
                                $('tr.popup_cur_' + key).find('.' + k).prop('checked', l);
                                if (l == true) {
                                    $('tr.popup_cur_' + key).find('.js-custom-rate').hide();
                                } else {
                                    $('tr.popup_cur_' + key).find('.js-custom-rate').show();
                                }
                            }else if(k == 'js-rate-margin'){
                                $('.currency_'+key).find('.currency_margin').text(l);
                                $('tr.popup_cur_' + key).find('.' + k).val(l);
                            }else if(k == 'margin_type'){
                                $('.currency_'+key).find('.currency_margin').append(l);
                                $('tr.popup_cur_' + key).find('.' + k).val(l);
                            }else {
                                $('tr.popup_cur_' + key).find('.' + k).val(l);

                            }
                        })
                    })
                }
                popup.addClass('hide_popup');
                $(window).off('resize', height);
                $('#content, .content-container').css({ 'position': '', 'z-index': ''});
                return false;
            })

            $('.pop-up-close-page, .apply-popup', popup).off().on('click', function(){
                popup.addClass('hide_popup');
                $(window).off('resize', height);
                $('#content, .content-container').css({ 'position': '', 'z-index': ''});
                return false;
            });

            return false;
        })
    })
</script>