
<div id="product-collection">
    <div class="heading-2">{$smarty.const.BUILD_YOUR_OWN_COMBO}</div>
    <div class="current-product">
      <input type="hidden" name="collections[]" value="{$product.products_id}">

      <div class="image">
        <img src="{$product.image}" alt="{str_replace('"', '″', $product.products_name)}" title="{str_replace('"', '″', $product.products_name)}">
      </div>

      <div class="stock">
        <span class="{$product.stock_indicator.text_stock_code}"><span class="{$product.stock_indicator.stock_code}-icon">&nbsp;</span>{$product.stock_indicator.stock_indicator_text}</span>
      </div>

      <div class="price">
        {if isset($product.price)}
          <span class="current">{$product.price}</span>
        {else}
          <span class="old">{$product.price_old}</span>
          <span class="specials">{$product.price_special}</span>
        {/if}
      </div>

      <div class="title">
        <a href="{$product.link}">{$product.products_name}</a>
      </div>

      <div class="qty">
        <input type="text" name="collections_qty[{$product.products_id}]" value="{$product.collections_qty}" class="qty-inp" data-max="{$product.stock_indicator.max_qty}" data-min="{$product.order_quantity_minimal}" data-step="{$product.order_quantity_step}" onchange="update_collection_attributes(this.form);">
      </div>

      <div class="attributes">
      {foreach $product.attributes_array as $item}
        <div class="select-box">
          <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$product.products_name|escape:'html'} - {$item.title}" onchange="update_collection_attributes(this.form);">
              <option value="0">{$smarty.const.SELECT} {$item.title}</option>
              {foreach $item.options as $option}
                <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
              {/foreach}
          </select>
        </div>
      {/foreach}
      </div>

    </div>

    <div class="chosen-products products-carousel">
      {foreach $chosenProducts as $prod}
        <div class="item" id="item-{$prod.products_id}" data-id="{$prod.products_id}">
          <input type="hidden" name="collections[]" value="{$prod.products_id}">

          <div class="image">
            <a href="{$prod.link}"><img src="{$prod.image}" alt="{str_replace('"', '″', $prod.products_name)}" title="{str_replace('"', '″', $prod.products_name)}"></a>
          </div>

          <div class="stock">
            <span class="{$prod.stock_indicator.text_stock_code}"><span class="{$prod.stock_indicator.stock_code}-icon">&nbsp;</span>{$prod.stock_indicator.stock_indicator_text}</span>
          </div>

          <div class="price">
            {if isset($prod.price)}
              <span class="current">{$prod.price}</span>
            {else}
              <span class="old">{$prod.price_old}</span>
              <span class="specials">{$prod.price_special}</span>
            {/if}
          </div>

          <div class="title">
            <a href="{$prod.link}">{$prod.products_name}</a>
          </div>

          <div class="qty">
            <input type="text" name="collections_qty[{$prod.products_id}]" value="{$prod.collections_qty}" class="qty-inp" data-max="{$prod.stock_indicator.max_qty}" data-min="{$prod.order_quantity_minimal}" data-step="{$prod.order_quantity_step}" onchange="update_collection_attributes(this.form);">
          </div>

          <div class="attributes">
          {foreach $prod.attributes_array as $item}
            <div class="select-box">
              <select class="select" name="{$item.name}" data-required="{$smarty.const.PLEASE_SELECT} {$product.products_name|escape:'html'} - {$item.title}" onchange="update_collection_attributes(this.form);">
                  <option value="0">{$smarty.const.SELECT} {$item.title}</option>
                  {foreach $item.options as $option}
                    <option value="{$option.id}"{if $option.id==$item.selected} selected{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>{$option.text}</option>
                  {/foreach}
              </select>
            </div>
          {/foreach}
          </div>

          <div class="remove-button">
              <span class="btn btn-remove" data-id="{$prod.products_id}" onclick="remove_from_collection('{$prod.products_id}');">{$smarty.const.TEXT_REMOVE_CART}</span>
          </div>
        </div>
      {/foreach}
    </div>

    <div class="right-area">
      <div class="price">
        <span class="separate-text">{$smarty.const.SEPARATE_SELLING_PRICE}:</span>
        <span class="separate">{$old}</span>
        <span class="together">{$smarty.const.TEXT_TOGETHER}:</span>
        <span class="special">{$special}</span>
        {if $save > 0}
        <div class="save">
          <span class="save-text">{$smarty.const.TEXT_SAVE}</span>
          <span class="save-percents">{$save}%</span>
          <span class="save-price">({$savePrice})</span>
        </div>
        {/if}
      </div>
      <div class="main-button">
        <button name="collection_submit" type="submit" class="btn-2 add-collection" style="display:none">
          {$smarty.const.ADD_COMBO_TO_CART}
        </button>
        <span class="btn add-collection-none">{$smarty.const.ADD_COMBO_TO_CART}</span>
      </div>
    </div>

    <div class="choose-products">
    {if {$products|default:array()|@count} > 0}
      <div class="heading-3">
        {$smarty.const.CHOOSE_FROM_COLLECTION}
      </div>
      <div class="collection-list products-carousel">
        {foreach $products as $prod}
          <div class="item">

            <div class="image">
              <a href="{$prod.link}"><img src="{$prod.image}" alt="{str_replace('"', '″', $prod.products_name)}" title="{str_replace('"', '″', $prod.products_name)}"></a>
            </div>

            <div class="stock">
              <span class="{$prod.stock_indicator.text_stock_code}"><span class="{$prod.stock_indicator.stock_code}-icon">&nbsp;</span>{$prod.stock_indicator.stock_indicator_text}</span>
            </div>

            <div class="price">
              {if isset($prod.price)}
                <span class="current">{$prod.price}</span>
              {else}
                <span class="old">{$prod.price_old}</span>
                <span class="specials">{$prod.price_special}</span>
              {/if}
            </div>

            <div class="title">
              <a href="{$prod.link}">{$prod.products_name}</a>
            </div>

            <div class="button">
              <span class="btn add-to-collection" data-id="{$prod.products_id}" onclick="add_to_collection('{$prod.products_id}');">{$smarty.const.ADD_TO_COMBO}</span>
            </div>
          </div>

        {/foreach}
      </div>
    {/if}
    </div>
</div>
