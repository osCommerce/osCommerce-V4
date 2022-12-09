{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{use class="common\helpers\Output"}
<link href="{$app->view->theme->baseUrl}/css/editor/product-box.css?4" rel="stylesheet" type="text/css" />

<div class="wb-or-prod product_adding edeit-order-add-products">
    <form name="cart_quantity" action="{\Yii::$app->urlManager->createUrl($queryParams)}" method="post" id="product-form">
        <input type="hidden" name="currentCart" value="{$currentCart}">
        <div class="popup-heading">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</div>
        <div class="products-content bundl-box">


            <div class="products-container">
                <div class="products-container-from">
                    <div class="search">
                        <input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}">
                    </div>
                    <div class="breadcrumbs"></div>
                    <div class="product-folders"></div>
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


            {if !$searchsuggest and false}
                   <div class="attr-box oreder-edit-tree-box oreder-edit-box-1">
                    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                        <div class="widget-header">
                            <h4>{$smarty.const.TEXT_PRODUCTS}</h4>
                            <div class="box-head-serch after search_product">
                                <input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}">
                                <button onclick="return clearTree()"></button>
                            </div>
                        </div>
                        <div class="widget-content">
                            <div id="tree" data-tree-server="{\Yii::$app->urlManager->createUrl($tree_server_url)}" data-data-save="{$tree_server_save_url}" class="oreder-edit-tree">
                                <ul>
                                  {foreach $category_tree_array as $tree_item }
                                      <li class="{if $tree_item.lazy}lazy {/if}{if $tree_item.folder}folder {/if}{if $tree_item.selected}selected {/if}" id="{$tree_item.key}" tooltip="123" data-products_id="{$tree_item.products_id}" data-name="{$tree_item.name}"><span>{$tree_item.title}</span></li>
                                  {/foreach}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="attr-box attr-box-2">
                    <span class="btn btn-primary" onclick="treeProduct()"></span>
                </div>
                <div class="attr-box attr-box-3 oreder-edit-box-2">
                    <div class="product_holder">
                        <div class="widget box box-no-shadow">
                            <div class="widget-content after">
                                {$smarty.const.TEXT_PRODUCT_NOT_SELECTED}
                            </div>
                        </div>
                    </div>
                </div>
            {*else*}
            <div class="attr-box attr-box-3">
                <table width="100%">
                  <tr>
                    <td>
                     <table border='0' cellpadding=2 cellspacing=0 width="100%">
                      <tr>
                        <td class="label_name">
                          {$smarty.const.HEADING_TITLE_SEARCH_PRODUCTS}
                        </td>
                        <td class="label_value" colspan=2>
                            <div class="f_td_group prods-wrap auto-wrapp"  style="width:100%;">
                                <div class="search_product"><input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}"></div>
                            </div>
                        </td>
                      </tr>
                     </table>
                    </td>
                  </tr>
                  <tr>
                    <td>
                        <div class="product_holder" style="display:none;">
                    </div>
                    </td>
                  </tr>
                </table>
             </div>
            {/if}
        </div>
        {tep_draw_hidden_field('action', 'add_product')}
		<div class="noti-btn three-btn">
		  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
          <div><input type="submit" class="btn btn-confirm btn-save" style="display:none;" value="{$smarty.const.IMAGE_ADD}"></div>
          <div class="btn-center"><span class="btn btn-reset" style="display:none;">{$smarty.const.TEXT_RESET}</span></div>
		</div>
	</form>
</div>
<script>

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

    $productFolders.on('scroll', function () {
        const lastChild = breadcrumbs.at(-1);
        if (breadcrumbs.length == 1 || (typeof lastChild.key == 'string' && lastChild.key.at() == 'c')) {
            scroll = $productFolders.scrollTop()
        }
    });

    $('#search_text').on('keyup', search);

    $('.products-container-from, .popup-heading').on('click', () => $('#search_text').trigger('focus'))

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

    function search(e){
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
            $.post("{\Yii::$app->urlManager->createUrl($tree_server_url)}", {
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
                    $('.item:first', $productFolders).addClass('selected');
                })

            },'json');
        } else if (!search) {
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
            if (item.key.at() == 'p') {
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
            $productFolders.html('').append(productTemplate(data));
            $.post('{\Yii::$app->urlManager->createUrl($queryParams)}', {
                'products_id':data.products_id,
                'action': 'load_product'
            }, function (response) {
                $productFolders.html('').append(productTemplate(data, response));
            }, 'json')
            return
        }

        if (categoriesData[id]) {
            $productFolders.html('');
            categoriesData[id].forEach(item => $productFolders.append(itemTemplate(item)));
            $('.item:first', $productFolders).addClass('selected');
        }

        $.post('{\Yii::$app->urlManager->createUrl($tree_server_url)}', {
            'do':'missing_lazy',
            id,
            'selected':0,
        }, function (response) {
            $productFolders.html('');
            response.forEach(item => $productFolders.append(itemTemplate(item)));
            $('.item:first', $productFolders).addClass('selected');
            categoriesData[id] = response
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
        const $item = $(`
            <div class="product-content ${ localData.products_id ? 'product': 'catalog'}">
                <div class="back-bar">{$smarty.const.IMAGE_BACK}</div>
                <div class="image">${ image}</div>
                <div class="product-info">
                    <div class="title" title="${ title}">
                        <div>${ localData.title}</div>
                    </div>

                    <div class="price">
                        {$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}:
                        <span class="final_price">${ localData.price_ex }</span>
                    </div>

                    <div class="price-tax">
                        {$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}:
                        <span class="final_price_tax "></span>
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

        if (data) {
            var _product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', data.products_id, (data.product.pack_unit > 0 || data.product.packaging > 0), false, $item);
            _product.getDetails();
        }

        $('.btn-add-product2', $item).on('click', () => loadProduct(data.products_id));

        $('img', $item).on('error', function(){
            $('.image', $item).html(`<img src="../{$smarty.const.DIR_WS_IMAGES}na.png" />`)
        });

        $('.back-bar', $item).on('click', function(){
            $productFolders.trigger('item-back', breadcrumbs.at(-2))
        })
        return $item
    }

    function itemTemplate(data, search){
        const title = $(`<div>${ data.title}</div>`).text();
        let image = '';
        if (data.image) {
            if (data.image.slice(0, 4) == '<img') {
                image = data.image;
            } else {
                image = `<img src="../{$smarty.const.DIR_WS_IMAGES}${ data.image}" />`;
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
            titleHtm = title.replace(new RegExp('(' + search + ')',"i"), '<span class="search-key">$1</span>');
            if (data.model.search(new RegExp(search,"i")) !== -1) {
                titleHtm = '<div class="model">{$smarty.const.TEXT_MODEL}: ' + data.model.replace(new RegExp('(' + search + ')',"i"), '<span class="search-key">$1</span>') + '</div>' + titleHtm;
            }
        }

        const $item = $(`
            <div class="item ${ data.products_id ? 'product': 'catalog'} ${ data.stock < 1 ? 'stock-empty': ''}">
                <div class="image">${ image }</div>
                <div class="title" title="${ title}"><div>${ titleHtm }</div></div>
                ${ data.products_id ? `
                    <div class="row-prod">
                        <div class="price">${ data.price_ex }</div>
                        <div class="button-add">
                            <span class="btn btn-primary">{$smarty.const.TEXT_ADD}</span>
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
        $('.image, .title', $item).on('click', () => $productFolders.trigger('item-click', data));
        $('.button-add .btn', $item).on('click', () => loadProduct(data.products_id));

        $item.on('mouseenter', function () {
            $('.selected', $productFolders).removeClass('selected');
            $(this).addClass('selected')
        })

        return $item
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
                                add = false;
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
    <div class="price-tax">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: <span class="final_price_tax "></span></div>
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
            return $('.product-details').size() - 1 - currentIndex;
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
		$('#search_text').autocomplete({
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
        });


</script>