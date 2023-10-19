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
import treeJs from "src/tree";
import Sortable, { AutoScroll } from 'sortablejs';
import interact from 'interactjs';


export function init(){
    $('.create_item').popUp();

    const $container = $('#container > #content > .container');

    $('.btn-export').on('click', function(){
        const $preloader = $('<div class="hided-box-holder"><div class="preloader"></div></div>')
        $container.addClass('hided-box').append($preloader)
        $.get("design/export-popup", {
            theme_name: entryData.theme_name
        }, function(response){
            $container.removeClass('hided-box');
            $preloader.remove();
            if (response === 'no-additionals') {
                $.post(`design/export?theme_name=${entryData.theme_name}`, {}, function(result){
                    window.location = `design/export?theme_name=${entryData.theme_name}`;
                })
            } else {
                const $html = $(response)
                $html.css({
                    height: $(window).height() - 250,
                    overflow: 'auto'
                });

                let $btnSave = $(`<span class="btn btn-save">${entryData.tr.IMAGE_SAVE}</span>`);
                let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

                let popup = draggablePopup($html, {
                    heading: entryData.tr.TEXT_EXPORT,
                    top: 100,
                    position: 'fixed',
                    buttons: [$btnCancel, $btnSave],
                });

                let requestData = {};

                $('.tree').fancytree({
                    extensions: ["glyph"],
                    checkbox: true,
                    selectMode: 3,
                    select: function(event, data) {
                        if (data.node.data.name) {
                            if (!requestData[data.node.data.type]) requestData[data.node.data.type] = {};
                            requestData[data.node.data.type][data.node.data.name] = data.node.selected
                        } else {
                            data.node.children.forEach(function(item){
                                if (!requestData[item.data.type]) requestData[item.data.type] = {};
                                requestData[item.data.type][item.data.name] = item.selected
                            })
                        }
                    },
                    source: entryData.exportItems,
                });

                $btnSave.on('click', function(){
                    $html.html('<div class="preloader"></div>')
                    $.post(`design/export?theme_name=${entryData.theme_name}`, requestData, function(result){
                        window.location = `design/export?theme_name=${entryData.theme_name}`;
                        popup.remove();
                    })
                });

                $btnCancel.on('click', function(){
                    popup.remove()
                });

                interact(popup.get(0))
                    .resizable({
                        edges: {
                            right: true,
                            bottom: true,
                        },
                    })
                    .on('resizemove', event => {
                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                            height: `${event.rect.height}px`,
                        });
                        $html.css({
                            height: parseInt($html.css('height')) + event.deltaRect.height + 'px'
                        })
                    });
            }
        })
    });

    $('.btn-import').each(function(){
        $(this).dropzone({
            url: `design/import?theme_name=${entryData.theme_name}`,
            timeout: 300000,
            success: function(){
                $container.removeClass('hided-box');
                $('.hided-box-holder', $container).remove()
                //location.reload();
            },
            sending: function(){
                $container.addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>')
            },
            error: function(){
                $container.removeClass('hided-box');
                $('.hided-box-holder').remove();
                alertMessage('<div class="alert-message">Error</div>')
            },
            acceptedFiles: '.zip'
        })
    });
};
