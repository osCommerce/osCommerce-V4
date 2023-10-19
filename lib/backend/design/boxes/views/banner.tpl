{use class="Yii"}

<div class="row">
    <div class="col-8 col-8">

        <div class="row align-items-center m-b-2">
            <label for="banners_type" class="col-4 col-4 align-right">{$smarty.const.TEXT_BANNERS_TYPE}:</label>
            <div class="col-8 col-8">
                <select name="setting[0][banners_type]" id="banners_type" class="form-control">
                    <option value="">{$smarty.const.CHOOSE_BANNER_TYPE}</option>
                    <option value="single"{if $settings[0].banners_type == 'single'} selected{/if}>{$smarty.const.SINGLE_BANNER}</option>
                    <option value="banner"{if $settings[0].banners_type == 'banner'} selected{/if}>{$smarty.const.ALL_BANNERS_IN_GROUP}</option>
                    <option value="carousel"{if $settings[0].banners_type == 'carousel'} selected{/if}>{$smarty.const.TEXT_CAROUSEL}</option>
                    <option value="random"{if $settings[0].banners_type == 'random'} selected{/if}>{$smarty.const.RANDOM_BANNER}</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center m-b-2">
            <label class="col-4 col-4 align-right">{$smarty.const.TEXT_BANNERS_GROUP}:</label>
            <div class="col-8 col-8">
                <select name="setting[0][banners_group]" id="banners_group" class="form-control">
                    <option value=""></option>
                    {foreach $bannersGroups as $group}
                        {*if $group.count > 0 && !$settings.designer_mode*}
                        <option value="{$group.id}"{if $group.banners_group == $settings[0].banners_group || $group.id == $settings[0].banners_group} selected{/if}{if !$group.count} class="empty-group"{/if}>{$group.banners_group} ({$group.count})</option>
                        {*/if*}
                    {/foreach}
                    <option value="page_setting"{if $settings[0].banners_group == 'page_setting'} selected{/if}>{$smarty.const.TEXT_FROM_PAGE_SETTING}</option>
                </select>
            </div>
        </div>

        <input type="hidden" name="setting[0][ban_id]" id="banners_id" value="{$settings[0].ban_id}"/>
        {*<div class="row single-settings m-b-2">
            <label class="col-4 align-right m-t-1">{$smarty.const.TEXT_BANNER}:</label>
            <div class="col-8">
                <input type="hidden" name="setting[0][ban_id]" id="banners_id" value="{$settings[0].ban_id}"/>

                <div class="banner-holder"></div>
            </div>
        </div>*}

        {if $settings.designer_mode == 'expert'}
        {if $microtime < '1675836928'}
        <div class="row align-items-center m-b-2 template-row">
            <label class="col-4 col-4 align-right">{$smarty.const.TEXT_TEMPLATE}:</label>
            <div class="col-8 col-8">
                <select name="setting[0][template]" class="form-control">
                    <option value=""{if $settings[0].template == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                    <option value="1"{if $settings[0].template == '1'} selected{/if}>{$smarty.const.TEXT_NEW}</option>
                </select>
            </div>
        </div>
        {/if}
        {/if}

    </div>
    <div class="col-4 col-4">

        <div class="row align-items-center m-b-2" style="min-height: 28px">
            {if $settings.designer_mode == 'expert'}
            <label class="col-7 col-7 align-right p-r-0" title='speed optimisation LCP (add &lt;link rel="preload"&gt; in head)'><i class="icon-info-circle"></i> {$smarty.const.TEXT_PRELOAD}:</label>
            <div class="col-5 col-5">
                <select name="setting[0][preload]" class="form-control">
                    <option value=""{if $settings[0].preload == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].preload == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
            {/if}
        </div>

        <div class="row align-items-center m-b-2 form-check form-switch" style="min-height: 28px">
            <input type="checkbox" class="form-check-input" id="show-empty-groups"/>
            <label class="form-check-label" for="show-empty-groups">{$smarty.const.SHOW_EMPTY_GROUPS}</label>
        </div>

        {if $settings.designer_mode == 'expert'}
        <div class="row align-items-center m-b-2">
            <label class="col-7 align-right p-r-0">webp:</label>
            <div class="col-5">
                <select name="setting[0][dont_use_webp]" class="form-control">
                    <option value=""{if $settings[0].dont_use_webp == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                    <option value="1"{if $settings[0].dont_use_webp == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center m-b-2">
            <label class="col-7 align-right p-r-0">{$smarty.const.LAZY_LOAD_IMAGES}:</label>
            <div class="col-5">
                <select name="setting[0][lazy_load]" id="" class="form-control">
                    <option value=""{if $settings[0].lazy_load == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].lazy_load == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        {/if}
    </div>
</div>

{if $settings.designer_mode}
<div class="row p-l-2 p-t-2">
    <div class="col-5">
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-6 align-right">{$smarty.const.TEXT_DOTS}:</label>
            <div class="col-5">
                <select name="setting[0][dots]" class="form-control">
                    <option value=""{if $settings[0].dots == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].dots == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-6 align-right">{$smarty.const.CENTER_MODE}:</label>
            <div class="col-5">
                <select name="setting[0][centerMode]" class="form-control">
                    <option value=""{if $settings[0].centerMode == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].centerMode == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-6 align-right">{$smarty.const.ADAPTIVE_HEIGHT}:</label>
            <div class="col-5">
                <select name="setting[0][adaptiveHeight]" class="form-control">
                    <option value=""{if $settings[0].adaptiveHeight == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].adaptiveHeight == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-6 align-right">{$smarty.const.TEXT_AUTOPLAY}:</label>
            <div class="col-5">
                <select name="setting[0][autoplay]" class="form-control">
                    <option value=""{if $settings[0].autoplay == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].autoplay == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-7">
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-5 align-right p-l-0">{$smarty.const.AUTOPLAY_SPEED} (ms):</label>
            <div class="col-5">
                <input type="number" name="setting[0][autoplaySpeed]" value="{$settings[0].autoplaySpeed}" class="form-control"/>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-5 align-right">{$smarty.const.TEXT_SPEED} (ms):</label>
            <div class="col-5">
                <input type="number" name="setting[0][speed]" value="{$settings[0].speed}" class="form-control"/>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-5 align-right">{$smarty.const.TEXT_FADE}:</label>
            <div class="col-5">
                <select name="setting[0][fade]" class="form-control">
                    <option value=""{if $settings[0].fade == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].fade == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-5 align-right">{$smarty.const.EASING_FUNCTION}:</label>
            <div class="col-5">
                <select name="setting[0][cssEase]" class="form-control">
                    <option value=""{if $settings[0].cssEase == ''} selected{/if}>ease</option>
                    <option value="linear"{if $settings[0].cssEase == 'linear'} selected{/if}>linear</option>
                    <option value="ease-in"{if $settings[0].cssEase == 'ease-in'} selected{/if}>ease-in</option>
                    <option value="ease-out"{if $settings[0].cssEase == 'ease-out'} selected{/if}>ease-out</option>
                    <option value="ease-in-out"{if $settings[0].cssEase == 'ease-in-out'} selected{/if}>ease-in-out</option>
                    <option value="step-start"{if $settings[0].cssEase == 'step-start'} selected{/if}>step-start</option>
                    <option value="step-end"{if $settings[0].cssEase == 'step-end'} selected{/if}>step-end</option>
                </select>
            </div>
        </div>
    </div>

</div>


{if $settings.designer_mode == 'expert'}
<div class="tabbable tabbable-custom carousel-settings">
    <ul class="nav nav-tabs">

        <li class="active" data-bs-toggle="tab" data-bs-target="#list"><a>{$smarty.const.TEXT_MAIN}</a></li>
        <li class="label">{$smarty.const.WINDOW_WIDTH}:</li>
        {foreach $settings.media_query as $item}
            <li data-bs-toggle="tab" data-bs-target="#list{$item.id}"><a>{$item.title}</a></li>
        {/foreach}

    </ul>
    <div class="tab-content">
        <div class="tab-pane active menu-list" id="list">

            <div class="row carousel-settings align-items-center">
                <label class="col-3 align-right">{$smarty.const.TEXT_COLUMNS_IN_ROW}:</label>
                <div class="col-2">
                    <input type="number" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
                </div>
            </div>

        </div>
        {foreach $settings.media_query as $item}
            <div class="tab-pane menu-list" id="list{$item.id}">

                <div class="row carousel-settings align-items-center">
                    <label class="col-3 align-right">{$smarty.const.TEXT_COLUMNS_IN_ROW}:</label>
                    <div class="col-2">
                        <input type="number" name="visibility[0][{$item.id}][col_in_row]" class="form-control" value="{$visibility[0][{$item.id}].col_in_row}"/>
                    </div>
                </div>

            </div>
        {/foreach}

    </div>
</div>
{else}

<div class="row p-l-2 p-t-2">
    <div class="col-5">
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-6 align-right">{$smarty.const.TEXT_COLUMNS_IN_ROW}:</label>
            <div class="col-5">
                <input type="number" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
            </div>
        </div>
    </div>
</div>
{/if}
{/if}

<div class="edit-banner-holder"></div>

<script type="text/javascript">
(function ($) { $(function () {
    $('.empty-group').hide();
    $('#show-empty-groups').on('change', function () {
        if (this.checked) {
            $('.empty-group').show();
        } else {
            $('.empty-group').hide();
        }
    })

    $('#banners_type').on('change', function () {
        switch ($(this).val()) {
            case 'carousel':
                $('.carousel-settings').show();
                $('.single-settings').hide();
                break;
            case 'single':
                $('.carousel-settings').hide();
                $('.single-settings').show();
                break;
            case '': case 'banner': case 'random': default:
                $('.carousel-settings').hide();
                $('.single-settings').hide();
                break;
        }
        selectBanner();
    }).trigger('change')

    $('#banners_group').on('change', selectBanner)
    $('#banners_type').on('change', selectBanner)
    $('body').on('saved-banner', selectBanner)

    function selectBanner() {
        const bannerType = $('#banners_type').val();
        const groupId = $('#banners_group').val();
        if (groupId) {
            $.get('banner_manager/group-banners', { 'banners_group': groupId }, function(response){

                const $table = $(`
                        <table class="table table-bordered">
                            <tr>
                                <tr>
                                    <th>{$smarty.const.TEXT_IMAGE}</th>
                                    <th>{$smarty.const.BANNER_TITLE}</th>
                                    ${ bannerType == 'single' && response.length > 1 ? '<th></th>' : ''}
                                    <th class="platforms-heading">{$smarty.const.ASSIGNED_SALES_CHANNELS}</th>
                                    <th></th>
                                </tr>
                            </thead>
                        </table>
                    `);
                if (bannerType == 'single' && response.length == 1) {
                    $('#banners_id').val(response[0].banners_id).trigger('change')
                }
                response.forEach(function(banner){
                    const $item = bannerItem(banner, bannerType == 'single', response.length);

                    $table.append($item)
                })
                let groupName = $('#banners_group option:selected').text().replace(/\([0-9]+\)/, '');

                const $newBanner = $(`<div class="m-t-4 m-b-2 align-center"><a href="banner_manager/banneredit?popup=1&group_id=${ groupId }" class="btn btn-primary">{$smarty.const.ADD_BANNER}</a></div>`);
                $('a', $newBanner).on('click', editBanner)

                $('.edit-banner-holder').html('')
                    .append(`<h4>{$smarty.const.BANNERS_FROM_GROUP} "${ groupName }"</h4>`)
                    .append($table)
                    .append($newBanner)
            }, 'json')
        } else {
            $('.edit-banner-holder').html('')
        }
    }

    function bannerItem(banner, chooseButton, itemsCount){
        let platforms = '';
        if (banner.platforms && banner.platforms.length) {
            banner.platforms.forEach(function (platform) {
                if (platform.platform_name) {
                    platforms += `<div>${ platform.platform_name}</div>`
                }
            })
        }

        const $bannersIdInput = $('#banners_id');
        const bannersId = $bannersIdInput.val();
        let notChecked = '';
        if (chooseButton && (bannersId != banner.banners_id || !bannersId)) {
            notChecked = 'check-disabled';
        }
        const $item = $(`
            <tr class="ban-holder${ banner.status == '0' ? ' banner-disabled' : ''} ${ notChecked }">
                <td class="img">${ banner.image ? banner.image : '<span class="no-image">Banner without image</span>'}</td>
                <td class="text">
                    ${ banner.banners_title ? `<span class="banner-title">${ banner.banners_title }</span>` : '<i class="need-enter-title">You need to enter banner title</i>'}
                    ${ banner.status == '0' ? '<span class="banner-disabled-text">({$smarty.const.TEXT_DISABLED})</span>' : ''}<br>
                    <a href="banner_manager/banneredit?popup=1&banners_id=${ banner.banners_id }">{$smarty.const.TEXT_BANNER_EDIT}</a>
                </td>
                ${ chooseButton && itemsCount > 1 ? `<td class="btn-choose-cell"><span class="btn btn-primary btn-choose" data-id="${ banner.banners_id }">Choose</span></td>` : ''}
                <td class="platforms">${ platforms }</td>
                <td class="delete-banner"><span class="btn-delete-banner" data-id="${ banner.banners_id }"></span></td>
            </tr>`);

        $('.btn-choose', $item).on('click', function () {
            $bannersIdInput.val($(this).data('id')).trigger('change');
            $('.edit-banner-holder .ban-holder').addClass('check-disabled');
            $(this).closest('.ban-holder').removeClass('check-disabled')
        });

        $('.btn-delete-banner', $item).on('click', function () {
            let response = '';
            if (banner.image) {
                response += '<div class="image">' + banner.image + '</div>';
            }
            if (banner.banners_title) {
                response += '<div class="title">' + banner.banners_title + '</div>';
            }
            response += '<div class="text">{$smarty.const.ARE_YOU_SURE_DELETE_BANNER}</div>';
            const id = $(this).data('id');
            bootbox.dialog({
                message: response,
                title: "Warning",
                className: 'delete-banner-popup',
                buttons: {
                    main: {
                        label: "{$smarty.const.IMAGE_CANCEL}",
                        className: "btn",
                        callback: function() {
                        }
                    },
                    success: {
                        label: "{$smarty.const.IMAGE_DELETE}",
                        className: "btn btn-primary",
                        callback: function() {
                            $.post('banner_manager/delete', { bID: [id]}, function () {
                                selectBanner()
                            })
                        }
                    }
                }
            });
        });

        $('a', $item).on('click', editBanner)

        return $item;
    }

    function editBanner(e) {
        e.preventDefault();
        e.stopPropagation();
        const newBanner = $(this).hasClass('new-banner');
        $.get($(this).attr('href'), function (response) {
            const $popup = alertMessage(`
                <div class="popup-heading">{$smarty.const.TEXT_BANNER_EDIT}</div>
                <div class="popup-content">${ response}</div>
                <div class="popup-buttons"></div>
            `, 'edit-banner-popup');

            const $btnSave = $('<span class="btn btn-save btn-primary">{$smarty.const.IMAGE_SAVE}</span>');
            const $btnCancel = $('<span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>');

            $btnCancel.on('click', selectBanner);
            $btnSave.on('click', () => $('#save_banner_form').trigger('submit'));

            $('.popup-buttons', $popup).append($btnSave).append($btnCancel);

            $('body').on('saved-banner', function () {
                $btnCancel.trigger('click')
            })
        })
    }

}) })(jQuery);
</script>

<script type="text/javascript" src="{$app->view->theme->baseUrl}/js/local-links.js?17"></script>
<link href="{$app->view->theme->baseUrl}/css/banners.css?17" rel="stylesheet" type="text/css" />