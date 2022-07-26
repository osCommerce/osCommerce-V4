<div class="cart-listing w-cart-listing{\frontend\design\Info::addBlockToWidgetsList('cart-listing')} type-2">
    <div class="multi-cart{if $smarty.const.GROUPS_IS_SHOW_PRICE === false} no-price{/if}">
  <div class="headings">
    <div class="head image">{$smarty.const.PRODUCTS}</div>
    <div class="head name">&nbsp;</div>
    <div class="head qty">{$smarty.const.QTY}</div>
    <div class="head price">{if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}{$smarty.const.PRICE}{/if}</div>
  </div>

  {foreach $products as $product}
    <div class="item{if strlen($product.parent) > 0} subitem{/if}">
      <div class="image">
          {if $product._status}
              <a href="{$product.link}"><img src="{$product.image}" alt="{$product.name}"></a>
          {else}
              <span><img src="{$product.image}" alt="{$product.name}"></span>
          {/if}
      </div>
      <div class="name">
        <table class="wrapper"><tr><td>
            {if $product._status}
                <a href="{$product.link}">{$product.name}</a>
            {else}
                <span>{$product.name}</span>
            {/if}
        </td></tr></table> {if $product.stock_info.order_instock_bound && $smarty.const.TEXT_INSTOCK_BOUND_MARKER}<span class="attention_mark">{$smarty.const.TEXT_INSTOCK_BOUND_MARKER}</span>{/if}
        <div class="attributes">
            {use class="\frontend\design\boxes\product\Packs"}
            {Packs::widget(['product' => $product])}
          {if isset($product.attr)}
            {foreach $product.attr as $attr}
            <div class="">
              <strong>{$attr.products_options_name}:</strong>
              <span>{$attr.products_options_values_name}</span>
            </div>
            {/foreach}
          {/if}
          {if isset($product.gift_wrapped) && $product.gift_wrapped}
            <div class="">
              <strong>{$smarty.const.GIFT_WRAP_OPTION}:</strong>
              <span>{$smarty.const.GIFT_WRAP_VALUE_YES}</span>
            </div>
          {/if}
        </div>
        {if isset($product.is_bundle) && $product.is_bundle}
          {foreach $product.bundles_info as $bundle_product }
            <div class="bundle_product">
              <table class="wrapper"><tr><td>{$bundle_product.x_name}</td></tr></table>
              {if $bundle_product.with_attr}
                <div class="attributes">
                  {foreach $bundle_product.attr as $attr}
                    <div class="">
                      <strong>{$attr.products_options_name}:</strong>
                      <span>{$attr.products_options_values_name}</span>
                    </div>
                  {/foreach}
                </div>
              {/if}
            </div>
          {/foreach}
        {/if}
        {if isset($product.stock_info) && $product.stock_info}
          <div class="{$product.stock_info.text_stock_code}"><span class="{$product.stock_info.stock_code}-icon">&nbsp;</span>{$product.stock_info.stock_indicator_text}</div>
        {/if}
      </div>
        <div class="qty">
          {$product.quantity_virtual}
        </div>
        <div class="price">
            {if $smarty.const.GROUPS_IS_SHOW_PRICE !== false}
            {$product.final_price}{if $product.standard_price !== false}<br/><small><i>(<strike>{$product.standard_price}</strike>)</i></small>{/if}
            {if isset($product.promo_message) && !empty($product.promo_message)}
                <br><small class="promo-message">{$product.promo_message}</small>
            {/if}
            {if !is_null($bonus_points)}
                {assign var="bonus" value=$bonus_points['bonuses']}
            {/if}
            {/if}
        </div>

        {if $product.bonus_points_price && $product.bonus_points_price > 0 && $product.bonus_points_cost && $product.bonus_points_cost > 0}
            <div class="points">
                {if $product.bonus_coefficient === false && $product.bonus_points_price && $product.bonus_points_price > 0}
                    <div class="points-redeem">
                        <b>{number_format($product.bonus_points_price * $product.quantity_virtual, 0)}</b>
                        {$smarty.const.TEXT_POINTS_REDEEM}
                    </div>
                {/if}
                {if $product.bonus_points_cost && $product.bonus_points_cost > 0}
                    <div class="points-earn">
                        <b>{number_format(floor($product.bonus_points_cost) * $product.quantity_virtual, 0)}</b>
                        {$smarty.const.TEXT_POINTS_EARN}
                        {if $product.bonus_coefficient !== false}
                            ({\common\helpers\Points::getBonusPointsPriceInCurrencyFormatted(floor($product.bonus_points_cost) * $product.quantity_virtual, $groupId)})
                        {/if}
                    </div>
                {/if}
            </div>
        {/if}
    </div>
  {/foreach}
    </div>
</div>
