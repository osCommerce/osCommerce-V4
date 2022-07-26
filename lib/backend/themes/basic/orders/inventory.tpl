<div id="product-inventory" class="inventory">
  {foreach $inventory as $item}
         <div>
                <input onchange="update_attributes(this.form);" type="radio" name="inv_uprid" value="{$item.products_id}" {if $item.selected}checked{/if}>{$item.attributes_names_short} 
                {*<span class="inventory-price">{$item.actual_price}</span>*}
        </div>
   {/foreach}
</div>

