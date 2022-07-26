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

import draggablePopup from "src/draggablePopup";

export default {
    name: 'tool_image',
    async init ({$, NS}) {
        const svgEditor = this;
        const svgCanvas = svgEditor.canvas;

        return {
            name: window.tr.TEXT_GALLERY_IMAGE,
            svgicons: svgEditor.curConfig.extIconsPath + 'ext-image.xml',
            buttons: [{
                id: 'tool_image',
                icon: svgEditor.curConfig.extIconsPath + 'helloworld.png',
                type: 'mode',
                title: window.tr.TEXT_GALLERY_IMAGE,
                events: {
                    click () {
                        svgCanvas.setMode('select');
                        openPopup(svgEditor, NS);
                    }
                }
            }],
            mouseDown () {
            },
            mouseUp (opts) {
            }
        };
    }
};

function openPopup(svgEditor, NS){

    let content = {};
    content.NS = NS;
    content.svgEditor = svgEditor;
    content.main = $(`<div></div>`);
    content.heading = $(`<div>${window.tr.TEXT_GALLERY_IMAGE}</div>`);

    let btnCancel = $(`<span class="btn btn-cancel">${window.tr.IMAGE_CANCEL}</span>`);

    content.popup = draggablePopup(content.main, {
        heading: content.heading,
        buttons: [btnCancel],
        className: 'fullscreen-popup',
        top: 10,
        draggable: false
    });
    btnCancel.on('click', function(){
        content.popup.remove();
    });

    imageSource(content);
    search(content);
    gallery(content);
    uploadButton(content);
    imageSourceChange(content)
}


function imageSource(content) {
    content.imageSource = $(`
        <div class="form-source">
            <div class="label">Choose source</div>
            <div class="field">
                <select>
                    <option value="gallery">Image gallery</option>
                    <option value="product">Product images</option>
                    <option value="category">Category images</option>
                </select>
            </div>
        </div>
        `);
    content.heading.append(content.imageSource);

}
function imageSourceChange(content) {
    $('select', content.imageSource).on('change', function(){

        content.gallery.html('');
        $('input', content.search).off();
        content.dropzoneArea = {};

        switch ($(this).val()){
            case 'gallery': sourceGallery(content); break;
            case 'product': sourceProduct(content); break;
            case 'category': sourceCategory(content); break;
        }
    }).trigger('change')
}

function search(content){

    content.search = $(`
        <div class="form-search">
            <div class="label">Search</div>
            <div class="field"><input type="text"></div>
        </div>
        `);

    content.heading.append(content.search);
}

function gallery(content){

    content.gallery = $(`<div class="gallery"></div>`);

    content.gallery.on('click', '.item-general, .product-image', function(){
        setImage(content, $(this).data('src'));
        content.popup.remove();
    });

    content.gallery.on('click', '.product-item', function(){
        $.get('banner_manager/product-images', {
            id: $(this).data('id')
        }, function(response){
            content.gallery.html('');

            for (let i in response) {
                for (let j in response[i]['image']) {
                    content.gallery.append(productImageTemplate(response[i]['image'][j]))
                }
            }

        }, 'json');
    });

    content.main.append(content.gallery);
}

function productImageTemplate(data){
    return `
<div class="product-image item" data-src="${data.url}">
    <div class="image"><img src="${data.url}"></div>
    <div class="name">${data.type}<br>${data.x}x${data.y}</div>
</div>
    `
}

function itemTemplate(data){
    return `
<div class="product-image item" data-src="${data.src}">
    <div class="image"><img src="${data.src}"></div>
    <div class="name">${data.name}</div>
</div>
    `
}

function uploadButton(content){

    content.uploadButton = $(`<span class="btn">Upload image</span>`);

    content.heading.append(content.uploadButton);
}

function sourceGallery(content){
    let files = '';

    content.uploadButton.show();

    $.get('banner_manager/gallery', function(response){
        content.gallery.append(response);
    });

    content.dropzoneBtn = content.uploadButton.dropzone({
        url: 'banner_manager/upload',
        uploadMultiple: true,
        previewTemplate: '<span data-dz-name></span>',
        init: function() {
            this.on('successmultiple', dropzoneSuccess);
            this.on('queuecomplete', dropzoneComplete);
        }
    });

    content.dropzoneArea = content.popup.dropzone({
        url: 'banner_manager/upload',
        uploadMultiple: true,
        previewTemplate: '<span data-dz-name></span>',
        clickable: false,
        init: function() {
            this.on('successmultiple', dropzoneSuccess);
            this.on('queuecomplete', dropzoneComplete);
        }
    });

    let keyShow = 0;
    content.dropzoneArea.on('dragenter', function(){
        keyShow++;
        if (keyShow === 2) {
            content.popup.append('<div class="drop-area-over">Drop images here</div>');
        }
        keyHide = 0;
    });
    let keyHide = 0;
    content.dropzoneArea.on('dragleave', function(){
        keyHide++;
        if (keyHide === 2) {
            $('.drop-area-over', content.popup).remove()
        }
        keyShow = 0;
    });
    function dropzoneSuccess (e, data) {
        let response = JSON.parse(data);
        $('.drop-area .drop-area-over').remove();
        response.forEach(function(file){
            files += '<tr class="status-' + file.status + '"><td>' + file.file + '</td><td>' + file.text  + '</td></tr>'
        });
    }
    function dropzoneComplete () {

        alertMessage(`<div>
                <table class="table">
                    <thead><tr><th>File</th><th>Status</th></tr></thead>
                    ${files}
                </table>
                </div>`);
        files = '';
    }


    $('input', content.search).on('keyup', function(){
        let val = $(this).val();

        $('.name', content.gallery).each(function(){
            let imageName = $(this).text();
            if ( imageName.search(val) !== -1 ){
                let selectedName = imageName.replace(val, `<span class="keywords">${val}</span>`);
                $(this).html(selectedName);
                $(this).closest('.item').show()
            } else {
                $(this).closest('.item').hide();
                $(this).html(imageName);
            }
        });
        if (!val) {
            $('.item', content.gallery).show();
        }
    });

}

function sourceProduct(content){
    content.uploadButton.hide();

    $('input', content.search).on('keyup', function(){
        let val = $(this).val();

        $.get('index/search-suggest', {
            keywords: val,
            no_click: true,
            json: true
        }, function(data){
            content.gallery.html('');
            data.forEach(function(product){
                content.gallery.append(productTemplate(product))
            })
        }, 'json')
    });
}

function sourceCategory(content){
    content.uploadButton.hide();
}

function setImage (content, dataUrl) {
    let svgdoc = document.getElementById('svgcanvas').ownerDocument;
    let svgCanvas = content.svgEditor.canvas;
    let bg = svgCanvas.getElem('svgcontent');
    let innerlayer = $(bg).find('.layer');
    let elem = svgdoc.createElementNS(content.NS.SVG, 'image');
    svgCanvas.setHref(elem, dataUrl);
    $(innerlayer).append(elem);
    svgCanvas.selectOnly([elem],true);
    let svgWidth = svgCanvas.contentW;
    let svgHeight = svgCanvas.contentH;
    let svgRel = svgHeight/svgWidth;

    $(new Image()).on('load',function () {
        if (svgWidth > this.width*10 && svgHeight > this.height*10) {
            $(elem).attr({
                width: svgWidth/10,
                height: svgWidth/10*(this.height/this.width)
            });
        } else if(svgWidth > this.width && svgHeight > this.height ){
            $(elem).attr({
                width: this.width,
                height: this.height
            });
        }else if(svgRel < (this.height/this.width)){
            $(elem).attr({
                width: svgHeight/(this.height/this.width),
                height: svgHeight
            });
        }else if(svgRel > (this.height/this.width)){
            $(elem).attr({
                width: svgWidth,
                height: svgWidth*(this.height/this.width)
            });
        }

        svgCanvas.selectorManager.requestSelector(elem).resize();

    }).attr('src', dataUrl);
}

function productTemplate(data){
    let imgUrl;
    if (data.image) {
        imgUrl = document.location.origin + data.image
    } else {
        imgUrl = '../themes/basic/img/na.png'
    }

    let specialPrice = '';
    if (data.special_price) {
        specialPrice = `<div class="special-price">${data.special_price}</div>`;
    }

    data.value = data.value.replace('\\\'', '\'');

    return `
<div class="product-item box" data-id="${data.id}">
    <div class="image"><img src="${imgUrl}"></div>
    <div class="name">${data.value}</div>
    <div class="${data.special_price ? 'old-' : ''}price">${data.price}</div>
    ${specialPrice}
</div>
    `;
}