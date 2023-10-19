{use class="backend\assets\BannersAsset"}
{BannersAsset::register($this)|void}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}

<form class="group-edit-form">
    <input type="hidden" name="group_id" value="{$groupId}"/>
    <input type="hidden" name="group[status]" value="{$group.status}"/>
<div class="group-edit-holder">
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs">
            <li class="active" data-bs-toggle="tab" data-bs-target="#main">
                <a>{$smarty.const.TEXT_MAIN_DETAILS}</a>
            </li>
            <li data-bs-toggle="tab" data-bs-target="#images">
                <a>{$smarty.const.TAB_IMAGES}</a>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="main">


                {if $new_group}
                <div class="row m-b-4">
                    <div class="col-md-6">

                        <h4>{$smarty.const.TEXT_CHOOSE_THEME}</h4>

                        <div class="group-themes">
                        {foreach $themes as $theme}
                            <div class="">
                                <label>
                                    <input type="radio" name="group[theme_name]" value="{$theme.theme_name}" class="theme-name"/>
                                    {$theme.title}
                                </label>
                            </div>
                        {/foreach}
                        </div>

                    </div>
                    <div class="col-md-6">

                        <h4>{$smarty.const.CHOOSE_PAGES}</h4>

                        <div id="tree"></div>

                    </div>
                </div>
                {/if}


                <div class="row m-b-2">
                    <div class="col-md-4">
                        <div class="file row align-items-center m-b-2">
                            <label class="col-3 align-right">{$smarty.const.ICON_FILE}:</label>
                            <div class="col-9">
                                <input type="text" name="group[file]" value="{$group.file}" class="form-control"/>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="category row align-items-center m-b-2">
                            <label class="col-3 align-right">{$smarty.const.TEXT_CATEGORY}:</label>
                            <div class="col-9">
                                {$group.categoryDropdown}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="page-type row align-items-center m-b-2">
                            <label class="col-4 align-right">{$smarty.const.SHOW_ON_PAGE_TYPE}:</label>
                            <div class="col-8">
                                {$group.typesDropdown}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tabbable tabbable-custom">
                {if count($languages) > 1}
                    <ul class="nav nav-tabs under_tabs_ul">
                        {foreach $languages as $lKey => $language}
                            <li{if $lKey == 0} class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_{$language['code']}"><a data-id="{$language['id']}">{$language['logo']}<span>{$language['name']}</span></a></li>
                        {/foreach}
                    </ul>
                {/if}
                <div class="tab-content {if count($languages) < 2}tab-content-no-lang{/if}" style="margin-bottom: 10px">
                    {foreach $languages as $lKey => $language}
                        <div class="tab-pane{if $lKey == 0} active{/if}" id="tab_{$language['code']}">

                            <div class="titles row align-items-center m-b-2">
                                <label class="col-3 align-right">{$smarty.const.TEXT_TITLE}:</label>
                                <div class="col-9">
                                    <input type="text" name="group[languages][{$language['id']}][title]" value="{$group.languages[$language['id']].title}" class="form-control group-title"/>
                                </div>
                            </div>

                            <div class="descriptions row">
                                <label class="col-3 align-right">{$smarty.const.TEXT_DESCRIPTION}:</label>
                                <div class="col-9">
                                    <textarea name="group[languages][{$language['id']}][description]" rows="10" class="form-control">{$group.languages[$language['id']].description}</textarea>
                                </div>
                            </div>

                        </div>
                    {/foreach}
                </div>
                </div>

            </div>
            <div class="tab-pane" id="images">

                <div class="images-wrap">

                    {foreach $images as $image}
                        <div class="image-item">

                        </div>
                    {/foreach}
                </div>

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

</div>
</form>
<link href="plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="plugins/fancytree/jquery.fancytree-all.min.js"></script>
<script>
    $(function(){

        $('#tree').fancytree({
            extensions: ["glyph"],
            checkbox: true,
            source: {
                url: "design-groups/get-pages"
            },
            selectMode: 3,
            glyph: {
                map: {
                    doc: "icon-file-o",//"fa fa-file-o",
                    docOpen: "icon-file-o", //"fa fa-file-o",
                    checkbox: "icon-check-empty",// "fa fa-square-o",
                    checkboxSelected: "icon-check",// "fa fa-check-square-o",
                    checkboxUnknown: "icon-check-empty", //"fa fa-square",
                    dragHelper: "fa fa-arrow-right",
                    dropMarker: "fa fa-long-arrow-right",
                    error: "fa fa-warning",
                    expanderClosed: "icon-plus-sign-alt", //"fa fa-caret-right",
                    expanderLazy: "icon-plus-sign-alt", //"icon-expand-alt", //"fa fa-angle-right",
                    expanderOpen: "icon-minus-sign-alt",//"fa fa-caret-down",
                    folder: "icon-folder-close-alt",//"fa fa-folder-o",
                    folderOpen: "icon-folder-open-alt",//"fa fa-folder-open-o",
                    loading: "icon-spinner" //"fa fa-spinner fa-pulse"
                }
            },
        });

        $('input.theme-name').on('change', function () {
            const themeName = $('input.theme-name:checked').val();
            const tree = $('#tree').fancytree('getTree');
            tree.options.source.url = 'design-groups/get-pages?theme_name=' + themeName;
            tree.reload();
        })



        const images = JSON.parse('{json_encode($group.images)}');

        const $addImage = $('<div class="image-item add-image"><span class="btn btn-primary btn-add-image">{$smarty.const.ADD_ONE_MORE_IMAGE}</span></div>');
        $('.images-wrap').append($addImage);

        $('.btn', $addImage).on('click', () => $addImage.before(imageBox('')))

        if (images.length) {
            images.forEach(function(image){
                $addImage.before(imageBox(image.file))
            })
        } else {
            $addImage.before(imageBox(''))
        }

        function imageBox(image){
            const $imageHolder = $(`
                <div class="image-item">
                    <div class="image-delete"></div>
                    <div class="upload-box upload-box-wrap"
                         data-name="group[images][]"
                         data-value="${ image}"
                         data-upload="group[image_upload][]"
                         data-delete="group[image_delete][]"
                         data-type="image"
                         data-folder="{$smarty.const.DIR_WS_IMAGES}widget-groups/{$groupId}/"
                         data-accepted-files="image/*"
                    >
                    </div>
                </div>
            `);
            $('.upload-box', $imageHolder).fileManager()
            $('.image-delete', $imageHolder).on('click', function () {
                $imageHolder.remove();
            })
            return $imageHolder;
        }


        const $form = $('.group-edit-form');

        $('.top-buttons .btn-save').on('click', () => $form.trigger('submit'))

        $form.on('submit', function (e) {
            e.preventDefault();
            const data = $form.serializeArray();
            $('.group-edit-holder').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>');

            const $themeName = $('input.theme-name:checked');

        {if $new_group}

            if (!$themeName.length) {
                alertMessage('{$smarty.const.PLEASE_CHOOSE_THEME}', 'alert-message');
                return null;
            }

            const selectedNodes = $('#tree').fancytree('getTree').getSelectedNodes();
            let pages = false;
            for (let i = 0; i < selectedNodes.length; i++) {
                if (!selectedNodes[i].children) {
                    data.push({ name: `group[pages][]`, value: selectedNodes[i].key });
                    pages = true
                }
            }

            if (!pages) {
                alertMessage('{$smarty.const.PLEASE_CHOOSE_PAGES}', 'alert-message');
                return null;
            }

        {/if}

            if (!$('input[name="group[file]"]').val()) {
                alertMessage('{$smarty.const.PLEASE_ENTER_FILE_NAME}', 'alert-message');
                return null;
            }

            if (!$('select[name="group[category]"]').val()) {
                alertMessage('{$smarty.const.PLEASE_CHOOSE_CATEGORY}', 'alert-message');
                return null;
            }

            let groupTitle = false;
            $('input.group-title').each(function () {
                if ($(this).val()) {
                    groupTitle = true
                }
            })
            if (!groupTitle) {
                alertMessage('{$smarty.const.PLEASE_ENTER_TITLE}', 'alert-message');
                return null;
            }

            $('.group-edit-holder').addClass('hided-box')
                .append('<div class="hided-box-holder"><div class="preloader"></div></div>');
            $.post(`design-groups/save?group_id={$groupId}${ $themeName && $themeName.length ? `&theme_name=${ $themeName.val()}` : '' }`, data, function (response, status) {

                $('.group-edit-holder').removeClass('hided-box');
                $('.hided-box-holder').remove()

                if (status != "success") {
                    alertMessage('Error', 'alert-message');
                    return '';
                }
                if (response.error) {
                    alertMessage(response.error, 'alert-message');
                    return '';
                }
                if (response.text) {
                    const $popUp = alertMessage(response.text, 'alert-message');
                    setTimeout(() => $popUp.remove(), 1000);
                }
                if (response.html) {
                    /*$('.content-container').html(response.html);
                    if (location.hash) {
                        $(`.nav a[href="${ location.hash }"]`).click();
                    }*/
                    const url = new URL(window.location.href);
                    url.searchParams.delete('new_group');
                    window.location = url.toString();
                }
            }, 'json')

        })
    })
</script>