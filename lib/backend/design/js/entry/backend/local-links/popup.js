/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

import draggablePopup from 'src/draggablePopup';

export default function (options) {
    const data = {
        json:1,
        name: 'all',
        platform_id: options.platform_id,
        languages_id: options.languages_id
    };

    $.get('information_manager/page-links', data, function (response, status) {
        if (status != 'success' || !response.items) {
            alertMessage('error', 'alert-message');
        }

        let $content = $(`
                <div class="tabbable tabbable-custom">
                    <ul class="nav nav-tabs"></ul>
                    <div class="tab-content"></div>
                </div>`);

        for (let pageType in response.items) {
            $('.nav', $content).append(`
                    <li class="" data-bs-toggle="tab" data-bs-target="#${pageType}">
                        <a><span>${response.items[pageType].title}</span></a>
                    </li>`);
            const $tabContent = $(`<div class="tab-pane topTabPane tabbable-custom" id="${pageType}"></div>`);
            $('.tab-content', $content).append($tabContent);

            $tabContent.append(tabTemplate(response.items[pageType]));
        }

        $('.nav li:first', $content).addClass('active');
        $('.tab-pane:first', $content).addClass('active');

        const $btnSave = $(`<span class="btn btn-save btn-primary">${entryData.tr.IMAGE_APPLY}</span>`);
        const $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

        let $popup = draggablePopup($content, {
            name: 'page-links',
            className: 'page-links',
            heading: 'Choose link',
            buttons: [$btnSave, $btnCancel],
            resizable: false,
        });

        $btnCancel.on('click', () => $popup.trigger('close'));

        $btnSave.on('click', function(){

            const pageLink = $('.tab-pane.active input[type="hidden"], .tab-pane.active select', $popup);

            if (options.editor) {
                const oEditor = CKEDITOR.instances[options.editor];
                const getText = function(){
                    if (pageLink.prop('tagName').toLowerCase() === 'select'){
                        return $.trim($('option:selected', pageLink).text());
                    } else {
                        return $.trim($('.page-name').val());
                    }
                };
                const getLink = function(){
                    return '##URL##' + pageLink.val();
                };

                if(oEditor.mode === 'wysiwyg'){
                    $('.pageLinksButton span').click(function(){
                        if(pageLink.val()){
                            oEditor.focus();
                            if(!oEditor.getSelection().getRanges()[0].collapsed){
                                const fragment = oEditor.getSelection().getRanges()[0].extractContents();
                                const container = CKEDITOR.dom.element.createFromHtml(`<a href="${getLink()}" />`, oEditor.document);
                                fragment.appendTo(container);
                                oEditor.insertElement(container);
                            }else{
                                const html = '<a href="' + getLink() + '">' + getText() + '</a>';
                                const newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                                oEditor.insertElement( newElement );
                            }
                        }
                        $(this).parents('.popup-box-wrap').remove();
                    });
                }else{
                    alertMessage('TEXT_PLEASE_TURN', 'alert-message');
                    $popup.remove();
                }
            } else {
                if(pageLink.val()){
                    $(`input[name="${options.field}"]`).val(pageLink.val());
                }
                $popup.remove();
            }
        });


        $('.tab-pane input[name="keywords"]', $popup).each(function(){
            const $inputSearch = $(this);
            const $searchBox = $inputSearch.parent();
            const $suggest = $('<div class="suggest"></div>');
            const $inputHidden = $('.page-link', $searchBox);
            $searchBox.append($suggest);
            $suggest.hide();

            $inputSearch.on('keyup', function(e){
                if ($(this).val()) {
                    $.get('index/search-suggest', {
                        keywords: $(this).val()
                    }, function(data){
                        $inputHidden.val('');
                        $suggest.html('').append(data).show();
                        $('a.item', $suggest).on('click', function (e) {
                            e.preventDefault();
                            $('a.item.active', $suggest).removeClass('active');
                            $(this).addClass('active');
                            $inputHidden.val('catalog/product?products_id='+$(this).data('id'));
                        });
                    });
                } else {
                    $suggest.html('');
                    $inputHidden.val('');
                }
            });
            $inputSearch.on('blur', function(){
                setTimeout($suggest.hide, 200);
            });
            $inputSearch.on('focus', function(){
                $suggest.show();
            });

        });

    }, 'json');
}

function tabTemplate(data) {

    let $html = '';

    if (data.suggest) {
        $html = `
            <div class="search">
                <input type="text" value="" placeholder="Enter your keywords" name="keywords" autocomplete="off" class="form-control page-name" onpaste="return false">
                <input type="hidden" value="" class="page-link">
            </div>
        `;
    } else {
        $html = $(`
            <select class="page-link form-control" size="15">
                <option value="">${entryData.tr.OPTION_NONE}</option>
            </select>
        `);

        for (let link in data.links) {
            $html.append(`<option value="${link}">${data.links[link]}</option>`);
        }
    }


    return $html;
}