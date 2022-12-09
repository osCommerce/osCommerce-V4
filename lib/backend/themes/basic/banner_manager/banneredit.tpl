{use class="yii\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}
{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}
<div id="banner_management_edit">
    <form id="save_banner_form" name="new_banner" action="{$app->urlManager->createUrl('banner_manager/submit')}" onSubmit="return saveBanner();">
        <div class="popupCategory">
            <div class="tabbable tabbable-custom">
                <ul class="nav nav-tabs top_tabs_ul">
                    {if $isMultiPlatforms }
                        <li class="{if !$banners_id} active {/if}heigh_col2"><a href="#tab_platform" data-toggle="tab"><span>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</span></a></li>
                                {/if}
                    <li class="{if $isMultiPlatforms && !$banners_id} {else}active{/if}"><a href="#tab_3" data-toggle="tab"><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
                    <li><a href="#tab_2" data-toggle="tab"><span>{$smarty.const.TEXT_NAME_DESCRIPTION}</span></a></li>            
                </ul>
                <div class="tab-content">
                    {if $isMultiPlatforms }
                        <div class="tab-pane topTabPane tabbable-custom{if $isMultiPlatforms && !$banners_id} active {/if}" id="tab_platform">
                            <div class="filter_pad">
                                <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                                    <thead>
                                        <tr>
                                            <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                                            <th>{$smarty.const.TABLE_HEAD_PLATFORM_BANNER_ASSIGN}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $platforms as $platform}
                                            <tr>
                                                <td>{$platform['text']}</td>
                                                <td>{$banners_data['platform_statuses'][$platform['id']]}</td>
                                            </tr>
                                        {/foreach}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    {/if}
                    <div class="tab-pane topTabPane tabbable-custom-b {if $isMultiPlatforms && !$banners_id} {else}active{/if}" id="tab_3">
                        <table cellspacing="0" cellpadding="0">
                            <tr>
                                <td class="label_name" valign="top" style="width: 180px">{$smarty.const.TEXT_BANNERS_GROUP}</td>
                                <td class="label_value"><div class="ban_group_div">{$banners_data['banners_group']}<a href="{Yii::$app->urlManager->createUrl(['banner_manager/newgroup'])}" class="popup btn">{$smarty.const.TEXT_ADD_NEW_BANNER}</a></div></td>
                            </tr>
                            {if not $isMultiPlatforms }
                                <tr>
                                    <td valign="top" class="label_name">{$smarty.const.TEXT_BANNER_STATUS}</td>
                                    <td class="label_value">{$banners_data['status']}</td>
                                </tr>
                            {/if}
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_BANNER_SORT_ORDER}</td>
                                <td class="label_value">{$banners_data['sort_order']}</td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name">{$smarty.const.TEXT_BANNERS_SCHEDULED_AT}</td>
                                <td class="label_value">{$banners_data['date_scheduled']}</td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name">{$smarty.const.TEXT_BANNERS_EXPIRES_ON}</td>
                                <td class="label_value">{$banners_data['expires_date']}</td>
                            </tr>
                            <tr>
                                <td valign="top" class="label_name">{$smarty.const.TEXT_REL_NOFOLLOW}</td>
                                <td class="label_value">{$banners_data['nofollow']}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="tab-pane topTabPane tabbable-custom" id="tab_2">
                        {if count($languages) > 1}                                    
                            <ul class="nav nav-tabs under_tabs_ul">
                                {foreach $languages as $lKey => $lItem}
                                    <li{if $lKey == 0} class="active"{/if}><a href="#tab_{$lItem['code']}" data-toggle="tab" data-id="{$lItem['id']}">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                                            {/foreach}
                            </ul>
                        {/if}
                        <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                            {foreach $banners_data.lang  as $mKey => $mItem}
                                <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">
                                    <table cellspacing="0" cellpadding="0" width="100%" class="banners-table">
                                        <tr>
                                            <td class="label_name">{$smarty.const.TEXT_BANNERS_TITLE}</td>
                                            <td class="label_value">{$mItem['banners_title']}</td>
                                        </tr>
                                        <tr>
                                            <td class="label_name">{$smarty.const.TEXT_BANNERS_URL}</td>
                                            <td class="label_value">{$mItem['banners_url']}</td>
                                        </tr>
                                        <tr>
                                            <td class="label_name"></td>
                                            <td class="label_value">{\backend\design\LocalLinksButtons::widget(['editor' => '', 'field' => $mItem['bannerUrl'], 'platform_id' => $page_data['platform_id'], 'languages_id' => $mItem['languages_id']])}</td>
                                        </tr>
                                        <tr>
                                            <td class="label_name">{$smarty.const.TEXT_TARGET}</td>
                                            <td class="label_value">{$mItem['target']}</td>
                                        </tr>
                                        <tr>
                                            <td class="label_name">{$smarty.const.TEXT_BANNERS_TYPE}</td>
                                            <td class="label_value">
                                                
<label class="radio-inline">
    <input type="radio" name="{$mItem['banner_display_name']}" value="0"
           {if $mItem['banner_display'] == '0'} checked{/if} data-id="{$mKey}" class="banner-display">
    {$smarty.const.TEXT_BANNER_IMAGE}
</label>
<label class="radio-inline">
    <input type="radio" name="{$mItem['banner_display_name']}" value="1"
           {if $mItem['banner_display'] == '1'} checked{/if} data-id="{$mKey}" class="banner-display">
    {$smarty.const.TEXT_BANNER_TEXT}
</label>
<label class="radio-inline">
    <input type="radio" name="{$mItem['banner_display_name']}" value="2"
            {if $mItem['banner_display'] == '2'} checked{/if} data-id="{$mKey}" class="banner-display">
    {$smarty.const.TEXT_AND_IMAGE}
</label>
<label class="radio-inline">
    <input type="radio" name="{$mItem['banner_display_name']}" value="3"
           {if $mItem['banner_display'] == '3'} checked{/if} data-id="{$mKey}" class="banner-display">
    SVG
</label>
<label class="radio-inline">
    <input type="radio" name="{$mItem['banner_display_name']}" value="4"
            {if $mItem['banner_display'] == '4'} checked{/if} data-id="{$mKey}" class="banner-display">
    {$smarty.const.TEXT_VIDEO}
</label>
                                                
                                            </td>
                                        </tr>
                                        <tr class="banner-pos-{$mKey}"{if $mItem['banner_display'] != '2' || $mItem['banner_display'] == '3'} style="display: none"{/if}>
                                            <td valign="top" class="label_name">{$smarty.const.TEXT_POSITION}</td>
                                            <td class="label_value">
                                                <select name="{$mItem['text_position_name']}" class="form-control" style="width: 200px;">
                                                    <option value="0"{if $mItem['text_position'] == '0'} selected{/if}>{$smarty.const.TEXT_TOP_LEFT}</option>
                                                    <option value="1"{if $mItem['text_position'] == '1'} selected{/if}>{$smarty.const.TEXT_TOP_CENTER}</option>
                                                    <option value="2"{if $mItem['text_position'] == '2'} selected{/if}>{$smarty.const.TEXT_TOP_RIGHT}</option>
                                                    <option value="3"{if $mItem['text_position'] == '3'} selected{/if}>{$smarty.const.TEXT_MIDDLE_LEFT}</option>
                                                    <option value="4"{if $mItem['text_position'] == '4'} selected{/if}>{$smarty.const.TEXT_MIDDLE_CENTER}</option>
                                                    <option value="5"{if $mItem['text_position'] == '5'} selected{/if}>{$smarty.const.TEXT_MIDDLE_RIGHT}</option>
                                                    <option value="6"{if $mItem['text_position'] == '6'} selected{/if}>{$smarty.const.TEXT_BOTTOM_LEFT}</option>
                                                    <option value="7"{if $mItem['text_position'] == '7'} selected{/if}>{$smarty.const.TEXT_BOTTOM_CENTER}</option>
                                                    <option value="8"{if $mItem['text_position'] == '8'} selected{/if}>{$smarty.const.TEXT_BOTTOM_RIGHT}</option>
                                                    <option value="9"{if $mItem['text_position'] == '9'} selected{/if}>{$smarty.const.TEXT_UNDER_IMAGE}</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="banner-svg-{$mKey}"{if $mItem['banner_display'] != '3'} style="display: none"{/if}>
                                            <td valign="top" class="label_name">SVG</td>
                                            <td class="label_value">

<div class="">{$mItem['svg']}</div>
<div class=""><span data-href="{$mItem['svg_url']}" class="btn btn-edit-svg">{$smarty.const.IMAGE_EDIT} SVG</span></div>



                                            </td>
                                        </tr>
                                        <tr class="banner-image-{$mKey}"{if $mItem['banner_display'] == '1' || $mItem['banner_display'] == '3' || $mItem['banner_display'] == '4'} style="display: none"{/if}>
                                            <td class="label_name" valign="top">{$smarty.const.TEXT_BANNERS_IMAGE}</td>
                                            <td class="label_value">
                                                <div class="upload-box">
                                    {\backend\design\Image::widget([
                                        'name' => $mItem['name'],
                                        'value' => $mItem['value'],
                                        'upload' => $mItem['upload'],
                                        'delete' => $mItem['delete']
                                    ])}
                                                </div>

                                                {*<div class="upload-box upload-box-wrap"
                                                     data-name="{$mItem['name']}"
                                                     data-value="{$mItem['value']}"
                                                     data-upload="{$mItem['upload']}"
                                                     data-delete="{$mItem['delete']}"
                                                     data-width="1200"
                                                     data-height="400">
                                                </div>*}
                                            </td>
                                        </tr>
                                        <tr class="banner-video-{$mKey}"{if $mItem['banner_display'] != '4'} style="display: none"{/if}>
                                            <td class="label_name" valign="top">Video</td>
                                            <td class="label_value">
                                                <div class="upload-box">
                                                    {\backend\design\Image::widget([
                                                    'name' => $mItem['name_video'],
                                                    'value' => $mItem['value_video'],
                                                    'upload' => $mItem['upload_video'],
                                                    'delete' => $mItem['delete_video'],
                                                    'type' => 'video'
                                                    ])}
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="banner-text-{$mKey}"{if $mItem['banner_display'] == '0' || $mItem['banner_display'] == '3' || $mItem['banner_display'] == '4'} style="display: none"{/if}>
                                            <td valign="top" class="label_name">{$smarty.const.TEXT_BANNERS_HTML_TEXT}</td>
                                            <td class="label_value">{$mItem['banners_html_text']}</td>
                                        </tr>
                                    </table>
                                    <div class="banner-groups-images lang-{$mItem['language_id']}" data-language_id="{$mItem['language_id']}"></div>
                                    <div class="banner-groups-svg lang-{$mItem['language_id']}" data-language_id="{$mItem['language_id']}"></div>
                                </div>        
                            {/foreach} 
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn-bar edit-btn-bar">
                <div class="btn-left"><a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return backStatement()">{$smarty.const.IMAGE_CANCEL}</a></div>
                <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
            </div>
        </div>
        {tep_draw_hidden_field( 'banners_id', $banners_id )}
    </form>
</div>
<script type="text/javascript">
    function closePopup() {
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        return false;
    }

    function saveBanner() {
        $.post("{$app->urlManager->createUrl('banner_manager/submit')}", $('#save_banner_form').serialize(), function (data, status) {
            if (status == "success") {
                $('#banner_management_edit').html(data);
            } else {
                alert("Request error.");
            }
        }, "html");

        return false;
    }
    function saveGroupnew() {
        var new_banners_group = $("#bannerpopup input[name='new_ban_group_popup']").val();
        $('select[name="banners_group"]').append($('<option>', {
            value: new_banners_group,
            text: new_banners_group
        }));
        $('select[name="banners_group"] option').removeAttr('selected');
        $('select[name="banners_group"] option[value="' + new_banners_group + '"]').attr('selected', 'selected');
        closePopup();
        return false;
    }
    function backStatement() {
        window.history.back();
        return false;
    }
    
    (function($){ $(function(){
        $('.banner-display').on('change', function(){
            if ($(this).val() == '0') {
                $('.banner-text-'+$(this).data('id')).hide();
                $('.banner-image-'+$(this).data('id')).show();
                $('.banner-pos-'+$(this).data('id')).hide();
                $('.banner-svg-'+$(this).data('id')).hide();
                $('.banner-video-'+$(this).data('id')).hide();
                $('.banner-groups-images').show()
                $('.banner-groups-svg').hide()
            }
            if ($(this).val() == '1') {
                $('.banner-text-'+$(this).data('id')).show();
                $('.banner-image-'+$(this).data('id')).hide();
                $('.banner-pos-'+$(this).data('id')).hide();
                $('.banner-svg-'+$(this).data('id')).hide();
                $('.banner-video-'+$(this).data('id')).hide();
                $('.banner-groups-images').hide()
                $('.banner-groups-svg').hide()
            }
            if ($(this).val() == '2') {
                $('.banner-text-'+$(this).data('id')).show();
                $('.banner-image-'+$(this).data('id')).show();
                $('.banner-pos-'+$(this).data('id')).show();
                $('.banner-svg-'+$(this).data('id')).hide();
                $('.banner-video-'+$(this).data('id')).hide();
                $('.banner-groups-images').show()
                $('.banner-groups-svg').hide()
            }
            if ($(this).val() == '3') {
                $('.banner-text-'+$(this).data('id')).hide();
                $('.banner-image-'+$(this).data('id')).hide();
                $('.banner-pos-'+$(this).data('id')).hide();
                $('.banner-svg-'+$(this).data('id')).show();
                $('.banner-video-'+$(this).data('id')).hide();
                $('.banner-groups-images').hide()
                $('.banner-groups-svg').show()
            }
            if ($(this).val() == '4') {
                $('.banner-text-'+$(this).data('id')).hide();
                $('.banner-image-'+$(this).data('id')).hide();
                $('.banner-pos-'+$(this).data('id')).hide();
                $('.banner-svg-'+$(this).data('id')).hide();
                $('.banner-video-'+$(this).data('id')).show();
                $('.banner-groups-images').hide()
                $('.banner-groups-svg').hide()
            }
        });
        var type = $('.banner-display:checked').val();
        if (type == '0' || type == '2') {
            $('.banner-groups-images').show()
        }
        if (type == '1' || type == '3' || type == '4') {
            $('.banner-groups-images').hide()
        }
        if (type == '3') {
            $('.banner-groups-svg').show()
        } else {
            $('.banner-groups-svg').hide()
        }

    });})(jQuery);
    
    $(document).ready(function () {
        //===== Date Pickers  =====//
        /*$(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
        });*/
        $('.datepicker').datetimepicker({
            format: 'DD MMM YYYY h:mm A'
        });

        $(".check_on_off").tlSwitch(
                {
                    onText: "{$smarty.const.SW_ON}",
                    offText: "{$smarty.const.SW_OFF}",
                    handleWidth: '20px',
                    labelWidth: '24px'
                }
        );

        $('.popup').popUp({
            box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_BANNER_NEW_GROUP}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
        });


        $('.ban_group_div select').on('click', groupsSizes);
        groupsSizes();

        function groupsSizes(){
            let currentGroup = $('.ban_group_div select').val();

            $.get('banner_manager/banner-group-images', {
                banners_group: currentGroup,
                banners_id: '{$banners_id}',
            }, function (data) {
                $('.banner-groups-images').each(function(){
                    $(this).html(data[$(this).data('language_id')].img)
                })
                $('.banner-groups-svg').each(function(){
                    $(this).html(data[$(this).data('language_id')].svg)
                })
            }, 'json')
        }
    })

    $(function(){
        $('.upload-box-wrap').fileManager()
    })
</script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/banner-editor.js"></script>
<script>
    bannerEditor.bannerEdit({
        tr: JSON.parse('{$tr}'),
        setLanguage: {$setLanguage},
    })
</script>