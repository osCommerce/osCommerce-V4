<div class="designer-mode">
    <label class="">{$smarty.const.EDIT_MODE}:</label>
    <div>
        <input type="radio" name="designer_mode" value=""{if !$designer_mode} checked{/if}/>
        <span>{$smarty.const.BASIC_MODE}</span>
    </div>
    <div>
        <input type="radio" name="designer_mode" value="advanced"{if $designer_mode == 'advanced'} checked{/if}/>
        <span>{$smarty.const.ADVANCED_MODE}</span>
    </div>
    <div>
        <input type="radio" name="designer_mode" value="expert"{if $designer_mode == 'expert'} checked{/if}/>
        <span>{$smarty.const.EXPERT_MODE}</span>
    </div>
</div>

<div class="theme-list" data-group_id="{$group_id}">
    {foreach $themes as $item}
        {if $item.theme_name}
            <div class="item theme" data-theme_name="{$item.theme_name}">
                <div class="img">
                    <img src="{DIR_WS_CATALOG}themes/basic/img/screenshot.png" alt="">
                    <div class="image">
                        {if $item.theme_image}
                            <img src="{DIR_WS_CATALOG}{$item.theme_image}" alt="">
                        {else}
                            <img src="{DIR_WS_CATALOG}themes/{$item.theme_name}/screenshot.png" alt="">
                        {/if}
                    </div>
                    <div class="theme-info">
                        <div class="directory">
                            {$smarty.const.TEXT_DIRECTORY}: {$item.theme_name}
                        </div>
                        {if $item.parent_theme_title}
                            <div class="parent">
                                {$smarty.const.TEXT_PARENT_THEME}: {$item.parent_theme_title}
                            </div>
                        {/if}
                        <div class="description">{$item.description}</div>
                    </div>
                    <div class="upload-preview-image">
                        {if $item.image}
                            <img src="../{$item.image}" alt="">
                        {else}
                            <img src="{DIR_WS_CATALOG}themes/basic/img/screenshot.png" alt="">
                        {/if}
                    </div>
                    <div class="upload-preview" style="display:none;">
                        <div class="upload-preview-title">{$smarty.const.UPLOAD_PREVIEW_IMAGE}</div>
                        <form>
                            <input type="hidden" name="theme_name" value="{$item.theme_name}"/>
                        {\backend\design\Image::widget([
                        'name' => 'preview['|cat:$item.theme_name|cat:']',
                        'value' => $item.image,
                        'upload' => 'preview_upload['|cat:$item.theme_name|cat:']',
                        'delete' => 'preview_delete['|cat:$item.theme_name|cat:']'
                        ])}
                        </form>
                    </div>
                    <span class="info-ico"></span>
                    <span class="img-ico"></span>
                </div>
                <div class="title{if $item.platforms} has-platforms{/if}">
                    <div class="title-holder">{$item.title}</div>
                    <form class="title-edit-input">
                        <input type="text" value="{$item.title}" class="form-control"/>
                    </form>
                    <div class="title-edit-ico" title="{$smarty.const.CHANGE_THEME_TITLE}"></div>
                    <div class="title-cancel" title="{$smarty.const.IMAGE_CANCEL}"></div>
                    <div class="btn title-apply">{$smarty.const.IMAGE_APPLY}</div>
                    <div class="title-preloader"></div>
                </div>
                <div class="platforms">
                    {foreach $item.platforms as $platform}
                        <div class="platform">
                            <div class="platform-title">{$platform.platform_name}</div>
                            <a href="{if $platform.ssl_enabled == '0'}http://{else}https://{/if}{$platform.platform_url}" target="_blank">{$platform.platform_url}</a>
                        </div>
                    {/foreach}
                </div>
                <div class="buttons">
                    <a href="{Yii::$app->urlManager->createUrl(['design/backups', 'theme_name' => $item.theme_name])}"
                       class="btn btn-primary btn-backups" title="{$smarty.const.TEXT_BACKUPS}"></a>
                    <a href="{Yii::$app->urlManager->createUrl(['design/theme-move', 'theme_name' => $item.theme_name])}"
                       class="btn btn-primary btn-move" title="Move to group"></a>
                    <a href="{Yii::$app->urlManager->createUrl(['design/theme-copy', 'theme_name' => $item.theme_name])}"
                       class="btn btn-primary btn-copy-t" title="Copy theme"></a>
                    <a href="{$item.link}" class="btn btn-primary btn-open">{$smarty.const.TEXT_CUSTOMIZE}</a>
                </div>

                <span data-href="{Yii::$app->urlManager->createUrl(['design/theme-remove', 'theme_name' => $item.theme_name])}"
                      class="remove remove-theme" title="{$smarty.const.TEXT_REMOVE}"></span>
            </div>
        {else}
            <div class="item group" data-group_id="{$item.themes_group_id}">
                <div class="img">
                    <img src="{DIR_WS_CATALOG}themes/basic/img/screenshot.png" alt="">
                    <div class="image folder-ico"></div>
                    <div class="theme-info">
                        <div class="">{$smarty.const.THEMES_IN_THIS_GROUP}</div>
                        {foreach $item.themes as $theme}
                            <div class=""><a href="{$theme.link}">{$theme.title}</a></div>
                        {/foreach}
                        <div class="description">{$item.description}</div>
                    </div>
                    <div class="upload-preview-image">
                        {if $item.image}
                            <img src="../{$item.image}" alt="">
                        {else}
                            <img src="{DIR_WS_CATALOG}themes/basic/img/screenshot.png" alt="">
                        {/if}
                    </div>
                    <div class="upload-preview" style="display:none;">
                        <div class="upload-preview-title">{$smarty.const.UPLOAD_PREVIEW_IMAGE}</div>
                        {\backend\design\Image::widget([
                        'name' => 'preview['|cat:$item.themes_group_id|cat:']',
                        'value' => $item.image,
                        'upload' => 'preview_upload['|cat:$item.themes_group_id|cat:']',
                        'delete' => 'preview_delete['|cat:$item.themes_group_id|cat:']'
                        ])}
                    </div>
                    <span class="info-ico"></span>
                    <span class="img-ico"></span>
                </div>
                <div class="title">
                    <div class="title-holder">{$item.title}</div>
                    <form class="title-edit-input">
                        <input type="text" value="{$item.title}" class="form-control"/>
                    </form>
                    <div class="title-edit-ico" title="{$smarty.const.CHANGE_THEME_TITLE}"></div>
                    <div class="title-cancel" title="{$smarty.const.IMAGE_CANCEL}"></div>
                    <div class="btn title-apply">{$smarty.const.IMAGE_APPLY}</div>
                    <div class="title-preloader"></div>
                </div>
                <div class="platforms"></div>
                <div class="buttons">
                    <a href="{$item.link}" class="btn btn-primary btn-open">{$smarty.const.TEXT_OPEN}</a>
                </div>

                <a href="{Yii::$app->urlManager->createUrl(['design/group-remove', 'group_id' => $item.themes_group_id])}"
                      class="remove remove-group" title="{$smarty.const.TEXT_REMOVE}"></a>
            </div>
        {/if}
    {/foreach}

</div>


<script type="text/javascript">
$(function () {

    $('.theme-list .remove-theme').on('click', function () {
        var _this = $(this);
        $.popUpConfirm('{$smarty.const.DO_YOU_WANT_REMOVE_THEME}: ' + _this.closest('.item').find('.title').text(), function () {
            document.location.href = _this.data('href')
        })
    });

    $('.theme-list').sortable({
        handle: ".image, .upload-preview-image",
        update: function(){
            let sort = [];
            $('.theme-list .item').each(function(){
                if ($(this).hasClass('theme')) {
                    sort.push({ theme_name: $(this).data('theme_name')})
                } else {
                    sort.push({ group_id: $(this).data('group_id')})
                }
            })
            $.post('design/theme-sort', { sort }, function(data, status){

            }, 'json')
        }
    });

    $('.theme-list .item').on('dblclick', function(){
        window.location = $('.btn-open', this).attr('href')
    })

    $('.theme-list .info-ico').on('click', function(){
        const $theme = $(this).closest('.item');
        $('.image, .upload-preview-image', $theme).hide();
        $('.theme-info', $theme).show();
        $('.info-ico', $theme).hide();
        $('.img-ico', $theme).show();
    })
    $('.theme-list .img-ico').on('click', function(){
        const $theme = $(this).closest('.item');
        $('.image, .upload-preview-image', $theme).show();
        $('.theme-info', $theme).hide();
        $('.info-ico', $theme).show();
        $('.img-ico', $theme).hide();

    })

    $('.top-buttons .btn-import-theme').popUp();
    $('.btn-move').popUp();
    $('.create-group').popUp();
    $('.remove-group').popUp();
    $('.btn-copy-t').popUp();

    $('.theme-list .title-edit-ico').on('click', function () {
        const $theme = $(this).closest('.item');
        $('.title-holder', $theme).hide();
        $('.title-edit-ico', $theme).hide();
        $('.title-edit-input', $theme).show();
        $('.remove', $theme).hide();
        $('.upload-preview', $theme).css('display', 'flex');
        $('.title-apply, .title-cancel', $theme).show()
    });

    $('.theme-list .title-cancel').on('click', function () {
        const $theme = $(this).closest('.item');
        $('.title-holder', $theme).show();
        $('.title-edit-ico', $theme).show();
        $('.title-edit-input', $theme).hide();
        $('.remove', $theme).show();
        $('.upload-preview', $theme).hide();
        $('.title-preloader', $theme).hide();
        $('.title-apply, .title-cancel', $theme).hide()
        $('.title-edit-input input', $theme).val($('.title-holder', $theme).text())
    });

    $('.theme-list .title-apply').on('click', themeNameApply);
    $('.title-edit-input').on('submit', themeNameApply);

    function themeNameApply(e){
        e.preventDefault();
        const $theme = $(this).closest('.item');
        const theme_name = $theme.data('theme_name') || '';
        const group_id = $theme.data('group_id');
        const title = $('.title-edit-input input', $theme).val();
        const image = $('input[name="preview['+(theme_name || group_id)+']"]', $theme).val();
        const image_upload = $('input[name="preview_upload['+(theme_name || group_id)+']"]', $theme).val();
        const image_delete = $('input[name="preview_delete['+(theme_name || group_id)+']"]', $theme).val();

        $('.title-apply, .title-cancel', $theme).hide()
        $('.title-preloader', $theme).show()

        const requestData = { theme_name, group_id, title};
        if (image && image !== '0') requestData.image = image;
        if (image_upload) requestData.image_upload = image_upload;
        if (image_delete) requestData.image_delete = image_delete;

        $.post('design/theme-title', requestData, function(data, status){
            $('.title-holder', $theme).show();
            $('.title-edit-ico', $theme).show();
            $('.title-edit-input', $theme).hide();
            $('.remove', $theme).show();
            $('.upload-preview', $theme).hide();
            $('.title-preloader', $theme).hide();
            $('.title-edit-input input', $theme).val($('.title-holder', $theme).text())

            if (status != "success") {
                alertMessage("Request error.", 'alert-message');
                return null;
            }
            if (data.error) {
                alertMessage(data.error, 'alert-message');
            }
            if (data.title) {
                $('.title-edit-input input', $theme).val(data.title)
                $('.title-holder', $theme).html(data.title);
            }
            if (data.image) {
                $('.image img', $theme).attr('src', '../' + data.image);
                $('.upload-preview-image img', $theme).attr('src', '../' + data.image);
            } else {
                $('.image img', $theme).attr('src', '{DIR_WS_CATALOG}themes/basic/img/screenshot.png');
                $('.upload-preview-image img', $theme).attr('src', '{DIR_WS_CATALOG}themes/basic/img/screenshot.png');
            }
        }, 'json')
    }

    let designerMode = $('.designer-mode input:checked').val();
    switchDesignerMode(designerMode);
    $('.designer-mode input').tlSwitch({
        onSwitchChange: function(e, status){
            if (designerMode == e.target.value) {
                return null;
            }

            let message = '';
            switch (e.target.value) {
                case 'advanced':
                    message = '{$smarty.const.ARE_YOU_SURE_MODE_ADVANCED}';
                    break;
                case 'expert':
                    message = '{$smarty.const.ARE_YOU_SURE_MODE_EXPERT}';
                    break;
                default:
                    message = '{$smarty.const.ARE_YOU_SURE_MODE_BASIC}';
            }

            bootbox.dialog({
                message: message,
                title: '{$smarty.const.MODE_SWITCH_WARNING}',
                className: 'edit-banner-popup',
                buttons: {
                    main: {
                        label: "{$smarty.const.IMAGE_CANCEL}",
                        className: "btn",
                        callback: function() {
                            $(`.designer-mode input[value="${ designerMode }"]`).trigger('click')
                        }
                    },
                    success: {
                        label: "{$smarty.const.IMAGE_SAVE}",
                        className: "btn btn-primary",
                        callback: function() {
                            designerMode = e.target.value;
                            switchDesignerMode(designerMode, true)
                        }
                    }
                }
            });
        }
    })

    function switchDesignerMode(mode, save = false){
        if (save) {
            $.post('design/save-admin-data', { designer_mode: mode})
        }
    }

    $.get('design/check-origin-theme');//create origin and new_theme if they don't exist
})
</script>

<link href="{$app->view->theme->baseUrl}/css/design.css" rel="stylesheet" type="text/css" />