{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{use class="yii\helpers\Html"}
{use class="common\helpers\Output"}
{*\backend\components\Currencies::widget(['currency' => $manager->get('currency')])*}
<div class="wb-or-prod product_adding">
{function driveBranch }    
    {foreach $tree_array as $key => $value}
        {if $value['folder']}  
            <ul id="{$value['key']}" value="{$value['key']}" class="category_item" disabled level="{$value['level']}">{$step}{$value['title']}
                {call driveBranch tree_array = $value['children'] step='&nbsp;&nbsp;&nbsp;'|cat:$step}
            </ul>            
        {else}            
            <li id="{$value['key']}" value="{$value['key']}" class="product_item">{$step}{$value['title']}</li>
        {/if}
    {/foreach}
{/function}
<form name="cart_quantity" action="{\Yii::$app->urlManager->createUrl($queryParams)}" method="post" id="product-form" onSubmit="return checkproducts();">
        <input type="hidden" name="currentCart" value="{$currentCart}">
        <div class="popup-heading">{$smarty.const.TEXT_ADD_A_NEW_PRODUCT}</div>
        <div class="widget-content after bundl-box">
            {if !$searchsuggest}
                {*
                <div class="attr-box attr-box-1 oreder-edit-box-1">
                    <div class="widget widget-attr-box box box-no-shadow" style="margin-bottom: 0;">
                        <div class="widget-header">
                            <h4>{$smarty.const.TEXT_PRODUCTS}</h4>
                            <div class="box-head-serch after search_product">
                                <input type="text" name="search" value="" id="search_text" class="form-control" autocomplete="off" placeholder="{$smarty.const.TEXT_TYPE_CHOOSE_PRODUCT}">
                                <button onclick="return false"></button>
                            </div>
                        </div>
                        <div class="widget-content">
                    <ul name="tree" size="20" style="width: 100%;overflow-y: scroll; height: 500px;list-style: none;">
                        {call driveBranch tree_array = $category_tree_array step='' }                
                    </ul>
                    </div>
                    </div>
                </div>
                <div class="attr-box attr-box-3 oreder-edit-box-2">
                    <div class="product_holder">
                        <div class="widget box box-no-shadow">
                            <div class="widget-content after">
                                {$smarty.const.TEXT_PRODUCT_NOT_SELECTED}
                            </div>
                        </div>
                    </div>
                </div>*}
                  <div id="tree" data-tree-server="{$tree_server_url}" data-data-save="{$tree_server_save_url}" style="height: 410px;overflow: auto;">
                    <ul>
                      {foreach $category_tree_array as $tree_item }
                      <li class="{if $tree_item.lazy}lazy {/if}{if $tree_item.folder}folder {/if}{if $tree_item.selected}selected {/if}" id="{$tree_item.key}">{$tree_item.title}</li>
                      {/foreach}
                    </ul>
                  </div>
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
                            <div class="f_td_group auto-wrapp"  style="width:100%;">
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
          <div class="btn-center"><span class="btn btn-default btn-reset" style="display:none;">{$smarty.const.TEXT_RESET}</span></div>		  
		</div>	
	</form>
</div>
<script>

var selected_product;
var selected_product_name;
var tree;
var loaded_products = [];
var _new = true;
(function($){
    
    order.activate_plus_minus('.product_adding');
    
    $('form[name=cart_quantity]').submit(function(e){
        if (checkproducts()){
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
        /*tree = document.querySelector('ul[name=tree]');
        tree.options = [];
        tree.copy = [];
        function getChildren(treeBranch, tree){            
            $.each(treeBranch.childNodes, function(i, e){                
                if (e.className == "category_item"){
                    if (e.hasChildNodes() ){
                        tree.options.push(e);
                        tree.copy.push(e.innerHTML);
                        getChildren(e, tree);
                    }
                } else if (e.className == "product_item"){
                    tree.options.push(e);
                    tree.copy.push(e.innerHTML);
                }
            }); 
        }
        
        getChildren(tree, tree);*/
    {/if}
    
        function loadProduct(id){
            $.post("{\Yii::$app->urlManager->createUrl($queryParams)}", {
                    'products_id':id,
                    'action': 'load_product'
                }, function (data, status){                
                    if (status == 'success'){
                        var add = true;
                        if (data.hasOwnProperty('products_id')){
                            if(!loaded_products.length){
                                //loaded_products.push({ id:data.products_id, hasA:data.hasAttributes, product: new getProduct(false, false) });// 1 false - no pack, 2 - is modified
                            } else {                                
                                loaded_products.forEach(function(e){
                                    if (e.id == data.products_id && !data.hasAttributes){
                                        add = false;
                                    }
                                });
                                if (add){
                                    //loaded_products.push({ id:data.products_id, hasA:data.hasAttributes, product: new getProduct(false, false) });
                                }                                
                            }
                        }
                        if (add){
                            if (_new) {
                                $('.product_holder').html('');
                                _new = false;
                            }
                            $('.product_holder').append(data.content).show();
                            var _product = new getProduct('{\Yii::$app->urlManager->createUrl($queryParams)}', data.products_id, false, false, $('.product_holder .product-details:last'));
                            loaded_products.push({ id:data.products_id, hasA:data.hasAttributes, product: _product  });// 1 false - no pack, 2 - is modified        
                            _product.getDetails();
                            $('.add-product .btn-save').show();
                            $('.add-product .btn-reset').show();
                        }
                    }
            }, 'json');
        }
        
        getDetails = function(obj){        
            loaded_products[$(obj).closest('.product-details').index()].product.getDetails();
        }
        
        changeTax = function(obj){
            loaded_products[$(obj).closest('.product-details').index()].product.getDetails();
        }
        
        getOrderRates = function(){
            var rates = [];
            {foreach $rates as $key => $rate}
                rates['{$key}'] = '{$rate}';
            {/foreach}
            return rates;
        }
        
        manualEdit = function(obj){
            loaded_products[$(obj).closest('.product-details').index()].product.manualEdit(obj);
        }
        
        $('body').on('change', '.new-product', function(e){
            loaded_products[$(e.target).closest('.product-details').index()].product.checkQuantity();
            loaded_products[$(e.target).closest('.product-details').index()].product.getDetails();
        })
        
        function seachText(text){
            $.each(tree.options, function(i, e){
                if (e.className == 'product_item'){
                    if (tree.copy[i].toLowerCase().indexOf(text.toLowerCase()) == -1){
                        if (selected_product == e.value) $('.append-product').hide();
                        tree.options[i].hidden = true;
                    } else {
                        tree.options[i].hidden = false;
                        var string = tree.copy[i];
                        var pos = string.search(new RegExp(text, "i"));
                        tree.options[i].innerHTML = string.substr(0, pos) + '<span style="background-color:#ebef16">' + string.substr(parseInt(pos),text.length) + '</span>' + string.substr(parseInt(pos)+parseInt(text.length));
                    }
                }
            });
            
            $('.category_item').not($('.category_item').has('.product_item:not(:hidden)')).hide();
        }        
        
        $('.product_item').click(function(){            
            $('.product_item.selected').removeClass('selected');
            $(this).addClass('selected');            
        }).dblclick(function(){
            selected_product = $(this).attr('value');
            $('.append-product').show();
            var $v = selected_product.substr(1).split("_");
            loadProduct($v[0]);
        });
        
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
                    $.each(tree.options, function(i, e){
                        if (e.className == 'product_item'){
                            tree.options[i].hidden = false;
                        }
                    });
                    $('.category_item').show();
                    {/if}
                }
			},
            minLength: 2,
            autoFocus: true,
            delay: 0,
            appendTo: '.auto-wrapp',
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
        
        {if !$searchsuggest}
        $('input[name=search]').keyup(function(){
            if (!$(this).val().length){
                $.each(tree.options, function(i, e){
                            if (e.className == 'product_item'){
                                tree.options[i].hidden = false;
                                if (tree.options[i].innerHTML != tree.copy[i])
                                    tree.options[i].innerHTML = tree.copy[i];
                            }
                    });   
            }
        })
        {/if}
		
})(jQuery);
</script>