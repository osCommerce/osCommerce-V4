import draggablePopup from 'src/draggablePopup';

export default function exportBlock(){
    let id;
    let page = false;
    if ($(this).hasClass('root-export')) {
        id = $(this).closest('div[data-name]').attr('data-name');
        page = true;
    } else {
        id = $(this).closest('div[id]').attr('id');
    }
    const tr = window.entryData.tr;

    const $root = $($(this).parents('div[data-name]').get(-1));
    const pageName = $root.data('name');
    const pageType = $root.data('type');

    const $form = $(`
            <form>
                <div class="row align-items-center m-b-2 block-name">
                    <div class="col-5 align-right">
                        <label>${tr.TEXT_NAME_THIS_BLOCK}<span class="colon">:</span></label>
                    </div>
                    <div class="col-7">
                        <input name="block-name" type="text" class="form-control" autofocus/>
                    </div>
                </div>
                <div class="row align-items-center m-b-2 save-to-groups">
                    <div class="col-5 align-right">
                        <label>${tr.SAVE_TO_WIDGET_GROUPS}<span class="colon">:</span></label>
                    </div>
                    <div class="col-7">
                        <input name="save-to-groups" type="checkbox" class="form-control" checked/>
                    </div>
                </div>
                <div class="row align-items-center m-b-2 download">
                    <div class="col-5 align-right">
                        <label>${tr.DOWNLOAD_ON_MY_COMPUTER}<span class="colon">:</span></label>
                    </div>
                    <div class="col-7">
                        <input name="download" type="checkbox" class="form-control" checked/>
                    </div>
                </div>
                <div class="row align-items-center m-b-2 group-categories">
                    <div class="col-5 align-right">
                        <label>${tr.WIDGET_GROUP_CATEGORY}<span class="colon">:</span></label>
                    </div>
                    <div class="col-7">
                        ${groupCategories(pageName).html()}
                    </div>
                </div>
                <div class="row m-b-2">
                    <div class="col-5 align-right">
                        <label>${tr.TEXT_COMMENTS}<span class="colon">:</span></label>
                    </div>
                    <div class="col-7">
                        <textarea name="comment" class="form-control"></textarea>
                    </div>
                </div>
            </form>
        `);

    const $cancel = $(`<span class="btn">${tr.IMAGE_CANCEL}</span>`);
    const $export = $(`<span class="btn btn-primary">${tr.TEXT_EXPORT}</span>`);

    let popup = draggablePopup($form, {
        heading: tr.TEXT_NAME_THIS_BLOCK,
        name: 'export',
        resizable: {
            edges: {
                top: false,
                left: false,
                right: false,
                bottom: false,
            }
        },
        buttons: [$cancel, $export]
    });

    const $blockName = $('input[name="block-name"]', $form);
    const $saveToGroups = $('input[name="save-to-groups"]', $form);
    const $groupCategories = $('select[name="group-categories"]', $form);
    const $groupCategoriesBox = $('.group-categories', $form);
    const $download = $('input[name="download"]', $form);
    const $comment = $('textarea[name="comment"]', $form);

    $blockName.trigger('focus');

    $cancel.on('click', () => popup.trigger('close'));
    $form.on('submit', exportGroup);
    $export.on('click', exportGroup);

    function exportGroup(){
        popup.trigger('close');
        const data = {
            id,
            'block-name': $blockName.val(),
            'block-title': $('option:selected', $blockName).text(),
            'save-to-groups': +$saveToGroups.prop('checked'),
            'group-categories': $groupCategories.val(),
            'download': $download.prop('checked'),
            'comment': $comment.val(),
            'page_type': pageType,
        };

        const $homePage = $(`<iframe src="${$('#info-view').attr('src')}" width="1200" height="0" frameborder="no"></iframe>`);
        $('body').append($homePage);
        $homePage.on('load', function(){
            const $div = page ? $homePage.contents().find('div[data-name="'+id+'"]') : $homePage.contents().find('#' + id);
            html2canvas($div.get(0))
                .then(function(canvas) {
                    data.image = canvas.toDataURL('image/png');
                    exportBlock(data);
                }).catch(function(){
                    exportBlock(data);
                });

            function exportBlock(data) {
                $homePage.remove();

                data.theme_name = window.entryData.theme_name;
                $.post(window.entryData.mainUrl + '/design/export-block', data, function (response, status) {
                    if (status != 'success') {
                        alertMessage('Request error', 'alert-message');
                    }
                    if (response.error) {
                        alertMessage(response.error, 'alert-message');
                    }
                    if (response.text) {
                        let text = response.text;
                        if (response.extensionWidgets && response.extensionWidgets.length) {
                            text += `<div class="warning">${tr.BLOCK_CONTAINS_EXTENSION_WIDGETS}:</div>`;
                            response.extensionWidgets.forEach(function(widget){
                                text += `<div class="info">${widget.name}</div>`;
                            });
                        }
                        const $infoPopup = alertMessage(text, 'alert-message');
                        if (!response.extensionWidgets || !response.extensionWidgets.length) {
                            setTimeout(() => $infoPopup.remove(), 1000);
                        }

                        if ($download.prop('checked') && response.filename) {

                            const url = new URL(window.entryData.mainUrl + '/design/download-block');
                            url.searchParams.set('filename', response.filename);
                            if ($saveToGroups.prop('checked') == false) {
                                url.searchParams.set('delete', 1);
                            }

                            window.location = url.toString();
                        }
                    }
                }, 'json');
            }
        });

    }
}

function groupCategories(pageName) {
    const $html = $(`<div><select name="group-categories" class="form-control">
                            <option value=""></option>
                            <option value="header"${pageName == 'header' ? ' selected' : ''}>Header</option>
                            <option value="footer"${pageName == 'footer' ? ' selected' : ''}>Footer</option>
                        </select></div>`);
    const $select = $('select', $html);

    for (let name in entryData.groups) {
        $select.append(`<optgroup label="${entryData.groups[name].title}" data-name="${name}"></optgroup>`);
    }

    for (let name in entryData.pages) {
        const $pageName = $(`optgroup[data-name="${entryData.pages[name].group}"]`, $select);
        $pageName.append(`<option value="${entryData.pages[name].page_name}"${pageName == entryData.pages[name].page_name ? ' selected' : ''}>${entryData.pages[name].title}</option>`);
    }

    return $html;
}