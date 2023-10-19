{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{use class="common\helpers\Output"}
<style>
    .order-edit-tree-box{ 
      {if $settings.extra_header}
      width:44%;
      {else}
      width:100%; 
      {/if}
    }
</style>
<link href="{$app->request->baseUrl}/plugins/fancytree/skin-bootstrap/ui.fancytree.min.css" rel="stylesheet"
      type="text/css"/>
<script type="text/javascript" src="{$app->request->baseUrl}/plugins/fancytree/jquery.fancytree-all.min.js"></script>

<div class="wb-or-prod product_adding edeit-order-add-products">
    <form name="cart_quantity" action="{\Yii::$app->urlManager->createUrl($queryParams)}" method="post" id="product-form">
        <input type="hidden" name="currentCart" value="{$currentCart}">
        {if $settings.extra_header}
        <div class="popup-heading">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</div>
        {/if}
        <div class="widget-content after bundl-box">
            {if !$searchsuggest}
                   <div class="attr-box order-edit-tree-box oreder-edit-box-1">
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
                {if $settings.popup_product}
                <div class="attr-box attr-box-3 oreder-edit-box-2">
                    <div class="product_holder">
                        
                    </div>
                </div>
                {/if}
            {else}
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
                      {if $settings.popup_product}
                        <div class="product_holder" style="display:none;">
                       {/if}
                    </div>
                    </td>
                  </tr>
                </table> 	
             </div>
            {/if}
        </div>		
        {tep_draw_hidden_field('action', 'add_product')}
        
    {if $settings.extra_header}
		<div class="noti-btn three-btn">
		  <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
          <div><input type="submit" class="btn btn-big btn-orange btn-confirm btn-save" style="display:none;" value="{$smarty.const.IMAGE_ADD}"></div>
          <div class="btn-center"><span class="btn btn-reset" style="display:none;">{$smarty.const.TEXT_RESET}</span></div>		  
		</div>
    {/if}
	</form>
</div>
<script>

var selected_product;
var selected_product_name;
var tree_data = '{json_encode($category_tree_array)}';
var tree;
var adata;
var loaded_products = [];
var _new = true;
var source = [];

function clearTree(){
    tree = $('#tree').fancytree('getRootNode');
    tree.removeChildren();
    tree.addChildren(source);
//    tree.clearFilter();
    return false;
}

(function($){

//    order.activate_plus_minus('.product_adding');
    
    $('form[name=cart_quantity]').submit(function(e){
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
    
    {if !$searchsuggest}
        $('#tree').fancytree({
            extensions: ["glyph", "filter"],
            checkbox:false,
            init: function(event, data){
                source = data.tree.rootNode.children;
            },
            lazyLoad: function(event, data){
              data.result = {
                url: $(this).attr('data-tree-server'),
                type: 'POST',
                data:{
                  'do':'missing_lazy',
                  'id':data.node.key,
                  'selected':data.node.selected?1:0,
                  //'selected_data': JSON.stringify(selected_data)
                },
                dataType: "json"
              };
            },            
            _postProcess: function(event, data) {            
                $('.fancytree-icon.icon-cubes').prev().hide();
            },
            focus: function(event, data){
                var node = data.node;
                if (!node.isFolder() && (!node.statusNodeType || node.statusNodeType !== 'nodata')){
                    selected_product = node.key;
                    showProduct(node)
                } else {
                    selected_product = false;
                    $('.fancytree-hover-container').remove();
                }
            },
            dblclick:function(event, data){
                var node = data.node;
                if (!node.isFolder()){
                    selected_product = node.key;
                    //$('.append-product').show();

                  $('.fancytree-hover-container').remove();
                  {if $settings.clickcallback}
                    {$settings.clickcallback}(node);
                  {else}
                    var $v = node.key.substr(1).split("_");
                    loadProduct($v[0]);
                  {/if}
                }
            },
            glyph: {
              map: {
                doc: "icon-cubes",//"fa fa-file-o",
                docOpen: "icon-cubes", //"fa fa-file-o",
                checkboxUnknown: "icon-check-empty", //"fa fa-square",
                dragHelper: "fa fa-arrow-right",
                dropMarker: "fa fa-long-arrow-right",
                error: "fa fa-warning",
                expanderClosed: "icon-plus-sign-alt",//"icon-expand", //"fa fa-caret-right",
                expanderLazy: "icon-plus-sign-alt", //"icon-expand-alt", //"fa fa-angle-right",
                expanderOpen: "icon-minus-sign-alt",//"fa fa-caret-down",
                folder: "icon-folder-close-alt",//"fa fa-folder-o",
                folderOpen: "icon-folder-open-alt",//"fa fa-folder-open-o",
                loading: "icon-spinner" //"fa fa-spinner fa-pulse"
              }
            },
            blurTree: function(){
                setTimeout(function(){
                    $('.fancytree-hover-container').remove();
                }, 300)
            }
          });          
    {/if}
    
        function showProduct(node) {

          {if !$settings.popup_product}
              return true;
          {/if}
            var data = node.data
            var position = $('.product_holder').offset();
            var left = position.left;
            var top = window.scrollY + position.top;// window.screen.height / 2;//position.top;

            $('.fancytree-hover-container').remove();
            $('body').append(`
<div class="fancytree-hover-container" style="left: ${ left}px; top: ${ top}px">
    <div class="close-container icon-remove"></div>
    <div class="name"><table><tr><td>${ data.name}</td></tr></table></div>
    ${ data.image ? `<div class="image">${ data.image}</div>` : ''}
    <div class="price">{$smarty.const.TABLE_HEADING_PRICE_EXCLUDING_TAX}: <span class="final_price">${ data.price_ex}</span></div>
    <div class="price-tax">{$smarty.const.TABLE_HEADING_PRICE_INCLUDING_TAX}: <span class="final_price_tax "></span></div>
    <div class="ed-or-pr-stock"><span>{$smarty.const.TEXT_STOCK_QTY} </span><span class="valid1">${ data.stock}</span><br><span class="valid"></span></div>
    <div class="btn btn-primary btn-add-product2">{$smarty.const.TEXT_CHOOSE}</div>

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

        }
    
        function loadProduct(id){
            var href = window.location.href;
            window.location.href = href + ((href.indexOf('?')===-1)?'?':'&')+'products_id='+id;
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
                'search':text,
                {foreach $settings as $k => $v}
                  'settings[{$k|escape:'javascript'}]': '{$v|escape:'javascript'}',
                {/foreach}
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
      delay: 300,
      appendTo: '.prods-wrap.auto-wrapp',
      select: function(event, ui) {
                //$("#search_text").val(ui.item.label);
                if (ui.item.id > 0){
                  $('.product_name').html(ui.item.label);
                  loadProduct(ui.item.id);
                }
              },
    }).focus(function () {
      $('#search_text').autocomplete("search");
    });
        
		
})(jQuery);
</script>
