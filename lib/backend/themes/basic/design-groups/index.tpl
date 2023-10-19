{include '../design/menu.tpl'}
{use class="backend\assets\DesignAsset"}
{DesignAsset::register($this)|void}

<div class="design-groups-wrap">
<form id="filterForm" name="filterForm">
    <div class="filters_btn groups-filter">
        <div class="breadcrumbs">
            <div class="top">{$smarty.const.TEXT_TOP}</div>
        </div>

        <input type="hidden" name="row" id="row_id" value="{$row}" />
        <input type="hidden" name="category" id="category" value="{$category}" />
        <input type="hidden" name="theme_name" id="category" value="{$theme_name}" />

        <div class="">
            <input type="checkbox" name="show_empty" class="show-empty" value="" /> {$smarty.const.SHOW_EMPTY_CATEGORIES}
        </div>
    </div>

</form>

<div class="categories-wrap"></div>

<div class="order-wrap ">

    <div class="row order-box-list">
        <div class="col-md-12">
            <div class="widget-content">
                <div class="alert fade in" style="display:none;">
                    <i data-dismiss="alert" class="icon-remove close"></i>
                    <span id="message_plce"></span>
                </div>




                <div class="table-holder">
                <table class="{if $isMultiPlatforms}tab_edt_page_mul{/if} table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tab-pages double-grid table-design-groups" checkable_list="" data_ajax="design-groups/list">
                    <thead>
                    <tr>
                        <th class="image-cell-heading"></th>
                        <th>{$smarty.const.TABLE_TEXT_NAME}</th>
                        <th class="file-heading-cell">{$smarty.const.ICON_FILE}</th>
                        <th class="type-heading-cell">{$smarty.const.TEXT_PAGE_TYPE}</th>
                        <th class="status-heading-cell">{$smarty.const.SHOW_ON_WIDGETS_LIST}</th>
                    </tr>
                    </thead>
                </table>
                    <div class="table-holder-dz-hover">{$smarty.const.TEXT_DROP_FILES}</div>
                </div>


            </div>
        </div>
    </div>

    <div class="row right_column" id="col_management" style="display: none;">
        <div class="widget box">
            <div class="widget-content" id="col_management_data">
                <div class="scroll_col"></div>
            </div>
        </div>
    </div>
</div>
</div>
<script>

    function setFilterState() {
        orig = $('#filterForm').serialize();
        var url = window.location.origin + window.location.pathname + '?' + orig.replace(/[^&]+=\.?(?:&|$)/g, '').replace(/\[/g, '%5B').replace(/\]/g, '%5D');
        window.history.replaceState({ }, '', url);
    }

    function resetStatement() {
        setFilterState();
        $(".order-wrap").show();

        $('#banner_management_data .scroll_col').html('');
        $('#banner_management').hide();

        var table = $('.table').DataTable();
        table.draw(false);

        updateCategories()

        return false;
    }

    function updateCategories(){

        $.get('design-groups/categories-list', $('#filterForm').serializeArray(), function (response) {
            const $categoriesWrap = $('.categories-wrap');
            $categoriesWrap.html('');

            if (response.groupsCount) {
                $('.order-wrap').show()
            } else {
                $('.order-wrap').hide()
            }

            response.categories.forEach(function (category) {
                const $item = $(`
                    <div class="item" data-name="${ category.name }">
                        <div class="image"><img src="{\common\classes\Images::getWSCatalogImagesPath()}widget-groups/categories/${ category.name }.png"></div>
                        <div class="title">
                            ${ category.title }
                            <span class="count-groups">(${ category.count })</span>
                        </div>
                    </div>
                `);
                $item.one('click', function () {
                    let categoryPath = $('#category').val();
                    categoryPath += (categoryPath ? '/' : '') + category.name;
                    $('#category').val(categoryPath);
                    applyFilter()
                });
                $('img', $item).on('error', function () {
                    $('.image', $item).html('<div class="catalog-ico"></div>')
                });
                $categoriesWrap.append($item)
            })
        }, 'json')
    }

    function onClickEvent(obj, table){
        const id = $(obj).find('.double-click').data('id');
        const name = $(obj).find('.double-click').data('name');
        const title = $(obj).find('.name-cell').text();
        const category_id = $(obj).find('.name-cell').data('category_id');

        const row_id = $('.table').DataTable().row('.selected').index();
        $("#row_id").val(row_id);
        if (id) {
            viewModule(id);
        }
        if (name) {
            viewCategory(name, title, category_id);
        }

        setBreadcrumbs();
        setFilterState();
    }

    function viewModule(group_id){
        const row_id = $("#row_id").val();
        const category = $("#category").val();
        $.get('design-groups/group-bar', { group_id, row_id, category }, function(data, status){
            if (status == "success") {
                $('#col_management_data .scroll_col').html(data);
                $("#col_management").show();
                heightColumn();
            } else {
                alert("Request error.");
            }
        });
    }

    function viewCategory(name, title, category_id){
        const row_id = $("#row_id").val();
        const category = $("#category").val();
        $.get('design-groups/group-category-bar', { name, category, row_id, title, category_id }, function(data, status){
            if (status == "success") {
                $('#col_management_data .scroll_col').html(data);
                $("#col_management").show();
                heightColumn();
            } else {
                alert("Request error.");
            }
        });
    }

    $(function() {
        const url = new URL(window.location.href)
        $("#row_id").val(url.searchParams.get('row'));

        updateCategories();

        $("div.table-holder, .btn-add-group").dropzone({
            url: "{Yii::$app->urlManager->createUrl('design-groups/upload')}",
            success: function(){
                $('.table').DataTable().ajax.reload();
            },
            previewTemplate: '<div></div>',
            acceptedFiles: '.zip',
        });

        $('.btn-add-group-category').on('click', function () {
            const $popup = alertMessage(`
                <div class="popup-heading">{$smarty.const.CATEGORY_NAME}</div>
                <div class="popup-content"><input type="text" class="form-control"></div>
                <div class="popup-buttons"></div>
            `);

            const $btnSave = $('<span class="btn btn-save btn-primary">{$smarty.const.IMAGE_SAVE}</span>');
            const $btnCancel = $('<span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>');
            $('.popup-buttons', $popup).append($btnSave).append($btnCancel);

            $btnSave.on('click', function () {
                const name = $('input', $popup).val();
                if (!name) {
                    alertMessage('{$smarty.const.PLEASE_ENTER_CATEGORY_NAME}', 'alert-message');
                    return null;
                }
                const url = new URL(window.location.href);
                $.post('design-groups/add-category', {
                    name,
                    category: url.searchParams.get('category') || ''
                }, function (response) {
                    if (response.error) {
                        alertMessage(response.error, 'alert-message')
                    }
                    if (response.text) {
                        $popup.remove();
                        const $message = alertMessage(response.text, 'alert-message');
                        setTimeout(() => $message.remove(), 1000);
                        resetStatement()
                    }
                }, 'json')

            });
        });

        $('.btn-create-group').on('click', function () {
            window.location.href = entryData.mainUrl.trim('/') + '/design-groups/edit?category=' + $("#category").val();
        })
    });

    function applyFilter() {
        var $platforms = $('.js_platform_checkboxes');
        if ( $platforms.length>0 ) {
            var http_method = false;
            $platforms.filter('[data-checked]').each(function(){
                if ( this.checked != ($(this).attr('data-checked')=='true') ) {
                    http_method = true;
                }
            });
            if ( http_method ) return true;
        }
        resetStatement();
        return false;
    }

    $(function () {
        $('body').on('dblclick', '.double-click', function(){
            const name = $(this).data('name');

            if (name) {
                let category = $('#category').val();
                category += (category ? '/' : '') + name;
                $('#category').val(category);
                applyFilter()
            } else {
                const id = $(this).data('id');
                const row_id = $('.table').DataTable().row('.selected').index();
                window.location.href = `design-groups/view?group_id=${ id }&row_id=${ row_id }&category=` + $('#category').val()
            }
        });

        $('.show-empty').tlSwitch({
            onSwitchChange: function (element, status) {
                applyFilter()
            }
        });


        $('.table').on('draw.dt', function() {
            $(".group-status").tlSwitch({
                onSwitchChange: function (element, status) {
                    //switchStatement([element.target.value], arguments);
                    $.post('design-groups/switch-status', {
                        groupId: $(this).val(),
                        status: status ? 1 : 0
                    }, function () {

                    }, 'json')
                    return true;
                }
            });

            if (!$('.name-cell.group-name').length) {
                $('.file-heading-cell').hide();
                $('.file-cell').parent().hide();
                $('.type-heading-cell').hide();
                $('.type-cell').parent().hide();
                $('.status-heading-cell').hide();
                $('.status-cell').parent().hide();
            } else {
                $('.file-heading-cell').show();
                $('.type-heading-cell').show();
                $('.status-heading-cell').show();
            }
        });
    })


    function setBreadcrumbs() {
        const $top = $(`<div><span class="name">{$smarty.const.TEXT_TOP}</span></div>`);
        const $categoryInput = $('#category');
        $('.breadcrumbs').html('').append($top)
        $top.on('click', function(){
            $categoryInput.val('');
            resetStatement()
        })
        if ($categoryInput.val()) {
            let categories = entryData.widgetGroupsCategories;
            $categoryInput.val().split('/').forEach(function (category) {
                let categoryName = category;
                if (categories && categories[category]) {
                    categoryName = categories[category].title;
                    categories = categories[category].children
                }
                const $category = $(`
                    <div>
                        <span class="name">${ categoryName }</span>
                    </div>`);
                $('.breadcrumbs').append($category);
                $category.on('click', function () {
                    const pos = $categoryInput.val().search(category);
                    if (pos != -1) {
                        $categoryInput.val($categoryInput.val().slice(0, pos + category.length));
                        resetStatement()
                    }
                })
            })
        }
    }

</script>