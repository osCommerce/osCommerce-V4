import style from './style.scss';

$.fn.selectProducts =function(options) {
    const settings = jQuery.extend({
        selectTitle: '',
        selectedName: '',
        selectedProducts: [],
        selectedPrefix: '',
        selectedSortName: '',
        selectedBackLink: '',
        selectedBackLink_c: '',
    },options);

    this.each(function() {
        if ($(this).hasClass('js-app')) {
            return null;
        }
        $(this).addClass('js-app');

        const $block = blockTemplate();
        $(this).append($block);

        const arrayChangeHandler = {
            set: function(target, property, value) {
                target[property] = value;
                if (property === 'length') {
                    $block.trigger('selectedProducts');
                    $('.products-container-from .item', $block).trigger('selectedProducts');
                }
                return true;
            }
        };
        const selectedProducts = new Proxy(settings.selectedProducts, arrayChangeHandler );

        $block.on('selectedProducts', addedProducts).trigger('selectedProducts');

        const $productFolders = $('.product-folders', $block);
        const $breadcrumbs = $('.products-container .breadcrumbs', $block);
        const categoriesData = {};
        const breadcrumbs = [];
        const searchResults = [];
        let scroll = '';
        let batchBackLink = false;

        getProducts(0);
        setBreadcrumbs({ title: entryData.tr.TEXT_ROOT, key: 0 });

        $productFolders.on('item-click', function(e, data){
            getProducts(data.key, data);
            setBreadcrumbs(data);
        });

        $productFolders.on('item-back', function(e, data){
            getProducts(data.key, data);
            setBreadcrumbs(data);
            setTimeout(() => $productFolders.scrollTop(scroll), 0);
        });

        $productFolders.on('scroll', function () {
            const lastChild = breadcrumbs.at(-1);
            if (breadcrumbs.length == 1 || (typeof lastChild.key == 'string' && lastChild.key.at() == 'c')) {
                scroll = $productFolders.scrollTop();
            }
        });

        $('.products-container-from .search input', $block).on('keyup', searchDelay).on('keydown', stopSubmit);
        $('.products-container-to .search input', $block).on('keyup', addedProducts).on('keydown', e => stopSubmit);
        function stopSubmit(e) {
            if (['Escape', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        }

        $('.products-container-from, .popup-heading').on('click', () => $('#search_text').trigger('focus'));

        $('.btn-add-selected', $block).on('click', function () {
            $('.product-folders .item.selected.product:not(.hidden)', $block).trigger('addProduct');
        });

        function blockTemplate(){
            return $(`
                <div class="products-content bundl-box">
        
                    <div class="products-container">
                        <div class="products-container-from">
                            <div class="search">
                                <input type="text" name="search" value="" class="form-control" autocomplete="off" placeholder="${ entryData.tr.TEXT_TYPE_CHOOSE_PRODUCT}">
                                <span class="btn btn-primary btn-add-selected" style="display: none">${entryData.tr.ADD_SELECTED_PRODUCTS}</span>
                            </div>
                            <div class="breadcrumbs"></div>
                            <div class="product-folders"></div>
                        </div>
                        <div class="products-container-to">
                            <div class="search">
                                <div class="title">${ entryData.tr.FIELDSET_ASSIGNED_PRODUCTS }</div>
                                <input type="text" name="search" value="" class="form-control" autocomplete="off" placeholder="${ entryData.tr.SEARCH_BY_ATTR}">
                            </div>
                            <div class="product-holder">
                                 ${ entryData.tr.TEXT_PRODUCT_NOT_SELECTED }
                            </div>
                        </div>
                    </div>
        
                </div>
            `);
        }


        function addedProducts() {
            if (selectedProducts.length) {
                const search = $('.products-container-to .search input', $block).val();
                const tooltipTitle = entryData.tr.BATCH_BACK_LINK_TOOLTIP_TITLE.replace('\%\s', (settings.selectTitle || 'cross product'));

                const $table = $(`
                    <table class="table assig-attr-sub-table">
                        <thead>
                        <tr role="row">
                            ${settings.selectedSortName ? '<th></th>' : ''}
                            <th>${entryData.tr.TEXT_IMG}</th>
                            <th>${entryData.tr.TEXT_LABEL_NAME}</th>
                            ${settings.selectedBackLink ? `
                                <th class="back-link">
                                    <span class="text-left" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="${tooltipTitle}">${entryData.tr.TEXT_BACKLINK}</span>
                                    <input type="checkbox"${batchBackLink ? ' checked' : ''} class="js-backlink-batch uniform">
                                </th>
                            ` : ''}
                            <th>${entryData.tr.TEXT_PRICE}</th>
                            <th><span class="remove-ast" title="Remove all"></span></th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                `);
                const $tbody = $('tbody', $table);
                const $batch = $('.uniform', $table).uniform();
                $tbody.html('');
                $('.remove-ast', $table).on('click', function(){
                    selectedProducts.splice(0, selectedProducts.length);
                });
                if ($('.back-link span', $table).length) {
                    new bootstrap.Tooltip($('.back-link span', $table)[0]);
                }

                selectedProducts.forEach(function(product, key){
                    let text = $(`<div>${product.name}</div>`).text();

                    if (search && text.toLowerCase().search(search.toLowerCase()) === -1) {
                        return null;
                    }
                    if (search) {
                        const re = new RegExp('(' + search + ')', 'i');
                        text = text.replace(re, `<span class="keywords">$1</span>`);
                    }
                    const $tr = $(`
                            <tr role="row" prefix="${settings.selectedPrefix}${product.id}"${product.status_class ? ` class="${product.status_class}"` : ''}>
                                ${settings.selectedSortName ? '<td class="sort-pointer"></td>' : ''}
                                <td class="img-ast img-ast-img">
                                    ${product.image || '<span class="product-ico"></span>'}
                                </td>
                                <td class="name-ast name-ast-xl">
                                    ${text}                                   
                                </td>
                                ${settings.selectedBackLink ? `
                                    <td class="back-link">
                                        <input type="checkbox" class="js-backlink uniform" name="${settings.selectedBackLink}[]" ${product.backlink || batchBackLink ? `checked` : ''} value="${product.id}">
                                        ${product.backlink ? `
                                            <input type="hidden" name="${settings.selectedBackLink_c}[]" value="${product.id}">
                                        ` : ''}
                                    </td>
                                ` : ''}
                                <td class="ast-price ast-price-xl">
                                    ${product.price}
                                    <input type="hidden" name="${settings.selectedName}[]" value="${product.id}">
                                </td>
                                <td>
                                    <span class="remove-ast"></span>
                                </td>
                            </tr>
                    `);

                    $('img', $tr).on('error', function(){
                        $('.img-ast', $item).html(`<span class="product-ico"></span>`);
                    });

                    $('.uniform', $tr).uniform();

                    $tbody.append($tr);

                    $('.remove-ast', $tr).on('click', function(){
                        selectedProducts.splice(key, 1);
                    });
                });

                if (settings.selectedBackLink) {
                    const $backLinkInputs = $('.js-backlink', $tbody);
                    $batch.on('change', function(){
                        if ($batch.prop('checked')) {
                            batchBackLink = true;
                            $backLinkInputs.prop('checked', true).uniform('refresh');
                        } else {
                            batchBackLink = false;
                            $backLinkInputs.prop('checked', false).uniform('refresh');
                        }
                    });
                    $backLinkInputs.on('change', function(){
                        if (!$(this).prop('checked')) {
                            batchBackLink = false;
                            $batch.prop('checked', false).uniform('refresh');
                        }
                        let allChecked = true;
                        $backLinkInputs.each(function(){
                            if (!$(this).prop('checked')) {
                                allChecked = false;
                            }
                        });
                        if (allChecked) {
                            batchBackLink = true;
                            $batch.prop('checked', true).uniform('refresh');
                        }
                    }).trigger('change');
                }

                $('.product-holder', $block).html($table);

                if (settings.selectedSortName) {
                    const $sortInput = $(`<input type="hidden" name="${settings.selectedSortName}">`);

                    $tbody.sortable({
                        handle: '.sort-pointer',
                        axis: 'y',
                        update: function( event, ui ) {
                            const data = $(this).sortable('serialize', { attribute: 'prefix' });
                            $sortInput.val(data);
                        }
                    });

                    const data = $tbody.sortable('serialize', { attribute: 'prefix' });
                    $sortInput.val(data);

                    $('.product-holder', $block).append($sortInput);
                }
            } else {
                $('.product-holder', $block).html(`<div class="product-not-selected">${ entryData.tr.TEXT_PRODUCT_NOT_SELECTED }</div>`);
            }
        }


        function moveSelectedItem(key) {
            const $selected = $('.selected', $productFolders);
            $selected.removeClass('selected');
            let $newSelected = $selected;
            let itemsInRow = Math.round($productFolders.width() / $selected.width());
            switch (key) {
                case 'ArrowUp':
                    for (let i = 0; i < itemsInRow; i++){
                        $newSelected = $newSelected.prev();
                        if (!$newSelected.length) {
                            $newSelected = $('.item:last', $productFolders);
                        }
                    }
                    break;
                case 'ArrowDown':
                    for (let i = 0; i < itemsInRow; i++){
                        $newSelected = $newSelected.next();
                        if (!$newSelected.length) {
                            $newSelected = $('.item:first', $productFolders);
                        }
                    }
                    break;
                case 'ArrowRight':
                    $newSelected = $selected.next();
                    if (!$newSelected.length) {
                        $newSelected = $('.item:first', $productFolders);
                    }
                    break;
                case 'ArrowLeft':
                    $newSelected = $selected.prev();
                    if (!$newSelected.length) {
                        $newSelected = $('.item:last', $productFolders);
                    }
                    break;
            }
            $newSelected.addClass('selected');

            if ($newSelected.position().top + $newSelected.height() > $productFolders.height()) {
                const scrollTop = $productFolders.scrollTop() + $newSelected.position().top + $newSelected.height() - $productFolders.height();
                $productFolders.animate({ scrollTop }, 300);
            }

            if ($newSelected.position().top < 0) {
                const scrollTop = $productFolders.scrollTop() + $newSelected.position().top;
                $productFolders.animate({ scrollTop }, 300);
            }
        }

        function enterItem(key) {

            if (key == 'Enter') {
                $('.selected .image, .product-info .btn-add-product2', $productFolders).trigger('click');

            } else if (key == 'Escape') {
                const $breadcrumbsItems = $(' > div', $breadcrumbs);
                const $breadcrumbsItem = $breadcrumbsItems.eq($breadcrumbsItems.length - 2);
                if ($breadcrumbsItem.length) {
                    $breadcrumbsItem.trigger('click');
                }
            }
        }

        let searchDelayKeys = '';
        let searchDelaySet = true;
        function searchDelay(e) {
            const input = this;
            searchDelayKeys = e;
            if (searchDelaySet) {
                searchDelaySet = false;
                setTimeout(function () {
                    searchDelaySet = true;
                    search.call(input, searchDelayKeys);
                }, 500)
            }
        }

        function search(e){
            e.stopPropagation();
            if (['ArrowUp', 'ArrowDown', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
                moveSelectedItem(e.key);
                return;
            }
            if (['Escape', 'Enter'].includes(e.key)) {
                enterItem(e.key);
                return;
            }

            const search = $(this).val();
            if (search.length > 2) {
                $.get('categories/seacrh-product', {
                    search,
                }, function(data){
                    $productFolders.html('');

                    searchResults.length = 0;
                    searchTree(data, [], search);

                    const template = breadcrumbs.slice(1).map(i => i.key);
                    searchResults.sort(function(a, b){
                        let aCount = 0;
                        let bCount = 0;
                        template.forEach(function(breadcrumbKey, index){
                            if (a.categories[index] && a.categories[index].key == breadcrumbKey) {
                                aCount++;
                            }
                            if (b.categories[index] && b.categories[index].key == breadcrumbKey) {
                                bCount++;
                            }
                        });
                        if (aCount == bCount) {
                            return Math.abs(template.length - aCount) - Math.abs(template.length - bCount);
                        } else {
                            return bCount - aCount;
                        }
                    });

                    searchResults.forEach(function(item){
                        $productFolders.append(searchCategoriesTemplate(item.categories));
                        item.products.forEach(item => $productFolders.append(itemTemplate(item, search)));
                        //$('.item:first', $productFolders).addClass('selected');
                    });

                    selectElements();

                },'json');
            } else if (!search) {
                getProducts(0);
                setBreadcrumbs({ title: entryData.tr.TEXT_ROOT, key: 0 });
            }
        }

        function searchTree(data, categories, search){
            const products = [];
            data.forEach(function (item) {
                if (item.children) {
                    searchTree(item.children, [...categories, item], search);
                }
                if (item.key.charAt() == 'p') {
                    products.push(item);
                }
            });

            if (products.length) {
                searchResults.push({
                    categories,
                    products
                });
                //$productFolders.append(searchCategoriesTemplate(categories))
                //products.forEach(item => $productFolders.append(itemTemplate(item, search)));
            }
        }

        function searchCategoriesTemplate(categories) {
            return `
                <div class="categories-tree">
                    ${ categories.reduce((str, item) => str += `<div>${ item.title }</div>`, '') }
                </div>`;
        }

        function setBreadcrumbs(item) {
            const current = breadcrumbs.findIndex(i => i.key == item.key);

            if (current == -1) {
                breadcrumbs.push(item);
            } else {
                breadcrumbs.splice(current+1, 100);
            }

            $breadcrumbs.html('');
            breadcrumbs.forEach(function(item){
                const title = $(`<div>${ item.title}</div>`).text();
                const $breadcrumbsItem = $(`<div>${ title}</div>`);
                $breadcrumbs.append($breadcrumbsItem);
                $breadcrumbsItem.on('click', () => $productFolders.trigger('item-click', item));
            });
        }

        function getProducts(id, data) {
            if (typeof id == 'string' && id.slice(0,1) == 'p') {
                $productFolders.html('').append(productTemplate(data));
                $.post('categories/seacrh-product', {
                    'products_id':data.products_id,
                    'action': 'load_product'
                }, function (response) {
                    $productFolders.html('').append(productTemplate(data, response));
                    selectElements();
                }, 'json');
                return;
            }

            if (categoriesData[id]) {
                $productFolders.html('');
                categoriesData[id].forEach(item => $productFolders.append(itemTemplate(item)));
                //$('.item:first', $productFolders).addClass('selected');
            }

            $.post('categories/load-tree', {
                'do':'missing_lazy',
                id,
                'selected':0,
            }, function (response) {
                $productFolders.html('');
                response.forEach(item => $productFolders.append(itemTemplate(item)));
                //$('.item:first', $productFolders).addClass('selected');
                categoriesData[id] = response;

                selectElements();
            }, 'json');
        }

        function productTemplate(localData, data){
            const title = $(`<div>${ localData.title}</div>`).text();
            let image = '';
            if (localData.image) {
                if (localData.image.slice(0, 4) == '<img') {
                    image = localData.image;
                } else {
                    image = `<img src="${ entryData.tr.DIR_WS_CATALOG_IMAGES }${ localData.image}" />`;
                }
            } else {
                image = `<img src="${ entryData.tr.DIR_WS_CATALOG_IMAGES }na.png" />`;
            }
            const $item = $(`
            <div class="product-content ${ localData.products_id ? 'product': 'catalog'}">
                <div class="back-bar">${ entryData.tr.IMAGE_BACK}</div>
                <div class="image">${ image}</div>
                <div class="product-info">
                    <div class="title" title="${ title}">
                        <div>${ localData.title}</div>
                    </div>

                    <div class="description">
                        <span class="">${ localData.description }</span>
                    </div>
                    <div class="model">
                        <span>${entryData.tr.TEXT_MODEL}:</span>
                        <span class="value">${ localData.products_model }</span>
                    </div>

                    <div class="price">
                        <span>${ entryData.tr.TABLE_HEADING_PRICE_EXCLUDING_TAX }:</span>
                        <span class="value">${ localData.price_ex }</span>
                    </div>
                    
                    <div class="stock">
                        <span>${ entryData.tr.TEXT_STOCK_QTY}: </span>
                        <span class="value">${ localData.stock }</span>
                    </div>
                    <div class="btn btn-primary btn-add-product2">${ entryData.tr.TEXT_ADD }</div>

                </div>
            </div>
        `);

            $('.btn-add-product2', $item).on('click', () => addProduct(localData));

            $('img', $item).on('error', function(){
                $('.image', $item).html(`<span class="product-ico"></span>`);
            });

            $('.back-bar', $item).on('click', function(){
                $productFolders.trigger('item-back', breadcrumbs.at(-2));
            });
            return $item;
        }

        function itemTemplate(data, search){
            const title = $(`<div>${ data.title}</div>`).text();
            let image = '';
            if (data.image) {
                if (data.image.slice(0, 4) == '<img') {
                    image = data.image;
                } else {
                    image = `<img src="${ entryData.tr.DIR_WS_CATALOG_IMAGES }${ data.image}" />`;
                }
            } else {
                if (data.products_id) {
                    image = '<span class="product-ico"></span>';
                } else {
                    image = '<span class="catalog-ico"></span>';
                }
            }

            let titleHtm = title;
            if (search) {
                titleHtm = title.replace(new RegExp('(' + search + ')','i'), '<span class="search-key">$1</span>');
                if (data.model && data.model.search(new RegExp(search,'i')) !== -1) {
                    titleHtm = '<div class="model">${ entryData.tr.TEXT_MODEL }: ' + data.model.replace(new RegExp('(' + search + ')','i'), '<span class="search-key">$1</span>') + '</div>' + titleHtm;
                }
            }

            const $item = $(`
            <div class="item ${ data.products_id ? 'product': 'catalog' } ${ data.stock < 1 ? 'stock-empty': '' }">
                <div class="image">${ image }</div>
                <div class="title" title="${ title}"><div>${ titleHtm }</div></div>
                ${ data.products_id ? `
                    <div class="row-prod">
                        <div class="price">${ data.price_ex }</div>
                        <div class="button-add">
                            <span class="btn btn-primary btn btn-add-prod">${ entryData.tr.TEXT_ADD }</span>
                            <span class="btn btn-added">${ entryData.tr.TEXT_ADDED }</span>
                        </div>
                    </div>
                ` : '' }
            </div>
        `);

            $item.on('selectedProducts', function(){
                if (selectedProducts.find(i => i.id == data.products_id)) {
                    $item.addClass('hidden');
                } else {
                    $item.removeClass('hidden');
                }
            }).trigger('selectedProducts');

            $('img', $item).on('error', function(){
                let image = '';
                if (data.products_id) {
                    image = '<span class="product-ico"></span>';
                } else {
                    image = '<span class="catalog-ico"></span>';
                }
                $('.image', $item).html(image);
            });
            $('.image, .title', $item).on('dblclick', () => $productFolders.trigger('item-click', data));
            $('.button-add .btn-add-prod', $item).on('click', () => addProduct(data));
            $item.on('addProduct', () => addProduct(data));

            /*$item.on('mouseenter', function () {
                $('.selected', $productFolders).removeClass('selected');
                $(this).addClass('selected');
            });*/

            return $item;
        }

        function addProduct(productData) {
            if (selectedProducts.find(i => i.id == productData.products_id)) {
                const message = alertMessage('Already added', 'alert-message');
                setTimeout(() => message.fadeOut(200), 300);
                setTimeout(() => message.remove(), 500);
                return null;
            }
            selectedProducts.push({
                image: productData.image,
                name: productData.title,
                price: productData.price_ex,
                id: productData.products_id,
            });
        }

        function selectElements() {

            const dragSelect = new DragSelect({
                selectables: document.querySelectorAll('.product-folders .item'),
                area: $('.product-folders')[0],
                selectedClass: 'selected',
                draggability: false
            });

            dragSelect.subscribe('elementselect', select);
            dragSelect.subscribe('elementunselect', select);

            function select(s) {
                if ($('.product-folders .item.selected.product:not(.hidden)', $block).length) {
                    $('.btn-add-selected', $block).show();
                } else {
                    $('.btn-add-selected', $block).hide();
                }
            }
        }
    });
};
