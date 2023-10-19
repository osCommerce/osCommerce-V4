{use class="yii\helpers\Html"}
{\backend\assets\BDTPAsset::register($this)|void}
{use class="backend\assets\BannersAsset"}
{use class="common\helpers\Hooks"}
{BannersAsset::register($this)|void}

{\backend\design\Data::addJsData(['tr' => \common\helpers\Translation::translationsForJs([
    'IMAGE_FIT', 'IMAGE_FIT_COVER', 'IMAGE_FIT_FILL', 'IMAGE_FIT_CONTAIN', 'IMAGE_FIT_NONE', 'IMAGE_FIT_SCALE_DOWN',
    'IMAGE_POSITION', 'TEXT_MIDDLE_CENTER', 'TEXT_TOP_LEFT', 'TEXT_TOP_CENTER', 'TEXT_TOP_RIGHT', 'TEXT_MIDDLE_LEFT',
    'TEXT_MIDDLE_RIGHT', 'TEXT_BOTTOM_LEFT', 'TEXT_BOTTOM_CENTER', 'TEXT_BOTTOM_RIGHT'
], false)])}

<form id="save_banner_form" name="new_banner" action="{$app->urlManager->createUrl('banner_manager/submit')}">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            {if $isMultiPlatforms }
                <li data-bs-toggle="tab" data-bs-target="#platform">
                    <a>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</a>
                </li>
            {/if}
            <li class="active" data-bs-toggle="tab" data-bs-target="#main">
                <a>{$smarty.const.TEXT_MAIN_DETAILS}</a>
            </li>
            <li data-bs-toggle="tab" data-bs-target="#settings">
                <a>{$smarty.const.TEXT_SETTINGS}</a>
            </li>
        </ul>
        <div class="tab-content">

            {if $isMultiPlatforms}
                <div class="tab-pane" id="platform">
                    <div class="filter_pad">
                        <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid platform_statuses">
                            <thead>
                                <tr>
                                    <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                                    <th>{$smarty.const.TABLE_HEAD_PLATFORM_BANNER_ASSIGN}</th>
                                    {foreach Hooks::getList('banner_manager/banneredit', 'platform-table-heading-cell') as $filename}
                                        {include file=$filename}
                                    {/foreach}
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $platforms as $platform}
                                    <tr>
                                        <td>{$platform['text']}</td>
                                        <td>{$banners_data['platform_statuses'][$platform['id']]}</td>
                                        {foreach Hooks::getList('banner_manager/banneredit', 'platform-table-cell') as $filename}
                                            {include file=$filename}
                                        {/foreach}
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
            {else}
                {$banners_data['platform_statuses']}
            {/if}

            <div class="tab-pane active" id="main">
                {if count($languages) > 1}
                    <ul class="nav nav-tabs under_tabs_ul">
                        {foreach $languages as $lKey => $lItem}
                            <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$lItem['code']}"><a data-id="{$lItem['id']}">{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                        {/foreach}
                    </ul>
                {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}">
                    {foreach $banners_data.lang  as $mKey => $mItem}
                        <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$mItem['code']}">

                            <div class="row">
                                <div class="col-md-6 m-b-2">

                                    <div class="title-row">
                                        <label>{$smarty.const.TEXT_BANNERS_TITLE}<span class="colon">:</span></label>
                                        {$mItem['banners_title']}
                                    </div>

                                </div>
                                <div class="col-md-6">

                                    <div class="link-row">
                                        <label>{$smarty.const.TEXT_BANNERS_URL}<span class="colon">:</span></label>
                                        <div class="link-holder">
                                            <div class="link-input-cell">
                                                <span class="local-links-button"
                                                      data-field="{$mItem['bannerUrl']}"
                                                      data-languages_id="{$mItem['language_id']}"
                                                      data-platform_id="{$page_data['platform_id']}"
                                                      title="Choose local links">
                                                    <i class="icon-catalog"></i>
                                                </span>
                                                {$mItem['banners_url']}
                                            </div>
                                            <div>
                                                {$mItem['target']} <label>{$smarty.const.OPEN_LINK_IN_NEW_TAB}</label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row m-b-4 main-image-row">
                                <div class="col-md-6">

                                    <div class="m-b-2 main-image">
                                        <h4>Main image/video</h4>
                                        <div class="upload-box upload-box-wrap"
                                             data-name="{$mItem['name']}"
                                             data-value="{$mItem['value']}"
                                             data-upload="{$mItem['upload']}"
                                             data-delete="{$mItem['delete']}"
                                             data-type="{$mItem['type']}"
                                             data-accepted-files="image/*,video/*"
                                             data-edit="1"
                                                {*data-width="1200"
                                                data-height="400"*}>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">

                                    <div class="row align-items-center">
                                        <label class="col-5"><h4>{$smarty.const.TEXT_TEXT}</h4></label>
                                        <div class="col-7">
                                            <div class="row align-items-center">
                                                <label class="col-6 align-right">{$smarty.const.TEXT_POSITION}<span class="colon">:</span></label>
                                                <div class="col-6">
                                                    <select name="{$mItem['text_position_name']}" class="form-control">
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
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {$mItem['banners_html_text']}

                                </div>
                            </div>


                            <div class="banner-groups-images lang-{$mItem['language_id']}" data-language_id="{$mItem['language_id']}"></div>

                        </div>
                    {/foreach}
                </div>
            </div>

            <div class="tab-pane" id="settings">

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_BANNERS_GROUP}</label>
                    <div class="col-4 col-lg-3">{$banners_data['banners_group']}</div>
                    <div class="col-3">
                        {*<span href="{Yii::$app->urlManager->createUrl(['banner_manager/newgroup'])}" class="btn btn-add-group">
                            {$smarty.const.TEXT_ADD_NEW_BANNER}
                        </span>*}
                    </div>
                </div>

                <div class="group-settings"></div>

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_BANNER_STATUS}</label>
                    <div class="col-4">{$banners_data['status']}</div>
                </div>

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_BANNER_SORT_ORDER}</label>
                    <div class="col-3 col-lg-2">{$banners_data['sort_order']}</div>
                </div>

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_BANNERS_SCHEDULED_AT}</label>
                    <div class="col-3 col-lg-2">{$banners_data['date_scheduled']}</div>
                </div>

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_BANNERS_EXPIRES_ON}</label>
                    <div class="col-3 col-lg-2">{$banners_data['expires_date']}</div>
                </div>

                <div class="row align-items-center m-b-2">
                    <label class="col-3 align-right">{$smarty.const.TEXT_REL_NOFOLLOW}</label>
                    <div class="col-3 col-lg-2">{$banners_data['nofollow']}</div>
                </div>

                {if !$isMultiPlatforms}
                    {$platformTableCells = Hooks::getList('banner_manager/banneredit', 'platform-table-cell')}
                    {foreach Hooks::getList('banner_manager/banneredit', 'platform-table-heading-cell') as $key => $filename}
                        <div class="row m-b-2">
                            <label class="col-3 align-right pt-1">{include file=$filename}</label>
                            <div class="col-3 col-lg-2">
                                {$platform = $platforms[0]}
                                {include file=$platformTableCells[$key]}
                            </div>
                        </div>
                    {/foreach}
                {/if}

            </div>
        </div>
    </div>

    <div class="btn-bar edit-btn-bar">
        <div class="btn-left">
            <a href="{$backUrl}" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button>
        </div>
    </div>
    {tep_draw_hidden_field( 'banners_id', $banners_id )}
</form>

<script>
    $(function () {
        $('.local-links-button').localLinks();

        $('#save_banner_form').on('submit', saveBanner);
        $('.top-buttons .btn-confirm').off('click').on('click', () => $('#save_banner_form').trigger('submit'));

        $('#save_banner_form .ck-editor').each(function(){
            CKEDITOR.replace(this, {
                height: '200px',
            })
        });
    })

    function saveBanner(e) {
        e.preventDefault();

        if (typeof(CKEDITOR) == 'object'){
            for ( instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
        }

        if (!$('select[name="group_id"]').val()) {
            $('a[href="#settings"]').trigger('click');
            alertMessage('{$smarty.const.CHOOSE_BANNER_GROUP}', 'alert-message');
            return null;
        }

        if ($('.platform_statuses input.check_on_off[type="checkbox"]').length && !$('.platform_statuses input:checked').length) {
            $('a[href="#platform"]').trigger('click');
            alertMessage('Please choose Sales Channel', 'alert-message');
            return null;
        }

        let bannerTitle = false;
        $('.banner-title').each(function () {
            if ($(this).val()) {
                bannerTitle = true;
            }
        });
        if (!bannerTitle) {
            $('a[href="#main"]').trigger('click');
            alertMessage('{$smarty.const.ENTER_BANNER_TITLE}', 'alert-message');
            return null;
        }

        $.post("{$app->urlManager->createUrl(['banner_manager/submit', 'platform_id' => $platform_id, 'group_id' => $group_id, 'row_id' => $row_id])}", $('#save_banner_form').serializeArray(), function (data, status) {
            if (status != "success") {
                alertMessage("Request error.", 'alert-message');
                return
            }
            if (data.error) {
                alertMessage(data.error, 'alert-message');
                return
            }
            if (data.text) {
                const $alert = alertMessage(data.text, 'alert-message');
                setTimeout(() => $alert.remove(), 1000)
            }
            $('body').trigger('saved-banner')
            {if !$popup}
            if (data.html) {
                $('.content-container').html(data.html);
                if (location.hash.length) {
                    let urlHashArr = location.hash.substr(1).split('/');
                    urlHashArr.forEach(function(hash){
                        const triggerTabList = document.querySelectorAll('[data-bs-target="#' + hash + '"]');
                        if (triggerTabList.length){
                            const tab = new bootstrap.Tab(triggerTabList[0]);
                            tab.show();
                            setTimeout(() => $(triggerTabList).trigger('shown.bs.tab'), 100)
                        }
                    })
                }
                $('body').trigger('saved-page')
            }
            {/if}
        }, "json");

        return false;
    }

    $(function () {
        $(".datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            autoSize: false,
            dateFormat: '{$smarty.const.DATE_FORMAT_DATEPICKER}'
        });

        $(".check_on_off").tlSwitch({
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        });


        $('select[name="group_id"]')
            .on('change', groupsSizes)
            .on('change', groupSettings);
        groupsSizes();
        groupSettings();

        function groupsSizes(){
            let currentGroup = $('select[name="group_id"]').val();

            $.get('banner_manager/banner-group-images', {
                group_id: currentGroup,
                banners_id: '{$banners_id}',
            }, function (data) {
                $('.banner-groups-images').each(function(){
                    $(this).html(data[$(this).data('language_id')].img)
                })
                $('.banner-groups-svg').each(function(){
                    $(this).html(data[$(this).data('language_id')].svg)
                })
                $('.banner-groups-images .upload-box-wrap').fileManager()
            }, 'json')
        }

        function groupSettings() {
            const group_id = $('select[name="group_id"]').val();
            const $groupSettings = $('.group-settings');

            $.get('banner_manager/banner-group-settings', {
                group_id,
            }, function (data) {
                if (!data.length) {
                    $groupSettings.html(`
                    <div class="row m-b-2">
                        <label class="col-3 align-right">{$smarty.const.GROUP_RESOLUTIONS}</label>
                        <div class="col-4 col-lg-3">
                            {$smarty.const.NO_RESOLUTIONS}
                        </div>
                        <div class="col-4"><span class="btn btn-edit-group">{$smarty.const.EDIT_GROUP}</span></div>
                    </div>
                `);
                    return
                }
                $groupSettings.html(`
                    <div class="row m-b-2">
                        <label class="col-3 align-right">{$smarty.const.GROUP_RESOLUTIONS}</label>
                        <div class="col-4 col-lg-3">
                            <table class="group-resolutions" width="100%">
                                <tr>
                                    <td><label>{$smarty.const.WINDOW_WIDTH}</label></td>
                                    <td><label>{$smarty.const.IMAGE_VIDEO_SIZES}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-4"><span class="btn btn-edit-group">{$smarty.const.EDIT_GROUP}</span></div>
                    </div>
                `);

                const groupResolutions = $('.group-resolutions', $groupSettings)

                data.forEach(function(size){
                    groupResolutions.append(`
                        <tr>
                            <td>form ${ size.width_from }${ (size.width_to && size.width_to !== '0' ? ' to ' + size.width_to  : '') }</td>
                            <td>${ size.image_width } &#215; ${ (size.image_height && size.image_height !== '0' ? size.image_height : 'X') }</td>
                        </tr>
                    `)
                })
            }, 'json')
        }

        $('.btn-add-group').on('click', function () {
            $.get('banner_manager/banner-groups-edit', function (response) {
                const $popup = alertMessage(`
                    <div class="popup-heading">{$smarty.const.NEW_GROUP}</div>
                    <div class="popup-content">
                        ${ response}
                    </div>
                    <div class="popup-buttons">
                        <span type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</span>
                        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                    </div>
                `, 'popup-groups');

                $('.field-select-group', $popup).remove();
                $('.field-new-group', $popup).attr('name', 'name').show();
                $('.btn-bar:last', $popup).remove();

                $('.btn-cancel', $popup).on('click', () => $popup.remove());

                const $form = $('.banner-group-form', $popup);
                $form.on('submit', saveGroup)
                $('.btn-save', $popup).on('click', saveGroup);

                function saveGroup() {
                    const data = $form.serializeArray();
                    data.push({ name: 'new_group', value: 1 });
                    $.post('banner_manager/banner-groups-save', data, function(response){
                        if (response.error) {
                            alertMessage(response.error, 'alert-message')
                            return '';
                        }
                        const val = $('.field-new-group', $popup).val();
                        $('select[name="group_id"]')
                            .append(`<option value="${ val }">${ val }</option>`)
                            .val(val)
                            .trigger('change');

                        $popup.remove()
                    }, 'json')
                }

            })
        })

        $('.group-settings').on('click', '.btn-edit-group', function () {
            const group_id = $('select[name="group_id"]').val();
            $.get('banner_manager/banner-groups-edit', { group_id },  function (response) {
                const $popup = alertMessage(`
                    <div class="popup-heading">Edit group "${ name }"</div>
                    <div class="popup-content">
                        ${ response}
                    </div>
                    <div class="popup-buttons">
                        <span type="submit" class="btn btn-primary btn-save">{$smarty.const.IMAGE_SAVE}</span>
                        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
                    </div>
                `, 'popup-groups');

                const $form = $('.banner-group-form', $popup);

                $form.append(`<input name="name" type="hidden" value="${ name }">`);

                $('.row', $popup).remove();
                $('.btn-bar:last', $popup).remove();

                $('.btn-cancel', $popup).on('click', () => $popup.remove());

                $form.on('submit', saveGroup);
                $('.btn-save', $popup).on('click', saveGroup);

                function saveGroup() {
                    const data = $form.serializeArray();
                    //data.push({ name: 'group_id', value: name});

                    $.post('banner_manager/banner-groups-save', data, function(response){
                        if (response.error) {
                            alertMessage(response.error, 'alert-message')
                            return '';
                        }
                        $('select[name="group_id"]').trigger('change')
                        $popup.remove();
                    }, 'json')
                }
            })
        })

        $('.main-image .upload-box-wrap').fileManager()
    })
</script>
<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/banner-editor.js"></script>
<script>
    bannerEditor.bannerEdit({
        tr: JSON.parse('{$tr}'),
        setLanguage: {$setLanguage},
    })
</script>