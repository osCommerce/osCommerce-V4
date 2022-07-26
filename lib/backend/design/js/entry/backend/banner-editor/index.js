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

import style from "./style.scss";
import draggablePopup from "src/draggablePopup";


export function init(incomingData){
    if (!window.bannerEditor) return false;

    let data = window.bannerEditor.data = incomingData;

    data.jObjects = {
        svgEditorIframe: $('.svg-editor-iframe'),
        saveButton: $('.btn-save-boxes'),
        backButton: $('.btm-back'),
    };

    iframeHeight();
    $(window).on('resize', iframeHeight);

    data.editorPromise = new Promise(function(resolve, reject){
        document.getElementById('svg_editor_frame').onload = function () {

            let frame;
            for (let i = 0; i < 10; i++) {
                if (window.frames[i].frameElement.id === 'svg_editor_frame') {
                    frame = window.frames[i];
                    break
                }
            }
            if (!frame) reject("SVG Editor hasn't loaded");

            data.frame = frame;
            data.svgEditor = frame.svgEditor.svgEditor;

            resolve(frame.svgEditor.svgEditor)
        }
    });

    uploadBanner();
    backButton();
    saveButton();
}

export function bannerEdit(incomingData){
    if (!window.bannerEditor) return false;

    let data = window.bannerEditor.data = incomingData;

    const tr = data.tr;

    let formChanged = false;

    const mainForm = $('#save_banner_form');
    mainForm.on('change', function(){
        formChanged = true;
    });

    $('body').on('click', '.btn-remove-svg', function(){
        $(this).closest('td').find('svg').remove();
        $(this).closest('td').find('.group-svg-remove').val(1);
    })

    $('body').on('click', '.btn-edit-svg', function(){

        const editorUrl = $(this).data('href');

        if (!formChanged) {
            window.location = editorUrl;
            return;
        }

        let btnSave = $(`<span class="btn btn-save">${tr.IMAGE_SAVE}</span>`);
        let btnNotSave = $(`<span class="btn btn-save">${tr.NOT_SAVE}</span>`);
        let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);
        let html = $(`<div>${tr.CHANGED_DATA_ON_PAGE}</div>`);

        let popup = draggablePopup(html, {
            heading: tr.GO_TO_BANNER_EDITOR,
            buttons: [btnSave, btnCancel, btnNotSave],
        });

        btnSave.on('click', function(){
            $.post(mainForm.attr('action'), mainForm.serialize(), function (data, status) {
                if (status === "success") {
                    window.location = editorUrl;
                } else {
                    alert("Request error.");
                }
            }, "html");
        });

        btnNotSave.on('click', function(){
            window.location = editorUrl;
        });

        btnCancel.on('click', function(){
            popup.remove()
        });

    });

    if (data.setLanguage) {
        $('.nav a[href="#tab_2"]').trigger('click');
        $(`.nav a[data-id="${data.setLanguage}"]`).trigger('click');
    }
}

function iframeHeight(){
    let data = window.bannerEditor.data;

    const obj =  data.jObjects;

    let height = $(window).height() - 200;

    obj.svgEditorIframe.css({
        height: height,
    })
}

function uploadBanner(){
    let data = window.bannerEditor.data;

    data.editorPromise.then(function(svgEditor){

        svgEditor.bannerUploaded = new Promise(function(resolve){
            $.get('banner_manager/get-svg', {
                banners_id: data.banners_id,
                language_id: data.language_id,
                banner_group: data.banner_group,
            }, function(d){
                svgEditor.loadFromString(d);
                resolve()
            });
        })

    }).catch(function (error) {
        console.error(new Error(error))
    })
}

function backButton(){
    let data = window.bannerEditor.data;
    const tr = data.tr;

    data.editorPromise.then(function(svgEditor){
        data.jObjects.backButton.on('click', function(){
            let bannerEditUrl = `banner_manager/banneredit?banners_id=${data.banners_id}&language_id=${data.language_id}`;

            if (!data.undoStackSize) {
                data.undoStackSize = 1
            }

            if (svgEditor.canvas.undoMgr.getUndoStackSize() === data.undoStackSize) {
                window.location = bannerEditUrl;
                return;
            }

            let btnSave = $(`<span class="btn btn-save">${tr.IMAGE_SAVE}</span>`);
            let btnNotSave = $(`<span class="btn btn-save">${tr.NOT_SAVE}</span>`);
            let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);
            let html = $(`<div>${tr.YOU_CHANGED_BANNER}</div>`);

            let popup = draggablePopup(html, {
                heading: tr.GO_TO_BANNER_PAGE,
                buttons: [btnSave, btnCancel, btnNotSave],
            });

            btnSave.on('click', function(){
                saveSvg();
                window.location = bannerEditUrl;
            });

            btnNotSave.on('click', function(){
                window.location = bannerEditUrl;
            });

            btnCancel.on('click', function(){
                popup.remove()
            });
        })

    }).catch(function (error) {
        console.error(new Error(error))
    })
}

function saveButton(){
    let data = window.bannerEditor.data;

    data.editorPromise.then(function(){

        data.jObjects.saveButton.on('click', saveSvg)

    }).catch(function (error) {
        console.error(new Error(error))
    })
}

function saveSvg(){
    let data = window.bannerEditor.data;

    if (!data.svgEditor) return false;

    let svgString = data.svgEditor.canvas.getSvgString();
    svgString = svgString.replace(/width="([.0-9]+)" height="([.0-9]+)"/, 'viewBox="0 0 $1 $2"');

    data.undoStackSize = data.svgEditor.canvas.undoMgr.getUndoStackSize();

    $.post('banner_manager/save-svg', {
        banners_id: data.banners_id,
        language_id: data.language_id,
        banner_group: data.banner_group,
        svg: svgString,
    }, function(response){

        let popup = draggablePopup(`<div class="alert-message">${response}</div>`);

        setTimeout(function(){
            popup.remove()
        }, 1000)

    });
}

