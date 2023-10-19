{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{use class="common\helpers\Output"}
<script src="{$app->request->baseUrl}/plugins/dragselect.js" type="text/javascript"></script>
<link href="{$app->view->theme->baseUrl}/css/select-products.css" rel="stylesheet" type="text/css" />
<link href="{$app->view->theme->baseUrl}/css/editor/product-box.css" rel="stylesheet" type="text/css" />

<div class="wb-or-prod product_adding edeit-order-add-products">
    <form name="cart_quantity" action="{\Yii::$app->urlManager->createUrl($queryParams)}" method="post" id="product-form">
        <input type="hidden" name="currentCart" value="{$currentCart}">
        <div class="popup-heading add-products-heading">
            {$smarty.const.TEXT_ADD_A_NEW_PRODUCT}
            <span class="added-totals">
                <span>{*$totals['ot_total']['title']*}{$smarty.const.ALREADY_ADDED}: </span>
                <span>{$totals['ot_subtotal']['text_inc_tax']}</span>
            </span>
        </div>
        <div class="products-content bundl-box">

            <div class="products-container view-{str_replace(' ', '-', strtolower($product_display_format))}">
                <div class="products-container-from">
                    <div class="search">
                        <input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}" tabindex="0">
                        <span tabindex="2" class="btn btn-primary btn-add-selected" style="display: none">{$smarty.const.ADD_SELECTED_PRODUCTS}</span>
                    </div>
                    <div class="breadcrumbs"></div>
                    <div class="back-bar">{$smarty.const.IMAGE_BACK}</div>
                    <div class="product-folders" tabindex="1"></div>
                </div>
                <div class="products-container-to">
                    <div class="product_holder">
                        <div class="widget box box-no-shadow">
                            <div class="widget-content after">
                                {$smarty.const.TEXT_PRODUCT_NOT_SELECTED}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        {tep_draw_hidden_field('action', 'add_product')}
		<div class="noti-btn three-btn">
		  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
          <div><input type="submit" class="btn btn-confirm btn-save" style="display:none;" value="{$smarty.const.IMAGE_ADD}"></div>
         {* <div class="btn-center"><span class="btn btn-reset" style="display:none;">{$smarty.const.TEXT_RESET}</span></div>*}
		</div>
	</form>
</div>
<script>

    entryData.tr.TEXT_EXC_VAT = '{$smarty.const.TEXT_EXC_VAT}';
    entryData.tr.TEXT_INC_VAT = '{$smarty.const.TEXT_INC_VAT}';
    entryData.tr.QUANTITY_DISCOUNT_DIFFERENT = '{$smarty.const.QUANTITY_DISCOUNT_DIFFERENT}';
    entryData.tr.ATTRIBUTE_PRICE_DIFFERENT = '{$smarty.const.ATTRIBUTE_PRICE_DIFFERENT}';
    entryData.tr.TEXT_CHANGE_TO = '{$smarty.const.TEXT_CHANGE_TO}';
    entryData.tr.TEXT_LEAVE = '{$smarty.const.TEXT_LEAVE}';
var selected_product;
var selected_product_name;
var tree_data = {json_encode($category_tree_array)};
var tree;
var adata;
var loaded_products = [];
var _new = true;
var source = [];

$(function () {

    const $productFolders = $('.product-folders');
    const $breadcrumbs = $('.products-container .breadcrumbs');
    const categoriesData = { };
    const breadcrumbs = [];
    const searchResults = [];
    let scroll = '';
    let lastCalledIndex='';
    const productDisplayEntities =JSON.parse('{$product_display_entities}');
    const LoadedPaths = { };
    const $backBar = $('.back-bar');
    const $backButton = $('<span class="btn btn-back" style="display: none">{$smarty.const.IMAGE_BACK}</span>');

    $('.popup-heading').prepend($backButton);

    getProducts(0);
    setBreadcrumbs({ title: '{$smarty.const.TEXT_ROOT}', key: 0 });

    $productFolders.on('item-click', function(e, data){
        getProducts(data.key, data);
        setBreadcrumbs(data)
    });

    $productFolders.on('item-back', function(e, data){
        getProducts(data.key, data);
        setBreadcrumbs(data);
        setTimeout(() => $productFolders.scrollTop(scroll), 0)
    });

    $backBar.on('click', function(){
        $productFolders.trigger('item-back', breadcrumbs.at(-2));
        $('#search_text').val('');
    });
    $backButton.on('click', function(){
        if ($('#search_text').val()) {
            search_prev_val = '';
            search.call($('#search_text')[0]);
            setBreadcrumbs(breadcrumbs.at(-2));
        } else {
            $productFolders.trigger('item-back', breadcrumbs.at(-2))
        }
    });

    $productFolders.on('scroll', function () {
        const lastChild = breadcrumbs.at(-1);
        if (breadcrumbs.length == 1 || (typeof lastChild.key == 'string' && lastChild.key.at() == 'c')) {
            scroll = $productFolders.scrollTop()
        }
    });

    $('#search_text').on('keyup', searchDelay);
    $('#search_text').on('keydown', searchDelay);
    $('#search_text').on('input', searchDelay);
    $('.product-folders').on('keyup', listClick);

    $('.products-container-from, .popup-heading').on('click', () => $('#search_text').trigger('focus'));

    $('.btn-add-selected').on('click', function () {
        $('.product-folders .item.selected.product:not(.hidden)').trigger('addProduct');
    });

    const observer = new MutationObserver(() => {
        $('img').on('error', function () {
            $(this).replaceWith('<span class="product-ico"></span>')
        })
    });
    observer.observe($('.products-container-to')[0], { subtree: true, childList: true});

    function moveSelectedItem(key) {
        const $selected = $('.selected', $productFolders)
        $selected.removeClass('selected');
        let $newSelected = $selected;
        let itemsInRow = Math.round($productFolders.width() / $selected.width())
        switch (key) {
            case 'ArrowUp':
                for (let i = 0; i < itemsInRow; i++){
                    $newSelected = $newSelected.prev();
                    if (!$newSelected.length) {
                        $newSelected = $('.item:last', $productFolders)
                    }
                }
                break;
            case 'ArrowDown':
                for (let i = 0; i < itemsInRow; i++){
                    $newSelected = $newSelected.next();
                    if (!$newSelected.length) {
                        $newSelected = $('.item:first', $productFolders)
                    }
                }
                break;
            case 'ArrowRight':
                $newSelected = $selected.next();
                if (!$newSelected.length) {
                    $newSelected = $('.item:first', $productFolders)
                }
                break;
            case 'ArrowLeft':
                $newSelected = $selected.prev();
                if (!$newSelected.length) {
                    $newSelected = $('.item:last', $productFolders)
                }
                break;
        }
        $newSelected.addClass('selected');

        if ($newSelected.position().top + $newSelected.height() > $productFolders.height()) {
            const scrollTop = $productFolders.scrollTop() + $newSelected.position().top + $newSelected.height() - $productFolders.height();
            $productFolders.animate({ scrollTop }, 300)
        }

        if ($newSelected.position().top < 0) {
            const scrollTop = $productFolders.scrollTop() + $newSelected.position().top;
            $productFolders.animate({ scrollTop }, 300)
        }
    }
    
    function enterItem(key) {

        if (key == 'Enter') {
            $('.selected .image, .product-info .btn-add-product2', $productFolders).trigger('click')

        } else if (key == 'Escape') {
            const $breadcrumbsItems = $(' > div', $breadcrumbs);
            const $breadcrumbsItem = $breadcrumbsItems.eq($breadcrumbsItems.length - 2);
            if ($breadcrumbsItem.length) {
                $breadcrumbsItem.trigger('click')
            }
        }
    }

    let searchDelayKeys = '';
    let searchTypingTimer;

    function searchDelay(e) {
        //if (['ArrowUp', 'ArrowDown', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
        if (['ArrowDown'].includes(e.key)) {
            if (e.type == 'keyup') {
                $('.product-folders .item:first').addClass('selected');
                moveSelectedItem(e.key);
                $('.product-folders').focus();
            }
            return;
        }
        /*if (['Escape', 'Enter'].includes(e.key)) {
            if (e.type == 'keyup') {
                enterItem(e.key);
            }
            return;
        }*/
        /*if (['Shift', 'Control'].includes(e.key)) {
            return;
        }*/

        const input = this;
        clearTimeout(searchTypingTimer);
        searchDelayKeys = e;
        searchTypingTimer=setTimeout(function () {
            lastCalledIndex=(Math.random() + 1).toString(36).substring(7);
            input._internallastCalledIndex=lastCalledIndex;
            search.call(input, searchDelayKeys);
        }, 350);

    }
    function listClick(e) {
        if (['ArrowUp', 'ArrowDown', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
            if (e.type == 'keyup') {
                moveSelectedItem(e.key);
            }
            return;
        }
        if (['Enter'].includes(e.key)) {
            if (e.type == 'keyup') {
                enterItem(e.key);
            }
            return;
        }
        if (['Escape'].includes(e.key)) {
            if (e.type == 'keyup') {
                enterItem(e.key);
            }
            return;
        }
        if (['Shift', 'Control'].includes(e.key)) {
            return;
        }
    }

    var search_prev_val = '';
    function search(e){
        var force = false;
        if (e && ['Enter'].includes(e.key)) {
            force = true;
        }
        const _internallastCalledIndex=this._internallastCalledIndex;
        const search = $(this).val();
        if (search.length > {if isset($min_search_text_lenght)}{$min_search_text_lenght-1}{else}2{/if} && (search_prev_val != search.trim() || force) ) {
            search_prev_val = search.trim();
            $('.products-container-from .product-folders').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>');
            $.post("{\Yii::$app->urlManager->createUrl($tree_server_url)}", {
                search,
            }, function(data){
                $('.product-folders').removeClass('hided-box');
                $('.product-folders .hided-box-holder').remove();

                if (_internallastCalledIndex!=lastCalledIndex) return;

                $(window).off('changedProduct');
                $productFolders.html('');

                searchResults.length = 0;
                searchTree(data, [], search);

                const template = breadcrumbs.slice(1).map(i => i.key);
                searchResults.sort(function(a, b){
                    let aCount = 0;
                    let bCount = 0;
                    template.forEach(function(breadcrumbKey, index){
                        if (a.categories[index] && a.categories[index].key == breadcrumbKey) {
                            aCount++
                        }
                        if (b.categories[index] && b.categories[index].key == breadcrumbKey) {
                            bCount++
                        }
                    })
                    if (aCount == bCount) {
                        return Math.abs(template.length - aCount) - Math.abs(template.length - bCount)
                    } else {
                        return bCount - aCount;
                    }
                })

                searchResults.forEach(function(item){
                    $productFolders.append(searchCategoriesTemplate(item.categories))
                    item.products.forEach(item => $productFolders.append(itemTemplate(item, search)));
                    //$('.item:first', $productFolders).addClass('selected');
                })

                selectElements();

            },'json');
        } else if (!search) {
            search_prev_val = search.trim();
            getProducts(0);
            setBreadcrumbs({ title: '{$smarty.const.TEXT_ROOT}', key: 0 });
        }
    }

    function searchTree(data, categories, search){
        const products = []
        data.forEach(function (item) {
            if (item.children) {
                searchTree(item.children, [...categories, item], search)
            }
            if (item.key.charAt() == 'p') {
                products.push(item)
            }
        })

        if (products.length) {
            searchResults.push({
                categories,
                products
            })
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
        const current = breadcrumbs.findIndex(i => i.key == item.key)

        if (current == -1) {
            breadcrumbs.push(item);
        } else {
            breadcrumbs.splice(current+1, 100);
        }
        if (breadcrumbs.length > 1 && item.folder) {
            $backBar.show();
        } else {
            $backBar.hide();
        }
        if (breadcrumbs.length > 1) {
            $backButton.show();
        } else {
            $backButton.hide();
        }

        $breadcrumbs.html('');
        breadcrumbs.forEach(function(item){
            const title = $(`<div>${ item.title}</div>`).text();
            const $breadcrumbsItem = $(`<div>${ title}</div>`);
            $breadcrumbs.append($breadcrumbsItem);
            $breadcrumbsItem.on('click', () => $productFolders.trigger('item-click', item))
        })
    }

    function getProducts(id, data) {
        if (typeof id == 'string' && id.slice(0,1) == 'p') {
            $(window).off('changedProduct');
            $productFolders.html('').append(productTemplate(data));
            $.post('{\Yii::$app->urlManager->createUrl($queryParams)}', {
                'products_id':data.products_id,
                'action': 'load_product'
            }, function (response) {
                $(window).off('changedProduct');
                $productFolders.html('').append(productTemplate(data, response));
                selectElements();
            }, 'json')
            return
        }

        if (categoriesData[id]) {
            $(window).off('changedProduct');
            $productFolders.html('');
            categoriesData[id].forEach(item => $productFolders.append(itemTemplate(item)));
            //$('.item:first', $productFolders).addClass('selected');
        }

        $.post('{\Yii::$app->urlManager->createUrl($tree_server_url)}', {
            'do':'missing_lazy',
            id,
            'selected':0,
        }, function (response) {
            $(window).off('changedProduct');
            $productFolders.html('');
            response.forEach(item => $productFolders.append(itemTemplate(item)));
            //$('.item:first', $productFolders).addClass('selected');
            categoriesData[id] = response
            selectElements();
        }, 'json')
    }

    function productTemplate(localData, data){
        const title = $(`<div>${ localData.title}</div>`).text();
        let image = '';
        if (localData.image) {
            if (localData.image.slice(0, 4) == '<img') {
                image = localData.image;
            } else {
                image = `<img src="../{$smarty.const.DIR_WS_IMAGES}${ localData.image}" />`;
            }
        } else {
            image = `<img src="../{$smarty.const.DIR_WS_IMAGES}na.png" />`;
        }
        let attributes = '';
        if (data && data.attributes_array) {
            attributes = '<div class="attributes">' + data.attributes_array.reduce(function(attr, current) {
                const options = current.options.reduce(function(option, item) {
                    return option + `<div class="option-item">${ item.text}</div>`;
                }, '');

                return attr + `<div class="attr-item">
                    <strong>${ current.title}</strong>
                    <div class="options">${ options}</div>
                </div>`;
            }, '') + '</div>';
        }
        const $item = $(`
            <div class="product-content ${ localData.products_id ? 'product': 'catalog'}">
                <div class="back-bar">{$smarty.const.IMAGE_BACK}</div>
                <div class="image">${ image}</div>
                <div class="product-info">
                    <div class="title" title="${ title}">
                        <div>${ localData.title}</div>
                    </div>
                    ${ attributes}
                    <div class="price">
                        {$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}:
                        <span class="final_price">${ localData.price_ex }</span>
                    </div>

                    <div class="ed-or-pr-stock">
                        <span>{$smarty.const.TEXT_STOCK_QTY} </span>
                        <span class="valid1">${ localData.stock_virtual }</span>
                        <span class="valid"></span>
                    </div>
                    <div class="btn btn-primary btn-add-product2">{$smarty.const.TEXT_ADD}</div>

                    <div class="hidden-container" style="display: none"></div>
                    <input type="hidden" class="qty">

                </div>
            </div>
        `);

        /*if (data) {
            var _product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', data.products_id, (data.product.pack_unit > 0 || data.product.packaging > 0), false, $item);
            _product.getDetails();
        }*/

        $('.btn-add-product2', $item).on('click', function () {
            if (!$item.hasClass('disabled')) {
                loadProduct(data.products_id);
                $item.addClass('disabled')
            }
        });
        $(window).on('changedProduct', function (e, id) {
            if (data && data.products_id && id == data.products_id) {
                $item.removeClass('disabled')
            }
        })

        $('img', $item).on('error', function(){
            $('.image', $item).html(`<img src="../{$smarty.const.DIR_WS_IMAGES}na.png" />`)
        });

        $('.back-bar', $item).on('click', function(){
            if ($('#search_text').val()) {
                search_prev_val = '';
                search.call($('#search_text')[0]);
                setBreadcrumbs(breadcrumbs.at(-2));
            } else {
                $productFolders.trigger('item-back', breadcrumbs.at(-2))
            }
        })
        return $item
    }

    function itemTemplate(data, search){
        const title = $(`<div>${ data.title}</div>`).text();
        try {
            var sTitle = $(`<div>${ data.title}</div>`).html();
        } catch (e) { console.log(e);}
        if (typeof sTitle == 'undefined') {
            sTitle = title;
        }
        let image = '';
        if (data.image) {
            if (data.image.slice(0, 4) == '<img') {
                image = data.image;
            } else {
                image = `<img src="../{$smarty.const.DIR_WS_IMAGES}${ data.image}" />`;
            }
        } else if(data.hasOwnProperty('image')) { //image have to show in output
            if (data.products_id) {
                image = '<span class="product-ico"></span>';
            } else {
                image = '<span class="catalog-ico"></span>';
            }
        } else if(data.products_id) {  //without image - row style
            image=null;
        }

        let titleHtm = sTitle;
        if (false && search) { // move highlight to server side
            titleHtm = title.replace(new RegExp('(' + search + ')',"i"), '<span class="search-key">$1</span>');
            if (productDisplayEntities.SKU && data.model) {
                if (data.model.search(new RegExp(search,"i")) !== -1) {
                    titleHtm+=" <i>"+data.model.replace(new RegExp('(' + search + ')',"i"), '<span class="search-key">$1</span>') + '</i>'
                } else 
                    titleHtm+=" <i>"+data.model + '</i>';
            } else if (data.model.search(new RegExp(search,"i")) !== -1) {
                titleHtm = '<div class="model">{$smarty.const.TEXT_MODEL}: ' + data.model.replace(new RegExp('(' + search + ')',"i"), '<span class="search-key">$1</span>') + '</div>' + titleHtm;
            }

        } else
        if (productDisplayEntities.SKU && data.model) {
            titleHtm+=" <i>"+data.model + '</i>';
        }
        
        if (data.hasOwnProperty('categories') && data.categories != '') {
            titleHtm +=data.categories;
        }   

        const $item = $(`
            <div data-pr='${ data.products_id }' class="item ${ data.products_id ? 'product': 'catalog'} ${ data.stock && data.stock < 1 ? 'stock-empty': ''}">
                ${ image !== null ? `<div class="image">${ image }</div>`:''}
                <div class="type-icons">${ data.type ? data.type.map(type => `<div class="type-${ type }" title="${ type }"></div>`) : ''}</div>
                <div class="title" title="${ title}"><div>${ titleHtm }</div></div>
                ${ data.products_id ? `
                    ${ data.hasOwnProperty('stock')? `<div class="stock">{$smarty.const.TEXT_STOCK_QTY}: <b>${ data.stock }</b></div>` : '' }
                    <div class="row-prod">
                        ${ data.price_ex ? `<div class="price">${ data.price_ex }</div>` :``}
                    {if $product_display_format != 'Standard'}
                        <div class="in-categories"
                            data-bs-toggle="tooltip"
                            data-bs-html="true"
                            data-bs-title=""><span></span></div>
                    {/if}
                        <div class="button-add">
                            <span class="btn btn-primary btn btn-add-prod">{$smarty.const.TEXT_ADD }</span>
                        </div>
                    </div>
                ` : '' }
            </div>
        `);
        $('img', $item).on('error', function(){
            let image = '';
            if (data.products_id) {
                image = '<span class="product-ico"></span>';
            } else {
                image = '<span class="catalog-ico"></span>';
            }
            $('.image', $item).html(image)
        })
        $('.image, .title', $item).on('dblclick', () => $productFolders.trigger('item-click', data));
        $('.button-add .btn-add-prod', $item).on('click', function () {
            if (!$item.hasClass('disabled')) {
                loadProduct(data.products_id);
                $item.addClass('disabled');
            }
        });
        $('.in-categories', $item).on('mouseover', () => getPopupProductDir(data.products_id, $item));
        $item.on('addProduct', function () {
            if (!$item.hasClass('disabled')) {
                loadProduct(data.products_id);
                $item.addClass('disabled');
            }
        });
        $(window).on('changedProduct', function (e, id) {
            if (id == data.products_id) {
                $item.removeClass('disabled')
            }
        })

        /*$item.on('mouseenter', function () {
            $('.selected', $productFolders).removeClass('selected');
            $(this).addClass('selected')
        })*/

        return $item
    }

    function showPopupProductDir(data,$item) {
        const $categories = $('.in-categories', $item);
        $categories.attr('data-bs-title', data.categories);
        const tooltip = new bootstrap.Tooltip($categories[0]);
        tooltip.show();
        setTimeout(() => tooltip.hide(), 2000);
    }

    function getPopupProductDir(id,$item) {
        if (LoadedPaths.hasOwnProperty(id)) {
             if (LoadedPaths[id]) {
                  showPopupProductDir(LoadedPaths[id],$item);
                  return; 
                }
             else if (LoadedPaths[id] !== false) return;   
        }
        
        LoadedPaths[id]=null;//on reuest mode

        $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", {
            'products_id':id,
            'action': 'load_categories'
        }, function (data, status) {
            if (status == 'success' && data.hasOwnProperty('categories') && data.categories != '') {
                LoadedPaths[id]=data;//push data if success
                showPopupProductDir(LoadedPaths[id],$item);
            } else 
               LoadedPaths[id]=false;//request error
        }, 'json');
    }

    function loadProduct(id){
        $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", {
            'products_id':id,
            'action': 'load_product'
        }, function (data, status){
            if (status == 'success'){
                var add = true;
                if (data.hasOwnProperty('products_id')){
                    if(!loaded_products.length){
                        //loaded_products.push({ id:data.products_id, hasA:data.isComplex, product: new getProduct(false, false) });// 1 false - no pack, 2 - is modified
                    } else {
                        loaded_products.forEach(function(e){
                            if (e.id == data.products_id && !data.isComplex){
                                //add = false;
                            }
                        });
                    }
                }
                if (add){
                    if (_new) {
                        $('.product_holder').html('');
                        _new = false;
                    }
                    $('.product_holder .product-details .toolbar i.icon-angle-down').trigger('click');
                    $('.product_holder').prepend(data.content).show();
                    var _product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', data.products_id, (data.product.pack_unit>0||data.product.packaging>0), false, $('.product_holder .product-details:first'));
                    loaded_products.push({ id:data.products_id, hasA:data.isComplex, product: _product  });// 1 false - no pack, 2 - is modified
                    _product.initDetails(data.product);
                    _product.getDetails();
                    $('.add-product .btn-save').show();
                    $('.add-product .btn-reset').show();
                    order.collapse($('.product_holder .product-details:first'));

                }
            }
        }, 'json');
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
            if ($('.product-folders .item.selected.product:not(.hidden)').length) {
                $('.btn-add-selected').show();
            } else {
                $('.btn-add-selected').hide();
            }
        }
    }
})


/*function clearTree(){
    tree = $('#tree').fancytree('getRootNode');
    tree.removeChildren();
    tree.addChildren(source);
    tree.clearFilter();
    return false;
}*/



    order.activate_plus_minus('.product_adding');

    $('form[name=cart_quantity]').submit(function(e){
        if (document.activeElement == $('#search_text').get(0)) {
            return false
        }
        if (checkproducts(loaded_products)){
            var params = [];
            if (loaded_products.length>0){
                params.push({ 'name': 'action', 'value': 'add_products'});
                params.push({ 'name': 'replace_same_product', 'value': ''});
                loaded_products.forEach(function(e){
                    params = params.concat(e.product.getProducts());
                });

                $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", params, function(data){
                    if (data.status == 'ok'){
                        window.location.reload();
                    } else if (data.hasOwnProperty('message')) {
                        order.showMessage(data.message, true);
                    }
                }, 'json');
            }
        }
        return false;
    })

        function showProduct(node) {

            var data = node.data
            var position = $('.product_holder').offset();
            var left = position.left;
            var top = position.top;

            $('.fancytree-hover-container').remove();
            $('body').append(`
<div class="fancytree-hover-container" style="left: ${ left }px; top: ${ top }px">
    <div class="close-container icon-remove"></div>
    <div class="name"><table><tr><td>${ data.name }</td></tr></table></div>
    ${ data.image ? `<div class="image">${ data.image }</div>` : ''}
    <div class="price">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: <span class="final_price">${ data.price_ex }</span></div>

    <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY} </span><span class="valid1">${ data.stock_virtual }</span><br><span class="valid"></span></div>
    <div class="btn btn-primary btn-add-product2">{$smarty.const.TEXT_ADD}</div>

    <div class="hidden-container" style="display: none"></div>
    <input type="hidden" class="qty">
</div>
`);

            $('.fancytree-hover-container .close-container').on('click', function(){
                $('.fancytree-hover-container').remove();
            })
            $('.fancytree-hover-container .btn-add-product2').on('click', function(){
                $('.fancytree-hover-container').remove();
                selected_product = node.key;
                var $v = node.key.substr(1).split("_");
                loadProduct($v[0]);
            })

            var id = data.products_id;
            $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", {
                'products_id':id,
                'action': 'load_product'
            }, function (data, status){
                if (status == 'success'){
                    var $hiddenContainer = $('.fancytree-hover-container .hidden-container');
                    if ($hiddenContainer.length) {
                        $hiddenContainer.prepend(data.content);
                        var _product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', data.products_id, (data.product.pack_unit > 0 || data.product.packaging > 0), false, $('.fancytree-hover-container'));
                        _product.getDetails();
                    }
                }
            }, 'json');
        }

        treeProduct = function(){
            if (selected_product){
                var $v = selected_product.substr(1).split("_");
                loadProduct($v[0]);
            }
        }

        getIndex = function (currentIndex){
            return $('.product-details').length - 1 - currentIndex;
        }

        getDetails = function(obj){
            loaded_products[getIndex($(obj).closest('.product-details').index())].product.getDetails(obj);
        }

        changeTax = function(obj){
            loaded_products[getIndex($(obj).closest('.product-details').index())].product.changeTax();
        }

        getOrderRates = function(){
            var rates = [];
            {foreach $rates as $key => $rate}
                rates['{$key}'] = '{$rate}';
            {/foreach}
            return rates;
        }

        manualEdit = function(obj){
            loaded_products[getIndex($(obj).closest('.product-details').index())].product.manualEdit(obj);
        }

        $('body').on('change', '.new-product', function(e){
            loaded_products[getIndex($(e.target).closest('.product-details').index())].product.checkQuantity();
            loaded_products[getIndex($(e.target).closest('.product-details').index())].product.getDetails(e.target);
        })

        $('.btn-reset').click(function(){
            loaded_products.forEach(function(e){
                e.product.resetDetails();
            });
        })

        function seachText(text){

            $.post($('#tree').attr('data-tree-server'), {
                'search':text
            }, function(data){
                tree = $('#tree').fancytree('getTree');
                tree.getRootNode().removeChildren();
                tree.getRootNode().addChildren(data);
                tree.filterNodes(text);
                $('.fancytree-icon.icon-cubes').prev().hide();
            }, 'json');
        }

        $('.search_product').click(function(e){
            if ((e.target.offsetWidth - e.offsetX) < e.target.offsetHeight){
                $('#search_text', this).val('');
                $('#search_text', this).trigger('keyup');
            }
        })


        $('#search_text').focus();
		/*$('#search_text').autocomplete({
			create: function(){
				$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>"+(item.hasOwnProperty('image') && item.image.length>0?"<img src='" + item.image + "' align='left' width='25px' height='25px'>":'')+"<span>" + item.label + "</span>&nbsp;&nbsp;<span class='price'>"+item.price+"</span></a>")
						.appendTo( ul );
					};
			},
			source: function(request, response){
				if (request.term.length > 2){
                    {if $searchsuggest}
                        $.get("{\Yii::$app->urlManager->createUrl($queryParams)}", {
                            'search':request.term,
                            'suggest':1
                        }, function(data){
                            response($.map(data, function(item, i) {
                                return {
                                        values: item.text,
                                        label: item.text,
                                        id: parseInt(item.id),
                                        image:item.image,
                                        price:item.price,
                                    };
                                }));
                        },'json');
                    {else}
                        seachText(request.term);
                    {/if}
				} else {
                    {if !$searchsuggest}
                        tree = $('#tree').fancytree('getRootNode');
                        tree.removeChildren();
                        tree.addChildren(source);
                    {/if}
                }
			},
            minLength: 2,
            autoFocus: true,
            delay: 0,
            appendTo: '.prods-wrap.auto-wrapp',
            select: function(event, ui) {
				//$("#search_text").val(ui.item.label);
				if (ui.item.id > 0){
					$('.product_name').html(ui.item.label)
                    loadProduct(ui.item.id);
				}
			},
        }).focus(function () {
			$('#search_text').autocomplete("search");
        });*/


</script>