<h2 class="bundle_title">{$smarty.const.TEXT_PRODUCTS_BUNDLE}</h2>
<div class="bundle-listing">
	<div class="bundle_row after">
    {foreach $products['bundle_products'] as $product name=bundles}
        {if $smarty.foreach.bundles.index % 2 == 0 && $smarty.foreach.bundles.index != 0}
        </div><div class="bundle_row after">
        {/if}
        <div class="bundle_item">
            <div class="bundle_image" style="min-height:40px;"><img src="{$product.image}" alt="{$product.products_name|escape:'html'}" title="{$product.products_name|escape:'html'}" width="50px" align="left">
                <div class="bundle_name" style="padding:5px 5px 5px 55px;">        
                  {$product.products_name}
                </div>
            </div>
            <div class="right-area-bundle">        
                <div class="bundle_attributes after">
                    {$manager->render('Attributes', ['attributes' => $product.attributes_array, 'settings' => ['onchange' => 'getDetails(this);' ]])}                  
                    
                    {*<div class="attributes-item tax">
                    $manager->render('Tax', ['manager' => $manager, 'product' => $product, 'tax_address' => $tax_address, 'tax_class_array' => $tax_class_array , 'is_multi' => true ])
                    </div>*}
                </div>
            </div>
        </div>
  {/foreach}
    </div>
</div>
