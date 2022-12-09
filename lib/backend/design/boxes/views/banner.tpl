{use class="Yii"}

<div class="row">
    <div class="col-xs-8">

        <div class="row align-items-center m-b-2">
            <label for="banners_type" class="col-xs-4 align-right">{$smarty.const.TEXT_BANNERS_TYPE}:</label>
            <div class="col-xs-8">
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
            <label class="col-xs-4 align-right">{$smarty.const.TEXT_BANNERS_GROUP}:</label>
            <div class="col-xs-8">
                <select name="setting[0][banners_group]" id="banners_group" class="form-control">
                    <option value=""></option>
                    {foreach $banners as $banner}
                        <option value="{$banner.banners_group}"{if $banner.banners_group == $settings[0].banners_group} selected{/if}>{$banner.banners_group}</option>
                    {/foreach}
                    <option value="page_setting"{if $settings[0].banners_group == 'page_setting'} selected{/if}>{$smarty.const.TEXT_FROM_PAGE_SETTING}</option>
                </select>
            </div>
        </div>

        <div class="row single-settings align-items-center m-b-2">
            <label class="col-xs-4 align-right">{$smarty.const.TEXT_BANNER}:</label>
            <div class="col-xs-8">
                <input type="hidden" name="setting[0][ban_id]" id="banners_id" value="{$settings[0].ban_id}"/>

                <div class="banner-holder"></div>
            </div>
        </div>

        <div class="row align-items-center m-b-2 template-row">
            <label class="col-xs-4 align-right">{$smarty.const.TEXT_TEMPLATE}:</label>
            <div class="col-xs-8">
                <select name="setting[0][template]" class="form-control">
                    <option value=""{if $settings[0].template == ''} selected{/if}>{$smarty.const.TEXT_DEFAULT}</option>
                    <option value="1"{if $settings[0].template == '1'} selected{/if}>{$smarty.const.TEXT_NEW}</option>
                </select>
            </div>
        </div>

    </div>
    <div class="col-xs-4">

        <div class="row align-items-center m-b-2">
            <label class="col-xs-7 align-right p-r-0" title='speed optimisation LCP (add &lt;link rel="preload"&gt; in head)'><i class="icon-info-circle"></i> {$smarty.const.TEXT_PRELOAD}:</label>
            <div class="col-xs-5">
                <select name="setting[0][preload]" class="form-control">
                    <option value=""{if $settings[0].preload == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].preload == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center m-b-2">
            <label class="col-xs-7 align-right p-r-0">webp:</label>
            <div class="col-xs-5">
                <select name="setting[0][dont_use_webp]" class="form-control">
                    <option value=""{if $settings[0].dont_use_webp == ''} selected{/if}>{$smarty.const.TEXT_YES}</option>
                    <option value="1"{if $settings[0].dont_use_webp == '1'} selected{/if}>{$smarty.const.TEXT_NO}</option>
                </select>
            </div>
        </div>

        <div class="row align-items-center m-b-2">
            <label class="col-xs-7 align-right p-r-0">{$smarty.const.LAZY_LOAD_IMAGES}:</label>
            <div class="col-xs-5">
                <select name="setting[0][lazy_load]" id="" class="form-control">
                    <option value=""{if $settings[0].lazy_load == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].lazy_load == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>

    </div>
</div>

<div class="row p-l-2 p-t-2">
    <div class="col-xs-5">
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-6 align-right">{$smarty.const.TEXT_DOTS}:</label>
            <div class="col-xs-5">
                <select name="setting[0][dots]" class="form-control">
                    <option value=""{if $settings[0].dots == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].dots == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-6 align-right">{$smarty.const.CENTER_MODE}:</label>
            <div class="col-xs-5">
                <select name="setting[0][centerMode]" class="form-control">
                    <option value=""{if $settings[0].centerMode == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].centerMode == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-6 align-right">{$smarty.const.ADAPTIVE_HEIGHT}:</label>
            <div class="col-xs-5">
                <select name="setting[0][adaptiveHeight]" class="form-control">
                    <option value=""{if $settings[0].adaptiveHeight == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].adaptiveHeight == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-6 align-right">{$smarty.const.TEXT_AUTOPLAY}:</label>
            <div class="col-xs-5">
                <select name="setting[0][autoplay]" class="form-control">
                    <option value=""{if $settings[0].autoplay == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].autoplay == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
    </div>
    <div class="col-xs-7">
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-5 align-right p-l-0">{$smarty.const.AUTOPLAY_SPEED} (ms):</label>
            <div class="col-xs-5">
                <input type="number" name="setting[0][autoplaySpeed]" value="{$settings[0].autoplaySpeed}" class="form-control"/>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-5 align-right">{$smarty.const.TEXT_SPEED} (ms):</label>
            <div class="col-xs-5">
                <input type="number" name="setting[0][speed]" value="{$settings[0].speed}" class="form-control"/>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-5 align-right">{$smarty.const.TEXT_FADE}:</label>
            <div class="col-xs-5">
                <select name="setting[0][fade]" class="form-control">
                    <option value=""{if $settings[0].fade == ''} selected{/if}>{$smarty.const.TEXT_NO}</option>
                    <option value="1"{if $settings[0].fade == '1'} selected{/if}>{$smarty.const.TEXT_YES}</option>
                </select>
            </div>
        </div>
        <div class="row carousel-settings align-items-center m-b-2">
            <label class="col-xs-5 align-right">{$smarty.const.EASING_FUNCTION}:</label>
            <div class="col-xs-5">
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


<div class="tabbable tabbable-custom carousel-settings">
    <ul class="nav nav-tabs">

        <li class="active"><a href="#list" data-toggle="tab">{$smarty.const.TEXT_MAIN}</a></li>
        {foreach $settings.media_query as $item}
            <li><a href="#list{$item.id}" data-toggle="tab">{$item.setting_value}</a></li>
        {/foreach}

    </ul>
    <div class="tab-content">
        <div class="tab-pane active menu-list" id="list">

            <div class="row carousel-settings align-items-center">
                <label class="col-xs-3 align-right">{$smarty.const.TEXT_COLUMNS_IN_ROW}:</label>
                <div class="col-xs-2">
                    <input type="number" name="setting[0][col_in_row]" class="form-control" value="{$settings[0].col_in_row}"/>
                </div>
            </div>

        </div>
        {foreach $settings.media_query as $item}
            <div class="tab-pane menu-list" id="list{$item.id}">

                <div class="row carousel-settings align-items-center">
                    <label class="col-xs-3 align-right">{$smarty.const.TEXT_COLUMNS_IN_ROW}:</label>
                    <div class="col-xs-2">
                        <input type="number" name="visibility[0][{$item.id}][col_in_row]" class="form-control" value="{$visibility[0][{$item.id}].col_in_row}"/>
                    </div>
                </div>

            </div>
        {/foreach}

    </div>
</div>


<script type="text/javascript">
(function ($) { $(function () {
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

    function selectBanner() {
        if ($('#banners_type').val() == 'single' && $('#banners_group').val()) {
            $.get('banner_manager/group-banners', { 'banners_group': $('#banners_group').val() }, function(response){
                let list = response.map(banner => ({
                    name: banner.banners_id,
                    value: `<div class="ban-holder">
                                <div class="img">${ banner.image}</div>
                                <div class="text">${ banner.banners_title}</div>
                            </div>`
                }));
                $('.banner-holder').html('').append(htmlDropdown(list, $('#banners_id'), 'Select banner'))
            }, 'json')
        } else {
            $('.banner-holder').html('').append(htmlDropdown([], $('#banners_id'), 'Select banner'))
        }
    }

}) })(jQuery);
</script>
