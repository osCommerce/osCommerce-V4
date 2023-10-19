{use class="yii\helpers\Html"}
{use class="common\helpers\PageStatus"}
<div class="top-btn after">
    <div><a href="javascript:void(0);" onclick="return backStatement();" class="btn btn-back">{$smarty.const.IMAGE_BACK}</a></div>
    {if $information_id>0}
      <div>

          {PageStatus::showStatus('information', $information_id)}
          {PageStatus::showButton('information', $information_id)}

          <a href="javascript:void(0);" class="btn btn-delete" onclick="return confirmDeleteInfoPage({$information_id})">{$smarty.const.IMAGE_DELETE}</a>
      </div>
    {/if}
</div>
<!-- TABS-->
<form id="infoPage_form" name="infoPage_edit" onSubmit="return saveInfoPage();">
    {if $isMultiPlatform}<div class="tab-radius">{/if}
<input type="hidden" name="information_id" value="{$information_id}">
<div class="tabbable tabbable-custom tabbable-ep">
    <ul class="nav nav-tabs nav-tabs-big {if $isMultiPlatform}tab-radius-ul{/if}">
        {if $isMultiPlatforms}<li data-bs-toggle="tab" data-bs-target="#platforms"><a><span>{$smarty.const.TEXT_COMMON_PLATFORM_TAB}</span></a></li>{/if}
        <li class="active" data-bs-toggle="tab" data-bs-target="#main"><a><span>{$smarty.const.TEXT_MAIN_DETAILS}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#seo"><a><span>{$smarty.const.TEXT_SEO}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#template"><a><span>{$smarty.const.TEXT_TEMPLATE}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#imageTab"><a><span>{$smarty.const.TEXT_IMAGE_}</span></a></li>
        <li data-bs-toggle="tab" data-bs-target="#settings"><a><span>{$smarty.const.BOX_PROMOTIONS_SETTINGS}</span></a></li>
    </ul>
    <div class="tab-content {if $isMultiPlatform}tab-content1{/if}">
        {if $isMultiPlatforms}
        <div class="tab-pane topTabPane tabbable-custom" id="platforms">
            <div class="filter_pad {if $isMultiPlatform}tab_edt_page_pl{/if}" style="padding: 0;">
                <table class="table tabl-res table-striped table-hover table-responsive table-bordered table-switch-on-off double-grid">
                    <thead>
                    <tr>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_NAME}</th>
                        <th>{$smarty.const.TABLE_HEAD_PLATFORM_PAGE_ASSIGN}</th>
                        {if $some_need_login}<th>{$smarty.const.SHOW_FOR_NON_LOGGED}</th>{/if}
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $platforms as $platform}
                        <tr>
                            <td>{$platform['text']}</td>
                            <td><input type="checkbox" name="visible[{$platform['id']}]" value="1" class="check_on_off" {if $pages_data[$platform['id']]['visible']} checked{/if}></td>
                          {if $some_need_login}
                            <td>
                              {if $platform.need_login}<input type="checkbox" name="no_logged[{$platform['id']}]" value="1" class="check_on_off" {if $pages_data[$platform['id']]['no_logged']} checked{/if}>{/if}
                            </td>
                          {/if}
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
        {else}
            {foreach $platforms as $platform}
                <input type="hidden" name="visible[{$platform['id']}]" value="{$pages_data[$platform['id']]['visible']}">
            {/foreach}
        {/if}
        <input type="hidden" name="visible_per_platform" value="1">

        <div class="tab-pane topTabPane tabbable-custom active" id="main">

            {if $isMultiPlatforms}
                <ul class="nav nav-tabs {if $isMultiPlatform}tab-radius-ul tab-radius-ul-white{/if}">
                    {foreach $platforms as $pKey => $platform}
                        <li{if $platform['id'] == $first_platform_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$platform['id']}"><a><span>{$platform['logo']}<span>{$platform['text']}</span></span></a></li>
                    {/foreach}
                </ul>
            {/if}
            {if $isMultiPlatforms}<div class="tab-content">{/if}

            {foreach $pages_data as $page_data}
                {if $isMultiPlatforms}
                 <div class="tab-pane topTabPane tabbable-custom{if $page_data['platform_id'] == $first_platform_id} active{/if}" id="tab_{$page_data['platform_id']}">
                {/if}
                {if count($languages) > 1}
            <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$page_data['platform_id']}_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
            </ul>
            {/if}
            <div class="tab-content {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $page_data.lang as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="tab_{$page_data['platform_id']}_{$mItem['code']}">
                        <table cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TITLE_PAGE_TITLE}</td>
                                <td class="label_value">{$mItem['c_page_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NAME_IN_MENU}</td>
                                <td class="label_value">{$mItem['c_info_title']}</td>
                            </tr>
							<tr>
                                <td class="label_name">{$smarty.const.TEXT_DESCRIPTION_LINKS}</td>
                                <td class="label_value">
                                    {\backend\design\LocalLinksButtons::widget(['editor' => $mItem['editor_id'], 'platform_id' => $page_data['platform_id'], 'languages_id' => $mItem['languages_id']])}
                                    {*$mItem['c_links']*}
                                    <div class="info_desc_links">
                                        {$smarty.const.TEXT_INFO_DESC_LINKS}
                                    </div>
                                </td>
                            </tr>

							<tr>
                                <td class="label_name">{$smarty.const.ADD_TO_DESCRIPTION}:</td>
                                <td class="label_value">

                                    {\backend\design\ComponentsButton::widget(['editor' => $mItem['editor_id'], 'platform_id' => $page_data['platform_id'], 'languages_id' => $mItem['languages_id']])}

                                </td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.DESCRIPTION_INFORMATION}</td>
                                <td class="label_value">{$mItem['c_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_PRODUCTS_DESCRIPTION_SHORT}</td>
                                <td class="label_value">{$mItem['c_description_short']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_KEYWORDS}</td>
                                <td class="label_value">{$mItem['c_meta_key']}</td>
                            </tr>
                        </table>
                    </div>
                {/foreach}
            </div>
                {if $isMultiPlatforms}
                         </div>
                {/if}
            {/foreach}
                {if $isMultiPlatforms}</div>{/if}
            </div>

        <div class="tab-pane topTabPane tabbable-custom" id="seo">
            {if $isMultiPlatforms}
                <ul class="nav nav-tabs {if $isMultiPlatform}tab-radius-ul tab-radius-ul-white{/if}">
                    {foreach $platforms as $pKey => $platform}
                        <li{if $platform['id'] == $first_platform_id} class="active"{/if} data-bs-toggle="tab" data-bs-target="#seo_tab_{$platform['id']}"><a><span>{$platform['logo']}<span>{$platform['text']}</span></span></a></li>
                    {/foreach}
                </ul>
            {/if}

            {if $isMultiPlatforms}<div class="tab-content">{/if}

            {foreach $pages_data as $page_data}
                {if $isMultiPlatforms}
                   <div class="tab-pane topTabPane tabbable-custom{if $page_data['platform_id'] == $first_platform_id} active{/if}" id="seo_tab_{$page_data['platform_id']}">
                {/if}
                {if count($languages) > 1}
            <ul class="nav nav-tabs {if $isMultiPlatform}nav-tabs3{/if}">
                {foreach $languages as $lKey => $lItem}
                    <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#seo_tab_{$page_data['platform_id']}_{$lItem['code']}"><a>{$lItem['logo']}<span>{$lItem['name']}</span></a></li>
                {/foreach}
            </ul>
            {/if}
            <div class="tab-content seoTab {if $isMultiPlatform}tab-content3{/if} {if count($languages) < 2}tab-content-no-lang{/if}">
                {foreach $page_data.lang as $mKey => $mItem}
                    <div class="tab-pane{if $mKey == 0} active{/if}" id="seo_tab_{$page_data['platform_id']}_{$mItem['code']}">
                        <table class="h-teg-table" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td class="label_name">{$smarty.const.TITLE_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['c_seo_page_name']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NO_INDEX}</td>
                                <td class="label_value">{$mItem['noindex_option']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_NO_FOLLOW}</td>
                                <td class="label_value">{$mItem['nofollow_option']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_CANONICAL}</td>
                                <td class="label_value">{$mItem['rel_canonical']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.META_TAG_TITLE}</td>
                                <td class="label_value meta_title">{$mItem['c_meta_title']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.INFO_DESCRIPTION_META_TAG}</td>
                                <td class="label_value meta_description">{$mItem['c_meta_description']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H1_TAG}</td>
                                <td class="label_value">{$mItem['c_h1_tag']}</td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H2_TAG}</td>
                                <td class="label_value"><span id="information_h2_tag-{$mItem['languages_id']}-{$mItem['platform_id']}">{foreach explode("\n", $mItem['c_h2_tag']) as $value}<span class="row"><input type="text" name="information_h2_tag[{$mItem['languages_id']}][{$mItem['platform_id']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('information_h2_tag-{$mItem['languages_id']}-{$mItem['platform_id']}', '{htmlspecialchars('<span class="row"><input type="text" name="information_h2_tag['|cat:$mItem['languages_id']|cat:']['|cat:$mItem['platform_id']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_H3_TAG}</td>
                                <td class="label_value"><span id="information_h3_tag-{$mItem['languages_id']}-{$mItem['platform_id']}">{foreach explode("\n", $mItem['c_h3_tag']) as $value}<span class="row"><input type="text" name="information_h3_tag[{$mItem['languages_id']}][{$mItem['platform_id']}][]" value="{$value|escape}" class="form-control" /><span class="del-pt del-tag"></span></span>{/foreach}</span><span onclick="addInput('information_h3_tag-{$mItem['languages_id']}-{$mItem['platform_id']}', '{htmlspecialchars('<span class="row"><input type="text" name="information_h3_tag['|cat:$mItem['languages_id']|cat:']['|cat:$mItem['platform_id']|cat:'][]" value="" class="form-control" /><span class="del-pt del-tag"></span></span>')}')" class="btn btn-add-more">{$smarty.const.TEXT_AND_MORE}</span></td>
                            </tr>
<!-- Moved to SeoRedirectsNamed {*
                            <tr>
                                <td class="label_name">{$smarty.const.TEXT_OLD_SEO_PAGE_NAME}</td>
                                <td class="label_value">{$mItem['c_old_seo_page_name']}
                                <a href="#" data-base-href="{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-home" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_BROWSER}">&nbsp;</a>
                                {if defined('HTTP_STATUS_CHECKER') && !empty($smarty.const.HTTP_STATUS_CHECKER)}
                                <a href="#" data-base-href="{$smarty.const.HTTP_STATUS_CHECKER}{$smarty.const.HTTP_SERVER}{$smarty.const.DIR_WS_CATALOG}" class="seo-link icon-external-link" target="_blank" title="{$smarty.const.TEXT_OLD_SEO_PAGE_NAME_STATUS}">&nbsp;</a>
                                {/if}
                                </td>
                            </tr>
*} -->
                            {if \common\helpers\Acl::checkExtensionAllowed('SeoRedirectsNamed', 'allowed')}
                               {\common\extensions\SeoRedirectsNamed\SeoRedirectsNamed::renderInfo($information_id, $mItem['languages_id'], $mItem['platform_id'])}
                            {/if}
                        </table>
                         <script>
                      $(document).ready(function(){
                        $('body').on('click', "#seo_tab_{$page_data['platform_id']}_{$mItem['code']} .icon-home", function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().val());
                        });
                        $('body').on('click', '#seo_tab_{$page_data['platform_id']}_{$mItem['code']} .icon-external-link', function(){
                          $(this).attr('href', $(this).attr('data-base-href')+$(this).prev().prev().val());
                        });
                        
                      })
                      </script>
                    </div>
                {/foreach}
            </div>
                {if $isMultiPlatforms}
                    </div>
                {/if}
            {/foreach}
                {if $isMultiPlatforms}</div>{/if}
            </div>
        <div class="tab-pane topTabPane tabbable-custom" id="template">

            {foreach $templates.list as $frontend}
                <div class="product-frontend frontend-{$frontend.id}">
                    <h4>{$frontend.text} <span>({$smarty.const.TEXT_THEME_NAME}: {$frontend.theme_title})</span></h4>
                    <div>
                        <label>
                            {$smarty.const.TEXT_DEFAULT}
                            <input type="radio" name="page_template[{$frontend.id}]" value=""
                                   class="check_give_wrap check_on_off"{if !$frontend.template} checked{/if}>
                        </label>
                        <label>
                            Blank
                            <input type="radio" name="page_template[{$frontend.id}]" value="0_blank"
                                   class="check_give_wrap check_on_off"{if $frontend.template == '0_blank'} checked{/if}>
                        </label>
                        {foreach $frontend.templates as $name}
                            <label>
                                {$name}
                                <input type="radio" name="page_template[{$frontend.id}]" value="{$name}"
                                       class="check_give_wrap check_on_off"{if $frontend.template == $name} checked{/if}>
                            </label>
                        {/foreach}
                    </div>
                    {if is_array($frontend.styles) && $frontend.styles|count > 0}
                    <div class="page-style-select row">
                        <div class="col-md-4">
                            <label for="">{$smarty.const.TEXT_PAGE_STYLE}</label>
                        </div>
                        <div class="col-md-8">
                            <select name="page_style[{$frontend.id}]" id="" class="form-control">
                                <option value=""></option>
                                {foreach $frontend.styles as $style}
                                    <option value="{$style.name}"{if $style.name == $frontend.page_style} selected{/if}>{$style.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    {else}
                        <input type="hidden" name="page_style[{$frontend.id}]" value=""/>
                    {/if}
                </div>
            {/foreach}

        </div>
        <div class="tab-pane topTabPane tabbable-custom" id="imageTab">
            <div class="row">
                <div class="col-md-12">
                    <div class="widget box">
                        <div class="widget-header">
                            <h4>{$smarty.const.TEXT_IMAGE_}</h4>
                        </div>
                        <div class="widget-content">
                            <div class="about-image">
                            </div>
                            {\backend\design\Image::widget([
                            'name' => 'imageGallery',
                            'value' => {$additionalFields.image},
                            'upload' => 'image',
                            'delete' => 'image_delete'
                            ])}
                        </div>
                        <div class="divider"></div>
                    </div>
                </div>
            </div>


            <div class="row">

                <div class="col-md-6">
                    <div class="widget box">
                        <div class="widget-header">
                            <h4>{$smarty.const.IMAGE_MAP}</h4>
                        </div>
                        <div class="widget-content">
                            <div class="category-image-map form-container">
                                <div class="row">

                                    <div class="col-md-3" style="padding: 20px 0">
                                        <label for="">{$smarty.const.IMAGE_MAP_NAME}</label>
                                    </div>
                                    <div class="col-md-6" style="padding: 20px 0">
                                        <input type="text" class="form-control map-name" name="map_name" value="{$mapsTitle}" autocomplete="off"/>
                                        <input type="hidden" name="maps_id" value="{$mapsId}"/>
                                        <div class="search-map"></div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="map-image-holder">
                                            <img src="../images/maps/{$mapsImage}" class="map-image" alt="" {if !$mapsImage} style="display: none" {/if}>
                                            <div class="map-image-remove" {if !$mapsImage} style="display: none" {/if}></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="divider"></div>
                    </div>
                </div>
            </div>

            <script>
                $(function(){

                    let searchProductBox = $('.search-map');
                    $('.map-name').keyup(function(e){
                        $.get('image-maps/search', {
                            key: $(this).val()
                        }, function(data){
                            $('.suggest').remove();
                            searchProductBox.append('<div class="suggest">'+data+'</div>');

                            $('a', searchProductBox).on('click', function(e){
                                e.preventDefault();

                                $('input[name="maps_id"]').val($(this).data('id'));
                                $('input[name="map_name"]').val($('.td_name', this).text());
                                $('.map-image').show().attr('src', '../images/maps/' + $(this).data('image'));
                                $('.map-image-remove').show();

                                $('.suggest').remove();
                                return false
                            })
                        })
                    });

                    $('.map-image-remove').on('click', function(){
                        $('input[name="maps_id"]').val('');
                        $('input[name="map_name"]').val('');
                        $('.map-image').show().attr('src', '');
                        $(this).hide()
                    })
                })
            </script>
        </div>

        <div class="tab-pane topTabPane tabbable-custom" id="settings">

            <div class="container form-container">

                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.SCHEDULE_STATUS_CHANGE}</label></div>
                    <div class="col-md-4">
                        {PageStatus::showSchedule('information', $information_id)}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.TABLE_HEADING_DATE_ADDED}</label></div>
                    <div class="col-md-4">
                        {Html::input('text', 'date_added1', $additionalFields.date_added1, ['class' =>'form-control datepicker'])}
                        {Html::input('hidden', 'date_added', $additionalFields.date_added, ['id' =>'altDate'])}
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.BOX_HEADING_CATALOG_PAGES}</label></div>
                    <div class="col-md-4">
                        <input type="checkbox" value="1" name="type" class="check_on_off"{if $additionalFields.type == 1} checked="checked"{/if}>
                    </div>
                </div>
                {if $showHidePage}
                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.HIDE_PAGE}</label></div>
                    <div class="col-md-4">
                        <input type="checkbox" name="hide_page"{if $hidePage} checked{/if} class="check_on_off"/>
                    </div>
                </div>
                {/if}
                <div class="row">
                    <div class="col-md-2"><label>{$smarty.const.DONT_SHOW_ON_XML}</label></div>
                    <div class="col-md-4">
                        <input type="checkbox" name="hide_on_xml"{if $additionalFields.hide_on_xml} checked{/if} class="check_on_off"/>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
{if $isMultiPlatform}</div>{/if}
<div class="btn-bar" style="padding: 0;">
    <div class="btn-left">
        <a href="javascript:void(0);" onclick="return backStatement();" class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</a>
    </div>
    <div class="btn-right">
        {if isset($preview_link) && $preview_link|@count > 1}
            <a href="#choose-frontend" class="btn btn-primary btn-choose-frontend">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
        {elseif isset($preview_link)}
            <a href="{$preview_link[0].link}" target="_blank" class="btn btn-primary">{$smarty.const.TEXT_PREVIEW_ON_SITE}</a>
        {/if}

        {if $information_id > 0}
           <span class="btn btn btn-delete" style="margin-right: 15px;" onClick="return confirmDeleteInfoPage({$information_id})">{$smarty.const.IMAGE_DELETE}</span>
        {/if}        
        <button class="btn btn-confirm" type="submit">{$smarty.const.IMAGE_SAVE}</button>
    </div>
</div>            
</form>

{if isset($preview_link) && $preview_link|@count > 1}
    <div id="choose-frontend" style="display: none">
        <div class="popup-heading">{$smarty.const.CHOOSE_FRONTEND}</div>
        <div class="popup-content frontend-links">
            {foreach $preview_link as $link}
                <p><a href="{$link.link}" target="_blank">{$link.name}</a></p>
            {/foreach}
        </div>
        <div class="noti-btn">
            <div><button class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</button></div>
        </div>
        <script type="text/javascript">
            (function($){
                $(function(){
                    $('.popup-box-wrap .frontend-links a').on('click', function(){
                        $('.popup-box-wrap').remove()
                    })
                })
            })(jQuery)
        </script>
    </div>
    <script type="text/javascript">
        (function($){
            $(function(){
                $('.btn-choose-frontend').popUp({ one_popup: false });
            })
        })(jQuery)
    </script>
{/if}

<!--END TABS-->
<script type="text/javascript">
    function backStatement() {
        {if $app->controller->view->usePopupMode|default:null}
        $('.popup-box:last').trigger('popup.close');
        $('.popup-box-wrap:last').remove();
        {else}
        window.history.back();
        {/if}
        return false;
    }

    function confirmDeleteInfoPage(info_id) {

    }
    $(function(){
        $('.btn-save-page').on('click', saveInfoPage)
    })
function saveInfoPage(){
    if (typeof(CKEDITOR) == 'object'){
        for ( instance in CKEDITOR.instances ) {
            CKEDITOR.instances[instance].updateElement();
        }
    }
    $.post("{Yii::$app->urlManager->createUrl('information_manager/page-save')}", $('#infoPage_form').serialize(), function(data, status){
        if (status == "success") {

            if (data.error) {
                $('.content-container').prepend(data.error);
                setTimeout(function(){
                    $('.content-container .popup-box-wrap').remove()
                }, 1000)
            }

            if (data.url) {
                window.location.href = data.url;
            }
            //location.reload();
        } else {
            alert("Request error.");
        }
    },"json");

    //$('input[name=categories_image_loaded]').val();

    return false;
}
    function confirmDeleteInfoPage(id) {
        bootbox.dialog({
            message: "{$smarty.const.JS_DELETE_PAGE_TEXT}",
            title: "{$smarty.const.JS_DELETE_PAGE_HEAD}",
            buttons: {
                success: {
                    label: "{$smarty.const.TEXT_BTN_YES}",
                    className: "btn-delete",
                    callback: function () {
                        $.post("information_manager/delete", { 'info_id': id }, function (data, status) {
                            if (status == "success") {
                                window.history.back();
                                /*
                                var table = $('.table').DataTable();
                                table.draw(false);
                                resetStatement();*/
                            } else {
                                alert("Request error.");
                            }
                        }, "html");
                    }
                },
                main: {
                    label: "{$smarty.const.TEXT_BTN_NO}",
                    className: "btn-cancel",
                    callback: function () {
                        //console.log("Primary button");
                    }
                }
            }
        });
        return;
    }
$(document).ready(function() {
    $('.meta_title input').limitValue('title');
    $('.meta_description textarea').limitValue('description');

    $('.check_on_off').bootstrapSwitch( {
        onText: "{$smarty.const.SW_ON}",
        offText: "{$smarty.const.SW_OFF}",
        handleWidth: '20px',
        labelWidth: '24px'
    } );

    $(window).resize(function(){
	//var lab_url = $('.edp-line .input-group-addon').width() + 14;
        //$('.input-width-url').css({ 'padding-left' : lab_url + 10, 'margin-left' : '-' + lab_url });
    });
    $(window).resize();
});
</script>
<script>
    $(function () {
        $(window).on('load', function(){
            $( ".datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true,
                showOtherMonths:true,
                autoSize: false,
                dateFormat: 'd MM yy',
                altFormat: "yy-mm-dd",
                altField: "#altDate"
            });
            $('.popupLinks').popUp({
                box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box'><div class='popup-heading cat-head'>{$smarty.const.TEXT_PAGE_LINKS}</div><div class='pop-up-close'></div><div class='pop-up-content'><div class='preloader'></div></div></div></div>"
            });
            $('.popupLinks').on('click', function(){
                $('.popup-heading').text($(this).text());
            })
        })
    })
function addInput (id, input) {
  $('#' + id).append(input);
}
$('body').on('click', '.del-pt.del-tag', function(){
  $(this).parent().remove();
});
</script>



